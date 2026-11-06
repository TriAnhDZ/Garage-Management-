<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/EmailService.php';

class EmailController extends BaseController {
    private EmailService $emailService;

    public function __construct() {
        parent::__construct();
        $this->emailService = new EmailService();
    }

    public function index(): void {
        $this->requireRole(['TiepNhan', 'ThuKho', 'KyThuat', 'QuanLy']);
        $type = $_GET['type'] ?? null;
        $emails = $this->emailService->getAllEmailLogs($type);

        $this->render('email/index', [
            'emails'      => $emails,
            'currentType' => $type
        ]);
    }

    public function preview(): void {
        $this->requireRole(['TiepNhan', 'ThuKho', 'KyThuat', 'QuanLy', 'KhachHang']);
        $id = (int)($_GET['id'] ?? 0);
        $email = $this->emailService->getEmailLogById($id);
        if (!$email) {
            die('Không tìm thấy thư');
        }

        $gmailWebUrl = $this->emailService->getEmailLogGmailUrl($id);

        $this->render('email/preview', [
            'email'       => $email,
            'gmailWebUrl' => $gmailWebUrl
        ], 'none');
    }

    /**
     * Hành động đưa thông tin sang trang soạn thảo Google Gmail (mail.google.com)
     */
    public function openWeb(): void {
        $this->requireRole(['TiepNhan', 'ThuKho', 'KyThuat', 'QuanLy']);
        $type = $_GET['type'] ?? '';
        $id = (int)($_GET['id'] ?? 0);
        $url = null;

        if ($type === 'invoice') {
            $url = $this->emailService->getInvoiceGmailUrl($id);
        } elseif ($type === 'repair') {
            $url = $this->emailService->getRepairGmailUrl($id);
        } elseif ($type === 'appointment') {
            $url = $this->emailService->getAppointmentGmailUrl($id);
        } elseif ($type === 'log') {
            $url = $this->emailService->getEmailLogGmailUrl($id);
        }

        if ($url) {
            header("Location: " . $url);
            exit;
        }

        $this->setFlash('danger', 'Không tìm thấy dữ liệu để mở Gmail Web!');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? 'index.php?c=email&a=index');
    }

    /**
     * Màn hình cấu hình Gmail SMTP và Test gửi trực tiếp
     */
    public function config(): void {
        $this->requireRole(['QuanLy']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['save_config'])) {
                $user = trim($_POST['gmail_smtp_user'] ?? '');
                $pass = trim($_POST['gmail_smtp_pass'] ?? '');
                $senderName = trim($_POST['gmail_smtp_sender_name'] ?? 'GARAGE PRO');
                $status = $_POST['gmail_smtp_status'] ?? 'Tat';

                $this->emailService->saveSmtpConfig($user, $pass, $senderName, $status);
                $this->setFlash('success', 'Đã lưu cấu hình Gmail SMTP thành công!');
                $this->redirect('index.php?c=email&a=config');
            } elseif (isset($_POST['send_test'])) {
                $testEmail = trim($_POST['test_email'] ?? '');
                if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
                    $this->setFlash('danger', 'Vui lòng nhập địa chỉ email hợp lệ để gửi thử nghiệm!');
                } else {
                    $cfg = $this->emailService->getSmtpConfig();
                    if (empty($cfg['gmail_smtp_user']) || empty($cfg['gmail_smtp_pass'])) {
                        $this->setFlash('danger', 'Chưa điền thông tin tài khoản Gmail hoặc Mật khẩu ứng dụng!');
                    } else {
                        $mailer = new SmtpMailer();
                        $testHtml = "
                        <div style='font-family: Arial; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                            <h2 style='color: #2ed573;'>Thử nghiệm kết nối Gmail SMTP thành công!</h2>
                            <p>Xin chào, đây là email kiểm tra tính năng gửi tự động từ hệ thống <b>GARAGE PRO</b>.</p>
                            <p>Thời gian gửi: <b>" . date('d/m/Y H:i:s') . "</b></p>
                            <hr style='border: none; border-top: 1px solid #edf2f7;'>
                            <small style='color: #718096;'>Hệ thống quản lý Garage Pro - Đại học Công nghệ Thông tin UIT</small>
                        </div>";

                        require_once __DIR__ . '/../models/PdfService.php';
                        $pdfService = new PdfService();
                        $testPdfPath = $pdfService->generateGeneralPdf('Khách Hàng Thử Nghiệm', $testEmail, 'Kiểm tra cấu hình kết nối Gmail SMTP thành công', $testHtml);

                        $res = $mailer->send(
                            $cfg['gmail_smtp_user'],
                            $cfg['gmail_smtp_pass'],
                            $cfg['gmail_smtp_sender_name'] ?: 'GARAGE PRO',
                            $testEmail,
                            'Khách Hàng Thử Nghiệm',
                            '🔔 [GARAGE PRO] Kiểm tra cấu hình kết nối Gmail SMTP thành công',
                            $testHtml,
                            $testPdfPath,
                            'Phieu_Kiem_Tra_He_Thong.pdf'
                        );

                        if ($res['success']) {
                            $this->setFlash('success', $res['message']);
                        } else {
                            $this->setFlash('danger', 'Thử nghiệm thất bại: ' . $res['message']);
                        }
                    }
                }
                $this->redirect('index.php?c=email&a=config');
            }
        }

        $config = $this->emailService->getSmtpConfig();
        $this->render('email/config', [
            'config' => $config
        ]);
    }

    public function sendInvoice(): void {
        $this->requireRole(['TiepNhan', 'QuanLy']);
        $id = (int)($_GET['id'] ?? 0);
        $res = $this->emailService->sendInvoiceReceipt($id);

        if ($res['success']) {
            $this->setFlash('success', $res['message']);
        } else {
            $this->setFlash('danger', $res['message']);
        }

        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? 'index.php?c=invoice&a=index';
        $this->redirect($redirectUrl);
    }

    public function sendCompletion(): void {
        $this->requireRole(['TiepNhan', 'KyThuat', 'QuanLy']);
        $id = (int)($_GET['id'] ?? 0);
        $res = $this->emailService->sendRepairCompletedNotice($id);

        if ($res['success']) {
            $this->setFlash('success', $res['message']);
        } else {
            $this->setFlash('danger', $res['message']);
        }

        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? 'index.php?c=repair&a=detail&id=' . $id;
        $this->redirect($redirectUrl);
    }

    public function sendAppointment(): void {
        $this->requireRole(['TiepNhan', 'QuanLy']);
        $id = (int)($_GET['id'] ?? 0);
        $res = $this->emailService->sendAppointmentConfirmation($id);

        if ($res['success']) {
            $this->setFlash('success', $res['message']);
        } else {
            $this->setFlash('danger', $res['message']);
        }

        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? 'index.php?c=appointment&a=index';
        $this->redirect($redirectUrl);
    }
}
