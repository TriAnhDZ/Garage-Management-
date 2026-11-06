<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Phần mềm Quản lý Gara Ô tô</title>
    <link rel="stylesheet" href="assets/fontawesomefree/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <style>
        body {
            font-family: 'Roboto', 'Segoe UI', Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 440px;
            padding: 35px 30px;
            border-radius: 14px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.25);
            color: #333;
        }
        .login-header {
            text-align: center;
            margin-bottom: 24px;
        }
        .login-header i {
            font-size: 2.8rem;
            color: #4834d4;
            margin-bottom: 8px;
        }
        .login-header h2 {
            margin: 0;
            font-size: 1.55rem;
            font-weight: 700;
            color: #2c3e50;
        }
        .login-header p {
            margin: 5px 0 0;
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        .btn-back-home {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 15px;
            background: #f1f5f9;
            color: #334155;
            font-size: 0.85rem;
            font-weight: 700;
            border-radius: 20px;
            text-decoration: none;
            border: 1px solid #cbd5e1;
            transition: all 0.2s ease;
        }
        .btn-back-home:hover {
            background: #4834d4;
            color: #ffffff;
            border-color: #4834d4;
            transform: translateX(-3px);
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }
        .shake-error {
            animation: shake 0.4s ease-in-out;
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.25) !important;
        }
        .btn-loading {
            pointer-events: none;
            opacity: 0.85;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <a href="index.php" class="btn-back-home" title="Trở về trang chủ Garage Pro">
                <i class="fas fa-arrow-left"></i> Quay lại trang chủ
            </a>
            <span style="font-size: 0.78rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">GARAGE PRO HÀ NỘI</span>
        </div>

        <div class="login-header">
            <i class="fas fa-car-side"></i>
            <h2>QUẢN LÝ GARA Ô TÔ</h2>
            <p>Đăng nhập vào hệ thống điều hành & Cổng dịch vụ</p>
        </div>

        <?php if (!empty($_SESSION['flash'])): ?>
            <?php 
                $f = $_SESSION['flash']; 
                unset($_SESSION['flash']); 
                $alertType = ($f['type'] === 'error') ? 'danger' : $f['type'];
            ?>
            <div class="alert alert-<?= $alertType ?>" id="message_container" style="border-radius: 8px; font-size: 0.92rem; line-height: 1.5; padding: 12px 15px; margin-bottom: 20px;">
                <i class="fas <?= ($alertType === 'danger') ? 'fa-exclamation-circle' : 'fa-info-circle' ?>"></i>
                <?= htmlspecialchars($f['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Form đăng nhập Email & Mật khẩu chuẩn xác thực -->
        <form method="POST" action="index.php?c=auth&a=login" id="form-password" onsubmit="handleLoginSubmit(event, 'btn-submit-login')">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Địa chỉ Email:</label>
                <input type="email" id="email" name="email" class="form-control <?= (!empty($errorField) && $errorField === 'email') ? 'shake-error' : '' ?>" value="<?= htmlspecialchars($email ?? '') ?>" placeholder="name@example.com" required autocomplete="email" <?= (empty($email)) ? 'autofocus' : '' ?>>
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label for="password" style="margin: 0;"><i class="fas fa-lock"></i> Mật khẩu:</label>
                    <a href="index.php?c=auth&a=forgotPassword" style="color: #2563eb; font-size: 0.86rem; font-weight: 600; text-decoration: none;">
                        Quên mật khẩu?
                    </a>
                </div>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" class="form-control <?= (!empty($errorField) && $errorField === 'password') ? 'shake-error' : '' ?>" placeholder="Nhập mật khẩu của bạn..." style="padding-right: 42px;" required autocomplete="current-password" <?= (!empty($errorField) && $errorField === 'password') ? 'autofocus' : '' ?>>
                    <button type="button" onclick="togglePasswordVisibility('password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer; padding: 6px; font-size: 1rem;" title="Hiện/Ẩn mật khẩu">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <?php if (!empty($errorField) && $errorField === 'password'): ?>
                    <small style="color: #ef4444; font-weight: 600; margin-top: 5px; display: block;">
                        <i class="fas fa-exclamation-circle"></i> Mật khẩu không chính xác. Bạn có thể bấm <a href="index.php?c=auth&a=forgotPassword" style="color: #2563eb; text-decoration: underline;">Quên mật khẩu?</a> để đặt lại mật khẩu mới.
                    </small>
                <?php endif; ?>
            </div>

            <div style="margin-top: 22px;">
                <button type="submit" id="btn-submit-login" class="btn btn-primary btn-block" style="padding: 12px; font-size: 1rem; font-weight: 700; border-radius: 8px;">
                    <i class="fas fa-sign-in-alt"></i> ĐĂNG NHẬP
                </button>
            </div>
        </form>

        <div style="text-align: center; margin-top: 22px; padding-top: 18px; border-top: 1px solid #e2e8f0; font-size: 0.92rem; color: #64748b;">
            Chưa có tài khoản? <a href="index.php?c=auth&a=register" style="color: #4834d4; font-weight: 700; text-decoration: none;">Đăng ký ngay</a>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function handleLoginSubmit(e, btnId) {
            const btn = document.getElementById(btnId);
            if (btn) {
                btn.disabled = true;
                btn.classList.add('btn-loading');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang đăng nhập...';
            }
        }
    </script>
</body>
</html>
