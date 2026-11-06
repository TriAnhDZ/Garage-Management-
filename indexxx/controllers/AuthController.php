<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController extends BaseController {
    private UserModel $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
    }

    public function login(): void {
        if ($this->isLoggedIn()) {
            $this->redirect('index.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($email) || empty($password)) {
                $this->setFlash('danger', 'Vui lòng nhập đầy đủ Email và Mật khẩu.');
                $this->render('auth/login', ['email' => $email], 'none');
                return;
            }

            // Kiểm tra tài khoản email có tồn tại không
            $existingUser = $this->userModel->findByEmail($email);
            if (!$existingUser) {
                $this->setFlash('danger', "Tài khoản với email '{$email}' không tồn tại trong hệ thống! Vui lòng kiểm tra lại hoặc Đăng ký tài khoản mới.");
                $this->render('auth/login', [
                    'email'      => $email,
                    'errorField' => 'email'
                ], 'none');
                return;
            }

            // Kiểm tra mật khẩu
            $user = $this->userModel->authenticate($email, $password);
            if ($user) {
                // Đăng nhập trực tiếp không cần OTP
                $_SESSION['user'] = $user;

                // Tự động hoàn tất phiếu hẹn đang chờ nếu có
                if (!empty($_SESSION['pending_appointment']) && ($user['vai_tro'] ?? '') === 'KhachHang') {
                    $appt = $_SESSION['pending_appointment'];
                    unset($_SESSION['pending_appointment']);

                    $maKh = (int)($user['ma_kh'] ?? 0);
                    if ($maKh > 0) {
                        require_once __DIR__ . '/../models/CarModel.php';
                        require_once __DIR__ . '/../models/AppointmentModel.php';
                        $carModel = new CarModel();
                        $appointmentModel = new AppointmentModel();

                        $bienSo = $appt['bien_so'];
                        $hangXe = $appt['hang_xe'];
                        $ngayHen = $appt['ngay_hen'];
                        $ghiChu = $appt['ghi_chu'];

                        $car = $carModel->getCarByPlate($bienSo);
                        if ($car) {
                            $maXe = (int)$car['ma_xe'];
                        } else {
                            $pdo = Database::getConnection();
                            $stmt = $pdo->prepare("INSERT INTO xe (ma_kh, bien_so, hang_xe, dong_xe, tien_no) VALUES (?, ?, ?, '', 0.00)");
                            $stmt->execute([$maKh, $bienSo, $hangXe]);
                            $maXe = (int)$pdo->lastInsertId();
                        }

                        $newMaLh = $appointmentModel->createAppointment($maKh, $maXe, $ngayHen, $ghiChu);

                        try {
                            require_once __DIR__ . '/../models/EmailService.php';
                            $emailService = new EmailService();
                            $emailService->sendAppointmentConfirmation($newMaLh);
                        } catch (Exception $ex) {}

                        $this->setFlash('success', "🎉 Đăng nhập thành công & Đã tự động tạo lịch hẹn cho xe [{$bienSo}]! Phiếu hẹn PDF đã được gửi đến Gmail của bạn.");
                        $this->redirect('index.php#lich-hen-cua-toi');
                        return;
                    }
                }

                $this->setFlash('success', "Chào mừng " . htmlspecialchars($user['ho_ten']) . " đã đăng nhập thành công!");
                $this->redirect('index.php');
                return;
            } else {
                // Sai mật khẩu cụ thể
                $this->setFlash('danger', "Mật khẩu không chính xác! Vui lòng kiểm tra lại phím Caps Lock hoặc bấm 'Quên mật khẩu?' để đặt lại mật khẩu mới.");
                $this->render('auth/login', [
                    'email'      => $email,
                    'errorField' => 'password'
                ], 'none');
                return;
            }
        }

        $this->render('auth/login', [], 'none');
    }

    /**
     * Màn hình xác thực mã OTP đăng nhập
     */
    public function verifyLoginOtp(): void {
        if ($this->isLoggedIn()) {
            $this->redirect('index.php');
        }

        if (empty($_SESSION['login_pending'])) {
            $this->redirect('index.php?c=auth&a=login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $otpInput = trim($_POST['otp'] ?? '');
            $pending = $_SESSION['login_pending'];

            if (time() > $pending['expires_at']) {
                $this->setFlash('danger', 'Mã OTP đã hết hiệu lực! Vui lòng bấm "Gửi lại mã OTP" để nhận mã mới.');
            } elseif ($otpInput !== $pending['otp']) {
                $this->setFlash('danger', 'Mã xác thực OTP không chính xác! Vui lòng kiểm tra lại email.');
            } else {
                // OTP hợp lệ -> Đăng nhập thành công
                $user = $pending['user'];
                unset($_SESSION['login_pending'], $_SESSION['login_otp_info']);
                $_SESSION['user'] = $user;

                $this->setFlash('success', "🛡️ Xác thực mã OTP thành công! Chào mừng " . htmlspecialchars($user['ho_ten']) . " đã đăng nhập an toàn vào hệ thống!");
                $this->redirect('index.php');
            }
        }

        $pending = $_SESSION['login_pending'];
        $otpInfo = $_SESSION['login_otp_info'] ?? null;
        $this->render('auth/verify_login_otp', [
            'pending' => $pending,
            'otpInfo' => $otpInfo
        ], 'none');
    }

    /**
     * Gửi lại mã OTP đăng nhập
     */
    public function resendLoginOtp(): void {
        if (empty($_SESSION['login_pending'])) {
            $this->redirect('index.php?c=auth&a=login');
        }

        $user = $_SESSION['login_pending']['user'];
        $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $_SESSION['login_pending']['otp'] = $otp;
        $_SESSION['login_pending']['expires_at'] = time() + 300;

        require_once __DIR__ . '/../models/EmailService.php';
        $emailService = new EmailService();
        $otpRes = $emailService->sendOtpEmail($user['email'], $user['ho_ten'], $otp, 'DangNhap');
        $_SESSION['login_otp_info'] = $otpRes;

        $this->setFlash('success', "Mã OTP mới đã được gửi lại tới email: " . htmlspecialchars($user['email']));
        $this->redirect('index.php?c=auth&a=verifyLoginOtp');
    }

    public function register(): void {
        if ($this->isLoggedIn()) {
            $this->redirect('index.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name     = trim($_POST['ho_ten'] ?? '');
            $phone    = trim($_POST['sdt'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $address  = trim($_POST['dia_chi'] ?? '');

            if (empty($name) || empty($phone) || empty($email) || empty($password)) {
                $this->setFlash('danger', 'Vui lòng điền đầy đủ các trường thông tin bắt buộc.');
                $this->render('auth/register', ['formData' => $_POST], 'none');
                return;
            }

            // Kiểm tra trùng email
            if ($this->userModel->findByEmail($email)) {
                $this->setFlash('danger', 'Email này đã tồn tại trên hệ thống! Vui lòng dùng email khác.');
                $this->render('auth/register', ['formData' => $_POST], 'none');
                return;
            }

            // Kiểm tra trùng số điện thoại
            if ($this->userModel->findByPhone($phone)) {
                $this->setFlash('danger', 'Số điện thoại này đã được đăng ký! Vui lòng dùng số điện thoại khác.');
                $this->render('auth/register', ['formData' => $_POST], 'none');
                return;
            }

            // Sinh mã xác thực OTP 6 số (chuẩn UC1 & AD1 trong tài liệu BTL)
            $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $_SESSION['register_pending'] = [
                'data' => [
                    'ho_ten'   => $name,
                    'sdt'      => $phone,
                    'email'    => $email,
                    'mat_khau' => $password,
                    'dia_chi'  => $address
                ],
                'otp'        => $otp,
                'expires_at' => time() + 300 // 5 phút
            ];

            // Gửi email OTP
            require_once __DIR__ . '/../models/EmailService.php';
            $emailService = new EmailService();
            $otpRes = $emailService->sendOtpEmail($email, $name, $otp, 'DangKy');
            $_SESSION['register_otp_info'] = $otpRes;

            $notice = (!empty($otpRes['sent_smtp']))
                ? "Mã OTP đã được gửi thẳng tới hòm thư Gmail của bạn ({$email}). Vui lòng kiểm tra hộp thư (hoặc mục Thư rác/Spam)."
                : "Mã xác thực OTP đã được gửi đến email: {$email}. Vui lòng nhập mã để hoàn tất đăng ký.";
            $this->setFlash('info', $notice);
            $this->redirect('index.php?c=auth&a=verifyRegisterOtp');
            return;
        }

        $this->render('auth/register', [], 'none');
    }

    /**
     * Màn hình nhập mã xác thực OTP đăng ký tài khoản
     */
    public function verifyRegisterOtp(): void {
        if ($this->isLoggedIn()) {
            $this->redirect('index.php');
        }

        if (empty($_SESSION['register_pending'])) {
            $this->redirect('index.php?c=auth&a=register');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $otpInput = preg_replace('/\D/', '', trim($_POST['otp'] ?? ''));
            $pending = $_SESSION['register_pending'];

            if (time() > $pending['expires_at']) {
                $this->setFlash('danger', 'Mã xác thực OTP đã hết hạn! Vui lòng bấm "Gửi lại mã OTP" để nhận mã mới.');
            } elseif ($otpInput !== $pending['otp']) {
                $this->setFlash('danger', 'Mã xác thực OTP không chính xác! Vui lòng kiểm tra kỹ email của bạn.');
            } else {
                // OTP hợp lệ -> Tạo tài khoản chính thức vào CSDL
                $d = $pending['data'];
                try {
                    $this->userModel->createCustomer($d['ho_ten'], $d['sdt'], $d['email'], $d['mat_khau'], $d['dia_chi']);
                    $newUser = $this->userModel->authenticate($d['email'], $d['mat_khau']);

                    unset($_SESSION['register_pending'], $_SESSION['register_otp_info']);

                    if ($newUser) {
                        $_SESSION['user'] = $newUser;

                        // TỰ ĐỘNG TẠO PHIẾU HẸN NẾU KHÁCH VỪA ĐIỀN TRƯỚC KHI ĐĂNG KÝ
                        if (!empty($_SESSION['pending_appointment'])) {
                            $appt = $_SESSION['pending_appointment'];
                            unset($_SESSION['pending_appointment']);

                            $maKh = (int)($newUser['ma_kh'] ?? 0);
                            if ($maKh > 0) {
                                require_once __DIR__ . '/../models/CarModel.php';
                                require_once __DIR__ . '/../models/AppointmentModel.php';
                                $carModel = new CarModel();
                                $appointmentModel = new AppointmentModel();

                                $bienSo = $appt['bien_so'];
                                $hangXe = $appt['hang_xe'];
                                $ngayHen = $appt['ngay_hen'];
                                $ghiChu = $appt['ghi_chu'];

                                $car = $carModel->getCarByPlate($bienSo);
                                if ($car) {
                                    $maXe = (int)$car['ma_xe'];
                                } else {
                                    $pdo = Database::getConnection();
                                    $stmt = $pdo->prepare("INSERT INTO xe (ma_kh, bien_so, hang_xe, dong_xe, tien_no) VALUES (?, ?, ?, '', 0.00)");
                                    $stmt->execute([$maKh, $bienSo, $hangXe]);
                                    $maXe = (int)$pdo->lastInsertId();
                                }

                                $newMaLh = $appointmentModel->createAppointment($maKh, $maXe, $ngayHen, $ghiChu);

                                // Gửi email xác nhận kèm PDF
                                try {
                                    require_once __DIR__ . '/../models/EmailService.php';
                                    $emailService = new EmailService();
                                    $emailService->sendAppointmentConfirmation($newMaLh);
                                } catch (Exception $ex) {}

                                $this->setFlash('success', "🎉 Đăng ký thành công & Đã tự động tạo phiếu hẹn #{$newMaLh} cho xe [{$bienSo}]! Phiếu hẹn PDF đã được gửi đến Gmail của bạn.");
                                $this->redirect('index.php#lich-hen-cua-toi');
                                return;
                            }
                        }

                        $this->setFlash('success', "🎉 Xác thực OTP thành công! Chào mừng Quý khách " . htmlspecialchars($d['ho_ten']) . " đã đăng ký tài khoản Garage Pro!");
                        $this->redirect('index.php');
                        return;
                    } else {
                        $this->setFlash('success', 'Xác thực OTP thành công! Bạn có thể đăng nhập ngay.');
                        $this->redirect('index.php?c=auth&a=login');
                    }
                } catch (Exception $e) {
                    $this->setFlash('danger', 'Đăng ký thất bại: ' . $e->getMessage());
                    $this->redirect('index.php?c=auth&a=register');
                }
            }
        }

        $pending = $_SESSION['register_pending'];
        $otpInfo = $_SESSION['register_otp_info'] ?? null;
        $this->render('auth/verify_register_otp', [
            'pending' => $pending,
            'otpInfo' => $otpInfo
        ], 'none');
    }

    /**
     * Gửi lại mã OTP đăng ký
     */
    public function resendRegisterOtp(): void {
        if (empty($_SESSION['register_pending'])) {
            $this->redirect('index.php?c=auth&a=register');
        }

        $d = $_SESSION['register_pending']['data'];
        $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $_SESSION['register_pending']['otp'] = $otp;
        $_SESSION['register_pending']['expires_at'] = time() + 300;

        require_once __DIR__ . '/../models/EmailService.php';
        $emailService = new EmailService();
        $otpRes = $emailService->sendOtpEmail($d['email'], $d['ho_ten'], $otp, 'DangKy');
        $_SESSION['register_otp_info'] = $otpRes;

        $this->setFlash('success', "Mã OTP mới đã được gửi lại tới email: " . htmlspecialchars($d['email']));
        $this->redirect('index.php?c=auth&a=verifyRegisterOtp');
    }

    public function logout(): void {
        unset($_SESSION['user']);
        session_destroy();
        session_start();
        $this->setFlash('info', 'Bạn đã đăng xuất an toàn khỏi hệ thống.');
        $this->redirect('index.php?c=auth&a=login');
    }

    public function profile(): void {
        $this->requireLogin();
        $user = $this->userModel->findById((int)$_SESSION['user']['ma_nd']);
        $this->render('auth/profile', ['user' => $user]);
    }

    public function updateProfile(): void {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maNd    = (int)$_SESSION['user']['ma_nd'];
            $name    = trim($_POST['ho_ten'] ?? '');
            $phone   = trim($_POST['sdt'] ?? '');
            $email   = trim($_POST['email'] ?? '');
            $address = trim($_POST['dia_chi'] ?? '');

            if ($this->userModel->updateProfile($maNd, $name, $phone, $email, $address)) {
                // Cập nhật session
                $_SESSION['user'] = $this->userModel->findById($maNd);
                $this->setFlash('success', 'Cập nhật thông tin cá nhân thành công!');
            } else {
                $this->setFlash('danger', 'Cập nhật thông tin thất bại.');
            }
        }
        $this->redirect('index.php?c=auth&a=profile');
    }

    public function changePassword(): void {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maNd       = (int)$_SESSION['user']['ma_nd'];
            $curPass    = trim($_POST['current_password'] ?? '');
            $newPass    = trim($_POST['new_password'] ?? '');
            $confirmPass= trim($_POST['confirm_password'] ?? '');

            if ($newPass !== $confirmPass) {
                $this->setFlash('danger', 'Mật khẩu xác nhận không khớp!');
                $this->redirect('index.php?c=auth&a=profile');
            }

            $user = $this->userModel->findById($maNd);
            $valid = password_verify($curPass, $user['mat_khau']) || ($curPass === $user['mat_khau']);

            if (!$valid) {
                $this->setFlash('danger', 'Mật khẩu hiện tại không chính xác!');
                $this->redirect('index.php?c=auth&a=profile');
            }

            if ($this->userModel->changePassword($maNd, $newPass)) {
                $this->setFlash('success', 'Đổi mật khẩu thành công!');
            } else {
                $this->setFlash('danger', 'Đổi mật khẩu thất bại.');
            }
        }
        $this->redirect('index.php?c=auth&a=profile');
    }

    public function createStaff(): void {
        $this->requireRole(['QuanLy']);
        $staffList = $this->userModel->getAllStaff();
        $this->render('auth/create_staff', ['staffList' => $staffList]);
    }

    /**
     * Màn hình yêu cầu Quên mật khẩu
     */
    public function forgotPassword(): void {
        if ($this->isLoggedIn()) {
            $this->redirect('index.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            if (empty($email)) {
                $this->setFlash('danger', 'Vui lòng nhập địa chỉ Email của tài khoản.');
                $this->render('auth/forgot_password', [], 'none');
                return;
            }

            $user = $this->userModel->findByEmail($email);
            if (!$user) {
                $this->setFlash('danger', "Không tìm thấy tài khoản nào gắn với email: {$email}. Vui lòng kiểm tra lại.");
                $this->render('auth/forgot_password', ['email' => $email], 'none');
                return;
            }

            // Sinh mã OTP 6 số
            $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $_SESSION['reset_password_pending'] = [
                'user'       => $user,
                'otp'        => $otp,
                'expires_at' => time() + 300 // 5 phút
            ];

            require_once __DIR__ . '/../models/EmailService.php';
            $emailService = new EmailService();
            $otpRes = $emailService->sendOtpEmail($user['email'], $user['ho_ten'], $otp, 'QuenMatKhau');
            $_SESSION['reset_otp_info'] = $otpRes;

            $notice = (!empty($otpRes['sent_smtp']))
                ? "Mã OTP đặt lại mật khẩu đã được gửi thẳng tới Gmail: {$email}. Vui lòng kiểm tra hộp thư."
                : "Mã xác thực OTP đã được gửi đến email: {$email}. Vui lòng nhập mã để đặt lại mật khẩu mới.";
            $this->setFlash('info', $notice);
            $this->redirect('index.php?c=auth&a=resetPassword');
            return;
        }

        $this->render('auth/forgot_password', [], 'none');
    }

    /**
     * Màn hình nhập mã OTP và Đặt lại mật khẩu mới
     */
    public function resetPassword(): void {
        if ($this->isLoggedIn()) {
            $this->redirect('index.php');
        }

        if (empty($_SESSION['reset_password_pending'])) {
            $this->setFlash('warning', 'Yêu cầu khôi phục mật khẩu đã hết hạn. Vui lòng thử lại.');
            $this->redirect('index.php?c=auth&a=forgotPassword');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $otpInput        = preg_replace('/\D/', '', trim($_POST['otp'] ?? ''));
            $newPassword     = trim($_POST['new_password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');
            $pending         = $_SESSION['reset_password_pending'];

            if (time() > $pending['expires_at']) {
                $this->setFlash('danger', 'Mã OTP đã hết hiệu lực! Vui lòng bấm "Gửi lại mã OTP" để nhận mã mới.');
            } elseif ($otpInput !== $pending['otp']) {
                $this->setFlash('danger', 'Mã xác thực OTP không chính xác! Vui lòng kiểm tra lại email.');
            } elseif (strlen($newPassword) < 6) {
                $this->setFlash('danger', 'Mật khẩu mới phải có tối thiểu 6 ký tự!');
            } elseif ($newPassword !== $confirmPassword) {
                $this->setFlash('danger', 'Mật khẩu xác nhận không khớp với mật khẩu mới!');
            } else {
                // Hợp lệ -> Đổi mật khẩu trong CSDL
                $user = $pending['user'];
                $this->userModel->changePassword((int)$user['ma_nd'], $newPassword);

                unset($_SESSION['reset_password_pending'], $_SESSION['reset_otp_info']);

                $this->setFlash('success', "🎉 Đặt lại mật khẩu thành công cho tài khoản " . htmlspecialchars($user['email']) . "! Bạn có thể đăng nhập ngay với mật khẩu mới.");
                $this->redirect('index.php?c=auth&a=login');
                return;
            }
        }

        $pending = $_SESSION['reset_password_pending'];
        $otpInfo = $_SESSION['reset_otp_info'] ?? null;
        $this->render('auth/reset_password', [
            'pending' => $pending,
            'otpInfo' => $otpInfo
        ], 'none');
    }

    /**
     * Gửi lại mã OTP khôi phục mật khẩu
     */
    public function resendResetPasswordOtp(): void {
        if (empty($_SESSION['reset_password_pending'])) {
            $this->redirect('index.php?c=auth&a=forgotPassword');
        }

        $user = $_SESSION['reset_password_pending']['user'];
        $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $_SESSION['reset_password_pending']['otp'] = $otp;
        $_SESSION['reset_password_pending']['expires_at'] = time() + 300;

        require_once __DIR__ . '/../models/EmailService.php';
        $emailService = new EmailService();
        $otpRes = $emailService->sendOtpEmail($user['email'], $user['ho_ten'], $otp, 'QuenMatKhau');
        $_SESSION['reset_otp_info'] = $otpRes;

        $this->setFlash('success', "Mã OTP mới đã được gửi lại tới email: " . htmlspecialchars($user['email']));
        $this->redirect('index.php?c=auth&a=resetPassword');
    }

    public function storeStaff(): void {
        $this->requireRole(['QuanLy']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name     = trim($_POST['ho_ten'] ?? '');
            $phone    = trim($_POST['sdt'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $role     = trim($_POST['vai_tro'] ?? 'TiepNhan');
            $position = trim($_POST['chuc_vu'] ?? 'Nhân viên');
            $salary   = (float)($_POST['luong_co_ban'] ?? 0);

            if (empty($name) || empty($phone) || empty($email) || empty($password)) {
                $this->setFlash('danger', 'Vui lòng điền đầy đủ thông tin nhân viên.');
                $this->redirect('index.php?c=auth&a=createStaff');
            }

            if ($this->userModel->findByEmail($email)) {
                $this->setFlash('danger', 'Email nhân viên đã tồn tại!');
                $this->redirect('index.php?c=auth&a=createStaff');
            }

            try {
                $this->userModel->createStaff($name, $phone, $email, $password, $role, $position, $salary);
                $this->setFlash('success', "Thêm tài khoản nhân viên {$name} ({$role}) thành công!");
            } catch (Exception $e) {
                $this->setFlash('danger', 'Lỗi tạo nhân viên: ' . $e->getMessage());
            }
        }
        $this->redirect('index.php?c=auth&a=createStaff');
    }
}

