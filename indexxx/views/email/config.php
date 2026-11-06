<div class="row">
    <div class="col-12 col-m-7">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fab fa-google" style="color: #ea4335;"></i>
                    <span>Cấu hình tài khoản Gmail gửi đi (Google SMTP)</span>
                </h3>
                <a href="index.php?c=email&a=index" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại Hộp thư
                </a>
            </div>

            <div class="card-content">
                <p style="color: #747d8c; margin-bottom: 20px;">
                    Cấu hình tài khoản Google Gmail của bạn để hệ thống tự động gửi email hóa đơn điện tử, thông báo hoàn tất sửa xe và xác nhận lịch hẹn trực tiếp đến hộp thư khách hàng.
                </p>

                <form method="POST" action="index.php?c=email&a=config">
                    <?= csrf_field() ?>
                    <input type="hidden" name="save_config" value="1">

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 6px;">Trạng thái gửi Gmail tự động:</label>
                        <select name="gmail_smtp_status" class="form-control">
                            <option value="Bat" <?= ($config['gmail_smtp_status'] === 'Bat') ? 'selected' : '' ?>>🟢 BẬT - Tự động kết nối Google SMTP gửi thẳng vào hộp thư khách</option>
                            <option value="Tat" <?= ($config['gmail_smtp_status'] === 'Tat') ? 'selected' : '' ?>>⚪ TẮT - Chỉ lưu vào nhật ký và mở thủ công qua Gmail Web</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 6px;">Tên hiển thị người gửi (Sender Name):</label>
                        <input type="text" name="gmail_smtp_sender_name" class="form-control" 
                               value="<?= htmlspecialchars($config['gmail_smtp_sender_name']) ?>" 
                               placeholder="VD: GARAGE PRO - TRUNG TÂM BẢO HÀNH Ô TÔ" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 6px;">Địa chỉ Gmail gửi đi (Email Google):</label>
                        <input type="email" name="gmail_smtp_user" class="form-control" 
                               value="<?= htmlspecialchars($config['gmail_smtp_user']) ?>" 
                               placeholder="VD: garapro.uit@gmail.com">
                        <small style="color: #747d8c;">Tài khoản Gmail chính của chủ Gara hoặc bộ phận CSKH.</small>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 6px;">
                            Mật khẩu ứng dụng Google (App Password - 16 ký tự):
                        </label>
                        <input type="password" name="gmail_smtp_pass" class="form-control" 
                               value="<?= htmlspecialchars($config['gmail_smtp_pass']) ?>" 
                               placeholder="VD: abcd efgh ijkl mnop">
                        <small style="color: #e53e3e;">
                            * Lưu ý: Google yêu cầu dùng <b>Mật khẩu ứng dụng (App Password)</b> gồm 16 ký tự, <u>không</u> dùng mật khẩu đăng nhập Gmail thông thường.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
                        <i class="fas fa-save"></i> Lưu cấu hình Gmail
                    </button>
                </form>
            </div>
        </div>

        <!-- Card thử nghiệm gửi email -->
        <div class="card" style="margin-top: 25px;">
            <div class="card-header">
                <h4 style="margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-paper-plane" style="color: #2ed573;"></i>
                    <span>Thử nghiệm gửi thư thực tế (Test Real SMTP)</span>
                </h4>
            </div>
            <div class="card-content">
                <p style="color: #747d8c; margin-bottom: 15px;">
                    Nhập địa chỉ email bất kỳ (ví dụ Gmail cá nhân của bạn) để kiểm tra xem hệ thống có kết nối và gửi thư thành công qua máy chủ Google hay không.
                </p>

                <form method="POST" action="index.php?c=email&a=config">
                    <?= csrf_field() ?>
                    <input type="hidden" name="send_test" value="1">
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <input type="email" name="test_email" class="form-control" style="flex: 1; min-width: 250px;" 
                               placeholder="Nhập email nhận thử (VD: test@gmail.com)" required>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-vial"></i> Gửi thử ngay
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cột bên phải: Hướng dẫn lấy mật khẩu ứng dụng Google -->
    <div class="col-12 col-m-5">
        <div class="card" style="border-top: 4px solid #4834d4;">
            <div class="card-header">
                <h4 style="margin: 0; display: flex; align-items: center; gap: 8px; color: #4834d4;">
                    <i class="fas fa-info-circle"></i>
                    <span>Hướng dẫn lấy Mật khẩu ứng dụng Google</span>
                </h4>
            </div>
            <div class="card-content" style="font-size: 0.92rem; line-height: 1.6;">
                <ol style="padding-left: 20px; margin-bottom: 15px;">
                    <li style="margin-bottom: 8px;">
                        Đăng nhập tài khoản Google của bạn tại: 
                        <a href="https://myaccount.google.com/" target="_blank" style="color: #1a73e8; font-weight: 500;">myaccount.google.com</a>
                    </li>
                    <li style="margin-bottom: 8px;">
                        Chọn mục <b>Bảo mật (Security)</b> ở thanh bên trái.
                    </li>
                    <li style="margin-bottom: 8px;">
                        Đảm bảo <b>Xác minh 2 bước (2-Step Verification)</b> đang ở trạng thái <b>BẬT</b>.
                    </li>
                    <li style="margin-bottom: 8px;">
                        Tìm kiếm mục <b>Mật khẩu ứng dụng (App passwords)</b> (hoặc truy cập trực tiếp: 
                        <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color: #1a73e8; font-weight: 500;">myaccount.google.com/apppasswords</a>).
                    </li>
                    <li style="margin-bottom: 8px;">
                        Nhập tên ứng dụng là: <code>GaragePro</code> và nhấn <b>Tạo (Create)</b>.
                    </li>
                    <li style="margin-bottom: 8px;">
                        Google sẽ hiển thị 1 đoạn mã 16 chữ cái (dạng: <code>abcd efgh ijkl mnop</code>). Hãy sao chép và dán vào ô <b>Mật khẩu ứng dụng</b> bên trái rồi nhấn Lưu.
                    </li>
                </ol>

                <div style="background: #fff8e1; border-left: 4px solid #ffb300; padding: 12px; border-radius: 4px; font-size: 0.88rem;">
                    <b>💡 Mẹo:</b> Nếu bạn chưa cấu hình SMTP, bạn hoàn toàn có thể dùng nút <b>"Mở Gmail Web"</b> để mở thẳng trang soạn thư của Google (mail.google.com) với nội dung đã được điền sẵn 100%!
                </div>
            </div>
        </div>
    </div>
</div>

