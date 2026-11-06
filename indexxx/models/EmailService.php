<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/SmtpMailer.php';

class EmailService extends BaseModel {

    public function getSmtpConfig(): array {
        $rows = $this->fetchAll(
            "SELECT ten_tham_so, ghi_chu FROM quy_dinh 
             WHERE ten_tham_so IN ('gmail_smtp_user', 'gmail_smtp_pass', 'gmail_smtp_sender_name', 'gmail_smtp_status')"
        );
        $config = [
            'gmail_smtp_user'        => '',
            'gmail_smtp_pass'        => '',
            'gmail_smtp_sender_name' => 'GARAGE PRO',
            'gmail_smtp_status'      => 'Tat'
        ];
        foreach ($rows as $r) {
            $config[$r['ten_tham_so']] = $r['ghi_chu'] ?? '';
        }
        return $config;
    }

    public function saveSmtpConfig(string $user, string $pass, string $senderName, string $status): bool {
        $data = [
            'gmail_smtp_user'        => $user,
            'gmail_smtp_pass'        => $pass,
            'gmail_smtp_sender_name' => $senderName,
            'gmail_smtp_status'      => $status
        ];
        foreach ($data as $k => $v) {
            $this->execute(
                "INSERT INTO quy_dinh (ten_tham_so, gia_tri, ghi_chu) VALUES (?, 0, ?)
                 ON DUPLICATE KEY UPDATE ghi_chu = VALUES(ghi_chu)",
                [$k, $v]
            );
        }
        return true;
    }

    /**
     * Tạo đường dẫn trực tiếp mở màn hình soạn thư Google Gmail (mail.google.com)
     */
    public static function buildGmailWebUrl(string $toEmail, string $subject, string $plainBody): string {
        $params = http_build_query([
            'view' => 'cm',
            'fs'   => '1',
            'to'   => $toEmail,
            'su'   => $subject,
            'body' => $plainBody
        ]);
        return "https://mail.google.com/mail/?" . $params;
    }

    /**
     * Tự động phát hiện đường dẫn PHP CLI trên hệ thống
     */
    public static function detectPhpCli(): string {
        $candidates = [
            'C:\\xampp\\php\\php.exe',
            'C:\\xampp\\php\\php-win.exe',
        ];
        foreach ($candidates as $c) {
            if (file_exists($c)) return $c;
        }
        if (defined('PHP_BINARY') && !empty(PHP_BINARY) && file_exists(PHP_BINARY)) {
            return PHP_BINARY;
        }
        return 'php';
    }

    /**
     * Gửi email mã xác thực OTP (Đăng ký tài khoản / Đăng nhập bảo mật 2FA / Quên mật khẩu)
     */
    public function sendOtpEmail(string $toEmail, string $recipientName, string $otp, string $purpose = 'DangKy'): array {
        if ($purpose === 'DangKy') {
            $actionText = 'đăng ký tài khoản Garage Pro';
            $subject = '🔐 [GARAGE PRO] Mã xác thực OTP đăng ký tài khoản: ' . $otp;
            $headerSub = 'Xác thực kích hoạt tài khoản';
        } elseif ($purpose === 'QuenMatKhau') {
            $actionText = 'khôi phục / đặt lại mật khẩu mới cho tài khoản Garage Pro';
            $subject = '🔑 [GARAGE PRO] Mã xác thực OTP khôi phục mật khẩu: ' . $otp;
            $headerSub = 'Khôi phục mật khẩu tài khoản';
        } else {
            $actionText = 'đăng nhập vào hệ thống Garage Pro';
            $subject = '🛡️ [GARAGE PRO] Mã xác thực OTP đăng nhập: ' . $otp;
            $headerSub = 'Xác thực bảo mật đăng nhập';
        }

        $plainBody = "Kính gửi Quý khách {$recipientName},\r\n\r\n"
                   . "Mã xác thực OTP của Quý khách để {$actionText} là:\r\n\r\n"
                   . "       >>>  {$otp}  <<<\r\n\r\n"
                   . "• Mã này có hiệu lực trong vòng 5 phút.\r\n"
                   . "• Tuyệt đối không cung cấp mã xác thực này cho bất kỳ ai để bảo vệ an toàn thông tin cá nhân.\r\n\r\n"
                   . "Trân trọng,\r\n"
                   . "GARAGE PRO HÀ NỘI\r\n"
                   . "Hotline hỗ trợ: 0825.893.162\r\n";

        $htmlBody = "
        <div style='font-family: Arial, sans-serif; max-width: 550px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; color: #2d3748;'>
            <div style='background: #1e3c72; color: #ffffff; padding: 25px; text-align: center;'>
                <h1 style='margin: 0; font-size: 24px; color: #2ed573;'>GARAGE PRO</h1>
                <p style='margin: 5px 0 0; font-size: 14px; opacity: 0.9;'>{$headerSub}</p>
            </div>
            <div style='padding: 30px 25px;'>
                <p>Xin chào <b>{$recipientName}</b>,</p>
                <p>Bạn vừa yêu cầu <b>{$actionText}</b> tại hệ thống Garage Pro. Dưới đây là mã xác thực OTP bảo mật của bạn:</p>
                
                <div style='text-align: center; margin: 25px 0;'>
                    <div style='display: inline-block; background: #f0fdf4; border: 2px dashed #22c55e; border-radius: 12px; padding: 15px 35px;'>
                        <span style='font-size: 32px; font-weight: 800; letter-spacing: 8px; color: #15803d; font-family: monospace;'>{$otp}</span>
                    </div>
                    <div style='color: #64748b; font-size: 13px; margin-top: 8px;'>⏱️ Mã có hiệu lực trong vòng <b>5 phút</b></div>
                </div>

                <div style='background: #fef2f2; border-left: 4px solid #ef4444; padding: 12px 15px; border-radius: 6px; font-size: 13px; color: #991b1b; margin-bottom: 20px;'>
                    ⚠️ <b>Lưu ý bảo mật:</b> Không cung cấp mã OTP này cho bất kỳ ai, kể cả nhân viên kỹ thuật.
                </div>

                <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;'>
                <p style='font-size: 13px; color: #64748b; margin: 0;'>
                    TRUNG TÂM SỬA CHỮA & BẢO DƯỠNG Ô TÔ GARAGE PRO<br>
                    📍 Địa chỉ: 123 Đường Cầu Giấy, Quận Cầu Giấy, Hà Nội<br>
                    📞 Hotline hỗ trợ 24/7: 0825.893.162
                </p>
            </div>
        </div>";

        // Ghi log vào email_log
        $sql = "INSERT INTO email_log (email_nguoi_nhan, ten_nguoi_nhan, tieu_de, noi_dung_html, loai_email, ma_tham_chieu, ngay_gui, trang_thai) 
                VALUES (?, ?, ?, ?, 'Khac', 0, NOW(), 'DaGui')";
        $this->execute($sql, [$toEmail, $recipientName, $subject, $htmlBody]);

        // Gửi qua SMTP Google thực tế nếu đã cấu hình
        $cfg = $this->getSmtpConfig();
        $sent = false;
        $smtpNote = '';
        if ($cfg['gmail_smtp_status'] === 'Bat' && !empty($cfg['gmail_smtp_user']) && !empty($cfg['gmail_smtp_pass'])) {
            try {
                require_once __DIR__ . '/SmtpMailer.php';
                $mailer = new SmtpMailer();
                $res = $mailer->send(
                    $cfg['gmail_smtp_user'],
                    $cfg['gmail_smtp_pass'],
                    $cfg['gmail_smtp_sender_name'] ?: 'GARAGE PRO',
                    $toEmail,
                    $recipientName,
                    $subject,
                    $htmlBody,
                    'none' // Gửi HTML thuần siêu nhanh trong 1s tới hộp thư người nhận
                );
                $sent = $res['success'];
                $smtpNote = $res['message'] ?? '';
            } catch (Exception $e) {
                $sent = false;
                $smtpNote = $e->getMessage();
            }
        }

        return [
            'success'   => true,
            'status'    => 'DaGui',
            'sent_smtp' => $sent,
            'smtp_note' => $smtpNote,
            'otp'       => $otp,
            'email'     => $toEmail,
            'subject'   => $subject,
            'gmail_url' => self::buildGmailWebUrl($toEmail, $subject, $plainBody)
        ];
    }

    /**
     * Kích hoạt tiến trình gửi email & xuất PDF ngầm (Asynchronous Background Worker)
     * Trả về ngay lập tức (< 0.05s) giúp giao diện người dùng siêu mượt mà, không bị đứng trang
     */
    public function dispatchAsync(string $action, int $id): bool {
        $workerScript = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'email_worker.php';
        if (!file_exists($workerScript)) {
            return false;
        }

        $logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . DIRECTORY_SEPARATOR . 'email_worker.log';

        $phpCli = self::detectPhpCli();

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cmd = "cmd /c start /B \"\" \"{$phpCli}\" \"{$workerScript}\" {$action} {$id}";
            $fp = @popen($cmd, "r");
            if ($fp) {
                pclose($fp);
                return true;
            }
        } else {
            $cmd = "\"{$phpCli}\" \"{$workerScript}\" {$action} {$id} >> \"{$logFile}\" 2>&1 &";
            @exec($cmd);
            return true;
        }
        return false;
    }

    public function sendEmail(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        string $type = 'HoaDon',
        ?int $refId = null,
        ?string $attachmentPath = null,
        ?string $attachmentName = null
    ): array {
        // ĐẢM BẢO NGUYÊN TẮC: LUÔN LUÔN GỬI KÈM 1 FILE PHIẾU PDF TINH GỌN (1 TRANG A4) CHO KHÁCH HÀNG
        if (empty($attachmentPath) || !file_exists($attachmentPath)) {
            require_once __DIR__ . '/PdfService.php';
            $pdfService = new PdfService();
            if ($type === 'LichHen' && !empty($refId)) {
                $attachmentPath = $pdfService->generateAppointmentPdf($refId);
                $attachmentName = "Phieu_Hen_Lich_LH" . str_pad((string)$refId, 6, '0', STR_PAD_LEFT) . ".pdf";
            } elseif (($type === 'HoanThanhXe' || $type === 'PhieuSuaChua') && !empty($refId)) {
                $attachmentPath = $pdfService->generateRepairPdf($refId);
                $attachmentName = "Phieu_Sua_Chua_PSC" . str_pad((string)$refId, 6, '0', STR_PAD_LEFT) . ".pdf";
            } elseif ($type === 'HoaDon' && !empty($refId)) {
                $attachmentPath = $pdfService->generateInvoicePdf($refId);
                $attachmentName = "Hoa_Don_Thu_Tien_HD" . str_pad((string)$refId, 6, '0', STR_PAD_LEFT) . ".pdf";
            } else {
                $attachmentPath = $pdfService->generateGeneralPdf($toName, $toEmail, $subject, $htmlBody);
                $attachmentName = "Phieu_Thong_Bao_Garage_Pro.pdf";
            }
        }

        $config = $this->getSmtpConfig();
        $realSmtpSent = false;
        $smtpNote = "";

        if ($config['gmail_smtp_status'] === 'Bat' && !empty($config['gmail_smtp_user']) && !empty($config['gmail_smtp_pass'])) {
            $mailer = new SmtpMailer();
            $smtpRes = $mailer->send(
                $config['gmail_smtp_user'],
                $config['gmail_smtp_pass'],
                $config['gmail_smtp_sender_name'] ?: 'GARAGE PRO',
                $toEmail,
                $toName,
                $subject,
                $htmlBody,
                $attachmentPath,
                $attachmentName
            );
            $realSmtpSent = $smtpRes['success'];
            $smtpNote = $smtpRes['message'];
        } else {
            // Thử hàm mail() mặc định của máy chủ
            if (!empty($attachmentPath) && file_exists($attachmentPath)) {
                $boundary = "==Multipart_Boundary_x" . md5((string)time()) . "x";
                $headers  = "MIME-Version: 1.0\r\n";
                $headers .= "From: GARAGE PRO <noreply@garapro.vn>\r\n";
                $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

                $fileName = $attachmentName ?: basename($attachmentPath);
                $fileData = file_get_contents($attachmentPath);

                $msg  = "--{$boundary}\r\n";
                $msg .= "Content-type: text/html; charset=UTF-8\r\n\r\n";
                $msg .= $htmlBody . "\r\n";
                $msg .= "--{$boundary}\r\n";
                $msg .= "Content-Type: application/pdf; name=\"{$fileName}\"\r\n";
                $msg .= "Content-Disposition: attachment; filename=\"{$fileName}\"\r\n";
                $msg .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $msg .= chunk_split(base64_encode($fileData)) . "\r\n";
                $msg .= "--{$boundary}--\r\n";

                @mail($toEmail, $subject, $msg, $headers);
            } else {
                $headers  = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                $headers .= "From: GARAGE PRO <noreply@garapro.vn>\r\n";
                @mail($toEmail, $subject, $htmlBody, $headers);
            }
            $smtpNote = "Đã lưu vào Nhật ký Hộp thư Gmail hệ thống.";
        }

        // Luôn lưu vào CSDL để xem trực quan và tra cứu
        $statusDb = ($realSmtpSent || $config['gmail_smtp_status'] !== 'Bat') ? 'DaGui' : 'ThatBai';
        $sql = "INSERT INTO email_log (email_nguoi_nhan, ten_nguoi_nhan, tieu_de, noi_dung_html, loai_email, ma_tham_chieu, ngay_gui, trang_thai) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
        $this->execute($sql, [$toEmail, $toName, $subject, $htmlBody, $type, $refId, $statusDb]);
        $maEmail = (int)$this->lastInsertId();

        $attachNote = !empty($attachmentName) ? " (Đã đính kèm file PDF {$attachmentName})" : "";

        return [
            'success'  => true,
            'ma_email' => $maEmail,
            'message'  => "Đã gửi email tới {$toEmail}!{$attachNote} " . $smtpNote
        ];
    }

    public function logWebDispatch(string $toEmail, string $toName, string $subject, string $type, ?int $refId, string $plainText): int {
        $htmlEquivalent = "<div style='font-family: Arial; white-space: pre-wrap; font-size: 14px; line-height: 1.6; color: #2d3748;'>" . htmlspecialchars($plainText) . "</div>";
        $sql = "INSERT INTO email_log (email_nguoi_nhan, ten_nguoi_nhan, tieu_de, noi_dung_html, loai_email, ma_tham_chieu, ngay_gui, trang_thai) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), 'MoGmailWeb')";
        $this->execute($sql, [$toEmail, $toName, $subject, $htmlEquivalent, $type, $refId]);
        return (int)$this->lastInsertId();
    }

    public function getAllEmailLogs(?string $type = null): array {
        $sql = "SELECT * FROM email_log WHERE 1=1";
        $params = [];
        if (!empty($type)) {
            $sql .= " AND loai_email = ?";
            $params[] = $type;
        }
        $sql .= " ORDER BY ma_email DESC";
        return $this->fetchAll($sql, $params);
    }

    public function getEmailLogById(int $id): ?array {
        return $this->fetchOne("SELECT * FROM email_log WHERE ma_email = ?", [$id]);
    }

    // --- CÁC HÀM TRUY VẤN DỮ LIỆU ĐỐI TƯỢNG GỬI ---
    public function getAppointmentData(int $maLh): ?array {
        $sql = "SELECT lh.*, x.bien_so, x.hang_xe, x.dong_xe,
                       nd.ho_ten AS ten_chu_xe, nd.email, nd.sdt
                FROM lich_hen lh
                JOIN xe x ON lh.ma_xe = x.ma_xe
                JOIN khach_hang kh ON lh.ma_kh = kh.ma_kh
                JOIN nguoi_dung nd ON kh.ma_nd = nd.ma_nd
                WHERE lh.ma_lh = ?";
        return $this->fetchOne($sql, [$maLh]);
    }

    public function getRepairData(int $maPsc): ?array {
        $sql = "SELECT psc.*, x.bien_so, x.hang_xe, x.dong_xe, x.tien_no,
                       nd.ho_ten AS ten_chu_xe, nd.email, nd.sdt,
                       nd_nv.ho_ten AS ten_nv
                FROM phieu_sua_chua psc
                JOIN xe x ON psc.ma_xe = x.ma_xe
                JOIN khach_hang kh ON x.ma_kh = kh.ma_kh
                JOIN nguoi_dung nd ON kh.ma_nd = nd.ma_nd
                LEFT JOIN nhan_vien nv ON psc.ma_nv = nv.ma_nv
                LEFT JOIN nguoi_dung nd_nv ON nv.ma_nd = nd_nv.ma_nd
                WHERE psc.ma_psc = ?";
        return $this->fetchOne($sql, [$maPsc]);
    }

    public function getInvoiceData(int $maHd): ?array {
        $sql = "SELECT hd.*, psc.ma_xe, psc.tong_tien AS tong_sua,
                       x.bien_so, x.hang_xe, x.dong_xe, x.tien_no,
                       nd.ho_ten AS ten_chu_xe, nd.email, nd.sdt,
                       nd_nv.ho_ten AS ten_nv_thu
                FROM hoa_don hd
                JOIN phieu_sua_chua psc ON hd.ma_psc = psc.ma_psc
                JOIN xe x ON psc.ma_xe = x.ma_xe
                JOIN khach_hang kh ON x.ma_kh = kh.ma_kh
                JOIN nguoi_dung nd ON kh.ma_nd = nd.ma_nd
                LEFT JOIN nhan_vien nv ON hd.ma_nv = nv.ma_nv
                LEFT JOIN nguoi_dung nd_nv ON nv.ma_nd = nd_nv.ma_nd
                WHERE hd.ma_hd = ?";
        $inv = $this->fetchOne($sql, [$maHd]);
        if ($inv) {
            require_once __DIR__ . '/InvoiceModel.php';
            $invoiceModel = new InvoiceModel();
            $inv['ma_hd_code'] = $invoiceModel->formatInvoiceCode($inv, true);
        }
        return $inv;
    }

    // --- CÁC HÀM TẠO NỘI DUNG VĂN BẢN THUẦN (PLAIN TEXT) SOẠN THẢO TRÊN GMAIL ---
    public function buildAppointmentPlainText(array $lh): string {
        $timeStr = date('H:i, d/m/Y', strtotime($lh['ngay_hen']));

        return "Kính gửi Quý khách {$lh['ten_chu_xe']},\r\n\r\n"
             . "GARAGE PRO xin trân trọng xác nhận lịch hẹn bảo dưỡng / sửa chữa ô tô của Quý khách như sau:\r\n\r\n"
             . "-----------------------------------------\r\n"
             . "📅 THÔNG TIN LỊCH HẸN BẢO DƯỠNG:\r\n"
             . "• Mã lịch hẹn: #{$lh['ma_lh']}\r\n"
             . "• Biển số xe: {$lh['bien_so']} ({$lh['hang_xe']} {$lh['dong_xe']})\r\n"
             . "• Thời gian hẹn: {$timeStr}\r\n"
             . "• Nội dung yêu cầu: {$lh['ghi_chu']}\r\n"
             . "-----------------------------------------\r\n\r\n"
             . "Quý khách vui lòng đến đúng giờ để đội ngũ kỹ thuật viên sắp xếp khoang sửa chữa phục vụ nhanh chóng và chu đáo nhất.\r\n"
             . "Nếu cần đổi giờ hẹn, vui lòng liên hệ hotline: 0825.893.162.\r\n\r\n"
             . "Trân trọng cảm ơn Quý khách!\r\n\r\n"
             . "TRUNG TÂM SỬA CHỮA & BẢO DƯỠNG Ô TÔ GARAGE PRO\r\n"
             . "📍 Địa chỉ: 123 Đường Cầu Giấy, Quận Cầu Giấy, Hà Nội\r\n"
             . "📞 Hotline 24/7: 0825.893.162\r\n"
             . "🌐 Tra cứu trực tuyến: http://localhost/indexxx/\r\n";
    }

    public function buildRepairPlainText(array $psc): string {
        $tongTienStr = number_format((float)$psc['tong_tien']) . " đ";
        $tienNoStr = number_format((float)$psc['tien_no']) . " đ";

        return "Kính gửi Quý khách {$psc['ten_chu_xe']},\r\n\r\n"
             . "GARAGE PRO xin trân trọng thông báo: Xe của Quý khách đã hoàn thành toàn bộ các hạng mục sửa chữa, bảo dưỡng và đã vượt qua quy trình kiểm tra chất lượng xuất xưởng (KCS) an toàn!\r\n\r\n"
             . "-----------------------------------------\r\n"
             . "🚗 THÔNG TIN XE SẴN SÀNG BÀN GIAO:\r\n"
             . "• Biển số xe: {$psc['bien_so']} ({$psc['hang_xe']} {$psc['dong_xe']})\r\n"
             . "• Mã phiếu sửa chữa: #{$psc['ma_psc']}\r\n"
             . "• Kỹ thuật viên phụ trách: {$psc['ten_nv']}\r\n"
             . "• Tổng chi phí dịch vụ: {$tongTienStr}\r\n"
             . "• Số tiền cần thanh toán: {$tienNoStr}\r\n"
             . "-----------------------------------------\r\n\r\n"
             . "Xe của Quý khách đã được vệ sinh sạch sẽ và sẵn sàng bàn giao. Kính mời Quý khách đến Garage Pro nhận xe trong khung giờ làm việc: 07:30 - 18:00 hàng ngày.\r\n\r\n"
             . "Trân trọng cảm ơn Quý khách!\r\n\r\n"
             . "TRUNG TÂM SỬA CHỮA & BẢO DƯỠNG Ô TÔ GARAGE PRO\r\n"
             . "📍 Địa chỉ: 123 Đường Cầu Giấy, Quận Cầu Giấy, Hà Nội\r\n"
             . "📞 Hotline tư vấn: 0825.893.162\r\n";
    }

    public function buildInvoicePlainText(array $inv): string {
        $maHdStr = $inv['ma_hd_code'] ?? ("#HD" . str_pad((string)$inv['ma_hd'], 6, '0', STR_PAD_LEFT));
        $soTienThuStr = number_format((float)$inv['tong_tien']) . " đ";
        $ngayThuStr = date('H:i, d/m/Y', strtotime($inv['ngay_thanh_toan']));
        $phuongThucStr = (stripos($inv['phuong_thuc'] ?? '', 'chuyen') !== false) ? 'Chuyển khoản ngân hàng' : 'Tiền mặt';

        return "Kính gửi Quý khách {$inv['ten_chu_xe']},\r\n\r\n"
             . "GARAGE PRO xin gửi Quý khách biên nhận thanh toán phí dịch vụ sửa chữa và bảo dưỡng xe:\r\n\r\n"
             . "-----------------------------------------\r\n"
             . "🧾 THÔNG TIN HÓA ĐƠN THANH TOÁN:\r\n"
             . "• Số hóa đơn: {$maHdStr}\r\n"
             . "• Phương tiện: {$inv['bien_so']} ({$inv['hang_xe']} {$inv['dong_xe']})\r\n"
             . "• Mã phiếu sửa chữa: #{$inv['ma_psc']}\r\n"
             . "• Thời gian lập: {$ngayThuStr}\r\n"
             . "• Hình thức thanh toán: {$phuongThucStr}\r\n"
             . "• Thu ngân tiếp nhận: {$inv['ten_nv_thu']}\r\n"
             . "• SỐ TIỀN ĐÃ THANH TOÁN: {$soTienThuStr}\r\n"
             . "• Số nợ còn lại của xe: 0 VNĐ (Đã hoàn tất thanh toán)\r\n"
             . "-----------------------------------------\r\n\r\n"
             . "🛡️ CHÍNH SÁCH BẢO HÀNH:\r\n"
             . "Toàn bộ phụ tùng và linh kiện thay thế được bảo hành chính hãng từ 6 đến 12 tháng.\r\n"
             . "Quý khách vui lòng giữ email này để tra cứu thông tin bảo hành khi cần.\r\n\r\n"
             . "Xin chân thành cảm ơn Quý khách đã tin tưởng và đồng hành cùng Garage Pro!\r\n\r\n"
             . "TRUNG TÂM SỬA CHỮA & BẢO DƯỠNG Ô TÔ GARAGE PRO\r\n"
             . "📍 Địa chỉ: 123 Đường Cầu Giấy, Quận Cầu Giấy, Hà Nội\r\n"
             . "📞 Hotline 24/7: 0825.893.162\r\n"
             . "🌐 Tra cứu trực tuyến: http://localhost/indexxx/\r\n";
    }

    public function convertHtmlToPlainText(string $html): string {
        $text = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
        $text = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $text);
        $text = preg_replace('/<tr[^>]*>/i', "\r\n• ", $text);
        $text = preg_replace('/<td[^>]*>/i', " ", $text);
        $text = preg_replace('/<br\s*\/?>/i', "\r\n", $text);
        $text = preg_replace('/<\/p>/i', "\r\n\r\n", $text);
        $text = preg_replace('/<\/h[1-6]>/i', "\r\n\r\n", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/\n{3,}/", "\r\n\r\n", $text);
        return trim($text);
    }

    // --- CÁC HÀM LẤY ĐƯỜNG DẪN TRỰC TIẾP TỚI WEB GMAIL ---
    public function getAppointmentGmailUrl(int $maLh): ?string {
        $lh = $this->getAppointmentData($maLh);
        if (!$lh) return null;
        $subject = "🚗 [GARAGE PRO] Xác nhận lịch hẹn sửa chữa xe #{$lh['ma_lh']} - Biển số {$lh['bien_so']}";
        $body = $this->buildAppointmentPlainText($lh);
        $this->logWebDispatch($lh['email'], $lh['ten_chu_xe'], $subject, 'LichHen', $maLh, $body);
        return self::buildGmailWebUrl($lh['email'], $subject, $body);
    }

    public function getRepairGmailUrl(int $maPsc): ?string {
        $psc = $this->getRepairData($maPsc);
        if (!$psc) return null;
        $subject = "✅ [GARAGE PRO] Xe {$psc['bien_so']} đã hoàn thành sửa chữa - Kính mời Quý khách nhận xe";
        $body = $this->buildRepairPlainText($psc);
        $this->logWebDispatch($psc['email'], $psc['ten_chu_xe'], $subject, 'HoanThanhXe', $maPsc, $body);
        return self::buildGmailWebUrl($psc['email'], $subject, $body);
    }

    public function getInvoiceGmailUrl(int $maHd): ?string {
        $inv = $this->getInvoiceData($maHd);
        if (!$inv) return null;
        $maHdStr = $inv['ma_hd_code'] ?? ("#HD" . str_pad((string)$inv['ma_hd'], 6, '0', STR_PAD_LEFT));
        $subject = "🧾 [GARAGE PRO] Hóa đơn điện tử thanh toán {$maHdStr} - Xe {$inv['bien_so']}";
        $body = $this->buildInvoicePlainText($inv);
        $this->logWebDispatch($inv['email'], $inv['ten_chu_xe'], $subject, 'HoaDon', $maHd, $body);
        return self::buildGmailWebUrl($inv['email'], $subject, $body);
    }

    public function getEmailLogGmailUrl(int $maEmail): ?string {
        $em = $this->getEmailLogById($maEmail);
        if (!$em) return null;
        $body = $this->convertHtmlToPlainText($em['noi_dung_html']);
        return self::buildGmailWebUrl($em['email_nguoi_nhan'], $em['tieu_de'], $body);
    }

    // --- TEMPLATE 1: XÁC NHẬN LỊCH HẸN (GỬI THẲNG SMTP/LOG) ---
    public function sendAppointmentConfirmation(int $maLh, bool $async = true): array {
        if ($async) {
            $dispatched = $this->dispatchAsync('appointment', $maLh);
            if ($dispatched) {
                return [
                    'success' => true,
                    'async'   => true,
                    'message' => 'Hệ thống đang xuất phiếu hẹn PDF và tự động gửi xác nhận về Gmail...'
                ];
            }
        }
        return $this->sendAppointmentConfirmationSync($maLh);
    }

    public function sendAppointmentConfirmationSync(int $maLh): array {
        $lh = $this->getAppointmentData($maLh);
        if (!$lh) {
            return ['success' => false, 'message' => 'Không tìm thấy lịch hẹn!'];
        }

        // Tự động tạo file PDF phiếu xác nhận lịch hẹn đính kèm
        require_once __DIR__ . '/PdfService.php';
        $pdfService = new PdfService();
        $pdfPath = $pdfService->generateAppointmentPdf($maLh);
        $pdfName = "Phieu_Hen_Lich_LH" . str_pad((string)$maLh, 6, '0', STR_PAD_LEFT) . ".pdf";

        $subject = "🚗 [GARAGE PRO] Xác nhận lịch hẹn sửa chữa xe #{$lh['ma_lh']} - Biển số {$lh['bien_so']}";
        $timeStr = date('H:i, d/m/Y', strtotime($lh['ngay_hen']));

        $htmlBody = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; color: #2d3748;'>
            <div style='background: #130f40; color: #ffffff; padding: 25px; text-align: center;'>
                <h1 style='margin: 0; font-size: 24px; color: #2ed573;'>GARAGE PRO</h1>
                <p style='margin: 5px 0 0; font-size: 14px; opacity: 0.9;'>Trung tâm sửa chữa & bảo dưỡng ô tô công nghệ cao</p>
            </div>
            <div style='padding: 25px;'>
                <h2 style='color: #4834d4; margin-top: 0;'>XÁC NHẬN LỊCH HẸN BẢO DƯỠNG</h2>
                <p>Kính chào <b>{$lh['ten_chu_xe']}</b>,</p>
                <p>Cảm ơn Quý khách đã tin tưởng dịch vụ của Garage Pro. Chúng tôi xin xác nhận lịch hẹn của Quý khách như sau:</p>
                
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tr style='background: #f7fafc;'><td style='padding: 10px; border: 1px solid #edf2f7; font-weight: bold;'>Mã lịch hẹn:</td><td style='padding: 10px; border: 1px solid #edf2f7; color: #4834d4; font-weight: bold;'>#{$lh['ma_lh']}</td></tr>
                    <tr><td style='padding: 10px; border: 1px solid #edf2f7; font-weight: bold;'>Biển số xe:</td><td style='padding: 10px; border: 1px solid #edf2f7;'><b>{$lh['bien_so']}</b> ({$lh['hang_xe']} {$lh['dong_xe']})</td></tr>
                    <tr style='background: #f7fafc;'><td style='padding: 10px; border: 1px solid #edf2f7; font-weight: bold;'>Thời gian hẹn:</td><td style='padding: 10px; border: 1px solid #edf2f7; color: #e53e3e; font-weight: bold;'>{$timeStr}</td></tr>
                    <tr><td style='padding: 10px; border: 1px solid #edf2f7; font-weight: bold;'>Nội dung yêu cầu:</td><td style='padding: 10px; border: 1px solid #edf2f7;'>{$lh['ghi_chu']}</td></tr>
                </table>

                <div style='background: #f0fff4; border-left: 4px solid #38a169; padding: 12px; margin: 20px 0; font-size: 14px;'>
                    📎 <b>File đính kèm:</b> Hệ thống đã tự động xuất và đính kèm file <b>{$pdfName}</b> cùng email này để Quý khách lưu lại thông tin tiếp nhận ưu tiên.
                </div>

                <div style='background: #ebf8ff; border-left: 4px solid #3182ce; padding: 12px; margin: 20px 0; font-size: 14px;'>
                    <b>Lưu ý:</b> Quý khách vui lòng đến đúng giờ hẹn để được phục vụ tốt nhất. Nếu có thay đổi lịch, vui lòng liên hệ hotline: <b>0825.893.162</b>.
                </div>

                <p style='margin-bottom: 0;'>Trân trọng cảm ơn Quý khách!</p>
                <p style='margin-top: 4px;'><b>Ban Điều Hành Garage Pro</b></p>
            </div>
            <div style='background: #edf2f7; padding: 15px; text-align: center; font-size: 12px; color: #718096;'>
                Địa chỉ: 123 Đường Cầu Giấy, Quận Cầu Giấy, Hà Nội | Hotline 24/7: 0825.893.162
            </div>
        </div>";

        return $this->sendEmail($lh['email'], $lh['ten_chu_xe'], $subject, $htmlBody, 'LichHen', $maLh, $pdfPath, $pdfName);
    }

    // --- TEMPLATE 2: THÔNG BÁO XE ĐÃ HOÀN THÀNH ---
    public function sendRepairCompletedNotice(int $maPsc, bool $async = true): array {
        if ($async) {
            $dispatched = $this->dispatchAsync('repair', $maPsc);
            if ($dispatched) {
                return [
                    'success' => true,
                    'async'   => true,
                    'message' => 'Hệ thống đang xuất phiếu sửa chữa PDF và tự động gửi thông báo về Gmail...'
                ];
            }
        }
        return $this->sendRepairCompletedNoticeSync($maPsc);
    }

    public function sendRepairCompletedNoticeSync(int $maPsc): array {
        $psc = $this->getRepairData($maPsc);
        if (!$psc) {
            return ['success' => false, 'message' => 'Không tìm thấy phiếu sửa chữa!'];
        }

        // Tự động tạo file PDF đính kèm phiếu sửa chữa
        require_once __DIR__ . '/PdfService.php';
        $pdfService = new PdfService();
        $pdfPath = $pdfService->generateRepairPdf($maPsc);
        $pdfName = "Phieu_Sua_Chua_PSC" . str_pad((string)$maPsc, 6, '0', STR_PAD_LEFT) . ".pdf";

        $subject = "✅ [GARAGE PRO] Xe {$psc['bien_so']} đã hoàn thành sửa chữa - Kính mời Quý khách nhận xe";
        $tongTienStr = number_format((float)$psc['tong_tien']) . " đ";
        $tienNoStr = number_format((float)$psc['tien_no']) . " đ";

        $htmlBody = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; color: #2d3748;'>
            <div style='background: #130f40; color: #ffffff; padding: 25px; text-align: center;'>
                <h1 style='margin: 0; font-size: 24px; color: #2ed573;'>GARAGE PRO</h1>
                <p style='margin: 5px 0 0; font-size: 14px; opacity: 0.9;'>Thông báo hoàn thành dịch vụ sửa chữa & bảo dưỡng</p>
            </div>
            <div style='padding: 25px;'>
                <h2 style='color: #2ed573; margin-top: 0;'>XE CỦA BẠN ĐÃ SẴN SÀNG NHẬN!</h2>
                <p>Kính chào <b>{$psc['ten_chu_xe']}</b>,</p>
                <p>Garage Pro xin thông báo xe <b>{$psc['bien_so']}</b> ({$psc['hang_xe']} {$psc['dong_xe']}) đã hoàn thành tất cả các hạng mục sửa chữa và kiểm tra xuất xưởng (KCS).</p>

                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tr style='background: #f7fafc;'><td style='padding: 10px; border: 1px solid #edf2f7; font-weight: bold;'>Mã phiếu sửa:</td><td style='padding: 10px; border: 1px solid #edf2f7; color: #4834d4; font-weight: bold;'>#{$psc['ma_psc']}</td></tr>
                    <tr><td style='padding: 10px; border: 1px solid #edf2f7; font-weight: bold;'>Kỹ thuật viên phụ trách:</td><td style='padding: 10px; border: 1px solid #edf2f7;'>{$psc['ten_nv']}</td></tr>
                    <tr style='background: #f7fafc;'><td style='padding: 10px; border: 1px solid #edf2f7; font-weight: bold;'>Tổng chi phí:</td><td style='padding: 10px; border: 1px solid #edf2f7; font-weight: bold; color: #4834d4;'>{$tongTienStr}</td></tr>
                    <tr><td style='padding: 10px; border: 1px solid #edf2f7; font-weight: bold;'>Số tiền cần thanh toán:</td><td style='padding: 10px; border: 1px solid #edf2f7; font-weight: bold; color: #e53e3e; font-size: 16px;'>{$tienNoStr}</td></tr>
                </table>

                <div style='background: #f0fff4; border-left: 4px solid #38a169; padding: 12px; margin: 20px 0; font-size: 14px;'>
                    📎 <b>File đính kèm:</b> Hệ thống đã tự động xuất và đính kèm file <b>{$pdfName}</b> cùng email này để Quý khách lưu trữ và tra cứu chi tiết danh mục phụ tùng, tiền công sửa chữa.
                </div>

                <div style='background: #ebf8ff; border-left: 4px solid #3182ce; padding: 12px; margin: 20px 0; font-size: 14px;'>
                    Xe đã được rửa sạch và kiểm định an toàn. Quý khách có thể đến gara bất cứ lúc nào trong khung giờ <b>07:30 - 18:00</b> để nghiệm thu và nhận xe.
                </div>

                <p style='margin-bottom: 0;'>Trân trọng cảm ơn Quý khách!</p>
                <p style='margin-top: 4px;'><b>Bộ phận Dịch vụ Khách hàng Garage Pro</b></p>
            </div>
            <div style='background: #edf2f7; padding: 15px; text-align: center; font-size: 12px; color: #718096;'>
                Địa chỉ: 123 Đường Cầu Giấy, Quận Cầu Giấy, Hà Nội | Hotline: 0825.893.162
            </div>
        </div>";

        return $this->sendEmail($psc['email'], $psc['ten_chu_xe'], $subject, $htmlBody, 'HoanThanhXe', $maPsc, $pdfPath, $pdfName);
    }

    // --- TEMPLATE 3: HÓA ĐƠN ĐIỆN TỬ (E-INVOICE) ---
    public function sendInvoiceReceipt(int $maHd, bool $async = true): array {
        if ($async) {
            $dispatched = $this->dispatchAsync('invoice', $maHd);
            if ($dispatched) {
                return [
                    'success' => true,
                    'async'   => true,
                    'message' => 'Hệ thống đang xuất hóa đơn điện tử PDF và tự động gửi biên nhận về Gmail...'
                ];
            }
        }
        return $this->sendInvoiceReceiptSync($maHd);
    }

    public function sendInvoiceReceiptSync(int $maHd): array {
        $inv = $this->getInvoiceData($maHd);
        if (!$inv) {
            return ['success' => false, 'message' => 'Không tìm thấy hóa đơn!'];
        }

        // Tự động tạo file PDF đính kèm hóa đơn
        require_once __DIR__ . '/PdfService.php';
        $pdfService = new PdfService();
        $pdfPath = $pdfService->generateInvoicePdf($maHd);
        $maHdStr = $inv['ma_hd_code'] ?? ("#HD" . str_pad((string)$maHd, 6, '0', STR_PAD_LEFT));
        $pdfName = "Hoa_Don_Thu_Tien_" . str_replace('#', '', $maHdStr) . ".pdf";

        $subject = "🧾 [GARAGE PRO] Hóa đơn điện tử thanh toán {$maHdStr} - Xe {$inv['bien_so']}";
        $soTienThuStr = number_format((float)$inv['tong_tien']) . " đ";
        $ngayThuStr = date('H:i - d/m/Y', strtotime($inv['ngay_thanh_toan']));
        $phuongThucStr = (stripos($inv['phuong_thuc'] ?? '', 'chuyen') !== false) ? 'Chuyển khoản ngân hàng' : 'Tiền mặt';

        $htmlBody = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; color: #2d3748;'>
            <div style='background: #130f40; color: #ffffff; padding: 25px; text-align: center;'>
                <h1 style='margin: 0; font-size: 24px; color: #2ed573;'>GARAGE PRO</h1>
                <p style='margin: 5px 0 0; font-size: 14px; opacity: 0.9;'>Biên nhận thanh toán & Hóa đơn điện tử</p>
            </div>
            <div style='padding: 25px;'>
                <div style='text-align: center; margin-bottom: 20px;'>
                    <span style='background: #c6f6d5; color: #22543d; font-weight: bold; padding: 4px 12px; border-radius: 20px; font-size: 12px;'>ĐÃ THANH TOÁN THÀNH CÔNG</span>
                    <h2 style='color: #2b6cb0; margin: 10px 0 5px;'>HÓA ĐƠN THU TIỀN</h2>
                    <small style='color: #718096;'>Số HĐ: {$maHdStr} | Ngày lập: {$ngayThuStr}</small>
                </div>

                <p>Kính gửi Quý khách <b>{$inv['ten_chu_xe']}</b>,</p>
                <p>Garage Pro xin gửi biên nhận thanh toán phí dịch vụ bảo dưỡng & sửa chữa cho phương tiện <b>{$inv['bien_so']}</b>:</p>

                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tr style='background: #f7fafc;'><td style='padding: 10px; border: 1px solid #edf2f7; font-weight: bold;'>Phương tiện:</td><td style='padding: 10px; border: 1px solid #edf2f7;'><b>{$inv['bien_so']}</b> - {$inv['hang_xe']} {$inv['dong_xe']}</td></tr>
                    <tr><td style='padding: 10px; border: 1px solid #edf2f7; font-weight: bold;'>Mã phiếu sửa chữa:</td><td style='padding: 10px; border: 1px solid #edf2f7;'>#{$inv['ma_psc']}</td></tr>
                    <tr style='background: #f7fafc;'><td style='padding: 10px; border: 1px solid #edf2f7; font-weight: bold;'>Phương thức thanh toán:</td><td style='padding: 10px; border: 1px solid #edf2f7;'>{$phuongThucStr}</td></tr>
                    <tr><td style='padding: 10px; border: 1px solid #edf2f7; font-weight: bold;'>Thu ngân tiếp nhận:</td><td style='padding: 10px; border: 1px solid #edf2f7;'>{$inv['ten_nv_thu']}</td></tr>
                    <tr style='background: #f0fff4;'><td style='padding: 12px; border: 1px solid #edf2f7; font-weight: bold; font-size: 16px;'>SỐ TIỀN THỰC THU:</td><td style='padding: 12px; border: 1px solid #edf2f7; font-weight: bold; font-size: 18px; color: #38a169;'>{$soTienThuStr}</td></tr>
                    <tr><td style='padding: 10px; border: 1px solid #edf2f7; font-weight: bold;'>Số nợ còn lại của xe:</td><td style='padding: 10px; border: 1px solid #edf2f7; font-weight: bold; color: #38a169;'>0 VNĐ (Đã thanh toán đủ)</td></tr>
                </table>

                <div style='background: #f0fff4; border-left: 4px solid #38a169; padding: 12px; margin: 20px 0; font-size: 14px;'>
                    📎 <b>File đính kèm:</b> Hệ thống đã tự động xuất và đính kèm file <b>{$pdfName}</b> cùng email này để Quý khách lưu trữ biên nhận thanh toán chính thức.
                </div>

                <div style='background: #ebf8ff; border-left: 4px solid #3182ce; padding: 12px; margin: 20px 0; font-size: 13px;'>
                    <b>Chính sách bảo hành:</b> Toàn bộ phụ tùng thay thế được bảo hành chính hãng từ 6 - 12 tháng. Vui lòng giữ lại email hóa đơn này để tra cứu bảo hành.
                </div>

                <p style='margin-bottom: 0;'>Xin chân thành cảm ơn sự đồng hành của Quý khách!</p>
                <p style='margin-top: 4px;'><b>Ban Tài Chính Kế Toán Garage Pro</b></p>
            </div>
            <div style='background: #edf2f7; padding: 15px; text-align: center; font-size: 12px; color: #718096;'>
                Email tự động gửi từ hệ thống quản lý Garage Pro | Hotline hỗ trợ: 0825.893.162
            </div>
        </div>";

        return $this->sendEmail($inv['email'], $inv['ten_chu_xe'], $subject, $htmlBody, 'HoaDon', $maHd, $pdfPath, $pdfName);
    }
}
