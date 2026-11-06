<?php
$ngayLap = date('d/m/Y H:i', strtotime($psc['ngay_lap']));
$tongTien = (float)$psc['tong_tien'];
$tongTienFormatted = number_format($tongTien);
$soPsc = "#PSC" . str_pad((string)$psc['ma_psc'], 6, '0', STR_PAD_LEFT);

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

// Chuyển đổi số thành chữ
function docTienInPhieuSua($number): string {
    $number = round((float)$number);
    if ($number <= 0) return 'Không đồng';
    $chuSo = ['không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
    $tien = ['', 'nghìn', 'triệu', 'tỷ', 'nghìn tỷ', 'triệu tỷ'];
    $docBlock3 = function($b, $dayDu) use ($chuSo) {
        $tram = floor($b / 100);
        $chuc = floor(($b % 100) / 10);
        $donvi = $b % 10;
        $res = '';
        if ($tram > 0 || $dayDu) {
            $res .= $chuSo[$tram] . ' trăm ';
            if ($chuc == 0 && $donvi > 0) $res .= 'lẻ ';
        }
        if ($chuc > 1) {
            $res .= $chuSo[$chuc] . ' mươi ';
            if ($donvi == 1) $res .= 'mốt ';
            else if ($donvi == 5) $res .= 'lăm ';
            else if ($donvi > 0) $res .= $chuSo[$donvi] . ' ';
        } elseif ($chuc == 1) {
            $res .= 'mười ';
            if ($donvi == 5) $res .= 'lăm ';
            else if ($donvi > 0) $res .= $chuSo[$donvi] . ' ';
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

$tienChu = docTienInPhieuSua($tongTien);
$invoice = $invoice ?? null;
$daThanhToan = ($psc['trang_thai'] === 'DaThanhToan' || !empty($invoice));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= $daThanhToan ? 'Hóa Đơn & Phiếu Sửa Chữa ' : 'In Phiếu Sửa Chữa ' ?><?= $soPsc ?></title>
    <style>
        @page { size: A4 portrait; margin: 8mm 12mm 6mm 12mm; }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { font-family: "Segoe UI", Roboto, -apple-system, BlinkMacSystemFont, Arial, sans-serif; margin: 20px auto; max-width: 900px; padding: 0 15px; font-size: 9pt; color: #1e293b; line-height: 1.35; background: #ffffff; }
        .brand-top-bar { height: 4px; background: linear-gradient(90deg, #0f172a 0%, #1e40af 50%, #0284c7 100%); margin-bottom: 8px; border-radius: 2px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .header-table td { vertical-align: top; padding: 0; }
        .company-name { font-size: 13.5pt; font-weight: 800; color: #0f172a; letter-spacing: 0.5px; text-transform: uppercase; margin: 0 0 2px 0; }
        .company-tagline { font-size: 7.5pt; font-weight: 700; color: #1e40af; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 4px; }
        .company-details { font-size: 7.8pt; color: #475569; line-height: 1.35; }
        .doc-title-main { font-size: 13.5pt; font-weight: 800; color: #0f172a; letter-spacing: 0.8px; text-transform: uppercase; margin: 0 0 1px 0; text-align: right; }
        .doc-title-sub { font-size: 7.2pt; font-weight: 600; color: #64748b; letter-spacing: 0.8px; text-transform: uppercase; text-align: right; margin-bottom: 4px; }
        .doc-meta-badge { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 4px 8px; font-size: 7.8pt; display: inline-block; text-align: left; }
        .dossier-card { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 5px; margin-bottom: 8px; padding: 8px 12px; }
        .dossier-table { width: 100%; border-collapse: collapse; }
        .dossier-table td { vertical-align: top; padding: 0; }
        .dossier-col-header { font-size: 7.2pt; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; padding-bottom: 2px; margin-bottom: 4px; }
        .dossier-row { margin-bottom: 2px; font-size: 8.5pt; color: #334155; }
        .dossier-label { color: #64748b; font-size: 8pt; display: inline-block; min-width: 90px; }
        .plate-badge { background: #ffffff; color: #0f172a; border: 1.5px solid #0f172a; border-radius: 4px; padding: 1px 7px; font-weight: 800; font-size: 10pt; letter-spacing: 1px; display: inline-block; }
        .sec-title { font-weight: 800; font-size: 8pt; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 6px; margin-bottom: 4px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .items-table th { background-color: #0f172a; color: #ffffff; font-size: 7.5pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 6px 8px; border: 1px solid #0f172a; }
        .items-table td { border: 1px solid #e2e8f0; padding: 5px 8px; font-size: 8.5pt; }
        .items-table tbody tr:nth-child(even) { background-color: #f8fafc; }
        .summary-table { width: 100%; border-collapse: collapse; margin-top: 4px; margin-bottom: 6px; }
        .summary-table td { vertical-align: top; padding: 0; }
        .words-box { font-size: 7.8pt; color: #475569; line-height: 1.45; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 4px; padding: 6px 10px; }
        .calculation-card { background: #ffffff; border: 1px solid #cbd5e1; border-radius: 4px; padding: 6px 10px; width: 310px; margin-left: auto; }
        .calc-row { display: flex; justify-content: space-between; margin-bottom: 3px; font-size: 8pt; color: #475569; }
        .grand-total-row { margin-top: 4px; padding: 5px 8px; background: #0f172a; color: #ffffff; border-radius: 3px; display: flex; justify-content: space-between; align-items: center; }
        .sign-table { width: 100%; border-collapse: collapse; margin-top: 8px; page-break-inside: avoid; }
        .sign-table td { text-align: center; vertical-align: top; padding: 0 6px; }
        .sign-role { font-size: 7.8pt; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
        .sign-note { font-size: 7.2pt; color: #64748b; font-style: italic; margin-bottom: 2px; }
        .sign-box-space { height: 44px; display: flex; align-items: center; justify-content: center; }
        .footer-terms { border-top: 1px solid #e2e8f0; margin-top: 8px; padding-top: 4px; font-size: 7pt; color: #64748b; line-height: 1.35; }

        @media print {
            body { margin: 0; padding: 0; max-width: 100%; font-size: 8.8pt; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 16px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: space-between; background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1.5px solid #cbd5e1; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
            <button onclick="window.print()" style="padding: 8px 16px; font-size: 13.5px; background: #0f172a; color: #fff; border: none; cursor: pointer; border-radius: 6px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                🖨️ In Phiếu Sửa Chữa
            </button>
            <a href="index.php?c=repair&a=downloadPdf&id=<?= $psc['ma_psc'] ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; font-size: 13.5px; background: #00b894; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 700;">
                📥 Tải file PDF chính thức
            </a>
            <?php if (!empty($isStaff)): ?>
                <a href="index.php?c=email&a=sendCompletion&id=<?= $psc['ma_psc'] ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; font-size: 13.5px; background: #1e40af; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 700;" onclick="if(!confirm('Hệ thống sẽ tạo file PDF đính kèm và tự động gửi Gmail thông báo tới khách hàng. Bạn có muốn gửi ngay?')) return false; this.innerHTML='⏳ Đang gửi Gmail...'; this.style.pointerEvents='none'; this.style.opacity='0.7'; return true;">
                    ✉️ Gửi Gmail báo xe xong (Kèm file PDF)
                </a>
                <a href="index.php?c=email&a=openWeb&type=repair&id=<?= $psc['ma_psc'] ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; font-size: 13.5px; background: #ea4335; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 700;">
                    📧 Soạn trên Gmail Web
                </a>
            <?php endif; ?>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
            <a href="index.php?c=repair&a=detail&id=<?= $psc['ma_psc'] ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; font-size: 13.5px; background: #4834d4; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 700;">
                ⬅ Quay về chi tiết phiếu sửa
            </a>
            <a href="index.php?c=repair&a=index" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; font-size: 13.5px; background: #0284c7; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600;">
                📋 Danh sách phiếu
            </a>
            <button onclick="smartClose()" style="padding: 8px 16px; font-size: 13.5px; background: #64748b; color: #fff; border: none; cursor: pointer; border-radius: 6px; font-weight: 700;">
                ✕ Đóng / Thoát
            </button>
        </div>
    </div>

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
            <td style="width: 48%; text-align: right;">
                <div class="doc-title-main"><?= $daThanhToan ? 'PHIẾU SỬA CHỮA &amp; HÓA ĐƠN THU TIỀN' : 'PHIẾU SỬA CHỮA DỊCH VỤ' ?></div>
                <div class="doc-title-sub"><?= $daThanhToan ? 'BIÊN BẢN NGHIỆM THU KIÊM HÓA ĐƠN QUYẾT TOÁN TÍCH HỢP' : 'VEHICLE REPAIR ORDER &amp; JOB CARD' ?></div>
                <div class="doc-meta-badge">
                    <div>Lệnh sửa chữa: <b style="color: #1e40af; font-family: monospace; font-size: 8.8pt;"><?= $soPsc ?></b></div>
                    <?php if (!empty($invoice)): ?>
                        <div>Mã hóa đơn: <b style="color: #047857; font-family: monospace; font-size: 8.8pt;"><?= htmlspecialchars($invoice['ma_hd_code'] ?? ('#HD' . $invoice['ma_hd'])) ?></b></div>
                        <div>Thanh toán: <b style="color: #047857;"><?= ($invoice['phuong_thuc'] === 'ChuyenKhoan') ? 'Chuyển khoản (VietQR)' : 'Tiền mặt' ?></b></div>
                    <?php else: ?>
                        <div>Trạng thái: <b style="color: #047857;"><?= htmlspecialchars($psc['trang_thai']) ?></b></div>
                    <?php endif; ?>
                    <div>Ngày lập: <b><?= $ngayLap ?></b></div>
                </div>
            </td>
        </tr>
    </table>

    <div class="dossier-card">
        <table class="dossier-table">
            <tr>
                <td style="width: 50%; padding-right: 10px; border-right: 1px solid #e2e8f0;">
                    <div class="dossier-col-header">I. THÔNG TIN KHÁCH HÀNG (CHỦ XE)</div>
                    <div class="dossier-row"><span class="dossier-label">Chủ xe:</span> <b style="color: #0f172a; font-size: 9.5pt;"><?= htmlspecialchars($psc['ten_chu_xe']) ?></b></div>
                    <div class="dossier-row"><span class="dossier-label">Điện thoại:</span> <b><?= htmlspecialchars($psc['sdt_chu_xe']) ?></b></div>
                    <div class="dossier-row"><span class="dossier-label">Địa chỉ:</span> <?= htmlspecialchars($psc['dia_chi'] ?? 'Chưa cập nhật') ?></div>
                </td>
                <td style="width: 50%; padding-left: 10px;">
                    <div class="dossier-col-header">II. HỒ SƠ PHƯƠNG TIỆN SỬA CHỮA</div>
                    <div class="dossier-row" style="margin-bottom: 2px;">
                        <span class="dossier-label" style="vertical-align: middle;">Biển số xe:</span> 
                        <span class="plate-badge"><?= htmlspecialchars($psc['bien_so']) ?></span>
                    </div>
                    <div class="dossier-row"><span class="dossier-label">Hiệu / Dòng xe:</span> <b><?= htmlspecialchars($psc['hang_xe']) ?> <?= htmlspecialchars($psc['dong_xe'] ?? '') ?></b></div>
                    <div class="dossier-row"><span class="dossier-label">Kỹ thuật viên:</span> <b><?= htmlspecialchars($psc['ten_nv'] ?? 'Tổ Kỹ Thuật Garage Pro') ?></b></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- 1. Bảng Dịch vụ Tiền công -->
    <div class="sec-title">1. TIỀN CÔNG DỊCH VỤ SỬA CHỮA &amp; BẢO DƯỠNG</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 32px; text-align: center;">STT</th>
                <th style="text-align: left;">HẠNG MỤC CÔNG VIỆC THỰC HIỆN</th>
                <th style="width: 50px; text-align: center;">SL</th>
                <th style="width: 95px; text-align: right;">ĐƠN GIÁ</th>
                <th style="width: 105px; text-align: right;">THÀNH TIỀN</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($services)): ?>
                <tr><td colspan="5" style="text-align: center; color: #64748b; padding: 6px;">Không có dịch vụ nào</td></tr>
            <?php else: ?>
                <?php $stt = 1; foreach ($services as $s): ?>
                    <tr>
                        <td style="text-align: center; color: #64748b;"><?= $stt++ ?></td>
                        <td>
                            <div style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($s['ten_dv']) ?></div>
                            <?php if (!empty($s['mo_ta'])): ?>
                                <div style="font-size: 7.2pt; color: #64748b;"><?= htmlspecialchars($s['mo_ta']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center; font-weight: 600;"><?= $s['so_luong'] ?></td>
                        <td style="text-align: right; color: #334155;"><?= number_format((float)$s['don_gia']) ?> đ</td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a;"><?= number_format((float)$s['thanh_tien']) ?> đ</td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- 2. Bảng Phụ tùng -->
    <div class="sec-title">2. VẬT TƯ &amp; PHỤ TÙNG THAY THẾ CHÍNH HÃNG</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 32px; text-align: center;">STT</th>
                <th style="text-align: left;">TÊN PHỤ TÙNG LINH KIỆN</th>
                <th style="width: 55px; text-align: center;">ĐVT</th>
                <th style="width: 45px; text-align: center;">SL</th>
                <th style="width: 95px; text-align: right;">ĐƠN GIÁ</th>
                <th style="width: 105px; text-align: right;">THÀNH TIỀN</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($parts)): ?>
                <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 6px;">Không thay thế phụ tùng</td></tr>
            <?php else: ?>
                <?php $stt = 1; foreach ($parts as $p): ?>
                    <tr>
                        <td style="text-align: center; color: #64748b;"><?= $stt++ ?></td>
                        <td>
                            <div style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($p['ten_pt']) ?></div>
                            <div style="font-size: 7.2pt; color: #64748b;">Phụ tùng chính ngạch OEM/Chính hãng</div>
                        </td>
                        <td style="text-align: center; color: #475569;"><?= htmlspecialchars($p['don_vi_tinh']) ?></td>
                        <td style="text-align: center; font-weight: 600;"><?= $p['so_luong'] ?></td>
                        <td style="text-align: right; color: #334155;"><?= number_format((float)$p['don_gia']) ?> đ</td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a;"><?= number_format((float)$p['thanh_tien']) ?> đ</td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td style="width: 50%; padding-right: 10px;">
                <div class="words-box">
                    <div style="font-weight: 700; color: #0f172a; margin-bottom: 2px;">SỐ TIỀN BẰNG CHỮ:</div>
                    <div style="font-style: italic; color: #1e40af; font-weight: 600;"><?= $tienChu ?></div>
                    <div style="margin-top: 3px; font-size: 6.8pt; color: #64748b;">
                        * Cam kết bảo hành kỹ thuật 90 ngày hoặc 5.000 km. Phụ tùng bảo hành chính hãng từ 12 đến 24 tháng.
                    </div>
                </div>
            </td>
            <td style="width: 50%;">
                <div class="calculation-card">
                    <div class="calc-row">
                        <span>Tổng chi phí công thợ:</span>
                        <b style="color: #0f172a;"><?= number_format($tienCong) ?> đ</b>
                    </div>
                    <div class="calc-row">
                        <span>Tổng phụ tùng linh kiện:</span>
                        <b style="color: #0f172a;"><?= number_format($tienPhuTung) ?> đ</b>
                    </div>
                    <div class="grand-total-row">
                        <span style="font-size: 7.8pt; text-transform: uppercase; font-weight: 700;">TỔNG QUYẾT TOÁN:</span>
                        <span style="font-size: 10.5pt; font-weight: 800; font-family: monospace;"><?= $tongTienFormatted ?> VNĐ</span>
                    </div>
                    <?php if ($daThanhToan): ?>
                        <div class="calc-row" style="margin-top: 5px; border-top: 1px dashed #cbd5e1; padding-top: 4px;">
                            <span style="font-weight: 700; color: #047857;">Số tiền đã thanh toán:</span>
                            <b style="color: #047857;"><?= number_format(!empty($invoice) ? (float)$invoice['tong_tien'] : $tongTien) ?> đ</b>
                        </div>
                        <div class="calc-row">
                            <span style="font-weight: 700; color: #047857;">Nợ còn lại của xe:</span>
                            <b style="color: #047857;">0 đ (Đã tất toán)</b>
                        </div>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    </table>

    <table class="sign-table">
        <tr>
            <td style="width: <?= $daThanhToan ? '25%' : '33.33%' ?>;">
                <div class="sign-role">KHÁCH HÀNG (CHỦ XE)</div>
                <div class="sign-note"><?= $daThanhToan ? '(Ký nhận xe &amp; đã thanh toán)' : '(Ký &amp; ghi rõ họ tên)' ?></div>
                <div class="sign-box-space"></div>
                <div style="font-weight: 700; font-size: 8.5pt; color: #0f172a;"><?= htmlspecialchars($psc['ten_chu_xe']) ?></div>
            </td>
            <td style="width: <?= $daThanhToan ? '25%' : '33.33%' ?>;">
                <div class="sign-role">KỸ THUẬT VIÊN KIỂM TRA</div>
                <div class="sign-note">(Ký xác nhận hoàn tất)</div>
                <div class="sign-box-space"></div>
                <div style="font-weight: 700; font-size: 8.5pt; color: #0f172a;"><?= htmlspecialchars($psc['ten_nv'] ?? 'Tổ Kỹ Thuật') ?></div>
            </td>
            <?php if ($daThanhToan): ?>
                <td style="width: 25%;">
                    <div class="sign-role">THỦ QUỸ / THU NGÂN</div>
                    <div class="sign-note">(Đã thu đủ tiền)</div>
                    <div class="sign-box-space"></div>
                    <div style="font-weight: 700; font-size: 8.5pt; color: #0f172a;"><?= htmlspecialchars(!empty($invoice['ten_nv_thu']) ? $invoice['ten_nv_thu'] : 'Thu Ngân Gara Pro') ?></div>
                </td>
            <?php endif; ?>
            <td style="width: <?= $daThanhToan ? '25%' : '33.33%' ?>;">
                <div class="sign-role">TRƯỞNG BAN KỸ THUẬT GARA PRO</div>
                <div class="sign-note">(Ký &amp; đóng dấu xác nhận)</div>
                <div class="sign-box-space">
                    <img src="assets/images/chuki.png" style="max-height: 48px; max-width: 110px; display: inline-block;" alt="Chữ ký &amp; Dấu">
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
                    Mã tra cứu: GP-PSC-<?= str_pad((string)$psc['ma_psc'], 6, '0', STR_PAD_LEFT) ?>
                </td>
            </tr>
        </table>
    </div>

    <script>
    function smartClose() {
        window.close();
        setTimeout(function() {
            if (!window.closed) {
                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    window.location.href = 'index.php?c=repair&a=detail&id=<?= $psc['ma_psc'] ?>';
                }
            }
        }, 150);
    }
    </script>
</body>
</html>
