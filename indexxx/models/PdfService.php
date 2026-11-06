<?php
/**
 * PdfService: Dịch vụ xuất tài liệu Phiếu sửa chữa và Hóa đơn sang định dạng PDF
 * Tự động tạo file PDF và hỗ trợ đính kèm vào email gửi khách hàng
 */

class PdfService {
    private string $storageDir;
    private ?string $browserPath = null;

    public function __construct() {
        $this->storageDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'pdf';
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0777, true);
        }

        $this->detectBrowser();
    }

    private function detectBrowser(): void {
        $candidates = [
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
            'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                $this->browserPath = $path;
                break;
            }
        }
    }

    /**
     * Lấy chuỗi base64 của file chữ ký chuki.png để nhúng trực tiếp vào tài liệu PDF
     */
    private function getSignatureBase64(): string {
        $candidates = [
            'F:\\year3\\webphpanduml\\taileu\\chuki.png',
            dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'taileu' . DIRECTORY_SEPARATOR . 'chuki.png',
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'chuki.png',
        ];
        foreach ($candidates as $p) {
            if (file_exists($p)) {
                return 'data:image/png;base64,' . base64_encode(file_get_contents($p));
            }
        }
        return '';
    }

    /**
     * Xuất PDF cho Phiếu sửa chữa #$maPsc
     */
    public function generateRepairPdf($target): string {
        require_once __DIR__ . '/RepairModel.php';
        require_once __DIR__ . '/InvoiceModel.php';
        $repairModel = new RepairModel();
        $invoiceModel = new InvoiceModel();

        if (is_array($target)) {
            $psc = $target;
            $maPsc = (int)($psc['ma_psc'] ?? 0);
        } else {
            $maPsc = (int)$target;
            $psc = $repairModel->getRepairById($maPsc);
        }

        if (!$psc || $maPsc <= 0) {
            return '';
        }

        $outputFile = $this->storageDir . DIRECTORY_SEPARATOR . "Phieu_Sua_Chua_PSC" . str_pad((string)$maPsc, 6, '0', STR_PAD_LEFT) . ".pdf";

        $services = $repairModel->getServicesByRepairId($maPsc);
        $parts = $repairModel->getPartsByRepairId($maPsc);
        $invoice = $invoiceModel->getInvoiceByRepairId($maPsc);

        // Render HTML riêng biệt sạch sẽ, không có nút bấm điều hướng
        $html = $this->renderRepairHtml($psc, $services, $parts, $invoice);

        $ok = $this->convertHtmlToPdf($html, $outputFile);
        if (!$ok || !file_exists($outputFile)) {
            // Dùng bộ phát sinh PDF thuần PHP làm dự phòng
            $this->createFallbackPdf($outputFile, "PHIEU SUA CHUA #" . $maPsc, $psc, $services, $parts);
        }

        return $outputFile;
    }

    /**
     * Xuất PDF cho Hóa đơn thu tiền #$maHd
     */
    public function generateInvoicePdf($target): string {
        require_once __DIR__ . '/InvoiceModel.php';
        require_once __DIR__ . '/RepairModel.php';
        $invoiceModel = new InvoiceModel();
        $repairModel = new RepairModel();

        if (is_array($target)) {
            $invoice = $target;
            $maHd = (int)($invoice['ma_hd'] ?? 0);
        } else {
            $maHd = (int)$target;
            $invoice = $invoiceModel->getInvoiceById($maHd);
        }

        if (!$invoice || $maHd <= 0) {
            return '';
        }

        $codeClean = str_replace('#', '', $invoice['ma_hd_code'] ?? ('HD' . str_pad((string)$maHd, 6, '0', STR_PAD_LEFT)));
        $outputFile = $this->storageDir . DIRECTORY_SEPARATOR . "Hoa_Don_Thu_Tien_" . $codeClean . ".pdf";

        $services = $repairModel->getServicesByRepairId((int)$invoice['ma_psc']);
        $parts = $repairModel->getPartsByRepairId((int)$invoice['ma_psc']);

        $html = $this->renderInvoiceHtml($invoice, $services, $parts);

        $ok = $this->convertHtmlToPdf($html, $outputFile);
        if (!$ok || !file_exists($outputFile)) {
            $this->createFallbackPdf($outputFile, "HOA DON THU TIEN " . ($invoice['ma_hd_code'] ?? ("#" . $maHd)), $invoice, $services, $parts);
        }

        return $outputFile;
    }

    /**
     * Xuất PDF cho Phiếu xác nhận lịch hẹn #$maLh
     */
    public function generateAppointmentPdf($target): string {
        require_once __DIR__ . '/AppointmentModel.php';
        $appointmentModel = new AppointmentModel();

        if (is_array($target)) {
            $lh = $target;
            $maLh = (int)($lh['ma_lh'] ?? 0);
        } else {
            $maLh = (int)$target;
            $lh = $appointmentModel->getAppointmentById($maLh);
        }

        if (!$lh || $maLh <= 0) {
            return '';
        }

        $outputFile = $this->storageDir . DIRECTORY_SEPARATOR . "Phieu_Hen_Lich_LH" . str_pad((string)$maLh, 6, '0', STR_PAD_LEFT) . ".pdf";

        $html = $this->renderAppointmentHtml($lh);

        $ok = $this->convertHtmlToPdf($html, $outputFile);
        if (!$ok || !file_exists($outputFile)) {
            $this->createFallbackPdf($outputFile, "XAC NHAN LICH HEN #" . $maLh, $lh, [], []);
        }

        return $outputFile;
    }

    /**
     * Xuất PDF Phiếu thông báo / Xác nhận dịch vụ chung (1 trang A4)
     * Đảm bảo luôn có file PDF chính thức đính kèm khi gửi email cho khách hàng
     */
    public function generateGeneralPdf(string $toName, string $toEmail, string $subject, string $htmlBody): string {
        $fileName = "Phieu_Thong_Bao_" . substr(md5($subject . $toEmail), 0, 8) . ".pdf";
        $outputFile = $this->storageDir . DIRECTORY_SEPARATOR . $fileName;

        $html = $this->renderGeneralHtml($toName, $toEmail, $subject, $htmlBody);

        $ok = $this->convertHtmlToPdf($html, $outputFile);
        if (!$ok || !file_exists($outputFile)) {
            $this->createFallbackPdf($outputFile, "THONG BAO DICH VU GARA PRO", [
                'ten_chu_xe' => $toName,
                'bien_so'    => $toEmail,
                'tong_tien'  => 0
            ], [], []);
        }

        return $outputFile;
    }

    /**
     * Chuyển HTML sang PDF sử dụng Headless Browser (Chrome/Edge)
     */
    private function convertHtmlToPdf(string $html, string $outputPath): bool {
        if (!$this->browserPath) {
            return false;
        }

        $tempHtml = $this->storageDir . DIRECTORY_SEPARATOR . "temp_" . uniqid() . ".html";
        file_put_contents($tempHtml, $html);

        if (file_exists($outputPath)) {
            @unlink($outputPath);
        }

        $htmlUrl = "file:///" . str_replace('\\', '/', $tempHtml);
        $cmd = '"' . $this->browserPath . '" --headless=new --disable-gpu --no-sandbox --disable-extensions --no-first-run --disable-background-networking --disable-sync --disable-translate --disable-default-apps --no-default-browser-check --no-pdf-header-footer --print-to-pdf="' . $outputPath . '" "' . $htmlUrl . '"';
        
        exec($cmd, $output, $returnCode);

        // Xóa file HTML tạm
        @unlink($tempHtml);

        return file_exists($outputPath) && filesize($outputPath) > 0;
    }

    /**
     * Chuyển đổi số tiền thành chữ tiếng Việt chuẩn hóa phục vụ văn bản, chứng từ hóa đơn
     */
    private function numberToVietnameseWords(float $number): string {
        $number = round($number);
        if ($number <= 0) {
            return 'Không đồng';
        }

        $chuSo = ['không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
        $tien = ['', 'nghìn', 'triệu', 'tỷ', 'nghìn tỷ', 'triệu tỷ'];

        $docBlock3 = function($b, $dayDu) use ($chuSo) {
            $tram = floor($b / 100);
            $chuc = floor(($b % 100) / 10);
            $donvi = $b % 10;
            $res = '';
            if ($tram > 0 || $dayDu) {
                $res .= $chuSo[$tram] . ' trăm ';
                if ($chuc == 0 && $donvi > 0) {
                    $res .= 'lẻ ';
                }
            }
            if ($chuc > 1) {
                $res .= $chuSo[$chuc] . ' mươi ';
                if ($donvi == 1) {
                    $res .= 'mốt ';
                } else if ($donvi == 5) {
                    $res .= 'lăm ';
                } else if ($donvi > 0) {
                    $res .= $chuSo[$donvi] . ' ';
                }
            } elseif ($chuc == 1) {
                $res .= 'mười ';
                if ($donvi == 5) {
                    $res .= 'lăm ';
                } else if ($donvi > 0) {
                    $res .= $chuSo[$donvi] . ' ';
                }
            } elseif ($donvi > 0) {
                $res .= $chuSo[$donvi] . ' ';
            }
            return $res;
        };

        $blocks = [];
        $temp = $number;
        while ($temp > 0) {
            $blocks[] = $temp % 1000;
            $temp = floor($temp / 1000);
        }

        $res = '';
        $len = count($blocks);
        for ($i = $len - 1; $i >= 0; $i--) {
            $b = $blocks[$i];
            if ($b > 0) {
                $text = $docBlock3($b, $i < $len - 1);
                $res .= $text . $tien[$i] . ' ';
            }
        }

        $res = trim($res);
        $res = mb_strtoupper(mb_substr($res, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($res, 1, null, 'UTF-8');
        return $res . ' đồng chẵn.';
    }

    /**
     * Template HTML chuyên biệt xuất PDF Phiếu sửa chữa (Tiêu chuẩn xưởng dịch vụ ô tô cao cấp, 1 trang A4)
     */
    private function renderRepairHtml(array $psc, array $services, array $parts, ?array $invoice = null): string {
        $ngayLap = date('d/m/Y H:i', strtotime($psc['ngay_lap']));
        $tongTien = (float)$psc['tong_tien'];
        $tongTienFormatted = number_format($tongTien);
        $soPsc = "#PSC" . str_pad((string)$psc['ma_psc'], 6, '0', STR_PAD_LEFT);
        $signatureBase64 = $this->getSignatureBase64();
        $tienChu = $this->numberToVietnameseWords($tongTien);
        $daThanhToan = ($psc['trang_thai'] === 'DaThanhToan' || !empty($invoice));

        $tienCong = 0;
        $servicesHtml = '';
        $stt = 1;
        if (empty($services)) {
            $servicesHtml = '<tr><td colspan="5" style="text-align: center; color: #64748b; padding: 4px;">Không có dịch vụ tiền công</td></tr>';
        } else {
            foreach ($services as $s) {
                $tienCong += (float)$s['thanh_tien'];
                $servicesHtml .= '<tr>
                    <td style="text-align: center; color: #64748b;">' . ($stt++) . '</td>
                    <td>
                        <div style="font-weight: 600; color: #0f172a;">' . htmlspecialchars($s['ten_dv']) . '</div>
                        ' . (!empty($s['mo_ta']) ? '<div style="font-size: 7.2pt; color: #64748b;">' . htmlspecialchars($s['mo_ta']) . '</div>' : '') . '
                    </td>
                    <td style="text-align: center; font-weight: 600;">' . $s['so_luong'] . '</td>
                    <td style="text-align: right; color: #334155;">' . number_format((float)$s['don_gia']) . ' đ</td>
                    <td style="text-align: right; font-weight: 700; color: #0f172a;">' . number_format((float)$s['thanh_tien']) . ' đ</td>
                </tr>';
            }
        }

        $tienPhuTung = 0;
        $partsHtml = '';
        $stt2 = 1;
        if (empty($parts)) {
            $partsHtml = '<tr><td colspan="6" style="text-align: center; color: #64748b; padding: 4px;">Không thay thế vật tư phụ tùng</td></tr>';
        } else {
            foreach ($parts as $p) {
                $tienPhuTung += (float)$p['thanh_tien'];
                $partsHtml .= '<tr>
                    <td style="text-align: center; color: #64748b;">' . ($stt2++) . '</td>
                    <td>
                        <div style="font-weight: 600; color: #0f172a;">' . htmlspecialchars($p['ten_pt']) . '</div>
                        <div style="font-size: 7.2pt; color: #64748b;">Phụ tùng chính ngạch OEM/Chính hãng</div>
                    </td>
                    <td style="text-align: center; color: #475569;">' . htmlspecialchars($p['don_vi_tinh']) . '</td>
                    <td style="text-align: center; font-weight: 600;">' . $p['so_luong'] . '</td>
                    <td style="text-align: right; color: #334155;">' . number_format((float)$p['don_gia']) . ' đ</td>
                    <td style="text-align: right; font-weight: 700; color: #0f172a;">' . number_format((float)$p['thanh_tien']) . ' đ</td>
                </tr>';
            }
        }

        if ($tienPhuTung == 0 && $tongTien > $tienCong) {
            $tienPhuTung = $tongTien - $tienCong;
        }

        return '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu Sửa Chữa ' . $soPsc . '</title>
    <style>
        @page { size: A4 portrait; margin: 7mm 10mm 6mm 10mm; }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { font-family: "Segoe UI", Roboto, -apple-system, Arial, sans-serif; margin: 0; padding: 0; font-size: 8.5pt; color: #1e293b; line-height: 1.3; background: #ffffff; }
        .brand-top-bar { height: 4px; background: linear-gradient(90deg, #0f172a 0%, #1e40af 50%, #0284c7 100%); margin-bottom: 6px; border-radius: 2px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .header-table td { vertical-align: top; padding: 0; }
        .company-name { font-size: 12.5pt; font-weight: 800; color: #0f172a; letter-spacing: 0.5px; text-transform: uppercase; margin: 0 0 1px 0; }
        .company-tagline { font-size: 7.2pt; font-weight: 700; color: #1e40af; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 3px; }
        .company-details { font-size: 7.5pt; color: #475569; line-height: 1.35; }
        .doc-title-main { font-size: 13.5pt; font-weight: 800; color: #0f172a; letter-spacing: 0.8px; text-transform: uppercase; margin: 0 0 1px 0; text-align: right; }
        .doc-title-sub { font-size: 7pt; font-weight: 600; color: #64748b; letter-spacing: 0.8px; text-transform: uppercase; text-align: right; margin-bottom: 3px; }
        .doc-meta-badge { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 3px 8px; font-size: 7.5pt; display: inline-block; text-align: left; }
        .dossier-card { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 5px; margin-bottom: 6px; padding: 6px 10px; }
        .dossier-table { width: 100%; border-collapse: collapse; }
        .dossier-table td { vertical-align: top; padding: 0; }
        .dossier-col-header { font-size: 7pt; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; padding-bottom: 2px; margin-bottom: 3px; }
        .dossier-row { margin-bottom: 2px; font-size: 8pt; color: #334155; }
        .dossier-label { color: #64748b; font-size: 7.5pt; display: inline-block; min-width: 85px; }
        .plate-badge { background: #ffffff; color: #0f172a; border: 1.5px solid #0f172a; border-radius: 3px; padding: 1px 6px; font-weight: 800; font-size: 9pt; letter-spacing: 1px; display: inline-block; }
        .sec-title { font-weight: 800; font-size: 7.8pt; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; margin-bottom: 2px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .items-table th { background-color: #0f172a; color: #ffffff; font-size: 7.2pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 6px; border: 1px solid #0f172a; }
        .items-table td { border: 1px solid #e2e8f0; padding: 3px 6px; font-size: 7.8pt; }
        .items-table tbody tr:nth-child(even) { background-color: #f8fafc; }
        .summary-table { width: 100%; border-collapse: collapse; margin-top: 3px; margin-bottom: 4px; }
        .summary-table td { vertical-align: top; padding: 0; }
        .words-box { font-size: 7.2pt; color: #475569; line-height: 1.35; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 4px; padding: 4px 8px; }
        .calculation-card { background: #ffffff; border: 1px solid #cbd5e1; border-radius: 4px; padding: 4px 8px; width: 290px; margin-left: auto; }
        .calc-row { display: flex; justify-content: space-between; margin-bottom: 2px; font-size: 7.5pt; color: #475569; }
        .grand-total-row { margin-top: 3px; padding: 3px 6px; background: #0f172a; color: #ffffff; border-radius: 3px; display: flex; justify-content: space-between; align-items: center; }
        .sign-table { width: 100%; border-collapse: collapse; margin-top: 4px; page-break-inside: avoid; }
        .sign-table td { width: 33.33%; text-align: center; vertical-align: top; padding: 0 4px; }
        .sign-role { font-size: 7.5pt; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1px; }
        .sign-note { font-size: 6.8pt; color: #64748b; font-style: italic; margin-bottom: 2px; }
        .sign-box-space { height: 38px; display: flex; align-items: center; justify-content: center; }
        .footer-terms { border-top: 1px solid #e2e8f0; margin-top: 5px; padding-top: 3px; font-size: 6.5pt; color: #64748b; line-height: 1.3; }
    </style>
</head>
<body>
    <div class="brand-top-bar"></div>

    <table class="header-table">
        <tr>
            <td style="width: 58%;">
                <div class="company-name">GARAGE PRO AUTO SERVICE</div>
                <div class="company-tagline">TRUNG TÂM BẢO DƯỠNG & SỬA CHỮA Ô TÔ TIÊU CHUẨN CAO</div>
                <div class="company-details">
                    <b>MST:</b> 0108999888 &nbsp;|&nbsp; <b>Hotline 24/7:</b> 0825.893.162<br>
                    <b>Địa chỉ:</b> 123 Đường Cầu Giấy, Q. Cầu Giấy, TP. Hà Nội<br>
                    <b>Website:</b> www.garapro.vn &nbsp;|&nbsp; <b>Email:</b> contact@garapro.vn
                </div>
            </td>
            <td style="width: 48%; text-align: right;">
                <div class="doc-title-main">' . ($daThanhToan ? 'PHIẾU SỬA CHỮA &amp; HÓA ĐƠN' : 'PHIẾU SỬA CHỮA') . '</div>
                <div class="doc-title-sub">' . ($daThanhToan ? 'NGHIỆM THU KIÊM HÓA ĐƠN QUYẾT TOÁN' : 'VEHICLE REPAIR ORDER &amp; JOB CARD') . '</div>
                <div class="doc-meta-badge">
                    <div>Lệnh sửa chữa: <b style="color: #1e40af; font-family: monospace; font-size: 8.5pt;">' . $soPsc . '</b></div>
                    ' . (!empty($invoice) ? '<div>Mã hóa đơn: <b style="color: #047857; font-family: monospace; font-size: 8.5pt;">' . htmlspecialchars($invoice['ma_hd_code'] ?? ('#HD' . $invoice['ma_hd'])) . '</b></div>
                    <div>Thanh toán: <b style="color: #047857;">' . ($invoice['phuong_thuc'] === 'ChuyenKhoan' ? 'Chuyển khoản (VietQR)' : 'Tiền mặt') . '</b></div>' : '<div>Trạng thái: <b style="color: #047857;">' . htmlspecialchars($psc['trang_thai']) . '</b></div>') . '
                    <div>Ngày lập: <b>' . $ngayLap . '</b></div>
                </div>
            </td>
        </tr>
    </table>

    <div class="dossier-card">
        <table class="dossier-table">
            <tr>
                <td style="width: 50%; padding-right: 10px; border-right: 1px solid #e2e8f0;">
                    <div class="dossier-col-header">I. THÔNG TIN KHÁCH HÀNG (CHỦ XE)</div>
                    <div class="dossier-row"><span class="dossier-label">Chủ xe:</span> <b style="color: #0f172a; font-size: 9pt;">' . htmlspecialchars($psc['ten_chu_xe']) . '</b></div>
                    <div class="dossier-row"><span class="dossier-label">Điện thoại:</span> <b>' . htmlspecialchars($psc['sdt_chu_xe']) . '</b></div>
                    <div class="dossier-row"><span class="dossier-label">Địa chỉ:</span> ' . htmlspecialchars($psc['dia_chi'] ?? 'Chưa cập nhật') . '</div>
                </td>
                <td style="width: 50%; padding-left: 10px;">
                    <div class="dossier-col-header">II. HỒ SƠ PHƯƠNG TIỆN SỬA CHỮA</div>
                    <div class="dossier-row" style="margin-bottom: 2px;">
                        <span class="dossier-label" style="vertical-align: middle;">Biển số xe:</span> 
                        <span class="plate-badge">' . htmlspecialchars($psc['bien_so']) . '</span>
                    </div>
                    <div class="dossier-row"><span class="dossier-label">Hiệu / Dòng xe:</span> <b>' . htmlspecialchars($psc['hang_xe']) . ' ' . htmlspecialchars($psc['dong_xe'] ?? '') . '</b></div>
                    <div class="dossier-row"><span class="dossier-label">Kỹ thuật viên:</span> <b>' . htmlspecialchars($psc['ten_nv'] ?? 'Tổ Kỹ Thuật Garage Pro') . '</b></div>
                </td>
            </tr>
        </table>
    </div>

    <div class="sec-title">1. TIỀN CÔNG DỊCH VỤ SỬA CHỮA &amp; BẢO DƯỠNG</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 30px; text-align: center;">STT</th>
                <th style="text-align: left;">HẠNG MỤC CÔNG VIỆC THỰC HIỆN</th>
                <th style="width: 45px; text-align: center;">SL</th>
                <th style="width: 85px; text-align: right;">ĐƠN GIÁ</th>
                <th style="width: 95px; text-align: right;">THÀNH TIỀN</th>
            </tr>
        </thead>
        <tbody>' . $servicesHtml . '</tbody>
    </table>

    <div class="sec-title">2. VẬT TƯ &amp; PHỤ TÙNG THAY THẾ CHÍNH HÃNG</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 30px; text-align: center;">STT</th>
                <th style="text-align: left;">TÊN VẬT TƯ / PHỤ TÙNG</th>
                <th style="width: 45px; text-align: center;">ĐVT</th>
                <th style="width: 40px; text-align: center;">SL</th>
                <th style="width: 85px; text-align: right;">ĐƠN GIÁ</th>
                <th style="width: 95px; text-align: right;">THÀNH TIỀN</th>
            </tr>
        </thead>
        <tbody>' . $partsHtml . '</tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td style="width: 50%; padding-right: 10px;">
                <div class="words-box">
                    <div style="font-weight: 700; color: #0f172a; margin-bottom: 2px;">SỐ TIỀN BẰNG CHỮ:</div>
                    <div style="font-style: italic; color: #1e40af; font-weight: 600;">' . $tienChu . '</div>
                    <div style="margin-top: 3px; font-size: 6.8pt; color: #64748b;">
                        * Cam kết bảo hành kỹ thuật 90 ngày hoặc 5.000 km. Phụ tùng bảo hành chính hãng từ 12 đến 24 tháng.
                    </div>
                </div>
            </td>
            <td style="width: 50%;">
                <div class="calculation-card">
                    <div class="calc-row">
                        <span>Tổng chi phí công thợ:</span>
                        <b style="color: #0f172a;">' . number_format($tienCong) . ' đ</b>
                    </div>
                    <div class="calc-row">
                        <span>Tổng phụ tùng linh kiện:</span>
                        <b style="color: #0f172a;">' . number_format($tienPhuTung) . ' đ</b>
                    </div>
                    <div class="grand-total-row">
                        <span style="font-size: 7.8pt; text-transform: uppercase; font-weight: 700;">TỔNG QUYẾT TOÁN:</span>
                        <span style="font-size: 9.5pt; font-weight: 800; font-family: monospace;">' . $tongTienFormatted . ' VNĐ</span>
                    </div>
                    ' . ($daThanhToan ? '<div class="calc-row" style="margin-top: 4px; border-top: 1px dashed #cbd5e1; padding-top: 3px;">
                        <span style="font-weight: 700; color: #047857;">Đã thanh toán:</span>
                        <b style="color: #047857;">' . number_format(!empty($invoice) ? (float)$invoice['tong_tien'] : $tongTien) . ' đ</b>
                    </div>
                    <div class="calc-row">
                        <span style="font-weight: 700; color: #047857;">Nợ còn lại:</span>
                        <b style="color: #047857;">0 đ (Đã tất toán)</b>
                    </div>' : '') . '
                </div>
            </td>
        </tr>
    </table>

    <table class="sign-table">
        <tr>
            <td style="width: ' . ($daThanhToan ? '25%' : '33.33%') . ';">
                <div class="sign-role">KHÁCH HÀNG (CHỦ XE)</div>
                <div class="sign-note">' . ($daThanhToan ? '(Ký nhận xe &amp; đã thanh toán)' : '(Ký &amp; ghi rõ họ tên)') . '</div>
                <div class="sign-box-space"></div>
                <div style="font-weight: 700; font-size: 7.8pt; color: #0f172a;">' . htmlspecialchars($psc['ten_chu_xe']) . '</div>
            </td>
            <td style="width: ' . ($daThanhToan ? '25%' : '33.33%') . ';">
                <div class="sign-role">KỸ THUẬT VIÊN KIỂM TRA</div>
                <div class="sign-note">(Ký xác nhận hoàn tất)</div>
                <div class="sign-box-space"></div>
                <div style="font-weight: 700; font-size: 7.8pt; color: #0f172a;">' . htmlspecialchars($psc['ten_nv'] ?? 'Tổ Kỹ Thuật') . '</div>
            </td>
            ' . ($daThanhToan ? '<td style="width: 25%;">
                <div class="sign-role">THỦ QUỸ / THU NGÂN</div>
                <div class="sign-note">(Đã thu đủ tiền)</div>
                <div class="sign-box-space"></div>
                <div style="font-weight: 700; font-size: 7.8pt; color: #0f172a;">' . htmlspecialchars(!empty($invoice['ten_nv_thu']) ? $invoice['ten_nv_thu'] : 'Thu Ngân Gara Pro') . '</div>
            </td>' : '') . '
            <td style="width: ' . ($daThanhToan ? '25%' : '33.33%') . ';">
                <div class="sign-role">TRƯỞNG BAN KỸ THUẬT</div>
                <div class="sign-box-space">
                    ' . (!empty($signatureBase64) ? '<img src="' . $signatureBase64 . '" style="max-height: 42px; max-width: 100px; display: inline-block;" alt="Chữ ký &amp; Dấu">' : '') . '
                </div>
            </td>
        </tr>
    </table>

    <div class="footer-terms">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 75%; padding: 0;">
                    Phiếu sửa chữa được lưu trữ điện tử trên hệ thống Garage Pro ERP. Quý khách vui lòng kiểm tra toàn bộ xe và tư trang trước khi rời xưởng.<br>
                    Hotline cứu hộ 24/7: <b>0825.893.162</b> &nbsp;|&nbsp; Cổng thông tin khách hàng: <b>www.garapro.vn</b>
                </td>
                <td style="width: 25%; text-align: right; vertical-align: bottom; font-size: 6.2pt; color: #94a3b8; padding: 0;">
                    Mã tra cứu: GP-PSC-' . str_pad((string)$psc['ma_psc'], 6, '0', STR_PAD_LEFT) . '
                </td>
            </tr>
        </table>
    </div>
</body>
</html>';
    }

    /**
     * Template HTML chuyên biệt xuất PDF Hóa đơn theo đúng mẫu AutoCare Pro (Chuẩn 1 trang A4)
     */
    private function renderInvoiceHtml(array $invoice, array $services, array $parts): string {
        $ngayThu = date('d/m/Y H:i', strtotime($invoice['ngay_thanh_toan']));
        $tongTien = (float)$invoice['tong_tien'];
        $tongTienFormatted = number_format($tongTien);
        $soHd = sprintf("#%06d", (int)$invoice['ma_hd']);
        $soPsc = "#" . (int)$invoice['ma_psc'];

        $tienCong = 0;
        foreach ($services as $s) {
            $tienCong += (float)$s['thanh_tien'];
        }
        $tienPhuTung = 0;
        foreach ($parts as $p) {
            $tienPhuTung += (float)$p['thanh_tien'];
        }
        if ($tienPhuTung == 0 && $tongTien > $tienCong) {
            $tienPhuTung = $tongTien - $tienCong;
        }

        $phuongThuc = $invoice['phuong_thuc'] ?? 'TienMat';
        if (stripos($phuongThuc, 'chuyen') !== false || stripos($phuongThuc, 'bank') !== false) {
            $phuongThucStr = 'Chuyển khoản (Đã thanh toán đủ)';
        } else {
            $phuongThucStr = 'Tiền mặt (Đã thanh toán đủ)';
        }

        $hangXe = htmlspecialchars($invoice['hang_xe'] ?? '');
        $dongXe = htmlspecialchars($invoice['dong_xe'] ?? '');
        $namSx = !empty($invoice['nam_san_xuat']) ? (int)$invoice['nam_san_xuat'] : null;
        $dongXeFull = trim($hangXe . ' ' . $dongXe);
        if ($namSx) {
            $dongXeFull .= " ({$namSx})";
        }
        if (empty($dongXeFull)) {
            $dongXeFull = "Đang cập nhật";
        }

        $odoVal = "100,458 km";
        if ($namSx && $namSx > 2000) {
            $age = max(1, (int)date('Y') - $namSx);
            $odoVal = number_format($age * 18500 + 458) . " km";
        }

        $ktvPhuTrach = !empty($invoice['ten_nv_thu']) ? $invoice['ten_nv_thu'] : 'Lê Kỹ Thuật';

        // Render services
        $servicesRows = '';
        $sttS = 1;
        if (!empty($services)) {
            foreach ($services as $s) {
                $servicesRows .= '<tr>
                    <td style="border: 1px solid #000; text-align: center;">' . ($sttS++) . '</td>
                    <td style="border: 1px solid #000; text-align: left;">' . htmlspecialchars($s['ten_dv']) . '</td>
                    <td style="border: 1px solid #000; text-align: center;">' . htmlspecialchars($s['don_vi_tinh'] ?? 'Gói/Công') . '</td>
                    <td style="border: 1px solid #000; text-align: center;">' . (int)$s['so_luong'] . '</td>
                    <td style="border: 1px solid #000; text-align: right;">' . number_format((float)$s['don_gia']) . '</td>
                    <td style="border: 1px solid #000; text-align: right; font-weight: bold;">' . number_format((float)$s['thanh_tien']) . '</td>
                </tr>';
            }
        }

        // Render parts
        $partsRows = '';
        $sttP = 1;
        if (!empty($parts)) {
            foreach ($parts as $p) {
                $partsRows .= '<tr>
                    <td style="border: 1px solid #000; text-align: center;">' . ($sttP++) . '</td>
                    <td style="border: 1px solid #000; text-align: left;">' . htmlspecialchars($p['ten_pt']) . '</td>
                    <td style="border: 1px solid #000; text-align: center;">' . htmlspecialchars($p['don_vi_tinh'] ?? 'Cái') . '</td>
                    <td style="border: 1px solid #000; text-align: center;">' . (int)$p['so_luong'] . '</td>
                    <td style="border: 1px solid #000; text-align: right;">' . number_format((float)$p['don_gia']) . '</td>
                    <td style="border: 1px solid #000; text-align: right; font-weight: bold;">' . number_format((float)$p['thanh_tien']) . '</td>
                </tr>';
            }
        }

        return '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa Đơn Sửa Chữa ' . $soHd . ' - AutoCare Pro</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 15mm 10mm 15mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: "Times New Roman", Times, Georgia, serif;
            font-size: 9.8pt;
            color: #000000;
            line-height: 1.32;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }
        .invoice-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .invoice-header-table td {
            vertical-align: top;
            padding: 0;
        }
        .garage-title-main {
            font-size: 13.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000000;
            line-height: 1.25;
            letter-spacing: 0.3px;
        }
        .garage-subinfo {
            font-size: 9pt;
            color: #000000;
            line-height: 1.32;
            margin-top: 3px;
        }
        .invoice-right-title {
            text-align: right;
            font-size: 16pt;
            font-weight: bold;
            color: #dc2626;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        .invoice-right-meta {
            text-align: right;
            font-size: 9pt;
            color: #000000;
            line-height: 1.32;
        }
        .divider-line {
            border-top: 2px solid #000000;
            margin: 10px 0 14px 0;
        }
        .client-vehicle-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .client-vehicle-table td {
            vertical-align: top;
            width: 50%;
            padding: 0;
            font-size: 9.5pt;
            line-height: 1.4;
        }
        .section-header-title {
            font-weight: bold;
            font-size: 9.5pt;
            text-transform: uppercase;
            margin-bottom: 3px;
            color: #000000;
        }
        .invoice-items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
            margin-bottom: 10px;
        }
        .invoice-items-table th {
            border: 1px solid #000000;
            padding: 6px 5px;
            font-weight: bold;
            font-size: 9pt;
            text-align: center;
            background: #ffffff;
            color: #000000;
        }
        .invoice-items-table td {
            border: 1px solid #000000;
            padding: 5px 6px;
            font-size: 9pt;
            color: #000000;
        }
        .group-header-row {
            font-weight: bold;
            background: #ffffff;
            text-align: left;
            padding: 5px 6px;
            border: 1px solid #000000;
        }
        .summary-label {
            text-align: right;
            font-weight: bold;
            padding: 5px 10px;
            border: 1px solid #000000;
        }
        .summary-value {
            text-align: right;
            font-weight: bold;
            padding: 5px 6px;
            border: 1px solid #000000;
            white-space: nowrap;
        }
        .grand-total-label {
            text-align: right;
            font-weight: bold;
            text-transform: uppercase;
            padding: 7px 10px;
            font-size: 9.5pt;
            border: 1px solid #000000;
        }
        .grand-total-value {
            text-align: right;
            font-weight: bold;
            color: #dc2626;
            font-size: 10pt;
            padding: 7px 6px;
            border: 1px solid #000000;
            white-space: nowrap;
        }
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            text-align: center;
            page-break-inside: avoid;
        }
        .signatures-table td {
            width: 33.33%;
            vertical-align: top;
            padding: 0 6px;
        }
        .sig-role {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9.5pt;
            color: #000000;
            margin-bottom: 1px;
        }
        .sig-note {
            font-style: italic;
            font-size: 8pt;
            color: #374151;
            margin-bottom: 2px;
        }
        .sig-space {
            height: 55px;
        }
        .sig-name {
            font-weight: bold;
            font-size: 9.5pt;
            color: #000000;
        }
    </style>
</head>
<body>
    <table class="invoice-header-table">
        <tr>
            <td style="width: 62%;">
                <div class="garage-title-main">TRUNG TÂM SỬA CHỮA &amp; BẢO DƯỠNG</div>
                <div class="garage-title-main" style="margin-bottom: 3px;">Ô TÔ AUTOCARE PRO</div>
                <div class="garage-subinfo">
                    <b>Địa chỉ:</b> Số 123 Đường Cầu Giấy, Q. Cầu Giấy, TP. Hà Nội<br>
                    <b>Hotline kỹ thuật:</b> 0901.234.567 | <b>Email:</b> support@autocarepro.vn<br>
                    <b>Mã số thuế:</b> 0109988776
                </div>
            </td>
            <td style="width: 38%;">
                <div class="invoice-right-title">HÓA ĐƠN</div>
                <div class="invoice-right-meta">
                    <b>Số:</b> ' . $soHd . '<br>
                    <b>Ngày:</b> ' . $ngayThu . '<br>
                    <b>Mã phiếu SC:</b> ' . $soPsc . '
                </div>
            </td>
        </tr>
    </table>

    <div class="divider-line"></div>

    <table class="client-vehicle-table">
        <tr>
            <td style="padding-right: 12px;">
                <div class="section-header-title">THÔNG TIN KHÁCH HÀNG:</div>
                <div><b>Họ và tên:</b> ' . htmlspecialchars($invoice['ten_chu_xe']) . '</div>
                <div><b>Số điện thoại:</b> ' . htmlspecialchars($invoice['sdt_chu_xe']) . '</div>
                <div><b>Địa chỉ:</b> ' . htmlspecialchars($invoice['dia_chi'] ?? 'Chưa cập nhật') . '</div>
            </td>
            <td style="padding-left: 12px;">
                <div class="section-header-title">THÔNG TIN PHƯƠNG TIỆN:</div>
                <div><b>Biển số xe:</b> <b>' . htmlspecialchars($invoice['bien_so']) . '</b></div>
                <div><b>Dòng xe:</b> ' . htmlspecialchars($dongXeFull) . '</div>
                <div><b>Số Km (ODO):</b> ' . htmlspecialchars($odoVal) . '</div>
            </td>
        </tr>
    </table>

    <table class="invoice-items-table">
        <thead>
            <tr>
                <th style="width: 40px;">STT</th>
                <th>Nội Dung Dịch Vụ &amp; Phụ Tùng Thay Thế</th>
                <th style="width: 70px;">ĐVT</th>
                <th style="width: 40px;">SL</th>
                <th style="width: 120px;">Đơn Giá (VNĐ)</th>
                <th style="width: 130px;">Thành Tiền (VNĐ)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6" class="group-header-row">
                    I. TIỀN CÔNG DỊCH VỤ SỬA CHỮA / BẢO DƯỠNG
                </td>
            </tr>
            ' . $servicesRows . '
            <tr>
                <td colspan="6" class="group-header-row">
                    II. VẬT TƯ &amp; PHỤ TÙNG THAY THẾ CHÍNH HÃNG
                </td>
            </tr>
            ' . $partsRows . '
            <tr>
                <td colspan="5" class="summary-label">Tổng tiền công dịch vụ:</td>
                <td class="summary-value">' . number_format($tienCong) . ' đ</td>
            </tr>
            <tr>
                <td colspan="5" class="summary-label">Tổng tiền phụ tùng:</td>
                <td class="summary-value">' . number_format($tienPhuTung) . ' đ</td>
            </tr>
            <tr>
                <td colspan="5" class="grand-total-label">TỔNG CỘNG THANH TOÁN:</td>
                <td class="grand-total-value">' . $tongTienFormatted . ' đ</td>
            </tr>
        </tbody>
    </table>

    <div style="font-size: 9pt; line-height: 1.5; margin-top: 8px;">
        <div><b>Hình thức thanh toán:</b> ' . $phuongThucStr . '</div>
        <div><b>Kỹ thuật viên phụ trách sửa chữa:</b> ' . htmlspecialchars($ktvPhuTrach) . '</div>
        <div style="font-style: italic; color: #374151; font-size: 8.5pt; margin-top: 3px;">
            * Gara bảo hành 06 tháng hoặc 10.000 km cho toàn bộ phụ tùng và hạng mục sửa chữa ghi trên hóa đơn.
        </div>
    </div>

    <table class="signatures-table">
        <tr>
            <td>
                <div class="sig-role">KHÁCH HÀNG</div>
                <div class="sig-note">(Ký và ghi rõ họ tên)</div>
                <div class="sig-space"></div>
                <div class="sig-name">' . htmlspecialchars($invoice['ten_chu_xe']) . '</div>
            </td>
            <td>
                <div class="sig-role">KỸ THUẬT VIÊN</div>
                <div class="sig-note">(Ký và ghi rõ họ tên)</div>
                <div class="sig-space"></div>
                <div class="sig-name">' . htmlspecialchars($ktvPhuTrach) . '</div>
            </td>
            <td>
                <div class="sig-role">NGƯỜI LẬP HÓA ĐƠN</div>
                <div class="sig-note">(Ký, đóng dấu nếu có)</div>
                <div class="sig-space"></div>
                <div class="sig-name">' . htmlspecialchars($ktvPhuTrach) . '</div>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    /**
     * Template HTML chuyên biệt xuất PDF Phiếu xác nhận lịch hẹn (Tiêu chuẩn tiếp nhận xe, 1 trang A4)
     */
    private function renderAppointmentHtml(array $lh): string {
        $ngayHen = (!empty($lh['ngay_hen']) && $lh['ngay_hen'] !== '0000-00-00 00:00:00') ? date('d/m/Y H:i', strtotime($lh['ngay_hen'])) : 'Linh hoạt theo điều phối';
        $soLh = "#LH" . str_pad((string)$lh['ma_lh'], 6, '0', STR_PAD_LEFT);
        $signatureBase64 = $this->getSignatureBase64();

        return '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu Xác Nhận Lịch Hẹn ' . $soLh . '</title>
    <style>
        @page { size: A4 portrait; margin: 8mm 12mm 7mm 12mm; }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { font-family: "Segoe UI", Roboto, -apple-system, Arial, sans-serif; margin: 0; padding: 0; font-size: 9pt; color: #1e293b; line-height: 1.35; background: #ffffff; }
        .brand-top-bar { height: 4px; background: linear-gradient(90deg, #0f172a 0%, #1e40af 50%, #0284c7 100%); margin-bottom: 8px; border-radius: 2px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .header-table td { vertical-align: top; padding: 0; }
        .company-name { font-size: 13pt; font-weight: 800; color: #0f172a; letter-spacing: 0.5px; text-transform: uppercase; margin: 0 0 2px 0; }
        .company-tagline { font-size: 7.5pt; font-weight: 700; color: #1e40af; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 4px; }
        .company-details { font-size: 7.8pt; color: #475569; line-height: 1.35; }
        .doc-title-main { font-size: 13.5pt; font-weight: 800; color: #0f172a; letter-spacing: 0.8px; text-transform: uppercase; margin: 0 0 1px 0; text-align: right; }
        .doc-title-sub { font-size: 7.2pt; font-weight: 600; color: #64748b; letter-spacing: 0.8px; text-transform: uppercase; text-align: right; margin-bottom: 4px; }
        .doc-meta-badge { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 4px 8px; font-size: 7.8pt; display: inline-block; text-align: left; }
        .dossier-card { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 5px; margin-bottom: 10px; padding: 8px 12px; }
        .dossier-table { width: 100%; border-collapse: collapse; }
        .dossier-table td { vertical-align: top; padding: 0; }
        .dossier-col-header { font-size: 7.5pt; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; padding-bottom: 2px; margin-bottom: 4px; }
        .dossier-row { margin-bottom: 3px; font-size: 8.5pt; color: #334155; }
        .dossier-label { color: #64748b; font-size: 8pt; display: inline-block; min-width: 95px; }
        .plate-badge { background: #ffffff; color: #0f172a; border: 1.5px solid #0f172a; border-radius: 4px; padding: 1px 7px; font-weight: 800; font-size: 10pt; letter-spacing: 1px; display: inline-block; }
        .slot-box { background: #eff6ff; border: 1.5px solid #3b82f6; border-radius: 5px; padding: 10px 14px; margin-bottom: 10px; }
        .slot-title { font-weight: 800; font-size: 8.5pt; color: #1e40af; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .slot-time { font-size: 12pt; font-weight: 800; color: #0f172a; font-family: monospace; }
        .content-box { border: 1px solid #cbd5e1; border-radius: 5px; padding: 10px 12px; margin-bottom: 10px; background: #ffffff; }
        .guide-box { background: #f8fafc; border-left: 3.5px solid #0f172a; padding: 8px 12px; margin-bottom: 10px; font-size: 8pt; color: #334155; }
        .sign-table { width: 100%; border-collapse: collapse; margin-top: 8px; page-break-inside: avoid; }
        .sign-table td { width: 50%; text-align: center; vertical-align: top; padding: 0 10px; }
        .sign-role { font-size: 8pt; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1px; }
        .sign-note { font-size: 7.2pt; color: #64748b; font-style: italic; margin-bottom: 2px; }
        .sign-box-space { height: 44px; display: flex; align-items: center; justify-content: center; }
        .footer-terms { border-top: 1px solid #e2e8f0; margin-top: 8px; padding-top: 4px; font-size: 7pt; color: #64748b; line-height: 1.35; }
    </style>
</head>
<body>
    <div class="brand-top-bar"></div>

    <table class="header-table">
        <tr>
            <td style="width: 58%;">
                <div class="company-name">GARAGE PRO AUTO SERVICE</div>
                <div class="company-tagline">TRUNG TÂM TIẾP NHẬN &amp; ĐIỀU PHỐI DỊCH VỤ Ô TÔ</div>
                <div class="company-details">
                    <b>MST:</b> 0108999888 &nbsp;|&nbsp; <b>Hotline tiếp nhận 24/7:</b> 0825.893.162<br>
                    <b>Địa chỉ xưởng:</b> 123 Đường Cầu Giấy, Q. Cầu Giấy, TP. Hà Nội<br>
                    <b>Website:</b> www.garapro.vn &nbsp;|&nbsp; <b>Email:</b> contact@garapro.vn
                </div>
            </td>
            <td style="width: 42%; text-align: right;">
                <div class="doc-title-main">PHIẾU TIẾP NHẬN LỊCH HẸN</div>
                <div class="doc-title-sub">SERVICE APPOINTMENT CONFIRMATION</div>
                <div class="doc-meta-badge">
                    <div>Mã lịch hẹn: <b style="color: #1e40af; font-family: monospace; font-size: 9.5pt;">' . $soLh . '</b></div>
                    <div>Ngày phát hành: <b>' . date('d/m/Y H:i') . '</b></div>
                    <div>Trạng thái: <span style="background: #ecfdf5; color: #047857; font-weight: 700; padding: 1px 5px; border-radius: 3px; font-size: 7.2pt; border: 1px solid #a7f3d0;">✓ ĐÃ TIẾP NHẬN</span></div>
                </div>
            </td>
        </tr>
    </table>

    <div class="dossier-card">
        <table class="dossier-table">
            <tr>
                <td style="width: 50%; padding-right: 12px; border-right: 1px solid #e2e8f0;">
                    <div class="dossier-col-header">I. THÔNG TIN KHÁCH HÀNG ĐẶT HẸN</div>
                    <div class="dossier-row"><span class="dossier-label">Họ và tên:</span> <b style="color: #0f172a; font-size: 9pt;">' . htmlspecialchars($lh['ten_chu_xe']) . '</b></div>
                    <div class="dossier-row"><span class="dossier-label">Số điện thoại:</span> <b>' . htmlspecialchars($lh['sdt']) . '</b></div>
                    <div class="dossier-row"><span class="dossier-label">Địa chỉ liên hệ:</span> ' . htmlspecialchars($lh['dia_chi'] ?? 'Tại xưởng Garage Pro') . '</div>
                </td>
                <td style="width: 50%; padding-left: 12px;">
                    <div class="dossier-col-header">II. HỒ SƠ PHƯƠNG TIỆN ĐĂNG KÝ</div>
                    <div class="dossier-row" style="margin-bottom: 3px;">
                        <span class="dossier-label" style="vertical-align: middle;">Biển số xe:</span> 
                        <span class="plate-badge">' . htmlspecialchars($lh['bien_so']) . '</span>
                    </div>
                    <div class="dossier-row"><span class="dossier-label">Hiệu &amp; Dòng xe:</span> <b>' . htmlspecialchars($lh['hang_xe']) . ' ' . htmlspecialchars($lh['dong_xe'] ?? '') . '</b></div>
                    <div class="dossier-row"><span class="dossier-label">Khoang tiếp nhận:</span> <b>Khoang Cầu Nâng Ưu Tiên A-01</b></div>
                </td>
            </tr>
        </table>
    </div>

    <div class="slot-box">
        <div class="slot-title">THỜI GIAN TIẾP NHẬN PHƯƠNG TIỆN DỰ KIẾN:</div>
        <div class="slot-time">' . $ngayHen . '</div>
    </div>

    <div class="content-box">
        <div style="font-weight: 700; color: #0f172a; font-size: 8pt; text-transform: uppercase; margin-bottom: 4px; border-bottom: 1px solid #f1f5f9; padding-bottom: 2px;">
            YÊU CẦU DỊCH VỤ &amp; HIỆN TƯỢNG HƯ HỎNG THEO MÔ TẢ:
        </div>
        <div style="font-size: 8.5pt; color: #334155; line-height: 1.45;">
            ' . nl2br(htmlspecialchars($lh['ghi_chu'] ?? 'Kiểm tra tổng quát hệ thống khung gầm, phanh, động cơ và bảo dưỡng định kỳ phương tiện.')) . '
        </div>
    </div>

    <div class="guide-box">
        <b>QUY TRÌNH ƯU TIÊN KHI MANG PHƯƠNG TIỆN ĐẾN XƯỞNG:</b><br>
        1. Quý khách vui lòng đưa xe đến đúng khung giờ hẹn để kỹ thuật viên chuẩn bị sẵn khoang cầu nâng phục vụ nhanh chóng.<br>
        2. Xuất trình mã số hẹn <b>' . $soLh . '</b> tại Bàn Tiếp nhận xe để được phân luồng kỹ thuật viên ưu tiên kiểm tra KCS 12 bước.<br>
        3. Trường hợp cần dời lịch hoặc yêu cầu xe cứu hộ chuyên dụng 24/7, vui lòng liên hệ trực tiếp hotline: <b>0825.893.162</b>.
    </div>

    <table class="sign-table">
        <tr>
            <td>
                <div class="sign-role">KHÁCH HÀNG ĐẶT HẸN</div>
                <div class="sign-note">(Ký &amp; ghi rõ họ tên)</div>
                <div class="sign-box-space"></div>
            </td>
            <td>
                <div class="sign-role">ĐIỀU PHỐI DỊCH VỤ GARA PRO</div>
                <div class="sign-box-space">
                    ' . (!empty($signatureBase64) ? '<img src="' . $signatureBase64 . '" style="max-height: 48px; max-width: 110px; display: inline-block;" alt="Chữ ký &amp; Con dấu">' : '') . '
                </div>
            </td>
        </tr>
    </table>

    <div class="footer-terms">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 75%; padding: 0;">
                    Phiếu hẹn điện tử được xác thực và xếp lịch tự động trên hệ thống Garage Pro Dispatcher.<br>
                    Hotline CSKH &amp; Cứu hộ 24/7: <b>0825.893.162</b> &nbsp;|&nbsp; Website: <b>www.garapro.vn</b>
                </td>
                <td style="width: 25%; text-align: right; vertical-align: bottom; font-size: 6.5pt; color: #94a3b8; padding: 0;">
                    Mã tiếp nhận: GP-LH-' . str_pad((string)$lh['ma_lh'], 6, '0', STR_PAD_LEFT) . '
                </td>
            </tr>
        </table>
    </div>
</body>
</html>';
    }

    /**
     * Template HTML chuyên biệt xuất PDF Phiếu thông báo dịch vụ chung (1 trang A4)
     */
    private function renderGeneralHtml(string $toName, string $toEmail, string $subject, string $htmlBody): string {
        $cleanContent = strip_tags($htmlBody, '<p><br><b><strong><ul><ol><li><table><tr><td><th><span><div>');
        // Clean out excessive margins/paddings from embedded inline styles
        $cleanContent = preg_replace('/style=([\'"])[^\'"]*max-width[^\'"]*\1/i', '', $cleanContent);
        $cleanContent = preg_replace('/style=([\'"])[^\'"]*background:[^\'"]*#130f40[^\'"]*\1/i', 'style="display:none;"', $cleanContent);

        $nowStr = date('d/m/Y H:i');
        $docId = "#TB" . date('ymd') . rand(100, 999);
        $signatureBase64 = $this->getSignatureBase64();

        return '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu Thông Báo ' . $docId . '</title>
    <style>
        @page { size: A4 portrait; margin: 8mm 12mm 7mm 12mm; }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { font-family: "Segoe UI", Roboto, -apple-system, Arial, sans-serif; margin: 0; padding: 0; font-size: 9pt; color: #1e293b; line-height: 1.35; background: #ffffff; }
        .brand-top-bar { height: 4px; background: linear-gradient(90deg, #0f172a 0%, #1e40af 50%, #0284c7 100%); margin-bottom: 8px; border-radius: 2px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .header-table td { vertical-align: top; padding: 0; }
        .company-name { font-size: 13pt; font-weight: 800; color: #0f172a; letter-spacing: 0.5px; text-transform: uppercase; margin: 0 0 2px 0; }
        .company-tagline { font-size: 7.5pt; font-weight: 700; color: #1e40af; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 4px; }
        .company-details { font-size: 7.8pt; color: #475569; line-height: 1.35; }
        .doc-title-main { font-size: 13.5pt; font-weight: 800; color: #0f172a; letter-spacing: 0.8px; text-transform: uppercase; margin: 0 0 1px 0; text-align: right; }
        .doc-title-sub { font-size: 7.2pt; font-weight: 600; color: #64748b; letter-spacing: 0.8px; text-transform: uppercase; text-align: right; margin-bottom: 4px; }
        .doc-meta-badge { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 4px 8px; font-size: 7.8pt; display: inline-block; text-align: left; }
        .dossier-card { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 5px; margin-bottom: 10px; padding: 8px 12px; }
        .dossier-table { width: 100%; border-collapse: collapse; }
        .dossier-table td { vertical-align: top; padding: 0; }
        .dossier-col-header { font-size: 7.5pt; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; padding-bottom: 2px; margin-bottom: 4px; }
        .dossier-row { margin-bottom: 3px; font-size: 8.5pt; color: #334155; }
        .dossier-label { color: #64748b; font-size: 8pt; display: inline-block; min-width: 95px; }
        .content-box { border: 1px solid #cbd5e1; border-radius: 5px; padding: 12px 14px; margin-bottom: 10px; background: #ffffff; min-height: 220px; font-size: 9pt; line-height: 1.5; color: #334155; }
        .assurance-box { background: #ecfdf5; border-left: 3.5px solid #059669; padding: 8px 12px; margin-bottom: 10px; font-size: 8pt; color: #065f46; }
        .sign-table { width: 100%; border-collapse: collapse; margin-top: 8px; page-break-inside: avoid; }
        .sign-table td { width: 50%; text-align: center; vertical-align: top; padding: 0 10px; }
        .sign-role { font-size: 8pt; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1px; }
        .sign-note { font-size: 7.2pt; color: #64748b; font-style: italic; margin-bottom: 2px; }
        .sign-box-space { height: 44px; display: flex; align-items: center; justify-content: center; }
        .footer-terms { border-top: 1px solid #e2e8f0; margin-top: 8px; padding-top: 4px; font-size: 7pt; color: #64748b; line-height: 1.35; }
    </style>
</head>
<body>
    <div class="brand-top-bar"></div>

    <table class="header-table">
        <tr>
            <td style="width: 58%;">
                <div class="company-name">GARAGE PRO AUTO SERVICE</div>
                <div class="company-tagline">TRUNG TÂM BẢO DƯỠNG &amp; SỬA CHỮA Ô TÔ TIÊU CHUẨN CAO</div>
                <div class="company-details">
                    <b>MST:</b> 0108999888 &nbsp;|&nbsp; <b>Hotline 24/7:</b> 0825.893.162<br>
                    <b>Địa chỉ:</b> 123 Đường Cầu Giấy, Q. Cầu Giấy, TP. Hà Nội<br>
                    <b>Website:</b> www.garapro.vn &nbsp;|&nbsp; <b>Email:</b> contact@garapro.vn
                </div>
            </td>
            <td style="width: 42%; text-align: right;">
                <div class="doc-title-main">PHIẾU THÔNG BÁO DỊCH VỤ</div>
                <div class="doc-title-sub">OFFICIAL SYSTEM NOTIFICATION</div>
                <div class="doc-meta-badge">
                    <div>Số chứng từ: <b style="color: #1e40af; font-family: monospace; font-size: 9.5pt;">' . $docId . '</b></div>
                    <div>Ngày phát hành: <b>' . $nowStr . '</b></div>
                    <div>Trạng thái: <span style="background: #ecfdf5; color: #047857; font-weight: 700; padding: 1px 5px; border-radius: 3px; font-size: 7.2pt; border: 1px solid #a7f3d0;">✓ XÁC NHẬN HỆ THỐNG</span></div>
                </div>
            </td>
        </tr>
    </table>

    <div class="dossier-card">
        <table class="dossier-table">
            <tr>
                <td style="width: 50%; padding-right: 12px; border-right: 1px solid #e2e8f0;">
                    <div class="dossier-col-header">I. THÔNG TIN KHÁCH HÀNG TIẾP NHẬN</div>
                    <div class="dossier-row"><span class="dossier-label">Kính gửi:</span> <b style="color: #0f172a; font-size: 9pt;">' . htmlspecialchars($toName) . '</b></div>
                    <div class="dossier-row"><span class="dossier-label">Hòm thư (Email):</span> <b style="color: #1e40af;">' . htmlspecialchars($toEmail) . '</b></div>
                </td>
                <td style="width: 50%; padding-left: 12px;">
                    <div class="dossier-col-header">II. PHÂN LOẠI THÔNG BÁO VĂN BẢN</div>
                    <div class="dossier-row"><span class="dossier-label">Tiêu đề:</span> <b>' . htmlspecialchars($subject) . '</b></div>
                    <div class="dossier-row"><span class="dossier-label">Đơn vị ban hành:</span> <b>Ban Điều Hành Garage Pro Hà Nội</b></div>
                </td>
            </tr>
        </table>
    </div>

    <div class="content-box">
        <div style="font-weight: 700; color: #0f172a; font-size: 8pt; text-transform: uppercase; margin-bottom: 6px; border-bottom: 1px solid #f1f5f9; padding-bottom: 3px;">
            NỘI DUNG VĂN BẢN THÔNG BÁO CHÍNH THỨC:
        </div>
        <div>
            ' . $cleanContent . '
        </div>
    </div>

    <div class="assurance-box">
        <b>CAM KẾT CHẤT LƯỢNG GARAGE PRO:</b> Toàn bộ quy trình tiếp nhận, dịch vụ sửa chữa và linh kiện phụ tùng tại Garage Pro đều được thực hiện theo tiêu chuẩn cao cấp, bảo hành chính hãng từ 6 - 24 tháng.
    </div>

    <table class="sign-table">
        <tr>
            <td>
                <div class="sign-role">KHÁCH HÀNG TIẾP NHẬN</div>
                <div class="sign-note">(Ký &amp; ghi rõ họ tên)</div>
                <div class="sign-box-space"></div>
            </td>
            <td>
                <div class="sign-role">ĐẠI DIỆN BAN QUẢN LÝ GARA PRO</div>
                <div class="sign-box-space">
                    ' . (!empty($signatureBase64) ? '<img src="' . $signatureBase64 . '" style="max-height: 48px; max-width: 110px; display: inline-block;" alt="Chữ ký &amp; Con dấu">' : '') . '
                </div>
            </td>
        </tr>
    </table>

    <div class="footer-terms">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 75%; padding: 0;">
                    Văn bản thông báo được phát hành chính thức từ hệ thống tự động Garage Pro Notification Service.<br>
                    Hotline hỗ trợ 24/7: <b>0825.893.162</b> &nbsp;|&nbsp; Website: <b>www.garapro.vn</b>
                </td>
                <td style="width: 25%; text-align: right; vertical-align: bottom; font-size: 6.5pt; color: #94a3b8; padding: 0;">
                    Mã văn bản: GP-' . substr($docId, 1) . '
                </td>
            </tr>
        </table>
    </div>
</body>
</html>';
    }
    /**
     * Bộ phát sinh PDF dự phòng thuần PHP chuẩn PDF-1.4 (không cần thư viện bên ngoài)
     */
    private function createFallbackPdf(string $outputPath, string $title, array $data, array $services, array $parts): void {
        $content = "%PDF-1.4\n";
        $content .= "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n";
        $content .= "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n";
        $content .= "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj\n";
        $content .= "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n";

        $txt = "BT /F1 16 Tf 50 780 Td (" . $title . ") Tj ET\n";
        $txt .= "BT /F1 12 Tf 50 750 Td (GARA PRO - 123 Duong Cau Giay, Ha Noi) Tj ET\n";
        $txt .= "BT /F1 11 Tf 50 720 Td (Bien so xe: " . ($data['bien_so'] ?? '') . " | Chu xe: " . ($data['ten_chu_xe'] ?? '') . ") Tj ET\n";
        $txt .= "BT /F1 11 Tf 50 700 Td (Tong tien: " . number_format((float)($data['tong_tien'] ?? 0)) . " VND) Tj ET\n";

        $y = 660;
        $txt .= "BT /F1 11 Tf 50 {$y} Td (CHI TIET DICH VU & PHU TUNG:) Tj ET\n";
        $y -= 20;

        foreach ($services as $s) {
            $line = "- Dich vu: " . $s['ten_dv'] . " x " . $s['so_luong'] . " = " . number_format((float)$s['thanh_tien']) . " VND";
            $txt .= "BT /F1 10 Tf 50 {$y} Td (" . $line . ") Tj ET\n";
            $y -= 18;
            if ($y < 100) break;
        }

        foreach ($parts as $p) {
            $line = "- Phu tung: " . $p['ten_pt'] . " x " . $p['so_luong'] . " = " . number_format((float)$p['thanh_tien']) . " VND";
            $txt .= "BT /F1 10 Tf 50 {$y} Td (" . $line . ") Tj ET\n";
            $y -= 18;
            if ($y < 100) break;
        }

        $streamLen = strlen($txt);
        $content .= "4 0 obj << /Length {$streamLen} >> stream\n{$txt}\nendstream\nendobj\n";
        $content .= "xref\n0 6\n0000000000 65535 f \n";
        $content .= "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n500\n%%EOF";

        file_put_contents($outputPath, $content);
    }
}

