<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/SettingModel.php';

class ServiceModel extends BaseModel {
    private SettingModel $settingModel;

    public function __construct() {
        parent::__construct();
        $this->settingModel = new SettingModel();
    }

    public function getAllServices(bool $activeOnly = false): array {
        $sql = "SELECT * FROM dich_vu";
        if ($activeOnly) {
            $sql .= " WHERE trang_thai = 'KhaDung'";
        }
        $sql .= " ORDER BY ten_dv ASC";
        return $this->fetchAll($sql);
    }

    public function getServiceById(int $id): ?array {
        return $this->fetchOne("SELECT * FROM dich_vu WHERE ma_dv = ?", [$id]);
    }

    public function createService(string $name, string $description, float $price, string $status = 'KhaDung'): array {
        $currentCount = (int)($this->fetchOne("SELECT COUNT(*) as cnt FROM dich_vu")['cnt'] ?? 0);
        $maxLimit = (int)$this->settingModel->getRuleValue('So loai tien cong', 50);

        if ($currentCount >= $maxLimit) {
            return [
                'success' => false,
                'message' => "Số loại dịch vụ / tiền công đã đạt giới hạn tối đa ({$maxLimit}) theo quy định!"
            ];
        }

        try {
            $this->execute(
                "INSERT INTO dich_vu (ten_dv, mo_ta, gia_tieu_chuan, trang_thai) VALUES (?, ?, ?, ?)",
                [$name, $description, $price, $status]
            );
            return [
                'success' => true,
                'id' => (int)$this->lastInsertId(),
                'message' => "Thêm dịch vụ / loại tiền công '{$name}' thành công!"
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Lỗi: " . $e->getMessage()
            ];
        }
    }

    public function updateService(int $id, string $name, string $description, float $price, string $status): bool {
        return $this->execute(
            "UPDATE dich_vu SET ten_dv = ?, mo_ta = ?, gia_tieu_chuan = ?, trang_thai = ? WHERE ma_dv = ?",
            [$name, $description, $price, $status, $id]
        );
    }

    public function deleteService(int $id): bool {
        // Kiểm tra xem đã dùng trong chi_tiet_dich_vu chưa
        $used = $this->fetchOne("SELECT COUNT(*) as cnt FROM chi_tiet_dich_vu WHERE ma_dv = ?", [$id]);
        if (!empty($used['cnt'])) {
            // Đổi trạng thái sang TamDung thay vì xóa cứng
            return $this->execute("UPDATE dich_vu SET trang_thai = 'TamDung' WHERE ma_dv = ?", [$id]);
        }
        return $this->execute("DELETE FROM dich_vu WHERE ma_dv = ?", [$id]);
    }
}

