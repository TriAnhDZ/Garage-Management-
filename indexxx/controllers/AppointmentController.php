<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/CarModel.php';

class AppointmentController extends BaseController {
    private AppointmentModel $appointmentModel;
    private CarModel $carModel;

    public function __construct() {
        parent::__construct();
        $this->appointmentModel = new AppointmentModel();
        $this->carModel = new CarModel();
    }

    public function index(): void {
        $this->requireLogin();
        $user = $this->getCurrentUser();
        $isCustomer = ($user['vai_tro'] === 'KhachHang');

        if ($isCustomer) {
            $this->redirect('index.php#lich-hen-cua-toi');
            return;
        }

        $status = $_GET['status'] ?? null;
        $appointments = $this->appointmentModel->getAllAppointments($status);

        $this->render('appointment/index', [
            'appointments'  => $appointments,
            'currentStatus' => $status,
            'isCustomer'    => false
        ]);
    }

    public function create(): void {
        $this->requireLogin();
        $user = $this->getCurrentUser();
        $isCustomer = ($user['vai_tro'] ?? '') === 'KhachHang';

        if ($isCustomer) {
            $this->redirect('index.php#dat-lich');
            return;
        }

        $cars = $this->carModel->getAllCars();
        $brands = $this->carModel->getAllBrands();
        $this->render('appointment/create', [
            'cars'       => $cars,
            'brands'     => $brands,
            'isCustomer' => false
        ]);
    }

    public function store(): void {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maXe      = (int)($_POST['ma_xe'] ?? 0);
            $ngayHen   = trim($_POST['ngay_hen'] ?? '');
            $ghiChu    = trim($_POST['ghi_chu'] ?? '');
            $maKh      = (int)($_SESSION['user']['ma_kh'] ?? 1);
            $isNewCar  = !empty($_POST['is_new_car']);
            $newBienSo = strtoupper(trim($_POST['new_bien_so'] ?? ''));
            $newHangXe = trim($_POST['new_hang_xe'] ?? '');
            $newDongXe = trim($_POST['new_dong_xe'] ?? '');

            if ($isNewCar) {
                if (empty($newBienSo) || empty($newHangXe) || empty($ngayHen)) {
                    $this->setFlash('danger', 'Vui lòng nhập Biển số, Hiệu xe và Thời gian hẹn.');
                    $this->redirect('index.php?c=appointment&a=create');
                }
                $car = $this->carModel->getCarByPlate($newBienSo);
                if ($car) {
                    $maXe = (int)$car['ma_xe'];
                } else {
                    $pdo = Database::getConnection();
                    $stmt = $pdo->prepare("INSERT INTO xe (ma_kh, bien_so, hang_xe, dong_xe, tien_no) VALUES (?, ?, ?, ?, 0.00)");
                    $stmt->execute([$maKh, $newBienSo, $newHangXe, $newDongXe]);
                    $maXe = (int)$pdo->lastInsertId();
                }
            } else {
                if ($maXe <= 0 || empty($ngayHen)) {
                    $this->setFlash('danger', 'Vui lòng chọn xe và thời gian hẹn.');
                    $this->redirect('index.php?c=appointment&a=create');
                }
            }

            try {
                $newMaLh = $this->appointmentModel->createAppointment($maKh, $maXe, $ngayHen, $ghiChu);
                $emailNotice = "";
                try {
                    require_once __DIR__ . '/../models/EmailService.php';
                    $emailService = new EmailService();
                    $emailRes = $emailService->sendAppointmentConfirmation($newMaLh);
                    if (!empty($emailRes['success'])) {
                        $emailNotice = " " . $emailRes['message'];
                    }
                } catch (Exception $ex) {}

                $this->setFlash('success', 'Đặt lịch hẹn sửa chữa thành công! Phiếu hẹn PDF 1 trang đã tự động gửi đến Gmail của bạn.' . $emailNotice);
                $this->redirect('index.php?c=appointment&a=index');
            } catch (Exception $e) {
                $this->setFlash('danger', 'Lỗi: ' . $e->getMessage());
                $this->redirect('index.php?c=appointment&a=create');
            }
        }
    }

    /**
     * Màn hình chuyên biệt: Danh sách xe chờ duyệt & Nhật ký thao tác KTV
     */
    public function approval(): void {
        $this->requireRole(['NhanVien', 'QuanLy', 'KyThuat', 'TiepNhan']);
        
        $pendingAppointments = $this->appointmentModel->getAllAppointments('ChoXacNhan');
        $approvedAppointments = $this->appointmentModel->getAllAppointments('DaXacNhan');
        
        require_once __DIR__ . '/../models/SettingModel.php';
        $settingModel = new SettingModel();
        $dailyLimit = (int)$settingModel->getRuleValue('So xe sua chua toi da', 30);
        $todayReceptionCount = $this->carModel->getDailyReceptionCount();

        require_once __DIR__ . '/../models/LogModel.php';
        $logModel = new LogModel();
        $logs = $logModel->getApprovalLogs(30);

        $this->render('appointment/approval', [
            'pendingAppointments' => $pendingAppointments,
            'approvedCount'       => count($approvedAppointments),
            'todayReceptionCount' => $todayReceptionCount,
            'dailyLimit'          => $dailyLimit,
            'logs'                => $logs
        ]);
    }

    public function updateStatus(): void {
        $this->requireRole(['NhanVien', 'QuanLy', 'KyThuat', 'TiepNhan']);
        $maLh   = (int)($_GET['id'] ?? 0);
        $status = trim($_GET['status'] ?? '');

        if ($this->appointmentModel->updateStatus($maLh, $status)) {
            try {
                require_once __DIR__ . '/../models/LogModel.php';
                $logModel = new LogModel();
                $lh = $this->appointmentModel->getAppointmentById($maLh);
                $bienSo = $lh['bien_so'] ?? 'N/A';
                $user = $this->getCurrentUser();
                $actionName = ($status === 'DaHuy') ? 'Hủy lịch hẹn' : "Cập nhật lịch hẹn ({$status})";
                $logModel->addLog(
                    $actionName,
                    "{$user['ho_ten']} đã chuyển lịch hẹn #{$maLh} (Xe {$bienSo}) sang trạng thái '{$status}'."
                );
            } catch (Exception $e) {}

            $this->setFlash('success', "Cập nhật trạng thái lịch hẹn #{$maLh} thành '{$status}' thành công!");
        } else {
            $this->setFlash('danger', 'Cập nhật thất bại.');
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php?c=appointment&a=index';
        $this->redirect($referer);
    }

    /**
     * KTV duyệt lịch hẹn 1-click:
     * Tự động duyệt lịch và tạo ngay phiếu sửa chữa đưa xe vào khoang làm việc
     */
    public function approveAndRepair(): void {
        $this->requireRole(['NhanVien', 'QuanLy', 'KyThuat', 'TiepNhan']);
        $maLh = (int)($_GET['id'] ?? 0);
        $user = $this->getCurrentUser();
        $maNv = (int)($user['ma_nv'] ?? 1);

        $res = $this->appointmentModel->approveAndCreateRepair($maLh, $maNv);
        if ($res['success']) {
            // Ghi nhật ký thao tác KTV
            try {
                require_once __DIR__ . '/../models/LogModel.php';
                $logModel = new LogModel();
                $lh = $this->appointmentModel->getAppointmentById($maLh);
                $bienSo = $lh['bien_so'] ?? 'N/A';
                $tenKh = $lh['ten_chu_xe'] ?? 'Khách';
                $logModel->addLog(
                    'Duyệt lịch hẹn & Nhận xe',
                    "KTV {$user['ho_ten']} đã duyệt lịch hẹn #{$maLh} của khách '{$tenKh}', tiếp nhận xe [{$bienSo}] vào khoang sửa chữa (Phiếu PSC #{$res['ma_psc']})."
                );
            } catch (Exception $e) {}

            $emailNotice = "";
            try {
                require_once __DIR__ . '/../models/EmailService.php';
                $emailService = new EmailService();
                $emailRes = $emailService->sendAppointmentConfirmation($maLh);
                if (!empty($emailRes['success'])) {
                    $emailNotice = " " . $emailRes['message'];
                }
            } catch (Exception $ex) {}

            $this->setFlash('success', $res['message'] . $emailNotice);
            // Chuyển thẳng KTV vào màn hình sửa chữa & phụ tùng
            $this->redirect('index.php?c=repair&a=detail&id=' . $res['ma_psc']);
        } else {
            $this->setFlash('danger', $res['message']);
            $this->redirect('index.php?c=appointment&a=approval');
        }
    }

    public function downloadPdf(): void {
        $this->requireRole(['TiepNhan', 'KyThuat', 'QuanLy', 'KhachHang']);
        $id = (int)($_GET['id'] ?? 0);
        $lh = $this->appointmentModel->getAppointmentById($id);
        if (!$lh) {
            die('Không tìm thấy lịch hẹn');
        }

        $user = $this->getCurrentUser();
        $role = $user['vai_tro'] ?? '';
        if ($role === 'KhachHang') {
            $maKh = (int)($user['ma_kh'] ?? 0);
            if ((int)$lh['ma_kh'] !== $maKh) {
                die('Bạn không có quyền tải phiếu lịch hẹn này!');
            }
        }

        require_once __DIR__ . '/../models/PdfService.php';
        $pdfService = new PdfService();
        $pdfPath = $pdfService->generateAppointmentPdf($id);

        if (file_exists($pdfPath)) {
            $filename = basename($pdfPath);
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($pdfPath));
            readfile($pdfPath);
            exit;
        } else {
            die('Không thể tạo file PDF');
        }
    }
}
