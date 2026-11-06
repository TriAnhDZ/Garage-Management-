<?php
require_once __DIR__ . '/BaseModel.php';

class SettingModel extends BaseModel {

    public function getAllRules(): array {
        return $this->fetchAll("SELECT * FROM quy_dinh ORDER BY ma_tham_so ASC");
    }

    public function getRuleValue(string $name, float $default = 0): float {
        $row = $this->fetchOne("SELECT gia_tri FROM quy_dinh WHERE ten_tham_so = ?", [$name]);
        return $row ? (float)$row['gia_tri'] : $default;
    }

    public function updateRule(int $id, float $value, ?string $note = null): bool {
        if ($note !== null) {
            return $this->execute("UPDATE quy_dinh SET gia_tri = ?, ghi_chu = ? WHERE ma_tham_so = ?", [$value, $note, $id]);
        }
        return $this->execute("UPDATE quy_dinh SET gia_tri = ? WHERE ma_tham_so = ?", [$value, $id]);
    }

    public function addRule(string $name, float $value, string $note = ''): bool {
        return $this->execute(
            "INSERT INTO quy_dinh (ten_tham_so, gia_tri, ghi_chu) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE gia_tri = VALUES(gia_tri), ghi_chu = VALUES(ghi_chu)",
            [$name, $value, $note]
        );
    }

    public function deleteRule(int $id): bool {
        return $this->execute("DELETE FROM quy_dinh WHERE ma_tham_so = ?", [$id]);
    }
}

