<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản - Quản lý Gara Ô tô</title>
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
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 20px;
        }
        .register-card {
            background: #ffffff;
            width: 100%;
            max-width: 490px;
            padding: 35px 30px;
            border-radius: 14px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.25);
            color: #333;
        }
        .register-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .register-header i {
            font-size: 2.8rem;
            color: #2ed573;
            margin-bottom: 8px;
        }
        .register-header h2 {
            margin: 0;
            font-size: 1.55rem;
            font-weight: 700;
            color: #2c3e50;
        }
        .register-header p {
            margin: 5px 0 0;
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        .otp-security-note {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.84rem;
            color: #166534;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
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
            background: #1e3c72;
            color: #ffffff;
            border-color: #1e3c72;
            transform: translateX(-3px);
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <a href="index.php" class="btn-back-home" title="Trở về trang chủ Garage Pro">
                <i class="fas fa-arrow-left"></i> Quay lại trang chủ
            </a>
            <span style="font-size: 0.78rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">GARAGE PRO HÀ NỘI</span>
        </div>

        <div class="register-header">
            <i class="fas fa-user-plus"></i>
            <h2>ĐĂNG KÝ TÀI KHOẢN</h2>
            <p>Trở thành thành viên để theo dõi lịch & hồ sơ sửa chữa xe</p>
        </div>

        <div class="otp-security-note">
            <i class="fas fa-shield-alt" style="font-size: 1.3rem; color: #16a34a;"></i>
            <div>
                <b>Bảo mật 2 bước:</b> Hệ thống sẽ gửi mã xác thực <b>OTP 6 chữ số</b> tới Email của bạn để hoàn tất đăng ký.
            </div>
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

        <?php 
            $fd = $formData ?? []; 
            if (empty($fd) && !empty($_SESSION['pending_appointment'])) {
                $pa = $_SESSION['pending_appointment'];
                $fd['ho_ten'] = $pa['ten_khach'] ?? '';
                $fd['sdt']    = $pa['sdt'] ?? '';
                $fd['email']  = $pa['email'] ?? '';
            }
        ?>

        <?php if (!empty($_SESSION['pending_appointment'])): ?>
            <?php $pa = $_SESSION['pending_appointment']; ?>
            <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; color: #166534; font-size: 0.88rem; box-shadow: 0 2px 6px rgba(16,185,129,0.1);">
                <div style="font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-clipboard-check" style="color: #16a34a; font-size: 1.15rem;"></i>
                    <span style="font-size: 0.95rem;">Đã lưu phiếu hẹn xe [<?= htmlspecialchars($pa['bien_so']) ?>]</span>
                </div>
                <div>
                    Hiệu xe: <b><?= htmlspecialchars($pa['hang_xe']) ?></b> &bull; Thời gian: <b><?= date('d/m/Y H:i', strtotime($pa['ngay_hen'])) ?></b>
                </div>
                <div style="margin-top: 5px; font-size: 0.8rem; color: #15803d;">
                    💡 Thông tin họ tên, SĐT và Email đã được tự động điền vào bên dưới. Quý khách chỉ cần đặt mật khẩu để hoàn tất đăng ký!
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?c=auth&a=register">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="ho_ten"><i class="fas fa-user"></i> Họ và tên chủ xe: <span style="color:red">*</span></label>
                <input type="text" id="ho_ten" name="ho_ten" class="form-control" value="<?= htmlspecialchars($fd['ho_ten'] ?? '') ?>" placeholder="Nguyễn Văn A" required>
            </div>

            <div class="form-group">
                <label for="sdt"><i class="fas fa-phone"></i> Số điện thoại: <span style="color:red">*</span></label>
                <input type="text" id="sdt" name="sdt" class="form-control" value="<?= htmlspecialchars($fd['sdt'] ?? '') ?>" placeholder="0912345678" required>
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email (nhận mã OTP): <span style="color:red">*</span></label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($fd['email'] ?? '') ?>" placeholder="nguyenvana@gmail.com" required>
            </div>

            <div class="form-group">
                <label for="dia_chi"><i class="fas fa-map-marker-alt"></i> Địa chỉ:</label>
                <input type="text" id="dia_chi" name="dia_chi" class="form-control" value="<?= htmlspecialchars($fd['dia_chi'] ?? '') ?>" placeholder="Số nhà, đường, quận/huyện...">
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Mật khẩu: <span style="color:red">*</span></label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Tối thiểu 6 ký tự..." required>
            </div>

            <div style="margin-top: 22px;">
                <button type="submit" class="btn btn-success btn-block" style="padding: 12px; font-size: 1rem; font-weight: 700; border-radius: 8px;">
                    <i class="fas fa-paper-plane"></i> TIẾP TỤC &amp; NHẬN MÃ OTP
                </button>
            </div>

            <div style="text-align: center; margin-top: 15px; font-size: 0.9rem;">
                Đã có tài khoản? <a href="index.php?c=auth&a=login" style="color: #1e3c72; font-weight: 700;">Đăng nhập tại đây</a>
            </div>
        </form>
    </div>
</body>
</html>
