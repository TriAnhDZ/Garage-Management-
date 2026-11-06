<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ReportModel.php';
require_once __DIR__ . '/../models/SettingModel.php';
require_once __DIR__ . '/../models/ServiceModel.php';
require_once __DIR__ . '/../models/PartModel.php';
require_once __DIR__ . '/../models/CarModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/RepairModel.php';

class HomeController extends BaseController {
    private ReportModel $reportModel;
    private SettingModel $settingModel;
    private ServiceModel $serviceModel;
    private PartModel $partModel;
    private CarModel $carModel;
    private AppointmentModel $appointmentModel;
    private RepairModel $repairModel;

    public function __construct() {
        parent::__construct();
        $this->reportModel = new ReportModel();
        $this->settingModel = new SettingModel();
        $this->serviceModel = new ServiceModel();
        $this->partModel = new PartModel();
        $this->carModel = new CarModel();
        $this->appointmentModel = new AppointmentModel();
        $this->repairModel = new RepairModel();
    }

    public function index(): void {
        // 1. DÀNH CHO KHÁCH VÃNG LAI (CHƯA ĐĂNG NHẬP)
        if (!$this->isLoggedIn()) {
            $lookupPlate = trim($_GET['lookup_plate'] ?? '');
            $carProgress = null;
            if (!empty($lookupPlate)) {
                $carProgress = $this->carModel->getPublicCarProgress($lookupPlate);
                if (!$carProgress) {
                    $this->setFlash('warning', "Không tìm thấy dữ liệu phương tiện biển số {$lookupPlate} trong hệ thống.");
                }
            }

            $services = $this->serviceModel->getAllServices(true);
            $parts = $this->partModel->getAllParts();
            $brands = $this->carModel->getAllBrands();

            $this->render('home/landing', [
                'services'     => $services,
                'parts'        => $parts,
                'brands'       => $brands,
                'lookupPlate'  => $lookupPlate,
                'carProgress'  => $carProgress
            ], 'none'); // Render trang landing công khai chuyên biệt
            return;
        }

        // 2. DÀNH CHO KHÁCH HÀNG ĐÃ ĐĂNG NHẬP (KhachHang)
        $user = $this->getCurrentUser();
        if ($user['vai_tro'] === 'KhachHang') {
            $maKh = (int)($user['ma_kh'] ?? 0);
            $myCars = $this->carModel->getCarsByCustomerId($maKh);
            $myAppointments = $this->appointmentModel->getAppointmentsByCustomerId($maKh);
            $myRepairs = $this->repairModel->getRepairsByCustomerId($maKh);

            $lookupPlate = trim($_GET['lookup_plate'] ?? '');
            $carProgress = null;
            if (!empty($lookupPlate)) {
                $carProgress = $this->carModel->getPublicCarProgress($lookupPlate);
                if (!$carProgress) {
                    $this->setFlash('warning', "Không tìm thấy dữ liệu phương tiện biển số {$lookupPlate} trong hệ thống.");
                }
            }

            $services = $this->serviceModel->getAllServices(true);
            $parts = $this->partModel->getAllParts();
            $brands = $this->carModel->getAllBrands();

            $this->render('home/landing', [
                'isCustomer'     => true,
                'currentUser'    => $user,
                'myCars'         => $myCars,
                'myAppointments' => $myAppointments,
                'myRepairs'      => $myRepairs,
                'services'       => $services,
                'parts'          => $parts,
                'brands'         => $brands,
                'lookupPlate'    => $lookupPlate,
                'carProgress'    => $carProgress
            ], 'none'); // Cổng thông tin & Giới thiệu Garage chuẩn thương hiệu cao cấp
            return;
        }

        // 3. DÀNH CHO BAN QUẢN LÝ & NHÂN VIÊN GARA (Admin / TiepNhan / KyThuat / ThuKho)
        $stats = $this->reportModel->getDashboardStats();
        $dailyLimit = (int)$this->settingModel->getRuleValue('So xe sua chua toi da', 30);

        $this->render('home/dashboard', [
            'stats'      => $stats,
            'dailyLimit' => $dailyLimit
        ]);
    }

    public function bookAppointment(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tenKhach = trim($_POST['ten_khach'] ?? '');
            $sdt      = trim($_POST['sdt'] ?? '');
            $email    = strtolower(trim($_POST['email'] ?? ''));
            $bienSo   = strtoupper(trim($_POST['bien_so'] ?? ''));
            $hangXe   = trim($_POST['hang_xe'] ?? '');
            $ngayHen  = trim($_POST['ngay_hen'] ?? '');
            $ghiChu   = trim($_POST['ghi_chu'] ?? '');

            if (empty($tenKhach) || empty($sdt) || empty($email) || empty($bienSo) || empty($hangXe) || empty($ngayHen)) {
                $this->setFlash('danger', 'Vui lòng điền đầy đủ Họ tên, SĐT, Email (Gmail), Biển số, Hiệu xe và Thời gian hẹn.');
                $this->redirect('index.php#dat-lich');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->setFlash('danger', 'Địa chỉ Email (Gmail) không đúng định dạng. Vui lòng kiểm tra lại!');
                $this->redirect('index.php#dat-lich');
            }

            $user = $this->getCurrentUser();
            if ($user && ($user['vai_tro'] ?? '') === 'KhachHang') {
                $maKh = (int)($user['ma_kh'] ?? 0);
                // Cập nhật email người dùng nếu đã thay đổi
                if (!empty($email) && ($email !== ($user['email'] ?? ''))) {
                    $pdo = Database::getConnection();
                    $stmt = $pdo->prepare("UPDATE nguoi_dung SET email = ? WHERE ma_nd = ?");
                    $stmt->execute([$email, (int)$user['ma_nd']]);
                }

                $car = $this->carModel->getCarByPlate($bienSo);
                if ($car) {
                    $maXe = (int)$car['ma_xe'];
                } else {
                    $pdo = Database::getConnection();
                    $stmt = $pdo->prepare("INSERT INTO xe (ma_kh, bien_so, hang_xe, dong_xe, tien_no) VALUES (?, ?, ?, '', 0.00)");
                    $stmt->execute([$maKh, $bienSo, $hangXe]);
                    $maXe = (int)$pdo->lastInsertId();
                }
                $newMaLh = $this->appointmentModel->createAppointment($maKh, $maXe, $ngayHen, $ghiChu);
                $res = ['success' => true, 'ma_lh' => $newMaLh, 'message' => 'Đặt lịch hẹn thành công!'];

                $emailNotice = "";
                if (!empty($res['ma_lh'])) {
                    try {
                        require_once __DIR__ . '/../models/EmailService.php';
                        $emailService = new EmailService();
                        $emailRes = $emailService->sendAppointmentConfirmation((int)$res['ma_lh']);
                        if (!empty($emailRes['success'])) {
                            $emailNotice = " " . $emailRes['message'];
                        }
                    } catch (Exception $ex) {}
                }
                $this->setFlash('success', 'Yêu cầu đặt lịch hẹn cho xe [' . htmlspecialchars($bienSo) . '] đã được tiếp nhận thành công! Phiếu hẹn PDF đã được gửi đến Gmail: <b>' . htmlspecialchars($email) . '</b>.' . $emailNotice);
                $this->redirect('index.php#lich-hen-cua-toi');
                return;
            } else {
                // KHÁCH CHƯA ĐĂNG NHẬP: LƯU PHIẾU HẸN VÀ CHUYỂN HƯỚNG ĐI ĐĂNG KÝ
                $_SESSION['pending_appointment'] = [
                    'ten_khach' => $tenKhach,
                    'sdt'       => $sdt,
                    'email'     => $email,
                    'bien_so'   => $bienSo,
                    'hang_xe'   => $hangXe,
                    'ngay_hen'  => $ngayHen,
                    'ghi_chu'   => $ghiChu
                ];

                require_once __DIR__ . '/../models/UserModel.php';
                $userModel = new UserModel();
                $existing = $userModel->findByEmail($email) ?: $userModel->findByPhone($sdt);

                if ($existing) {
                    $this->setFlash('info', "Thông tin của Quý khách đã có tài khoản trên hệ thống. Phiếu hẹn xe [{$bienSo}] đã được lưu tạm, vui lòng đăng nhập để hệ thống tự động hoàn tất tạo lịch hẹn!");
                    $this->redirect('index.php?c=auth&a=login&email=' . urlencode($email));
                    return;
                } else {
                    $this->setFlash('info', "Thông tin phiếu hẹn xe [{$bienSo}] đã được lưu tạm! Quý khách vui lòng tạo mật khẩu để hoàn tất đăng ký tài khoản thành viên, hệ thống sẽ tự động kích hoạt phiếu hẹn và gửi PDF về Gmail ngay sau khi đăng ký.");
                    $this->redirect('index.php?c=auth&a=register');
                    return;
                }
            }
        }
    }
}
