<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/SettingModel.php';

class PartModel extends BaseModel {
    private SettingModel $settingModel;

    public function __construct() {
        parent::__construct();
        $this->settingModel = new SettingModel();
    }

    public function getAllParts(?int $maKho = null): array {
        $sql = "SELECT pt.*, ncc.ten_ncc, k.ten_kho 
                FROM phu_tung pt
                JOIN nha_cung_cap ncc ON pt.ma_ncc = ncc.ma_ncc
                LEFT JOIN kho k ON pt.ma_kho = k.ma_kho
                WHERE 1=1";
        $params = [];
        if ($maKho !== null && $maKho > 0) {
            $sql .= " AND pt.ma_kho = ?";
            $params[] = $maKho;
        }
        $sql .= " ORDER BY pt.ten_pt ASC";
        return $this->fetchAll($sql, $params);
    }

    public function getLowStockParts(?int $maKho = null): array {
        $sql = "SELECT pt.*, ncc.ten_ncc, k.ten_kho 
                FROM phu_tung pt
                JOIN nha_cung_cap ncc ON pt.ma_ncc = ncc.ma_ncc
                LEFT JOIN kho k ON pt.ma_kho = k.ma_kho
                WHERE pt.so_luong_ton <= pt.muc_canh_bao";
        $params = [];
        if ($maKho !== null && $maKho > 0) {
            $sql .= " AND pt.ma_kho = ?";
            $params[] = $maKho;
        }
        $sql .= " ORDER BY pt.so_luong_ton ASC";
        return $this->fetchAll($sql, $params);
    }

    public function getPartById(int $id): ?array {
        $sql = "SELECT pt.*, ncc.ten_ncc, k.ten_kho 
                FROM phu_tung pt
                JOIN nha_cung_cap ncc ON pt.ma_ncc = ncc.ma_ncc
                LEFT JOIN kho k ON pt.ma_kho = k.ma_kho
                WHERE pt.ma_pt = ?";
        return $this->fetchOne($sql, [$id]);
    }

    public function getAllSuppliers(): array {
        return $this->fetchAll("SELECT * FROM nha_cung_cap ORDER BY ten_ncc ASC");
    }

    public function createPart(
        string $name,
        int $maNcc,
        string $unit,
        float $importPrice,
        ?float $sellPrice = null,
        int $initStock = 0,
        int $warningLevel = 5,
        int $maKho = 1
    ): array {
        $count = (int)($this->fetchOne("SELECT COUNT(*) as cnt FROM phu_tung")['cnt'] ?? 0);
        $maxLimit = (int)$this->settingModel->getRuleValue('So loai vat tu', 100);

        if ($count >= $maxLimit) {
            return [
                'success' => false,
                'message' => "Số loại vật tư phụ tùng trong kho đã đạt giới hạn tối đa ({$maxLimit}) theo quy định!"
            ];
        }

        // Tỉ lệ giá bán theo quy định nếu chưa nhập giá bán
        if ($sellPrice === null || $sellPrice <= 0) {
            $ratio = $this->settingModel->getRuleValue('Ti le don gia vtpt', 105) / 100;
            $sellPrice = $importPrice * $ratio;
        }

        try {
            $sql = "INSERT INTO phu_tung (ten_pt, ma_ncc, don_vi_tinh, gia_nhap, gia_ban, so_luong_ton, muc_canh_bao, ma_kho) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $this->execute($sql, [$name, $maNcc, $unit, $importPrice, $sellPrice, $initStock, $warningLevel, $maKho]);
            return [
                'success' => true,
                'id' => (int)$this->lastInsertId(),
                'message' => "Thêm phụ tùng '{$name}' vào kho thành công!"
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Lỗi: " . $e->getMessage()
            ];
        }
    }

    public function updatePart(
        int $id,
        string $name,
        int $maNcc,
        string $unit,
        float $importPrice,
        float $sellPrice,
        int $warningLevel,
        int $maKho = 1
    ): bool {
        $sql = "UPDATE phu_tung 
                SET ten_pt = ?, ma_ncc = ?, don_vi_tinh = ?, gia_nhap = ?, gia_ban = ?, muc_canh_bao = ?, ma_kho = ? 
                WHERE ma_pt = ?";
        return $this->execute($sql, [$name, $maNcc, $unit, $importPrice, $sellPrice, $warningLevel, $maKho, $id]);
    }

    public function deletePart(int $id): bool {
        $used = $this->fetchOne("SELECT COUNT(*) as cnt FROM chi_tiet_phu_tung WHERE ma_pt = ?", [$id]);
        if (!empty($used['cnt'])) {
            return false;
        }
        return $this->execute("DELETE FROM phu_tung WHERE ma_pt = ?", [$id]);
    }

    public function importStock($partInput, int $quantity, float $importPrice, int $maNv, ?string $note = null, string $unit = 'Cái', int $supplierId = 1, int $warehouseId = 1): array {
        if ($quantity <= 0) {
            return ['success' => false, 'message' => 'Số lượng nhập phải lớn hơn 0'];
        }

        $part = null;
        if (is_numeric($partInput) && (int)$partInput > 0) {
            $part = $this->getPartById((int)$partInput);
        }

        if (!$part && is_string($partInput)) {
            $partName = trim($partInput);
            if ($partName === '') {
                return ['success' => false, 'message' => 'Vui lòng chọn hoặc gõ tên phụ tùng cần nhập kho'];
            }
            // Tìm theo tên trong CSDL
            $existing = $this->fetchOne("SELECT * FROM phu_tung WHERE LOWER(TRIM(ten_pt)) = LOWER(?) LIMIT 1", [$partName]);
            if ($existing) {
                $part = $existing;
            } else {
                // Tự động tạo phụ tùng mới trực tiếp khi nhập hàng mới về xưởng
                $ratio = $this->settingModel->getRuleValue('Ti le don gia vtpt', 105) / 100;
                $initialSellPrice = $importPrice * $ratio;
                $unit = !empty($unit) ? trim($unit) : 'Cái';
                $supplierId = $supplierId > 0 ? $supplierId : 1;
                $warehouseId = $warehouseId > 0 ? $warehouseId : 1;

                $this->execute(
                    "INSERT INTO phu_tung (ten_pt, ma_ncc, don_vi_tinh, gia_nhap, gia_ban, so_luong_ton, muc_canh_bao, ma_kho) 
                     VALUES (?, ?, ?, ?, ?, 0, 5, ?)",
                    [$partName, $supplierId, $unit, $importPrice, $initialSellPrice, $warehouseId]
                );
                $newMaPt = (int)$this->lastInsertId();
                $part = $this->getPartById($newMaPt);
            }
        }

        if (!$part) {
            return ['success' => false, 'message' => 'Không tìm thấy hoặc không thể khởi tạo phụ tùng'];
        }

        $maPt = (int)$part['ma_pt'];

        $this->beginTransaction();
        try {
            $thanhTien = $quantity * $importPrice;

            // 1. Tạo phiếu nhập
            $sqlPn = "INSERT INTO phieu_nhap (ma_ncc, ma_nv, ngay_nhap, tong_tien, ghi_chu) VALUES (?, ?, NOW(), ?, ?)";
            $this->execute($sqlPn, [$part['ma_ncc'], $maNv, $thanhTien, $note]);
            $maPn = (int)$this->lastInsertId();

            // 2. Tạo chi tiết nhập
            $sqlCtn = "INSERT INTO chi_tiet_nhap (ma_pn, ma_pt, so_luong, don_gia_nhap, thanh_tien) VALUES (?, ?, ?, ?, ?)";
            $this->execute($sqlCtn, [$maPn, $maPt, $quantity, $importPrice, $thanhTien]);

            // 3. Tự động tính giá bán theo tỉ lệ quy định
            $ratio = $this->settingModel->getRuleValue('Ti le don gia vtpt', 105) / 100;
            $newSellPrice = $importPrice * $ratio;

            // 4. Cập nhật tồn kho & giá trong bảng phu_tung
            $sqlPt = "UPDATE phu_tung 
                      SET so_luong_ton = so_luong_ton + ?, gia_nhap = ?, gia_ban = ? 
                      WHERE ma_pt = ?";
            $this->execute($sqlPt, [$quantity, $importPrice, $newSellPrice, $maPt]);

            $this->commit();
            return [
                'success' => true,
                'message' => "Nhập kho thành công {$quantity} {$part['don_vi_tinh']} \"{$part['ten_pt']}\"!"
            ];
        } catch (Exception $e) {
            $this->rollBack();
            return [
                'success' => false,
                'message' => "Lỗi nhập kho: " . $e->getMessage()
            ];
        }
    }

    public function getImportHistory(): array {
        $sql = "SELECT pn.*, ctn.so_luong, ctn.don_gia_nhap, ctn.thanh_tien,
                       pt.ten_pt, pt.don_vi_tinh,
                       ncc.ten_ncc,
                       k.ten_kho,
                       nd.ho_ten AS ten_nv
                FROM phieu_nhap pn
                JOIN chi_tiet_nhap ctn ON pn.ma_pn = ctn.ma_pn
                JOIN phu_tung pt ON ctn.ma_pt = pt.ma_pt
                JOIN nha_cung_cap ncc ON pn.ma_ncc = ncc.ma_ncc
                LEFT JOIN kho k ON pt.ma_kho = k.ma_kho
                JOIN nhan_vien nv ON pn.ma_nv = nv.ma_nv
                JOIN nguoi_dung nd ON nv.ma_nd = nd.ma_nd
                ORDER BY pn.ma_pn DESC";
        return $this->fetchAll($sql);
    }
}
