<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/CarModel.php';
require_once __DIR__ . '/PartModel.php';
require_once __DIR__ . '/ServiceModel.php';

class RepairModel extends BaseModel {
    private CarModel $carModel;
    private PartModel $partModel;
    private ServiceModel $serviceModel;

    public function __construct() {
        parent::__construct();
        $this->carModel = new CarModel();
        $this->partModel = new PartModel();
        $this->serviceModel = new ServiceModel();
    }

    public function getAllRepairs(?string $status = null, ?string $keyword = null): array {
        $sql = "SELECT psc.*, 
                       x.bien_so, x.hang_xe, x.dong_xe, x.tien_no,
                       kh.ma_kh, nd_kh.ho_ten AS ten_chu_xe, nd_kh.sdt AS sdt_chu_xe,
                       nd_nv.ho_ten AS ten_nv
                FROM phieu_sua_chua psc
                JOIN xe x ON psc.ma_xe = x.ma_xe
                JOIN khach_hang kh ON x.ma_kh = kh.ma_kh
                JOIN nguoi_dung nd_kh ON kh.ma_nd = nd_kh.ma_nd
                LEFT JOIN nhan_vien nv ON psc.ma_nv = nv.ma_nv
                LEFT JOIN nguoi_dung nd_nv ON nv.ma_nd = nd_nv.ma_nd
                WHERE 1=1";
        $params = [];

        if (!empty($status)) {
            $sql .= " AND psc.trang_thai = ?";
            $params[] = $status;
        }

        if (!empty($keyword)) {
            $sql .= " AND (x.bien_so LIKE ? OR nd_kh.ho_ten LIKE ? OR psc.ma_psc = ?)";
            $params[] = "%{$keyword}%";
            $params[] = "%{$keyword}%";
            $params[] = is_numeric($keyword) ? (int)$keyword : 0;
        }

        $sql .= " ORDER BY psc.ma_psc DESC";
        return $this->fetchAll($sql, $params);
    }

    public function getRepairById(int $maPsc): ?array {
        $sql = "SELECT psc.*, 
                       x.bien_so, x.hang_xe, x.dong_xe, x.tien_no, x.nam_san_xuat,
                       kh.ma_kh, nd_kh.ho_ten AS ten_chu_xe, nd_kh.sdt AS sdt_chu_xe, nd_kh.email AS email_chu_xe, kh.dia_chi,
                       nd_nv.ho_ten AS ten_nv, nv.chuc_vu
                FROM phieu_sua_chua psc
                JOIN xe x ON psc.ma_xe = x.ma_xe
                JOIN khach_hang kh ON x.ma_kh = kh.ma_kh
                JOIN nguoi_dung nd_kh ON kh.ma_nd = nd_kh.ma_nd
                LEFT JOIN nhan_vien nv ON psc.ma_nv = nv.ma_nv
                LEFT JOIN nguoi_dung nd_nv ON nv.ma_nd = nd_nv.ma_nd
                WHERE psc.ma_psc = ?";
        return $this->fetchOne($sql, [$maPsc]);
    }

    public function getRepairsByCustomerId(int $maKh): array {
        $sql = "SELECT psc.*, x.bien_so, x.hang_xe 
                FROM phieu_sua_chua psc 
                JOIN xe x ON psc.ma_xe = x.ma_xe 
                WHERE x.ma_kh = ? ORDER BY psc.ma_psc DESC";
        return $this->fetchAll($sql, [$maKh]);
    }

    public function getServicesByRepairId(int $maPsc): array {
        $sql = "SELECT ctdv.*, dv.ten_dv, dv.mo_ta
                FROM chi_tiet_dich_vu ctdv
                JOIN dich_vu dv ON ctdv.ma_dv = dv.ma_dv
                WHERE ctdv.ma_psc = ?
                ORDER BY ctdv.ma_ctdv ASC";
        return $this->fetchAll($sql, [$maPsc]);
    }

    public function getPartsByRepairId(int $maPsc): array {
        $sql = "SELECT ctpt.*, pt.ten_pt, pt.don_vi_tinh, pt.so_luong_ton
                FROM chi_tiet_phu_tung ctpt
                JOIN phu_tung pt ON ctpt.ma_pt = pt.ma_pt
                WHERE ctpt.ma_psc = ?
                ORDER BY ctpt.ma_ctpt ASC";
        return $this->fetchAll($sql, [$maPsc]);
    }

    public function createRepair(int $maXe, int $maNv, ?int $maLh = null): int {
        $sql = "INSERT INTO phieu_sua_chua (ma_xe, ma_nv, ma_lh, ngay_lap, trang_thai, tong_tien) 
                VALUES (?, ?, ?, NOW(), 'TiepNhan', 0.00)";
        $this->execute($sql, [$maXe, $maNv, $maLh]);
        return (int)$this->lastInsertId();
    }

    public function addServiceItem(int $maPsc, $serviceInput, int $soLuong = 1, ?float $donGia = null): array {
        $psc = $this->getRepairById($maPsc);
        if (!$psc) {
            return ['success' => false, 'message' => 'Phiếu sửa chữa không tồn tại'];
        }

        $dv = null;
        if (is_numeric($serviceInput) && (int)$serviceInput > 0) {
            $dv = $this->serviceModel->getServiceById((int)$serviceInput);
        }

        if (!$dv && is_string($serviceInput)) {
            $serviceName = trim($serviceInput);
            if ($serviceName === '') {
                return ['success' => false, 'message' => 'Vui lòng chọn hoặc nhập tên dịch vụ'];
            }
            // Tìm theo tên dịch vụ trong CSDL
            $existing = $this->fetchOne("SELECT * FROM dich_vu WHERE LOWER(TRIM(ten_dv)) = LOWER(?) LIMIT 1", [$serviceName]);
            if ($existing) {
                $dv = $existing;
            } else {
                // Tự động tạo dịch vụ mới vào danh mục khi nhập tay
                $standardPrice = ($donGia !== null && $donGia >= 0) ? $donGia : 0;
                $this->execute(
                    "INSERT INTO dich_vu (ten_dv, mo_ta, gia_tieu_chuan, trang_thai) VALUES (?, ?, ?, 'KhaDung')",
                    [$serviceName, 'Dịch vụ tùy chỉnh được thêm từ phiếu sửa chữa #' . $maPsc, $standardPrice]
                );
                $newMaDv = (int)$this->lastInsertId();
                $dv = $this->serviceModel->getServiceById($newMaDv);
            }
        }

        if (!$dv) {
            return ['success' => false, 'message' => 'Không thể xác định hoặc tạo dịch vụ'];
        }

        $maDv = (int)$dv['ma_dv'];
        $donGia = ($donGia !== null && $donGia >= 0) ? $donGia : (float)$dv['gia_tieu_chuan'];
        $thanhTien = $soLuong * $donGia;

        $this->beginTransaction();
        try {
            $sql = "INSERT INTO chi_tiet_dich_vu (ma_psc, ma_dv, so_luong, don_gia, thanh_tien) 
                    VALUES (?, ?, ?, ?, ?)";
            $this->execute($sql, [$maPsc, $maDv, $soLuong, $donGia, $thanhTien]);

            // Cập nhật tổng tiền phiếu sửa chữa & nợ của xe
            $this->execute("UPDATE phieu_sua_chua SET tong_tien = tong_tien + ? WHERE ma_psc = ?", [$thanhTien, $maPsc]);
            $this->execute("UPDATE xe SET tien_no = tien_no + ? WHERE ma_xe = ?", [$thanhTien, $psc['ma_xe']]);

            $this->commit();
            return ['success' => true, 'message' => 'Đã thêm dịch vụ: ' . $dv['ten_dv']];
        } catch (Exception $e) {
            $this->rollBack();
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    public function addPartItem(int $maPsc, $partInput, int $soLuong = 1, ?float $donGia = null): array {
        $psc = $this->getRepairById($maPsc);
        if (!$psc) {
            return ['success' => false, 'message' => 'Phiếu sửa chữa không tồn tại'];
        }

        $pt = null;
        if (is_numeric($partInput) && (int)$partInput > 0) {
            $pt = $this->partModel->getPartById((int)$partInput);
        }

        if (!$pt && is_string($partInput)) {
            $partName = trim($partInput);
            if ($partName === '') {
                return ['success' => false, 'message' => 'Vui lòng chọn hoặc nhập tên phụ tùng'];
            }
            $existing = $this->fetchOne("SELECT * FROM phu_tung WHERE LOWER(TRIM(ten_pt)) = LOWER(?) LIMIT 1", [$partName]);
            if ($existing) {
                $pt = $existing;
            }
        }

        if (!$pt) {
            return ['success' => false, 'message' => 'Không tìm thấy phụ tùng này trong kho hoặc danh mục!'];
        }

        $maPt = (int)$pt['ma_pt'];
        if ($pt['so_luong_ton'] < $soLuong) {
            return [
                'success' => false, 
                'message' => "Số lượng tồn kho không đủ! Hiện còn {$pt['so_luong_ton']} {$pt['don_vi_tinh']}."
            ];
        }

        $donGia = ($donGia !== null && $donGia >= 0) ? $donGia : (float)$pt['gia_ban'];
        $thanhTien = $soLuong * $donGia;

        $this->beginTransaction();
        try {
            $sql = "INSERT INTO chi_tiet_phu_tung (ma_psc, ma_pt, so_luong, don_gia, thanh_tien) 
                    VALUES (?, ?, ?, ?, ?)";
            $this->execute($sql, [$maPsc, $maPt, $soLuong, $donGia, $thanhTien]);

            // Giảm tồn kho phụ tùng
            $this->execute("UPDATE phu_tung SET so_luong_ton = so_luong_ton - ? WHERE ma_pt = ?", [$soLuong, $maPt]);

            // Cập nhật tổng tiền phiếu sửa chữa & nợ của xe
            $this->execute("UPDATE phieu_sua_chua SET tong_tien = tong_tien + ? WHERE ma_psc = ?", [$thanhTien, $maPsc]);
            $this->execute("UPDATE xe SET tien_no = tien_no + ? WHERE ma_xe = ?", [$thanhTien, $psc['ma_xe']]);

            $this->commit();
            return ['success' => true, 'message' => 'Đã xuất phụ tùng: ' . $pt['ten_pt']];
        } catch (Exception $e) {
            $this->rollBack();
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    public function deleteServiceItem(int $maCtdv): array {
        $item = $this->fetchOne(
            "SELECT ctdv.*, psc.ma_xe FROM chi_tiet_dich_vu ctdv 
             JOIN phieu_sua_chua psc ON ctdv.ma_psc = psc.ma_psc 
             WHERE ctdv.ma_ctdv = ?",
            [$maCtdv]
        );
        if (!$item) {
            return ['success' => false, 'message' => 'Chi tiết dịch vụ không tồn tại'];
        }

        $this->beginTransaction();
        try {
            $thanhTien = (float)$item['thanh_tien'];
            $maPsc = (int)$item['ma_psc'];
            $maXe = (int)$item['ma_xe'];

            $this->execute("DELETE FROM chi_tiet_dich_vu WHERE ma_ctdv = ?", [$maCtdv]);
            $this->execute("UPDATE phieu_sua_chua SET tong_tien = GREATEST(0, tong_tien - ?) WHERE ma_psc = ?", [$thanhTien, $maPsc]);
            $this->execute("UPDATE xe SET tien_no = GREATEST(0, tien_no - ?) WHERE ma_xe = ?", [$thanhTien, $maXe]);

            $this->commit();
            return ['success' => true, 'ma_psc' => $maPsc, 'message' => 'Đã xóa dịch vụ khỏi phiếu!'];
        } catch (Exception $e) {
            $this->rollBack();
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    public function deletePartItem(int $maCtpt): array {
        $item = $this->fetchOne(
            "SELECT ctpt.*, psc.ma_xe FROM chi_tiet_phu_tung ctpt 
             JOIN phieu_sua_chua psc ON ctpt.ma_psc = psc.ma_psc 
             WHERE ctpt.ma_ctpt = ?",
            [$maCtpt]
        );
        if (!$item) {
            return ['success' => false, 'message' => 'Chi tiết phụ tùng không tồn tại'];
        }

        $this->beginTransaction();
        try {
            $thanhTien = (float)$item['thanh_tien'];
            $soLuong = (int)$item['so_luong'];
            $maPt = (int)$item['ma_pt'];
            $maPsc = (int)$item['ma_psc'];
            $maXe = (int)$item['ma_xe'];

            $this->execute("DELETE FROM chi_tiet_phu_tung WHERE ma_ctpt = ?", [$maCtpt]);
            // Hoàn lại kho phụ tùng
            $this->execute("UPDATE phu_tung SET so_luong_ton = so_luong_ton + ? WHERE ma_pt = ?", [$soLuong, $maPt]);
            $this->execute("UPDATE phieu_sua_chua SET tong_tien = GREATEST(0, tong_tien - ?) WHERE ma_psc = ?", [$thanhTien, $maPsc]);
            $this->execute("UPDATE xe SET tien_no = GREATEST(0, tien_no - ?) WHERE ma_xe = ?", [$thanhTien, $maXe]);

            $this->commit();
            return ['success' => true, 'ma_psc' => $maPsc, 'message' => 'Đã xóa phụ tùng và hoàn lại tồn kho!'];
        } catch (Exception $e) {
            $this->rollBack();
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    public function updateStatus(int $maPsc, string $status): bool {
        $extra = "";
        $params = [$status, $maPsc];
        if ($status === 'HoanThanh' || $status === 'DaThanhToan') {
            $extra = ", ngay_hoan_thanh = NOW()";
        }
        return $this->execute("UPDATE phieu_sua_chua SET trang_thai = ? {$extra} WHERE ma_psc = ?", $params);
    }
}

