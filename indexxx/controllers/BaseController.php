<?php
/**
 * BaseController: Lớp điều khiển cơ sở
 */

require_once __DIR__ . '/../models/CsrfService.php';

abstract class BaseController {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    /**
     * Xác thực Anti-CSRF token cho các form POST (CWE-352)
     */
    protected function validateCsrf(): bool {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CsrfService::validate()) {
                $this->setFlash('danger', 'Yêu cầu bảo mật không hợp lệ hoặc phiên thao tác đã hết hạn (CSRF Token Mismatch). Vui lòng gửi lại!');
                $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
                $this->redirect($referer);
                return false;
            }
        }
        return true;
    }

    protected function render(string $viewPath, array $data = [], string $layout = 'main'): void {
        $csrfToken = CsrfService::getToken();
        $data['csrf_token'] = $csrfToken;
        extract($data);
        $user = $this->getCurrentUser();
        $flash = $this->getFlash();

        $viewFile = __DIR__ . '/../views/' . $viewPath . '.php';
        if (!file_exists($viewFile)) {
            die("View not found: " . htmlspecialchars($viewPath));
        }

        if ($layout === 'none') {
            require $viewFile;
            return;
        }

        // Layout chung có header, sidebar, footer
        $headerFile  = __DIR__ . '/../views/layouts/header.php';
        $sidebarFile = __DIR__ . '/../views/layouts/sidebar.php';
        $footerFile  = __DIR__ . '/../views/layouts/footer.php';

        require $headerFile;
        require $sidebarFile;
        echo '<div class="wrapper"><div class="content">';
        
        // Hiển thị thông báo flash nếu có
        if ($flash) {
            $alertClass = 'alert-' . ($flash['type'] === 'error' ? 'danger' : $flash['type']);
            echo '<div class="alert ' . $alertClass . '" id="message_container">';
            echo htmlspecialchars($flash['message']);
            echo '</div>';
        }

        require $viewFile;
        echo '</div></div>';
        require $footerFile;
    }

    protected function redirect(string $url): void {
        header("Location: " . $url);
        exit;
    }

    protected function setFlash(string $type, string $message): void {
        $_SESSION['flash'] = [
            'type'    => $type, // success, danger/error, warning, info
            'message' => $message
        ];
    }

    protected function getFlash(): ?array {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    protected function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function getCurrentUser(): ?array {
        return $_SESSION['user'] ?? null;
    }

    protected function isLoggedIn(): bool {
        return isset($_SESSION['user']);
    }

    protected function requireLogin(): void {
        if (!$this->isLoggedIn()) {
            $this->setFlash('warning', 'Vui lòng đăng nhập để tiếp tục.');
            $this->redirect('index.php?c=auth&a=login');
        }
    }

    protected function requireRole(array $allowedRoles): void {
        $this->requireLogin();
        $userRole = $_SESSION['user']['vai_tro'] ?? '';

        // 1. Quản lý (Admin) có toàn quyền truy cập mọi tính năng
        if ($userRole === 'QuanLy') {
            return;
        }

        // 2. Nhân viên (Kỹ thuật viên) có toàn quyền thực hiện các nghiệp vụ Gara
        // (Lịch hẹn, Tiếp nhận xe, Sửa chữa, Phụ tùng, Thu tiền, Kho vật tư)
        if ($userRole === 'NhanVien') {
            $staffRoles = ['TiepNhan', 'KyThuat', 'ThuKho', 'NhanVien'];
            if (!empty(array_intersect($allowedRoles, $staffRoles))) {
                return;
            }
        }

        if (!in_array($userRole, $allowedRoles, true)) {
            $this->setFlash('danger', 'Bạn không có quyền truy cập chức năng này!');
            $this->redirect('index.php');
        }
    }
}

