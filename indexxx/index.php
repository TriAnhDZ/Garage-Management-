<?php
/**
 * Front Controller & Router
 * Điều phối mọi request trong hệ thống Quản lý Gara Ô tô
 */

// 1. Bảo mật: Ẩn thông tin phiên bản máy chủ & PHP (OWASP WSTG-INFO-08 / CWE-497 / CWE-1395)
if (function_exists('header_remove')) {
    header_remove('X-Powered-By');
}
@ini_set('expose_php', 'off');
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// 2. Bảo mật: Thiết lập Cookie an toàn với HttpOnly, SameSite, Secure (CWE-1004 & CWE-1275)
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

// 3. Bảo mật: Thiết lập các HTTP Security Headers chuẩn OWASP (CWE-693 / CWE-1021)
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
header("X-XSS-Protection: 1; mode=block");

// Content-Security-Policy (CSP)
$csp = "default-src 'self'; "
     . "script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; "
     . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; "
     . "font-src 'self' https://fonts.gstatic.com data:; "
     . "img-src 'self' data: https:; "
     . "frame-ancestors 'self'; "
     . "connect-src 'self' https:;";
header("Content-Security-Policy: " . $csp);

// Nạp dịch vụ bảo vệ Anti-CSRF
require_once __DIR__ . '/models/CsrfService.php';
CsrfService::getToken(); // Khởi tạo sẵn token trong session

$controllerName = strtolower(trim($_GET['c'] ?? 'home'));
$actionName     = trim($_GET['a'] ?? 'index');

// Bản đồ ánh xạ Router
$routes = [
    'home'        => 'HomeController',
    'auth'        => 'AuthController',
    'reception'   => 'ReceptionController',
    'repair'      => 'RepairController',
    'invoice'     => 'InvoiceController',
    'part'        => 'PartController',
    'service'     => 'ServiceController',
    'search'      => 'SearchController',
    'report'      => 'ReportController',
    'setting'     => 'SettingController',
    'appointment' => 'AppointmentController',
    'warehouse'   => 'WarehouseController',
    'email'       => 'EmailController',
];

if (!array_key_exists($controllerName, $routes)) {
    http_response_code(404);
    $e = new Exception("Đường dẫn hoặc trang '{$controllerName}' không tồn tại trên hệ thống.");
    require_once __DIR__ . '/views/layouts/error.php';
    exit;
}

$className = $routes[$controllerName];
$controllerFile = __DIR__ . '/controllers/' . $className . '.php';

if (!file_exists($controllerFile)) {
    http_response_code(500);
    $e = new Exception("Không tìm thấy tệp điều khiển: {$className}.php");
    require_once __DIR__ . '/views/layouts/error.php';
    exit;
}

require_once $controllerFile;

if (!class_exists($className)) {
    http_response_code(500);
    $e = new Exception("Lớp điều khiển {$className} không tồn tại.");
    require_once __DIR__ . '/views/layouts/error.php';
    exit;
}

$controller = new $className();

if (!method_exists($controller, $actionName)) {
    http_response_code(404);
    $e = new Exception("Chức năng '{$actionName}' không tồn tại trong {$className}.");
    require_once __DIR__ . '/views/layouts/error.php';
    exit;
}

// Bắt và xử lý ngoại lệ toàn cục cho toàn bộ hệ thống
try {
    $controller->$actionName();
} catch (Throwable $e) {
    // 1. Tự động ghi nhật ký sự cố vào CSDL
    try {
        require_once __DIR__ . '/models/LogModel.php';
        $logModel = new LogModel();
        $logModel->addLog(
            'Lỗi hệ thống (Exception)',
            $e->getMessage() . " tại " . basename($e->getFile()) . ":" . $e->getLine()
        );
    } catch (Throwable $logEx) {}

    // 2. Hiển thị giao diện báo lỗi thân thiện chuyên nghiệp
    http_response_code(500);
    require_once __DIR__ . '/views/layouts/error.php';
    exit;
}

