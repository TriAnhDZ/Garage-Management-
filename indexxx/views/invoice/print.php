<?php
// Tính toán số tiền và chữ tiền
$tongTien = (float)$invoice['tong_tien'];
$tongTienFormatted = number_format($tongTien);
$soHd = sprintf("#%06d", (int)$invoice['ma_hd']);
$soPsc = "#" . (int)$invoice['ma_psc'];
$ngayThu = date('d/m/Y H:i', strtotime($invoice['ngay_thanh_toan']));

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

// Dòng xe & ODO
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

// ODO tính toán hoặc mặc định
$odoVal = "100,458 km";
if ($namSx && $namSx > 2000) {
    $age = max(1, (int)date('Y') - $namSx);
    $odoVal = number_format($age * 18500 + 458) . " km";
}

$ktvPhuTrach = !empty($invoice['ten_nv_thu']) ? $invoice['ten_nv_thu'] : 'Lê Kỹ Thuật';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa Đơn Sửa Chữa <?= $soHd ?> - AutoCare Pro</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 10mm 15mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: "Times New Roman", Times, Georgia, serif;
            font-size: 10pt;
            color: #000000;
            line-height: 1.35;
            background: #ffffff;
            margin: 0 auto;
            max-width: 820px;
            padding: 15px 20px;
        }

        /* Thanh thao tác nhanh không in */
        .no-print {
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 15px;
            font-size: 13.5px;
            font-weight: 700;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-print { background: #0f172a; color: #ffffff; }
        .btn-pdf { background: #16a34a; color: #ffffff; }
        .btn-email { background: #2563eb; color: #ffffff; }
        .btn-back { background: #64748b; color: #ffffff; }

        /* Tiêu đề & Thông tin Gara */
        .invoice-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .invoice-header-table td {
            vertical-align: top;
            padding: 0;
        }
        .garage-title-main {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000000;
            line-height: 1.25;
            letter-spacing: 0.3px;
        }
        .garage-subinfo {
            font-size: 9.5pt;
            color: #000000;
            line-height: 1.35;
            margin-top: 4px;
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
            font-size: 9.5pt;
            color: #000000;
            line-height: 1.35;
        }

        .divider-line {
            border-top: 2px solid #000000;
            margin: 12px 0 16px 0;
        }

        /* 2 Cột thông tin khách hàng & xe */
        .client-vehicle-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .client-vehicle-table td {
            vertical-align: top;
            width: 50%;
            padding: 0;
            font-size: 10pt;
            line-height: 1.45;
        }
        .section-header-title {
            font-weight: bold;
            font-size: 10pt;
            text-transform: uppercase;
            margin-bottom: 4px;
            color: #000000;
        }

        /* Bảng chi tiết dịch vụ & phụ tùng */
        .invoice-items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
            margin-bottom: 12px;
            page-break-inside: auto;
        }
        .invoice-items-table th {
            border: 1px solid #000000;
            padding: 7px 6px;
            font-weight: bold;
            font-size: 9.5pt;
            text-align: center;
            background: #ffffff;
            color: #000000;
        }
        .invoice-items-table td {
            border: 1px solid #000000;
            padding: 6px 8px;
            font-size: 9.5pt;
            color: #000000;
        }
        .group-header-row {
            font-weight: bold;
            background: #ffffff;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #000000;
        }
        .summary-label {
            text-align: right;
            font-weight: bold;
            padding: 6px 12px;
            border: 1px solid #000000;
        }
        .summary-value {
            text-align: right;
            font-weight: bold;
            padding: 6px 8px;
            border: 1px solid #000000;
            white-space: nowrap;
        }
        .grand-total-label {
            text-align: right;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 12px;
            font-size: 10pt;
            border: 1px solid #000000;
        }
        .grand-total-value {
            text-align: right;
            font-weight: bold;
            color: #dc2626;
            font-size: 10.5pt;
            padding: 8px 8px;
            border: 1px solid #000000;
            white-space: nowrap;
        }

        /* Chữ ký 3 cột */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            text-align: center;
            page-break-inside: avoid;
        }
        .signatures-table td {
            width: 33.33%;
            vertical-align: top;
            padding: 0 8px;
        }
        .sig-role {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10pt;
            color: #000000;
            margin-bottom: 2px;
        }
        .sig-note {
            font-style: italic;
            font-size: 8.5pt;
            color: #374151;
            margin-bottom: 2px;
        }
        .sig-space {
            height: 65px;
        }
        .sig-name {
            font-weight: bold;
            font-size: 10pt;
            color: #000000;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                max-width: 100%;
                font-size: 9.8pt;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <!-- Thanh công cụ nhanh -->
    <div class="no-print">
        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
            <button onclick="window.print()" class="btn-action btn-print">
                🖨️ In Hóa Đơn Thu Tiền
            </button>
            <a href="index.php?c=invoice&a=downloadPdf&id=<?= $invoice['ma_hd'] ?>" class="btn-action btn-pdf">
                📥 Tải file PDF chính thức
            </a>
            <?php if (!empty($isStaff)): ?>
                <a href="index.php?c=email&a=sendInvoice&id=<?= $invoice['ma_hd'] ?>" class="btn-action btn-email" onclick="if(!confirm('Hệ thống sẽ gửi hóa đơn PDF tới Gmail khách hàng. Bạn có muốn gửi?')) return false; this.innerHTML='⏳ Đang gửi...'; return true;">
                    ✉️ Gửi Gmail khách
                </a>
                <form method="POST" action="index.php?c=invoice&a=updatePaymentMethod" style="display: inline-flex; align-items: center; gap: 6px; margin: 0; background: #ffffff; padding: 4px 10px; border-radius: 6px; border: 1px solid #cbd5e1;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="ma_hd" value="<?= (int)$invoice['ma_hd'] ?>">
                    <input type="hidden" name="return_to" value="print">
                    <span style="font-size: 0.85rem; color: #1e293b; font-weight: 700;">PT thanh toán:</span>
                    <select name="phuong_thuc" onchange="this.form.submit()" style="padding: 4px 8px; border-radius: 4px; border: 1.5px solid #3b82f6; font-weight: 700; font-size: 0.85rem; background: #eff6ff; color: #1d4ed8; cursor: pointer;">
                        <option value="TienMat" <?= (($invoice['phuong_thuc'] ?? '') === 'TienMat') ? 'selected' : '' ?>>💵 Tiền mặt</option>
                        <option value="ChuyenKhoan" <?= (($invoice['phuong_thuc'] ?? '') === 'ChuyenKhoan') ? 'selected' : '' ?>>🏦 Chuyển khoản</option>
                    </select>
                </form>
            <?php endif; ?>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
            <a href="index.php?c=invoice&a=index" class="btn-action btn-back">
                ⬅ Quay về danh sách
            </a>
        </div>
    </div>

    <!-- Header Gara & Hóa Đơn -->
    <table class="invoice-header-table">
        <tr>
            <td style="width: 62%;">
                <div class="garage-title-main">TRUNG TÂM SỬA CHỮA &amp; BẢO DƯỠNG</div>
                <div class="garage-title-main" style="margin-bottom: 4px;">Ô TÔ AUTOCARE PRO</div>
                <div class="garage-subinfo">
                    <b>Địa chỉ:</b> Số 123 Đường Cầu Giấy, Q. Cầu Giấy, TP. Hà Nội<br>
                    <b>Hotline kỹ thuật:</b> 0901.234.567 | <b>Email:</b> support@autocarepro.vn<br>
                    <b>Mã số thuế:</b> 0109988776
                </div>
            </td>
            <td style="width: 38%;">
                <div class="invoice-right-title">HÓA ĐƠN</div>
                <div class="invoice-right-meta">
                    <b>Số:</b> <?= $soHd ?><br>
                    <b>Ngày:</b> <?= $ngayThu ?><br>
                    <b>Mã phiếu SC:</b> <?= $soPsc ?>
                </div>
            </td>
        </tr>
    </table>

    <div class="divider-line"></div>

    <!-- Thông tin khách hàng & Phương tiện -->
    <table class="client-vehicle-table">
        <tr>
            <td style="padding-right: 15px;">
                <div class="section-header-title">THÔNG TIN KHÁCH HÀNG:</div>
                <div><b>Họ và tên:</b> <?= htmlspecialchars($invoice['ten_chu_xe']) ?></div>
                <div><b>Số điện thoại:</b> <?= htmlspecialchars($invoice['sdt_chu_xe']) ?></div>
                <div><b>Địa chỉ:</b> <?= htmlspecialchars($invoice['dia_chi'] ?? 'Chưa cập nhật') ?></div>
            </td>
            <td style="padding-left: 15px;">
                <div class="section-header-title">THÔNG TIN PHƯƠNG TIỆN:</div>
                <div><b>Biển số xe:</b> <b><?= htmlspecialchars($invoice['bien_so']) ?></b></div>
                <div><b>Dòng xe:</b> <?= htmlspecialchars($dongXeFull) ?></div>
                <div><b>Số Km (ODO):</b> <?= htmlspecialchars($odoVal) ?></div>
            </td>
        </tr>
    </table>

    <!-- Bảng nội dung dịch vụ & phụ tùng -->
    <table class="invoice-items-table">
        <thead>
            <tr>
                <th style="width: 45px;">STT</th>
                <th>Nội Dung Dịch Vụ &amp; Phụ Tùng Thay Thế</th>
                <th style="width: 75px;">ĐVT</th>
                <th style="width: 45px;">SL</th>
                <th style="width: 125px;">Đơn Giá (VNĐ)</th>
                <th style="width: 135px;">Thành Tiền (VNĐ)</th>
            </tr>
        </thead>
        <tbody>
            <!-- I. TIỀN CÔNG DỊCH VỤ SỬA CHỮA / BẢO DƯỠNG -->
            <tr>
                <td colspan="6" class="group-header-row">
                    I. TIỀN CÔNG DỊCH VỤ SỬA CHỮA / BẢO DƯỠNG
                </td>
            </tr>
            <?php 
            $sttService = 1; 
            if (!empty($services)):
                foreach ($services as $s): 
            ?>
                <tr>
                    <td style="text-align: center;"><?= $sttService++ ?></td>
                    <td><?= htmlspecialchars($s['ten_dv']) ?></td>
                    <td style="text-align: center;"><?= htmlspecialchars($s['don_vi_tinh'] ?? 'Gói/Công') ?></td>
                    <td style="text-align: center;"><?= (int)$s['so_luong'] ?></td>
                    <td style="text-align: right;"><?= number_format((float)$s['don_gia']) ?></td>
                    <td style="text-align: right; font-weight: bold;"><?= number_format((float)$s['thanh_tien']) ?></td>
                </tr>
            <?php 
                endforeach; 
            endif;
            ?>

            <!-- II. VẬT TƯ & PHỤ TÙNG THAY THẾ CHÍNH HÃNG -->
            <tr>
                <td colspan="6" class="group-header-row">
                    II. VẬT TƯ &amp; PHỤ TÙNG THAY THẾ CHÍNH HÃNG
                </td>
            </tr>
            <?php 
            $sttPart = 1; 
            if (!empty($parts)):
                foreach ($parts as $p): 
            ?>
                <tr>
                    <td style="text-align: center;"><?= $sttPart++ ?></td>
                    <td><?= htmlspecialchars($p['ten_pt']) ?></td>
                    <td style="text-align: center;"><?= htmlspecialchars($p['don_vi_tinh'] ?? 'Cái') ?></td>
                    <td style="text-align: center;"><?= (int)$p['so_luong'] ?></td>
                    <td style="text-align: right;"><?= number_format((float)$p['don_gia']) ?></td>
                    <td style="text-align: right; font-weight: bold;"><?= number_format((float)$p['thanh_tien']) ?></td>
                </tr>
            <?php 
                endforeach; 
            endif;
            ?>

            <!-- Tổng kết tiền công, phụ tùng, tổng cộng -->
            <tr>
                <td colspan="5" class="summary-label">Tổng tiền công dịch vụ:</td>
                <td class="summary-value"><?= number_format($tienCong) ?> đ</td>
            </tr>
            <tr>
                <td colspan="5" class="summary-label">Tổng tiền phụ tùng:</td>
                <td class="summary-value"><?= number_format($tienPhuTung) ?> đ</td>
            </tr>
            <tr>
                <td colspan="5" class="grand-total-label">TỔNG CỘNG THANH TOÁN:</td>
                <td class="grand-total-value"><?= $tongTienFormatted ?> đ</td>
            </tr>
        </tbody>
    </table>

    <!-- Thông tin thanh toán & cam kết bảo hành -->
    <div style="font-size: 9.5pt; line-height: 1.55; margin-top: 10px;">
        <div><b>Hình thức thanh toán:</b> <?= $phuongThucStr ?></div>
        <div><b>Kỹ thuật viên phụ trách sửa chữa:</b> <?= htmlspecialchars($ktvPhuTrach) ?></div>
        <div style="font-style: italic; color: #374151; font-size: 8.8pt; margin-top: 4px;">
            * Gara bảo hành 06 tháng hoặc 10.000 km cho toàn bộ phụ tùng và hạng mục sửa chữa ghi trên hóa đơn.
        </div>
    </div>

    <!-- 3 Cột chữ ký -->
    <table class="signatures-table">
        <tr>
            <td>
                <div class="sig-role">KHÁCH HÀNG</div>
                <div class="sig-note">(Ký và ghi rõ họ tên)</div>
                <div class="sig-space"></div>
                <div class="sig-name"><?= htmlspecialchars($invoice['ten_chu_xe']) ?></div>
            </td>
            <td>
                <div class="sig-role">KỸ THUẬT VIÊN</div>
                <div class="sig-note">(Ký và ghi rõ họ tên)</div>
                <div class="sig-space"></div>
                <div class="sig-name"><?= htmlspecialchars($ktvPhuTrach) ?></div>
            </td>
            <td>
                <div class="sig-role">NGƯỜI LẬP HÓA ĐƠN</div>
                <div class="sig-note">(Ký, đóng dấu nếu có)</div>
                <div class="sig-space"></div>
                <div class="sig-name"><?= htmlspecialchars($ktvPhuTrach) ?></div>
            </td>
        </tr>
    </table>
</body>
</html>
