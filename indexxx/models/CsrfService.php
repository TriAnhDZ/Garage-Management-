<?php
/**
 * CsrfService: Dịch vụ bảo vệ phòng chống tấn công Cross-Site Request Forgery (CSRF - CWE-352)
 * Chuẩn OWASP WSTG-v42-SESS-05
 */
class CsrfService {

    /**
     * Lấy token CSRF hiện tại trong Session, nếu chưa có thì sinh mã ngẫu nhiên an toàn 32 bytes (64 hex chars)
     */
    public static function getToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Tạo thẻ input hidden HTML chứa mã Anti-CSRF token
     */
    public static function getField(): string {
        $token = htmlspecialchars(self::getToken(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    /**
     * Xác thực tính hợp lệ của token gửi lên từ form hoặc request header
     */
    public static function validate(?string $token = null): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $sessionToken = $_SESSION['csrf_token'] ?? '';
        if (empty($sessionToken)) {
            return false;
        }

        // Lấy từ tham số truyền vào, hoặc từ $_POST, hoặc từ HTTP header
        if ($token === null) {
            $token = $_POST['csrf_token'] 
                  ?? $_SERVER['HTTP_X_CSRF_TOKEN'] 
                  ?? $_SERVER['HTTP_X_XSRF_TOKEN'] 
                  ?? '';
        }

        if (!is_string($token) || empty($token)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Đổi token mới (ví dụ sau khi đăng nhập / đăng xuất)
     */
    public static function regenerate(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }
}

// Global helper functions
if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        return CsrfService::getToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return CsrfService::getField();
    }
}

