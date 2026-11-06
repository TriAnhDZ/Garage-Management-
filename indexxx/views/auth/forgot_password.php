<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Garage Pro</title>
    <link rel="stylesheet" href="assets/fontawesomefree/css/all.min.css">
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
        .forgot-card {
            background: #ffffff;
            width: 100%;
            max-width: 460px;
            padding: 35px 30px;
            border-radius: 14px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.25);
            color: #333;
        }
        .forgot-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .forgot-header i {
            font-size: 2.8rem;
            color: #f59e0b;
            margin-bottom: 8px;
        }
        .forgot-header h2 {
            margin: 0;
            font-size: 1.55rem;
            font-weight: 700;
            color: #2c3e50;
        }
        .forgot-header p {
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
        .test-chip {
            display: inline-block;
            background: #edf2f7;
            padding: 4px 10px;
            margin: 3px 2px;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.8rem;
        }
        .test-chip:hover {
            background: #4834d4;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="forgot-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <a href="index.php?c=auth&a=login" class="btn-back-home" title="Trở lại trang đăng nhập">
                <i class="fas fa-arrow-left"></i> Quay lại Đăng nhập
            </a>
            <span style="font-size: 0.78rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">GARAGE PRO HÀ NỘI</span>
        </div>

        <div class="forgot-header">
            <i class="fas fa-key"></i>
            <h2>QUÊN MẬT KHẨU</h2>
            <p>Khôi phục quyền truy cập tài khoản qua mã OTP Email</p>
        </div>

        <?php if (!empty($_SESSION['flash'])): ?>
            <?php 
                $f = $_SESSION['flash']; 
                unset($_SESSION['flash']); 
                $alertType = ($f['type'] === 'error') ? 'danger' : $f['type'];
            ?>
            <div class="alert alert-<?= $alertType ?>" id="message_container">
                <?= htmlspecialchars($f['message']) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?c=auth&a=forgotPassword">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Nhập Email đăng ký tài khoản:</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars(filter_var($email ?? '', FILTER_SANITIZE_EMAIL), ENT_QUOTES, 'UTF-8') ?>" placeholder="admin@garapro.vn hoặc khachhang@gmail.com" required autofocus>
                <small style="color: #64748b; font-size: 0.82rem; margin-top: 5px; display: block;">
                    Hệ thống sẽ gửi mã xác thực <b>OTP 6 số</b> về email này để bạn đặt lại mật khẩu mới an toàn.
                </small>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-warning btn-block" style="padding: 12px; font-size: 1rem; font-weight: 700; border-radius: 8px; color: #1e293b;">
                    <i class="fas fa-paper-plane"></i> GỬI MÃ OTP VÀO EMAIL
                </button>
            </div>
        </form>

        <div style="text-align: center; margin-top: 18px; font-size: 0.9rem;">
            Nhớ lại mật khẩu? <a href="index.php?c=auth&a=login" style="color: #4834d4; font-weight: 700;">Đăng nhập ngay</a>
        </div>
    </div>
</body>
</html>

