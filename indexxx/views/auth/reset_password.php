<?php
    $expiresAt = (int)($pending['expires_at'] ?? (time() + 300));
    $remainingSeconds = max(0, $expiresAt - time());
    $initM = floor($remainingSeconds / 60);
    $initS = $remainingSeconds % 60;
    $initTimeText = sprintf('%02d:%02d', $initM, $initS);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - Garage Pro</title>
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
        .reset-card {
            background: #ffffff;
            width: 100%;
            max-width: 480px;
            padding: 35px 30px;
            border-radius: 14px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.25);
            color: #333;
        }
        .reset-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .otp-icon-wrap {
            width: 76px;
            height: 76px;
            margin: 0 auto 15px;
            background: #fef3c7;
            color: #d97706;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            box-shadow: 0 8px 20px rgba(217, 119, 6, 0.2);
        }
        .otp-input {
            width: 100%;
            max-width: 260px;
            height: 52px;
            font-size: 1.8rem;
            font-weight: 800;
            text-align: center;
            letter-spacing: 10px;
            font-family: 'Courier New', Courier, monospace;
            border: 2px solid #cbd5e1;
            border-radius: 10px;
            color: #1e293b;
            background: #f8fafc;
            margin: 8px auto 14px;
            display: block;
            transition: all 0.2s;
        }
        .otp-input:focus {
            border-color: #f59e0b;
            background: #ffffff;
            outline: none;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.2);
        }
        .countdown {
            font-weight: 700;
            color: #dc2626;
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <div class="otp-icon-wrap">
            <i class="fas fa-lock-open"></i>
        </div>

        <div class="reset-header">
            <h2 style="margin: 0 0 6px; font-size: 1.5rem; color: #0f172a; font-weight: 700;">
                ĐẶT LẠI MẬT KHẨU
            </h2>
            <p style="margin: 0; color: #64748b; font-size: 0.92rem;">
                Tài khoản: <b style="color: #d97706;"><?= htmlspecialchars($pending['user']['email']) ?></b>
            </p>
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

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin: 15px 0; text-align: left; font-size: 0.88rem; color: #475569;">
            <div style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 5px;">
                <i class="fas fa-envelope-open-text" style="color: #d97706; font-size: 1.1rem;"></i>
                <span>Kiểm tra email của bạn</span>
            </div>
            <div style="line-height: 1.5;">
                Mã OTP xác thực đã được gửi tới email <b><?= htmlspecialchars($pending['user']['email']) ?></b>. Vui lòng kiểm tra hộp thư (bao gồm cả thư mục Spam) và nhập mã vào bên dưới.
            </div>
        </div>

        <form method="POST" action="index.php?c=auth&a=resetPassword">
            <?= csrf_field() ?>
            <div class="form-group" style="text-align: center;">
                <label for="otp" style="font-weight: 700; color: #334155; font-size: 0.9rem; display: block;">
                    Nhập mã xác thực OTP (6 chữ số):
                </label>
                <input type="text" id="otp" name="otp" class="otp-input" maxlength="6" pattern="[0-9]{6}" placeholder="------" autocomplete="one-time-code" autofocus required>
                <div style="color: #64748b; font-size: 0.82rem;">
                    ⏱️ Hiệu lực: <span id="countdown" class="countdown"><?= ($remainingSeconds > 0) ? $initTimeText : 'Hết hạn! Bấm Gửi lại mã' ?></span>
                </div>
            </div>

            <div class="form-group">
                <label for="new_password"><i class="fas fa-key"></i> Mật khẩu mới:</label>
                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Tối thiểu 6 ký tự..." required>
            </div>

            <div class="form-group">
                <label for="confirm_password"><i class="fas fa-check-double"></i> Xác nhận mật khẩu mới:</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu mới..." required>
            </div>

            <div style="margin-top: 22px;">
                <button type="submit" class="btn btn-warning btn-block" style="padding: 12px; font-size: 1rem; font-weight: 700; border-radius: 8px; color: #1e293b;">
                    <i class="fas fa-save"></i> ĐẶT LẠI MẬT KHẨU &amp; HOÀN TẤT
                </button>
            </div>
        </form>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 0.88rem;">
            <a href="index.php?c=auth&a=resendResetPasswordOtp" style="color: #d97706; font-weight: 600; text-decoration: none;" onclick="this.innerHTML='⏳ Đang gửi lại...';">
                <i class="fas fa-redo"></i> Gửi lại mã OTP
            </a>
            <a href="index.php?c=auth&a=login" style="color: #64748b; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Quay lại Đăng nhập
            </a>
        </div>
    </div>

    <script>
        // Đếm ngược theo thời gian thực tế còn lại từ server
        let timeLeft = <?= (int)$remainingSeconds ?>;
        const countdownEl = document.getElementById('countdown');
        if (timeLeft <= 0) {
            countdownEl.innerText = "Hết hạn! Bấm Gửi lại mã";
        } else {
            const timer = setInterval(function() {
                timeLeft--;
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    countdownEl.innerText = "Hết hạn! Bấm Gửi lại mã";
                } else {
                    let m = Math.floor(timeLeft / 60);
                    let s = timeLeft % 60;
                    countdownEl.innerText = (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
                }
            }, 1000);
        }
    </script>
</body>
</html>

