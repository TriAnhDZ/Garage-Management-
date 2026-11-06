<?php
/**
 * Background Email Worker CLI
 * Chạy tiến trình ngầm không đồng bộ (Asynchronous Worker) để xử lý tạo PDF & gửi Gmail
 * Không chặn (non-blocking) người dùng trên giao diện web, giúp website phản hồi tức thì (< 0.1s)
 */

if (php_sapi_name() !== 'cli') {
    die("Direct access not permitted");
}

set_time_limit(180);
ignore_user_abort(true);

$action = $argv[1] ?? '';
$id     = (int)($argv[2] ?? 0);

if (empty($action) || $id <= 0) {
    exit(0);
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/EmailService.php';

$service = new EmailService();

try {
    switch ($action) {
        case 'appointment':
            $service->sendAppointmentConfirmationSync($id);
            break;
        case 'repair':
            $service->sendRepairCompletedNoticeSync($id);
            break;
        case 'invoice':
            $service->sendInvoiceReceiptSync($id);
            break;
    }
} catch (Throwable $e) {
    error_log("[EmailWorker Error] " . $e->getMessage());
}

