<?php
require_once __DIR__ . '/BaseModel.php';

class AppointmentModel extends BaseModel {

    public function getAllAppointments(?string $status = null): array {
        $sql = "SELECT lh.*, 
                       x.bien_so, x.hang_xe, x.dong_xe,
                       kh.ma_kh, nd.ho_ten AS ten_chu_xe, nd.sdt
                FROM lich_hen lh
                JOIN xe x ON lh.ma_xe = x.ma_xe
                JOIN khach_hang kh ON lh.ma_kh = kh.ma_kh
                JOIN nguoi_dung nd ON kh.ma_nd = nd.ma_nd
                WHERE 1=1";
        $params = [];
        if (!empty($status)) {
            $sql .= " AND lh.trang_thai = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY lh.ngay_hen DESC";
        return $this->fetchAll($sql, $params);
    }

    public function getAppointmentById(int $id): ?array {
        $sql = "SELECT lh.*, 
                       x.bien_so, x.hang_xe, x.dong_xe,
                       kh.ma_kh, nd.ho_ten AS ten_chu_xe, nd.sdt, kh.dia_chi
                FROM lich_hen lh
                JOIN xe x ON lh.ma_xe = x.ma_xe
                JOIN khach_hang kh ON lh.ma_kh = kh.ma_kh
                JOIN nguoi_dung nd ON kh.ma_nd = nd.ma_nd
                WHERE lh.ma_lh = ?";
        return $this->fetchOne($sql, [$id]);
    }

    public function getAppointmentsByCustomerId(int $maKh): array {
        $sql = "SELECT lh.*, x.bien_so, x.hang_xe, x.dong_xe,
                       nd.ho_ten AS ten_chu_xe, nd.sdt
                FROM lich_hen lh 
                JOIN xe x ON lh.ma_xe = x.ma_xe 
                JOIN khach_hang kh ON lh.ma_kh = kh.ma_kh
                JOIN nguoi_dung nd ON kh.ma_nd = nd.ma_nd
                WHERE lh.ma_kh = ? ORDER BY lh.ngay_hen DESC";
        return $this->fetchAll($sql, [$maKh]);
    }

    public function createAppointment(int $maKh, int $maXe, string $ngayHen, ?string $ghiChu = null): int {
        $sql = "INSERT INTO lich_hen (ma_kh, ma_xe, ngay_hen, trang_thai, ghi_chu, ngay_tao) 
                VALUES (?, ?, ?, 'ChoXacNhan', ?, NOW())";
        $this->execute($sql, [$maKh, $maXe, $ngayHen, $ghiChu]);
        return (int)$this->lastInsertId();
    }

    public function createGuestAppointment(
        string $tenKhach,
        string $sdt,
        string $email,
        string $bienSo,
        string $hangXe,
        string $ngayHen,
        ?string $ghiChu = null
    ): array {
        $bienSo = strtoupper(trim($bienSo));
        $email = strtolower(trim($email));
        $this->beginTransaction();
        try {
            // 1. Tìm hoặc tạo khách hàng theo SĐT hoặc Email
            $khach = null;
            if (!empty($sdt)) {
                $khach = $this->fetchOne(
                    "SELECT kh.ma_kh, kh.ma_nd, nd.email FROM khach_hang kh 
                     JOIN nguoi_dung nd ON kh.ma_nd = nd.ma_nd 
                     WHERE nd.sdt = ? LIMIT 1",
                    [$sdt]
                );
            }
            if (!$khach && !empty($email)) {
                $khach = $this->fetchOne(
                    "SELECT kh.ma_kh, kh.ma_nd, nd.email FROM khach_hang kh 
                     JOIN nguoi_dung nd ON kh.ma_nd = nd.ma_nd 
                     WHERE nd.email = ? LIMIT 1",
                    [$email]
                );
            }

            if ($khach) {
                $maKh = (int)$khach['ma_kh'];
                $maNd = (int)$khach['ma_nd'];
                // Cập nhật thông tin email mới để bảo đảm gửi Gmail thành công
                if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->execute("UPDATE nguoi_dung SET email = ?, ho_ten = ? WHERE ma_nd = ?", [$email, $tenKhach, $maNd]);
                } else {
                    $this->execute("UPDATE nguoi_dung SET ho_ten = ? WHERE ma_nd = ?", [$tenKhach, $maNd]);
                }
            } else {
                $finalEmail = (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL))
                    ? $email
                    : ('guest_' . preg_replace('/[^0-9]/', '', $sdt) . '_' . time() . '@gara.vn');

                $existNd = $this->fetchOne("SELECT ma_nd FROM nguoi_dung WHERE email = ? LIMIT 1", [$finalEmail]);
                if ($existNd) {
                    $maNd = (int)$existNd['ma_nd'];
                    $existKh = $this->fetchOne("SELECT ma_kh FROM khach_hang WHERE ma_nd = ? LIMIT 1", [$maNd]);
                    if ($existKh) {
                        $maKh = (int)$existKh['ma_kh'];
                    } else {
                        $this->execute("INSERT INTO khach_hang (ma_nd, dia_chi, ngay_dang_ky) VALUES (?, '', CURDATE())", [$maNd]);
                        $maKh = (int)$this->lastInsertId();
                    }
                } else {
                    $defaultPass = password_hash('123456', PASSWORD_BCRYPT);
                    $this->execute(
                        "INSERT INTO nguoi_dung (ho_ten, email, mat_khau, sdt, vai_tro, trang_thai) 
                         VALUES (?, ?, ?, ?, 'KhachHang', 'HoatDong')",
                        [$tenKhach, $finalEmail, $defaultPass, $sdt]
                    );
                    $maNd = (int)$this->lastInsertId();

                    $this->execute(
                        "INSERT INTO khach_hang (ma_nd, dia_chi, ngay_dang_ky) VALUES (?, '', CURDATE())",
                        [$maNd]
                    );
                    $maKh = (int)$this->lastInsertId();
                }
            }

            // 2. Tìm hoặc tạo xe
            $xe = $this->fetchOne("SELECT ma_xe FROM xe WHERE bien_so = ? LIMIT 1", [$bienSo]);
            if ($xe) {
                $maXe = (int)$xe['ma_xe'];
                // Đồng bộ chủ sở hữu xe và hiệu xe sang khách hàng hiện tại
                $this->execute("UPDATE xe SET ma_kh = ?, hang_xe = ? WHERE ma_xe = ?", [$maKh, $hangXe, $maXe]);
            } else {
                $this->execute(
                    "INSERT INTO xe (ma_kh, bien_so, hang_xe, dong_xe, tien_no) VALUES (?, ?, ?, '', 0.00)",
                    [$maKh, $bienSo, $hangXe]
                );
                $maXe = (int)$this->lastInsertId();
            }

            // 3. Tạo lịch hẹn
            $maLh = $this->createAppointment($maKh, $maXe, $ngayHen, $ghiChu);
            $this->commit();

            return [
                'success' => true,
                'ma_lh' => $maLh,
                'message' => "Quý khách đã đặt lịch hẹn thành công (Mã lịch: #{$maLh})."
            ];
        } catch (Exception $e) {
            $this->rollBack();
            return [
                'success' => false,
                'message' => "Lỗi đặt lịch hẹn: " . $e->getMessage()
            ];
        }
    }

    public function updateStatus(int $maLh, string $status): bool {
        return $this->execute("UPDATE lich_hen SET trang_thai = ? WHERE ma_lh = ?", [$status, $maLh]);
    }

    /**
     * KTV duyệt lịch hẹn bằng 1 cú click:
     * - Cập nhật trạng thái lịch hẹn -> 'DaXacNhan'
     * - Tự động tạo Phiếu sửa chữa cho xe đó (trạng thái: DangSuaChua)
     * - Tự động gán KTV phụ trách
     * - Trả về mã phiếu sửa chữa để chuyển thẳng vào màn hình làm việc
     */
    public function approveAndCreateRepair(int $maLh, int $maNv): array {
        $lh = $this->getAppointmentById($maLh);
        if (!$lh) {
            return ['success' => false, 'message' => 'Không tìm thấy lịch hẹn!'];
        }

        $this->beginTransaction();
        try {
            // 1. Cập nhật trạng thái lịch hẹn
            $this->execute("UPDATE lich_hen SET trang_thai = 'DaXacNhan' WHERE ma_lh = ?", [$maLh]);

            // 2. Kiểm tra xem đã có phiếu sửa chữa nào liên kết với lịch hẹn này chưa
            $existingPsc = $this->fetchOne("SELECT ma_psc FROM phieu_sua_chua WHERE ma_lh = ? LIMIT 1", [$maLh]);
            
            if ($existingPsc) {
                $maPsc = (int)$existingPsc['ma_psc'];
            } else {
                // Kiểm tra xem xe này có đang có phiếu sửa chữa nào chưa hoàn thành không
                $activePsc = $this->fetchOne(
                    "SELECT ma_psc FROM phieu_sua_chua WHERE ma_xe = ? AND trang_thai NOT IN ('DaThanhToan', 'HoanThanh') ORDER BY ma_psc DESC LIMIT 1",
                    [$lh['ma_xe']]
                );

                if ($activePsc) {
                    $maPsc = (int)$activePsc['ma_psc'];
                    // Gán ma_lh vào phiếu đang mở
                    $this->execute("UPDATE phieu_sua_chua SET ma_lh = ? WHERE ma_psc = ?", [$maLh, $maPsc]);
                } else {
                    // Tạo mới phiếu sửa chữa ngay lập tức
                    $sqlPsc = "INSERT INTO phieu_sua_chua (ma_xe, ma_nv, ma_lh, ngay_lap, trang_thai, tong_tien) 
                               VALUES (?, ?, ?, NOW(), 'DangSuaChua', 0.00)";
                    $this->execute($sqlPsc, [$lh['ma_xe'], $maNv, $maLh]);
                    $maPsc = (int)$this->lastInsertId();
                }
            }

            $this->commit();

            return [
                'success' => true,
                'ma_psc'  => $maPsc,
                'message' => "⚡ KTV đã duyệt lịch hẹn #{$maLh}! Xe {$lh['bien_so']} ({$lh['hang_xe']}) đã được đưa ngay vào khoang sửa chữa (Phiếu #{$maPsc})."
            ];
        } catch (Exception $e) {
            $this->rollBack();
            return [
                'success' => false,
                'message' => 'Lỗi xử lý duyệt lịch hẹn: ' . $e->getMessage()
            ];
        }
    }

    public function getPendingCount(): int {
        $row = $this->fetchOne("SELECT COUNT(*) AS cnt FROM lich_hen WHERE trang_thai = 'ChoXacNhan'");
        return (int)($row['cnt'] ?? 0);
    }
}
