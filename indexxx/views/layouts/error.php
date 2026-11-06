<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo sự cố - Hệ thống Quản lý Gara Ô tô</title>
    <link rel="stylesheet" href="assets/fontawesomefree/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            font-family: 'Roboto', 'Segoe UI', Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-card {
            background: #ffffff;
            width: 100%;
            max-width: 600px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
            border-top: 5px solid #ef4444;
        }
        .error-header {
            padding: 25px 25px 15px;
            text-align: center;
        }
        .error-icon {
            width: 70px;
            height: 70px;
            background: #fee2e2;
            color: #ef4444;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 15px;
        }
        .error-title {
            margin: 0 0 8px;
            font-size: 1.4rem;
            color: #0f172a;
            font-weight: 700;
        }
        .error-subtitle {
            margin: 0;
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .error-body {
            padding: 15px 25px 25px;
        }
        .error-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 700;
            text-decoration: none;
            font-size: 0.92rem;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .btn-primary-action {
            background: #4834d4;
            color: #ffffff;
        }
        .btn-primary-action:hover {
            background: #3c2bb7;
        }
        .btn-secondary-action {
            background: #e2e8f0;
            color: #334155;
        }
        .btn-secondary-action:hover {
            background: #cbd5e1;
        }
        .error-debug {
            margin-top: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 0.85rem;
        }
        .error-debug summary {
            cursor: pointer;
            font-weight: 700;
            color: #64748b;
            outline: none;
        }
        .error-debug pre {
            margin: 10px 0 0;
            white-space: pre-wrap;
            word-break: break-all;
            background: #1e293b;
            color: #f87171;
            padding: 10px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-header">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 class="error-title">Đã xảy ra sự cố khi xử lý</h2>
            <p class="error-subtitle">
                Yêu cầu của bạn tạm thời chưa được hoàn tất do một lỗi xử lý dữ liệu. Hệ thống đã tự động ghi nhận nhật ký để kỹ thuật viên kiểm tra.
            </p>
        </div>

        <div class="error-body">
            <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 12px 15px; border-radius: 4px; font-size: 0.9rem; color: #991b1b;">
                <b><i class="fas fa-shield-alt"></i> Bảo đảm dữ liệu:</b> Toàn bộ thông tin trước đó trong cơ sở dữ liệu của bạn vẫn an toàn và không bị thay đổi.
            </div>

            <div class="error-actions">
                <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.href='index.php';" class="btn-action btn-secondary-action">
                    <i class="fas fa-arrow-left"></i> Quay lại trang trước
                </button>
                <a href="index.php" class="btn-action btn-primary-action">
                    <i class="fas fa-home"></i> Về trang chủ Garage Pro
                </a>
            </div>

            <?php if (isset($e)): ?>
            <details class="error-debug">
                <summary><i class="fas fa-code"></i> Chi tiết kỹ thuật dành cho Kỹ thuật viên / Lập trình viên</summary>
                <pre><?= htmlspecialchars(get_class($e) . ": " . $e->getMessage() . "\nTại file: " . $e->getFile() . " (dòng " . $e->getLine() . ")") ?></pre>
            </details>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

