<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/SettingModel.php';

class CarModel extends BaseModel {
    private SettingModel $settingModel;

    public function __construct() {
        parent::__construct();
        $this->settingModel = new SettingModel();
    }

    public function getDailyReceptionCount(string $date = null): int {
        $date = $date ?: date('Y-m-d');
        $sql = "SELECT COUNT(*) as cnt FROM phieu_sua_chua WHERE DATE(ngay_lap) = ?";
        $res = $this->fetchOne($sql, [$date]);
        return (int)($res['cnt'] ?? 0);
    }

    public function getTodayReceptions(): array {
        $today = date('Y-m-d');
        $sql = "SELECT psc.*, 
                       x.bien_so, x.hang_xe, x.dong_xe, x.tien_no,
                       kh.ma_kh, nd_kh.ho_ten AS ten_chu_xe, nd_kh.sdt, kh.dia_chi,
                       nd_nv.ho_ten AS ten_nv
                FROM phieu_sua_chua psc
                JOIN xe x ON psc.ma_xe = x.ma_xe
                JOIN khach_hang kh ON x.ma_kh = kh.ma_kh
                JOIN nguoi_dung nd_kh ON kh.ma_nd = nd_kh.ma_nd
                LEFT JOIN nhan_vien nv ON psc.ma_nv = nv.ma_nv
                LEFT JOIN nguoi_dung nd_nv ON nv.ma_nd = nd_nv.ma_nd
                WHERE DATE(psc.ngay_lap) = ?
                ORDER BY psc.ma_psc DESC";
        return $this->fetchAll($sql, [$today]);
    }

    public function createReception(
        string $tenChuXe,
        string $sdt,
        string $diaChi,
        string $bienSo,
        string $hangXe,
        string $dongXe,
        ?int $namSx,
        int $maNv,
        ?string $ghiChu = null
    ): array {
        $todayCount = $this->getDailyReceptionCount();
        $limit = (int)$this->settingModel->getRuleValue('So xe sua chua toi da', 30);

        if ($todayCount >= $limit) {
            return [
                'success' => false,
                'message' => "Đã vượt quá số lượng xe tiếp nhận tối đa trong ngày ({$limit} xe) theo quy định hiện hành!"
            ];
        }

        $bienSo = strtoupper(trim($bienSo));

        $this->beginTransaction();
        try {
            // 1. Kiểm tra khách hàng theo SĐT
            $sqlKh = "SELECT kh.ma_kh, nd.ma_nd FROM khach_hang kh
                      JOIN nguoi_dung nd ON kh.ma_nd = nd.ma_nd
                      WHERE nd.sdt = ? LIMIT 1";
            $khach = $this->fetchOne($sqlKh, [$sdt]);

            if ($khach) {
                $maKh = (int)$khach['ma_kh'];
                // Cập nhật tên và địa chỉ nếu thay đổi
                $this->execute("UPDATE nguoi_dung SET ho_ten = ? WHERE ma_nd = ?", [$tenChuXe, $khach['ma_nd']]);
                if (!empty($diaChi)) {
                    $this->execute("UPDATE khach_hang SET dia_chi = ? WHERE ma_kh = ?", [$diaChi, $maKh]);
                }
            } else {
                // Tạo tài khoản người dùng mới cho khách
                $email = 'kh_' . preg_replace('/[^0-9]/', '', $sdt) . '_' . time() . '@gara.vn';
                $defaultPass = password_hash('123456', PASSWORD_BCRYPT);
                $this->execute(
                    "INSERT INTO nguoi_dung (ho_ten, email, mat_khau, sdt, vai_tro, trang_thai) 
                     VALUES (?, ?, ?, ?, 'KhachHang', 'HoatDong')",
                    [$tenChuXe, $email, $defaultPass, $sdt]
                );
                $maNd = (int)$this->lastInsertId();

                $this->execute(
                    "INSERT INTO khach_hang (ma_nd, dia_chi, ngay_dang_ky) VALUES (?, ?, CURDATE())",
                    [$maNd, $diaChi]
                );
                $maKh = (int)$this->lastInsertId();
            }

            // 2. Kiểm tra xe theo biển số
            $xe = $this->fetchOne("SELECT ma_xe FROM xe WHERE bien_so = ? LIMIT 1", [$bienSo]);
            if ($xe) {
                $maXe = (int)$xe['ma_xe'];
                $this->execute(
                    "UPDATE xe SET ma_kh = ?, hang_xe = ?, dong_xe = ?, nam_san_xuat = ? WHERE ma_xe = ?",
                    [$maKh, $hangXe, $dongXe, $namSx, $maXe]
                );
            } else {
                $this->execute(
                    "INSERT INTO xe (ma_kh, bien_so, hang_xe, dong_xe, nam_san_xuat, tien_no) 
                     VALUES (?, ?, ?, ?, ?, 0.00)",
                    [$maKh, $bienSo, $hangXe, $dongXe, $namSx]
                );
                $maXe = (int)$this->lastInsertId();
            }

            // 3. Tạo phiếu sửa chữa ở trạng thái TiepNhan
            $this->execute(
                "INSERT INTO phieu_sua_chua (ma_xe, ma_nv, ngay_lap, trang_thai, tong_tien) 
                 VALUES (?, ?, NOW(), 'TiepNhan', 0.00)",
                [$maXe, $maNv]
            );
            $maPsc = (int)$this->lastInsertId();

            $this->commit();
            return [
                'success' => true,
                'ma_psc' => $maPsc,
                'message' => "Tiếp nhận xe biển số {$bienSo} thành công! Mã phiếu: #{$maPsc}"
            ];
        } catch (Exception $e) {
            $this->rollBack();
            return [
                'success' => false,
                'message' => "Lỗi tiếp nhận: " . $e->getMessage()
            ];
        }
    }

    public function searchCars(string $bienSo = '', string $hangXe = '', string $tenKhach = ''): array {
        $sql = "SELECT x.*, 
                       kh.ma_kh, nd.ho_ten AS ten_chu_xe, nd.sdt, kh.dia_chi,
                       (SELECT COUNT(*) FROM phieu_sua_chua psc WHERE psc.ma_xe = x.ma_xe) AS so_lan_sua
                FROM xe x
                JOIN khach_hang kh ON x.ma_kh = kh.ma_kh
                JOIN nguoi_dung nd ON kh.ma_nd = nd.ma_nd
                WHERE 1=1";
        $params = [];

        if (!empty($bienSo)) {
            $sql .= " AND x.bien_so LIKE ?";
            $params[] = "%" . trim($bienSo) . "%";
        }
        if (!empty($hangXe)) {
            $sql .= " AND x.hang_xe LIKE ?";
            $params[] = "%" . trim($hangXe) . "%";
        }
        if (!empty($tenKhach)) {
            $sql .= " AND nd.ho_ten LIKE ?";
            $params[] = "%" . trim($tenKhach) . "%";
        }

        $sql .= " ORDER BY x.ma_xe DESC";
        return $this->fetchAll($sql, $params);
    }

    public function getCarById(int $maXe): ?array {
        $sql = "SELECT x.*, kh.ma_kh, nd.ho_ten AS ten_chu_xe, nd.sdt, nd.email, kh.dia_chi
                FROM xe x
                JOIN khach_hang kh ON x.ma_kh = kh.ma_kh
                JOIN nguoi_dung nd ON kh.ma_nd = nd.ma_nd
                WHERE x.ma_xe = ?";
        return $this->fetchOne($sql, [$maXe]);
    }

    public function getCarByPlate(string $bienSo): ?array {
        $sql = "SELECT x.*, kh.ma_kh, nd.ho_ten AS ten_chu_xe, nd.sdt, nd.email, kh.dia_chi
                FROM xe x
                JOIN khach_hang kh ON x.ma_kh = kh.ma_kh
                JOIN nguoi_dung nd ON kh.ma_nd = nd.ma_nd
                WHERE x.bien_so = ?";
        return $this->fetchOne($sql, [strtoupper(trim($bienSo))]);
    }

    public function getAllCars(): array {
        $sql = "SELECT x.*, kh.ma_kh, nd.ho_ten AS ten_chu_xe, nd.sdt, kh.dia_chi
                FROM xe x
                JOIN khach_hang kh ON x.ma_kh = kh.ma_kh
                JOIN nguoi_dung nd ON kh.ma_nd = nd.ma_nd
                ORDER BY x.bien_so ASC";
        return $this->fetchAll($sql);
    }

    public function getAllBrands(): array {
        $sql = "SELECT DISTINCT hang_xe FROM xe WHERE hang_xe IS NOT NULL AND hang_xe != '' ORDER BY hang_xe ASC";
        $rows = $this->fetchAll($sql);
        $dbBrands = array_column($rows, 'hang_xe');
        
        // Danh mục gợi ý các hãng xe phổ biến tại Việt Nam để chọn nhanh
        $defaults = [
            'Toyota', 'Hyundai', 'Kia', 'Honda', 'Mazda', 
            'Ford', 'Mitsubishi', 'VinFast', 'Mercedes-Benz', 'BMW', 
            'Audi', 'Lexus', 'Porsche', 'Nissan', 'Suzuki', 
            'Subaru', 'Peugeot', 'Land Rover', 'Volvo', 'Volkswagen', 
            'Chevrolet', 'Isuzu', 'Jeep', 'MG', 'Haval', 'BYD', 'Wuling'
        ];
        $all = array_values(array_unique(array_filter(array_merge($defaults, $dbBrands))));
        sort($all);
        return $all;
    }

    public function getCarsByCustomerId(int $maKh): array {
        $sql = "SELECT x.*, 
                       (SELECT COUNT(*) FROM phieu_sua_chua psc WHERE psc.ma_xe = x.ma_xe) as so_lan_sua
                FROM xe x WHERE x.ma_kh = ? ORDER BY x.ma_xe DESC";
        return $this->fetchAll($sql, [$maKh]);
    }

    public function searchCustomerCars(int $maKh, string $bienSo = '', string $hangXe = ''): array {
        $sql = "SELECT x.*, 
                       kh.ma_kh, nd.ho_ten AS ten_chu_xe, nd.sdt, kh.dia_chi,
                       (SELECT COUNT(*) FROM phieu_sua_chua psc WHERE psc.ma_xe = x.ma_xe) AS so_lan_sua
                FROM xe x
                JOIN khach_hang kh ON x.ma_kh = kh.ma_kh
                JOIN nguoi_dung nd ON kh.ma_nd = nd.ma_nd
                WHERE x.ma_kh = ?";
        $params = [$maKh];

        if (!empty($bienSo)) {
            $sql .= " AND x.bien_so LIKE ?";
            $params[] = "%" . trim($bienSo) . "%";
        }
        if (!empty($hangXe)) {
            $sql .= " AND x.hang_xe LIKE ?";
            $params[] = "%" . trim($hangXe) . "%";
        }

        $sql .= " ORDER BY x.ma_xe DESC";
        return $this->fetchAll($sql, $params);
    }

    public function updateDebt(int $maXe, float $deltaAmount): bool {
        return $this->execute("UPDATE xe SET tien_no = tien_no + ? WHERE ma_xe = ?", [$deltaAmount, $maXe]);
    }

    public function getPublicCarProgress(string $bienSo): ?array {
        $car = $this->getCarByPlate($bienSo);
        if (!$car) {
            return null;
        }

        // Lấy phiếu sửa chữa mới nhất của xe
        $sqlPsc = "SELECT psc.*, nd_nv.ho_ten AS ten_nv
                   FROM phieu_sua_chua psc
                   LEFT JOIN nhan_vien nv ON psc.ma_nv = nv.ma_nv
                   LEFT JOIN nguoi_dung nd_nv ON nv.ma_nd = nd_nv.ma_nd
                   WHERE psc.ma_xe = ?
                   ORDER BY psc.ma_psc DESC LIMIT 1";
        $latestRepair = $this->fetchOne($sqlPsc, [$car['ma_xe']]);

        $services = [];
        $parts = [];
        if ($latestRepair) {
            $services = $this->fetchAll(
                "SELECT ctdv.*, dv.ten_dv FROM chi_tiet_dich_vu ctdv 
                 JOIN dich_vu dv ON ctdv.ma_dv = dv.ma_dv 
                 WHERE ctdv.ma_psc = ?",
                [$latestRepair['ma_psc']]
            );
            $parts = $this->fetchAll(
                "SELECT ctpt.*, pt.ten_pt, pt.don_vi_tinh FROM chi_tiet_phu_tung ctpt 
                 JOIN phu_tung pt ON ctpt.ma_pt = pt.ma_pt 
                 WHERE ctpt.ma_psc = ?",
                [$latestRepair['ma_psc']]
            );
        }

        return [
            'car'          => $car,
            'latestRepair' => $latestRepair,
            'services'     => $services,
            'parts'        => $parts
        ];
    }
}

