<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($email['tieu_de']) ?> - Google Gmail</title>
    <link rel="stylesheet" href="assets/fontawesomefree/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', Arial, sans-serif;
            background-color: #f6f8fc;
            color: #202124;
        }
        .gmail-topbar {
            background: #ffffff;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            border-bottom: 1px solid #e0e0e0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .gmail-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 20px;
            color: #ea4335;
            font-weight: 700;
        }
        .gmail-container {
            max-width: 850px;
            margin: 25px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(60,64,67,0.3), 0 4px 8px 3px rgba(60,64,67,0.15);
            padding: 30px;
        }
        .gmail-subject {
            font-size: 22px;
            font-weight: 600;
            color: #202124;
            margin: 0 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .gmail-sender-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f1f3f4;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .sender-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #130f40;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-right: 14px;
        }
        .sender-info {
            display: flex;
            align-items: center;
        }
        .sender-name {
            font-weight: 700;
            color: #202124;
        }
        .sender-email {
            color: #5f6368;
            font-size: 13px;
        }
        .recipient-info {
            font-size: 12px;
            color: #5f6368;
            margin-top: 2px;
        }
        .mail-date {
            color: #5f6368;
            font-size: 13px;
        }
        .mail-content-wrapper {
            padding: 10px 0;
        }
        .gmail-actions {
            display: flex;
            gap: 10px;
        }
        .btn-gmail {
            background: #f1f3f4;
            border: none;
            padding: 8px 14px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            color: #3c4043;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-gmail:hover {
            background: #e8eaed;
        }
        .btn-gmail-red {
            background: #ea4335 !important;
            color: #ffffff !important;
            font-weight: 500;
        }
        .btn-gmail-red:hover {
            background: #d93025 !important;
            box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15);
        }
        .banner-redirect {
            background: #e8f0fe;
            border: 1px solid #d2e3fc;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }
    </style>
</head>
<body>

    <div class="gmail-topbar">
        <div class="gmail-logo">
            <i class="fab fa-google"></i>
            <span style="color: #202124;">Google</span>
            <span style="color: #ea4335; font-weight: 400;">Gmail</span>
        </div>
        <div class="gmail-actions">
            <?php if (!empty($gmailWebUrl)): ?>
                <a href="<?= $gmailWebUrl ?>" target="_blank" class="btn-gmail btn-gmail-red">
                    <i class="fab fa-google"></i> Mở & gửi trên Gmail Web (mail.google.com)
                </a>
            <?php endif; ?>
            <button onclick="window.print()" class="btn-gmail">
                <i class="fas fa-print"></i> In email
            </button>
            <button onclick="window.close()" class="btn-gmail">
                <i class="fas fa-times"></i> Đóng
            </button>
        </div>
    </div>

    <div class="gmail-container">
        <!-- Banner thông báo chuyển tiếp -->
        <?php if (!empty($gmailWebUrl)): ?>
            <div class="banner-redirect">
                <div style="font-size: 13.5px; color: #1a73e8; line-height: 1.5;">
                    <i class="fas fa-paper-plane" style="margin-right: 6px;"></i>
                    <b>Bạn muốn gửi thư này từ tài khoản cá nhân của mình?</b> Bấm nút màu đỏ để mở trực tiếp trang soạn thư của <b>Gmail Web</b> đã điền sẵn 100% nội dung.
                </div>
                <a href="<?= $gmailWebUrl ?>" target="_blank" class="btn-gmail btn-gmail-red" style="white-space: nowrap;">
                    <i class="fab fa-google"></i> Soạn trên Gmail Web
                </a>
            </div>
        <?php endif; ?>

        <div class="gmail-subject">
            <span><?= htmlspecialchars($email['tieu_de']) ?></span>
            <span style="background: #e8f0fe; color: #1a73e8; font-size: 12px; font-weight: 500; padding: 4px 10px; border-radius: 4px;">
                Hộp thư đến
            </span>
        </div>

        <div class="gmail-sender-box">
            <div class="sender-info">
                <div class="sender-avatar">
                    <i class="fas fa-car-side"></i>
                </div>
                <div>
                    <div>
                        <span class="sender-name">GARAGE PRO</span>
                        <span class="sender-email">&lt;noreply@garapro.vn&gt;</span>
                    </div>
                    <div class="recipient-info">
                        tới: <b><?= htmlspecialchars($email['ten_nguoi_nhan']) ?></b> &lt;<?= htmlspecialchars($email['email_nguoi_nhan']) ?>&gt;
                    </div>
                </div>
            </div>

            <div class="mail-date">
                <?= date('H:i - d \t\h\g m, Y', strtotime($email['ngay_gui'])) ?>
            </div>
        </div>

        <div class="mail-content-wrapper">
            <!-- Thân Email HTML thực tế -->
            <?= $email['noi_dung_html'] ?>
        </div>
    </div>

</body>
</html>
