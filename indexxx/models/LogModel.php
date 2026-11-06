<?php
require_once __DIR__ . '/BaseModel.php';

class LogModel extends BaseModel {

    /**
     * Ghi lại một hoạt động vào nhật ký hệ thống
     */
    public function addLog(
        string $action, 
        ?string $detail = null, 
        ?int $userId = null, 
        ?string $userName = null, 
        ?string $role = null
    ): int {
        // Tự động nhận diện người dùng từ phiên đăng nhập nếu không truyền vào
        if ($userId === null && !empty($_SESSION['user'])) {
            $userId   = (int)($_SESSION['user']['ma_nd'] ?? 0);
            $userName = $_SESSION['user']['ho_ten'] ?? 'Người dùng';
            $role     = $_SESSION['user']['vai_tro'] ?? 'NhanVien';
        }

        $userId   = $userId ?: 0;
        $userName = $userName ?: 'Hệ thống';
        $role     = $role ?: 'HeThong';

        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (strpos($ip, ',') !== false) {
            $ip = trim(explode(',', $ip)[0]);
        }

        $sql = "INSERT INTO nhat_ky (ma_nd, ho_ten, vai_tro, hanh_dong, chi_tiet, dia_chi_ip, thoi_gian) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $this->execute($sql, [$userId, $userName, $role, $action, $detail, $ip]);
        return (int)$this->lastInsertId();
    }

    /**
     * Lấy danh sách nhật ký mới nhất
     */
    public function getRecentLogs(int $limit = 50, ?string $action = null): array {
        $sql = "SELECT * FROM nhat_ky WHERE 1=1";
        $params = [];
        if (!empty($action)) {
            $sql .= " AND hanh_dong LIKE ?";
            $params[] = "%" . $action . "%";
        }
        $sql .= " ORDER BY ma_log DESC LIMIT " . (int)$limit;
        return $this->fetchAll($sql, $params);
    }

    /**
     * Lấy các nhật ký liên quan đến thao tác Duyệt xe / Lịch hẹn / Tiếp nhận
     */
    public function getApprovalLogs(int $limit = 20): array {
        $sql = "SELECT * FROM nhat_ky 
                WHERE hanh_dong IN ('Duyệt lịch hẹn & Nhận xe', 'Hủy lịch hẹn', 'Duyệt xe', 'Tiếp nhận xe', 'Dọn dẹp dữ liệu ZAP')
                   OR hanh_dong LIKE '%Duyệt%' OR hanh_dong LIKE '%Lịch hẹn%'
                ORDER BY ma_log DESC LIMIT " . (int)$limit;
        return $this->fetchAll($sql);
    }
}

