<?php
/**
 * SmtpMailer: Bộ gửi email thuần qua SMTP Google (smtp.gmail.com)
 * Không cần thư viện ngoài, chạy trực tiếp trên PHP socket + OpenSSL TLS
 */

class SmtpMailer {
    private string $host = 'smtp.gmail.com';
    private int $port = 587;
    private int $timeout = 10;

    public function send(
        string $smtpUser,
        string $smtpPass,
        string $fromName,
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        ?string $attachmentPath = null,
        ?string $attachmentName = null
    ): array {
        if (empty($smtpUser) || empty($smtpPass)) {
            return [
                'success' => false,
                'message' => 'Chưa cấu hình tài khoản Gmail gửi hoặc Mật khẩu ứng dụng (App Password).'
            ];
        }

        // ĐẢM BẢO LUÔN GỬI KÈM 1 FILE PHIẾU PDF TINH GỌN (1 TRANG) QUA GMAIL (Trừ khi chỉ định 'none' như mã OTP)
        if ($attachmentPath !== 'none' && (empty($attachmentPath) || !file_exists($attachmentPath))) {
            require_once __DIR__ . '/PdfService.php';
            $pdfService = new PdfService();
            $attachmentPath = $pdfService->generateGeneralPdf($toName, $toEmail, $subject, $htmlBody);
            $attachmentName = "Phieu_Thong_Bao_Garage_Pro.pdf";
        } elseif ($attachmentPath === 'none') {
            $attachmentPath = null;
            $attachmentName = null;
        }

        // Loại bỏ khoảng trắng nếu copy pass từ Google (vd: abcd efgh ijkl mnop)
        $cleanPass = str_replace(' ', '', $smtpPass);

        $fp = @stream_socket_client("tcp://{$this->host}:{$this->port}", $errno, $errstr, $this->timeout);
        if (!$fp) {
            return [
                'success' => false,
                'message' => "Không thể kết nối đến máy chủ {$this->host}:587 ({$errstr})"
            ];
        }

        stream_set_timeout($fp, $this->timeout);

        try {
            $banner = $this->readResponse($fp);
            if (!str_starts_with($banner, '220')) {
                throw new Exception("Lỗi kết nối banner: " . $banner);
            }

            // 1. EHLO
            $this->sendCommand($fp, "EHLO localhost");
            $this->readResponse($fp);

            // 2. STARTTLS
            $this->sendCommand($fp, "STARTTLS");
            $tlsRes = $this->readResponse($fp);
            if (!str_starts_with($tlsRes, '220')) {
                throw new Exception("Lỗi kích hoạt TLS: " . $tlsRes);
            }

            // Nâng cấp kết nối lên TLS bảo mật
            $cryptoOk = @stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if (!$cryptoOk) {
                throw new Exception("Không thể thương lượng mã hóa TLS với Google SMTP.");
            }

            // 3. EHLO lần 2 sau khi bật TLS
            $this->sendCommand($fp, "EHLO localhost");
            $this->readResponse($fp);

            // 4. AUTH LOGIN
            $this->sendCommand($fp, "AUTH LOGIN");
            $authRes = $this->readResponse($fp);
            if (!str_starts_with($authRes, '334')) {
                throw new Exception("Lỗi khởi tạo đăng nhập: " . $authRes);
            }

            // Gửi Username (Base64)
            $this->sendCommand($fp, base64_encode($smtpUser));
            $userRes = $this->readResponse($fp);
            if (!str_starts_with($userRes, '334')) {
                throw new Exception("Tài khoản Gmail không hợp lệ: " . $userRes);
            }

            // Gửi Mật khẩu ứng dụng (Base64)
            $this->sendCommand($fp, base64_encode($cleanPass));
            $passRes = $this->readResponse($fp);
            if (!str_starts_with($passRes, '235')) {
                throw new Exception("Google từ chối mật khẩu! Vui lòng dùng Mật khẩu ứng dụng 16 ký tự (App Password) thay vì mật khẩu thông thường. Chi tiết: " . $passRes);
            }

            // 5. MAIL FROM
            $this->sendCommand($fp, "MAIL FROM:<{$smtpUser}>");
            $fromRes = $this->readResponse($fp);
            if (!str_starts_with($fromRes, '250')) {
                throw new Exception("Lỗi MAIL FROM: " . $fromRes);
            }

            // 6. RCPT TO
            $this->sendCommand($fp, "RCPT TO:<{$toEmail}>");
            $rcptRes = $this->readResponse($fp);
            if (!str_starts_with($rcptRes, '250')) {
                throw new Exception("Lỗi RCPT TO: " . $rcptRes);
            }

            // 7. DATA
            $this->sendCommand($fp, "DATA");
            $dataRes = $this->readResponse($fp);
            if (!str_starts_with($dataRes, '354')) {
                throw new Exception("Lỗi DATA: " . $dataRes);
            }

            // 8. Headers & Body
            $encodedSubject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
            $encodedFromName = "=?UTF-8?B?" . base64_encode($fromName) . "?=";
            $encodedToName = "=?UTF-8?B?" . base64_encode($toName) . "?=";

            $headers  = "Date: " . date('r') . "\r\n";
            $headers .= "From: {$encodedFromName} <{$smtpUser}>\r\n";
            $headers .= "To: {$encodedToName} <{$toEmail}>\r\n";
            $headers .= "Subject: {$encodedSubject}\r\n";
            $headers .= "MIME-Version: 1.0\r\n";

            if (!empty($attachmentPath) && file_exists($attachmentPath)) {
                $boundary = "==Multipart_Boundary_x" . md5((string)time()) . "x";
                $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";
                $headers .= "X-Mailer: GaragePro-Mailer-PHP\r\n";

                $fileName = $attachmentName ?: basename($attachmentPath);
                $fileData = file_get_contents($attachmentPath);

                $payload  = $headers . "\r\n";
                // Phần 1: Nội dung HTML
                $payload .= "--{$boundary}\r\n";
                $payload .= "Content-Type: text/html; charset=UTF-8\r\n";
                $payload .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $payload .= chunk_split(base64_encode($htmlBody)) . "\r\n";

                // Phần 2: Đính kèm file PDF
                $payload .= "--{$boundary}\r\n";
                $payload .= "Content-Type: application/pdf; name=\"{$fileName}\"\r\n";
                $payload .= "Content-Disposition: attachment; filename=\"{$fileName}\"\r\n";
                $payload .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $payload .= chunk_split(base64_encode($fileData)) . "\r\n";
                $payload .= "--{$boundary}--\r\n.\r\n";
            } else {
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                $headers .= "Content-Transfer-Encoding: base64\r\n";
                $headers .= "X-Mailer: GaragePro-Mailer-PHP\r\n";
                $payload = $headers . "\r\n" . chunk_split(base64_encode($htmlBody)) . "\r\n.\r\n";
            }
            fwrite($fp, $payload);

            $sendRes = $this->readResponse($fp);
            if (!str_starts_with($sendRes, '250')) {
                throw new Exception("Lỗi hoàn tất gửi: " . $sendRes);
            }

            // 9. QUIT
            $this->sendCommand($fp, "QUIT");
            fclose($fp);

            return [
                'success' => true,
                'message' => "Đã gửi email thực tế qua máy chủ Gmail thành công tới {$toEmail}!"
            ];
        } catch (Exception $e) {
            @fclose($fp);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function sendCommand($fp, string $cmd): void {
        fwrite($fp, $cmd . "\r\n");
    }

    private function readResponse($fp): string {
        $response = "";
        while ($line = fgets($fp, 512)) {
            $response .= $line;
            // Nếu ký tự thứ 4 là khoảng trắng hoặc kết thúc thì xong reply code
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return trim($response);
    }
}

