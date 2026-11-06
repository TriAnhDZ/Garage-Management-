<?php
require_once __DIR__ . '/BaseModel.php';

class WarehouseModel extends BaseModel {

    public function getAllWarehouses(): array {
        return $this->fetchAll("SELECT * FROM kho ORDER BY ma_kho ASC");
    }

    public function getWarehouseById(int $id): ?array {
        return $this->fetchOne("SELECT * FROM kho WHERE ma_kho = ?", [$id]);
    }

    public function createWarehouse(string $name, ?string $address = null, ?string $phone = null, ?string $description = null): int {
        $sql = "INSERT INTO kho (ten_kho, dia_chi, sdt, mo_ta, trang_thai) VALUES (?, ?, ?, ?, 'HoatDong')";
        $this->execute($sql, [$name, $address, $phone, $description]);
        return (int)$this->lastInsertId();
    }

    public function updateWarehouse(int $id, string $name, ?string $address = null, ?string $phone = null, ?string $description = null, string $status = 'HoatDong'): bool {
        $sql = "UPDATE kho SET ten_kho = ?, dia_chi = ?, sdt = ?, mo_ta = ?, trang_thai = ? WHERE ma_kho = ?";
        return $this->execute($sql, [$name, $address, $phone, $description, $status, $id]);
    }

    public function deleteWarehouse(int $id): bool {
        // Kiểm tra xem kho có đang chứa phụ tùng không
        $partCount = (int)($this->fetchOne("SELECT COUNT(*) AS cnt FROM phu_tung WHERE ma_kho = ?", [$id])['cnt'] ?? 0);
        if ($partCount > 0) {
            return false;
        }
        return $this->execute("DELETE FROM kho WHERE ma_kho = ?", [$id]);
    }

    public function getWarehouseStockSummary(): array {
        $sql = "SELECT k.*, 
                       COUNT(pt.ma_pt) AS so_mat_hang,
                       COALESCE(SUM(pt.so_luong_ton), 0) AS tong_ton_kho,
                       COALESCE(SUM(pt.so_luong_ton * pt.gia_nhap), 0) AS tong_gia_tri_nhap
                FROM kho k
                LEFT JOIN phu_tung pt ON k.ma_kho = pt.ma_kho
                GROUP BY k.ma_kho
                ORDER BY k.ma_kho ASC";
        return $this->fetchAll($sql);
    }

    public function transferPart(int $maPt, int $fromKho, int $toKho, int $quantity, int $maNv, ?string $note = null): array {
        if ($fromKho === $toKho) {
            return ['success' => false, 'message' => 'Kho nguồn và kho đích không được trùng nhau!'];
        }

        $part = $this->fetchOne("SELECT * FROM phu_tung WHERE ma_pt = ?", [$maPt]);
        if (!$part) {
            return ['success' => false, 'message' => 'Không tìm thấy phụ tùng!'];
        }

        $this->beginTransaction();
        try {
            // Ghi nhận nhật ký điều chuyển kho
            $sqlCk = "INSERT INTO chuyen_kho (ma_pt, tu_kho, den_kho, so_luong, ngay_chuyen, ma_nv, ghi_chu) 
                      VALUES (?, ?, ?, ?, NOW(), ?, ?)";
            $this->execute($sqlCk, [$maPt, $fromKho, $toKho, $quantity, $maNv, $note]);

            // Cập nhật vị trí kho mới cho phụ tùng
            $this->execute("UPDATE phu_tung SET ma_kho = ? WHERE ma_pt = ?", [$toKho, $maPt]);

            $this->commit();
            return [
                'success' => true,
                'message' => "Đã điều chuyển phụ tùng '{$part['ten_pt']}' sang kho mới thành công!"
            ];
        } catch (Exception $e) {
            $this->rollBack();
            return ['success' => false, 'message' => 'Lỗi điều chuyển kho: ' . $e->getMessage()];
        }
    }

    public function getTransferHistory(): array {
        $sql = "SELECT ck.*, 
                       pt.ten_pt, pt.don_vi_tinh,
                       k1.ten_kho AS ten_tu_kho,
                       k2.ten_kho AS ten_den_kho,
                       nd.ho_ten AS ten_nv
                FROM chuyen_kho ck
                JOIN phu_tung pt ON ck.ma_pt = pt.ma_pt
                JOIN kho k1 ON ck.tu_kho = k1.ma_kho
                JOIN kho k2 ON ck.den_kho = k2.ma_kho
                JOIN nhan_vien nv ON ck.ma_nv = nv.ma_nv
                JOIN nguoi_dung nd ON nv.ma_nd = nd.ma_nd
                ORDER BY ck.ma_ck DESC";
        return $this->fetchAll($sql);
    }
}

