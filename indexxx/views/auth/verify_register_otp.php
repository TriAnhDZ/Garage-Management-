<?php
    $email = $pending['data']['email'] ?? 'bạn';
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
    <title>Xác thực mã OTP - Garage Pro</title>
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
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 20px;
        }
        .otp-card {
            background: #ffffff;
            width: 100%;
            max-width: 480px;
            padding: 35px 30px;
            border-radius: 14px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.25);
            color: #333;
            text-align: center;
        }
        .otp-icon-wrap {
            width: 80px;
            height: 80px;
            margin: 0 auto 18px;
            background: #ecfdf5;
            color: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.4rem;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.2);
        }
        .otp-input {
            width: 100%;
            max-width: 280px;
            height: 56px;
            font-size: 2rem;
            font-weight: 800;
            text-align: center;
            letter-spacing: 12px;
            font-family: 'Courier New', Courier, monospace;
            border: 2px solid #cbd5e1;
            border-radius: 10px;
            color: #1e293b;
            background: #f8fafc;
            margin: 15px auto;
            display: block;
            transition: all 0.2s;
        }
        .otp-input:focus {
            border-color: #2ed573;
            background: #ffffff;
            outline: none;
            box-shadow: 0 0 0 4px rgba(46, 213, 115, 0.2);
        }
        .countdown {
            font-weight: 700;
            color: #dc2626;
        }
    </style>
</head>
<body>
    <div class="otp-card">
        <div class="otp-icon-wrap">
            <i class="fas fa-shield-alt"></i>
        </div>

        <h2 style="margin: 0 0 8px; font-size: 1.5rem; color: #0f172a; font-weight: 700;">
            XÁC THỰC MÃ OTP
        </h2>
        <p style="margin: 0 0 15px; color: #64748b; font-size: 0.93rem;">
            Hệ thống đã gửi mã OTP 6 chữ số đến email:<br>
            <b style="color: #1e3c72;"><?= htmlspecialchars($pending['data']['email']) ?></b>
        </p>

        <?php if (!empty($_SESSION['flash'])): ?>
            <?php 
                $f = $_SESSION['flash']; 
                unset($_SESSION['flash']); 
                $alertType = ($f['type'] === 'error') ? 'danger' : $f['type'];
            ?>
            <div class="alert alert-<?= $alertType ?>" id="message_container" style="text-align: left;">
                <?= htmlspecialchars($f['message']) ?>
            </div>
        <?php endif; ?>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin: 18px 0; text-align: left; font-size: 0.88rem; color: #475569;">
            <div style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 5px;">
                <i class="fas fa-envelope-open-text" style="color: #10b981; font-size: 1.1rem;"></i>
                <span>Kiểm tra hộp thư đến</span>
            </div>
            <div style="line-height: 1.5;">
                Mã xác thực đã được gửi tới email của bạn. Vui lòng kiểm tra hộp thư (bao gồm cả mục <b>Spam / Thư rác</b>) và nhập mã vào bên dưới để hoàn tất đăng ký.
            </div>
        </div>

        <form method="POST" action="index.php?c=auth&a=verifyRegisterOtp">
            <?= csrf_field() ?>
            <label for="otp" style="font-weight: 600; color: #334155; font-size: 0.9rem; display: block;">
                Nhập mã xác thực gồm 6 chữ số:
            </label>
            <input type="text" id="otp" name="otp" class="otp-input" maxlength="6" pattern="[0-9]{6}" placeholder="------" autocomplete="one-time-code" autofocus required>

            <div style="margin-bottom: 18px; color: #64748b; font-size: 0.85rem;">
                ⏱️ Mã có hiệu lực trong: <span id="countdown" class="countdown"><?= ($remainingSeconds > 0) ? $initTimeText : 'Hết hạn! Vui lòng bấm Gửi lại mã' ?></span>
            </div>

            <button type="submit" class="btn btn-success btn-block" style="padding: 12px; font-size: 1.05rem; font-weight: 700; border-radius: 8px;">
                <i class="fas fa-check-circle"></i> XÁC NHẬN & HOÀN TẤT ĐĂNG KÝ
            </button>
        </form>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 0.88rem;">
            <a href="index.php?c=auth&a=resendRegisterOtp" style="color: #2563eb; font-weight: 600; text-decoration: none;" onclick="this.innerHTML='⏳ Đang gửi lại...';">
                <i class="fas fa-redo"></i> Gửi lại mã OTP
            </a>
            <a href="index.php?c=auth&a=register" style="color: #64748b; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Sửa thông tin
            </a>
        </div>
    </div>

    <script>
        // Đếm ngược theo thời gian thực tế còn lại từ server
        let timeLeft = <?= (int)$remainingSeconds ?>;
        const countdownEl = document.getElementById('countdown');
        if (timeLeft <= 0) {
            countdownEl.innerText = "Hết hạn! Vui lòng bấm Gửi lại mã";
        } else {
            const timer = setInterval(function() {
                timeLeft--;
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    countdownEl.innerText = "Hết hạn! Vui lòng bấm Gửi lại mã";
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

