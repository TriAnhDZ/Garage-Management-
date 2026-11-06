<div class="row">
    <!-- Header thông tin phiếu sửa chữa -->
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h2 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                        <span>Phiếu sửa chữa #<?= $psc['ma_psc'] ?></span>
                        <span class="badge badge-info" style="font-size: 1.1rem;"><?= htmlspecialchars($psc['bien_so']) ?></span>
                    </h2>
                    <small style="color: #747d8c;">
                        Khởi tạo lúc: <?= date('d/m/Y H:i', strtotime($psc['ngay_lap'])) ?> | Kỹ thuật viên phụ trách: <b><?= htmlspecialchars($psc['ten_nv'] ?? 'Chưa chỉ định') ?></b>
                    </small>
                </div>
                <div class="card-actions" style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <a href="index.php?c=repair&a=print&id=<?= $psc['ma_psc'] ?>" target="_blank" onclick="window.open(this.href, 'print_repair', 'width=1050,height=850,scrollbars=yes'); return false;" class="btn btn-secondary">
                        <i class="fas fa-print"></i> In phiếu
                    </a>
                    <a href="index.php?c=repair&a=downloadPdf&id=<?= $psc['ma_psc'] ?>" class="btn btn-primary">
                        <i class="fas fa-file-pdf"></i> Tải PDF
                    </a>
                    <?php if ($psc['trang_thai'] === 'HoanThanh' || $psc['trang_thai'] === 'DaThanhToan'): ?>
                        <a href="index.php?c=email&a=sendCompletion&id=<?= $psc['ma_psc'] ?>" class="btn btn-info" id="btnSendRepairEmail" title="Gửi email tự động qua hệ thống kèm file PDF phiếu sửa chữa" onclick="if(!confirm('Hệ thống sẽ tạo file PDF và tự động gửi Gmail thông báo tới khách hàng. Bạn có muốn gửi ngay?')) return false; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Đang gửi Gmail...'; this.style.pointerEvents='none'; this.style.opacity='0.7'; return true;">
                            <i class="fas fa-paper-plane"></i> Tự động gửi Gmail (Kèm PDF)
                        </a>
                        <a href="index.php?c=email&a=openWeb&type=repair&id=<?= $psc['ma_psc'] ?>" target="_blank" class="btn btn-danger" title="Mở trực tiếp trên web Gmail (mail.google.com) với nội dung soạn sẵn để báo khách nhận xe">
                            <i class="fab fa-google"></i> Mở Gmail Web
                        </a>
                    <?php endif; ?>
                    <?php if ($psc['trang_thai'] === 'DaThanhToan' || !empty($invoice)): ?>
                        <a href="#section-payment" class="btn btn-success" style="font-weight: 700;">
                            <i class="fas fa-check-circle"></i> Đã thu tiền (<?= !empty($invoice['ma_hd_code']) ? htmlspecialchars($invoice['ma_hd_code']) : 'Hóa đơn' ?>)
                        </a>
                    <?php elseif ($psc['tien_no'] > 0 || (float)$psc['tong_tien'] > 0): ?>
                        <a href="#section-payment" class="btn btn-success" style="font-weight: 700;">
                            <i class="fas fa-file-invoice-dollar"></i> Thu tiền &amp; Xuất hóa đơn
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-content">
                <div class="row">
                    <div class="col-12 col-m-4">
                        <p style="margin: 4px 0;"><b>Chủ xe:</b> <?= htmlspecialchars($psc['ten_chu_xe']) ?></p>
                        <p style="margin: 4px 0;"><b>Điện thoại:</b> <?= htmlspecialchars($psc['sdt_chu_xe']) ?></p>
                        <p style="margin: 4px 0;"><b>Địa chỉ:</b> <?= htmlspecialchars($psc['dia_chi'] ?? 'Chưa có') ?></p>
                    </div>
                    <div class="col-12 col-m-4">
                        <p style="margin: 4px 0;"><b>Hiệu xe:</b> <?= htmlspecialchars($psc['hang_xe']) ?> <?= htmlspecialchars($psc['dong_xe'] ?? '') ?></p>
                        <p style="margin: 4px 0;"><b>Năm sản xuất:</b> <?= htmlspecialchars($psc['nam_san_xuat'] ?? 'Không rõ') ?></p>
                        <p style="margin: 4px 0;"><b>Nợ hiện tại của xe:</b> <b style="color: #eb4d4b; font-size: 1.1rem;"><?= number_format((float)$psc['tien_no']) ?> đ</b></p>
                    </div>
                    <div class="col-12 col-m-4" style="background-color: var(--second-bg-color, #f8f9fa); padding: 15px; border-radius: 8px;">
                        <div style="font-size: 0.9rem; color: #747d8c;">Tổng chi phí phiếu sửa chữa:</div>
                        <div style="font-size: 1.8rem; font-weight: 700; color: #4834d4; margin-bottom: 10px;">
                            <?= number_format((float)$psc['tong_tien']) ?> đ
                        </div>
                        
                        <!-- Cập nhật trạng thái phiếu -->
                        <form method="POST" action="index.php?c=repair&a=updateStatus" style="display: flex; gap: 8px;" onsubmit="var b=this.querySelector('button[type=submit]'); if(b){b.innerHTML='<i class=\'fas fa-spinner fa-spin\'>
                    <?= csrf_field() ?></i> Lưu'; b.disabled=true;}">
                            <input type="hidden" name="ma_psc" value="<?= $psc['ma_psc'] ?>">
                            <select name="trang_thai" class="form-control" style="font-size: 0.85rem; padding: 4px 8px;">
                                <option value="TiepNhan" <?= ($psc['trang_thai'] === 'TiepNhan') ? 'selected' : '' ?>>1. Tiếp nhận</option>
                                <option value="DangKiemTra" <?= ($psc['trang_thai'] === 'DangKiemTra') ? 'selected' : '' ?>>2. Đang kiểm tra</option>
                                <option value="ChoKhachDuyet" <?= ($psc['trang_thai'] === 'ChoKhachDuyet') ? 'selected' : '' ?>>3. Chờ khách duyệt</option>
                                <option value="DangSuaChua" <?= ($psc['trang_thai'] === 'DangSuaChua') ? 'selected' : '' ?>>4. Đang sửa chữa</option>
                                <option value="HoanThanh" <?= ($psc['trang_thai'] === 'HoanThanh') ? 'selected' : '' ?>>5. Hoàn thành</option>
                                <option value="DaThanhToan" <?= ($psc['trang_thai'] === 'DaThanhToan') ? 'selected' : '' ?>>6. Đã thanh toán</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">Lưu</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Phần 1: Chi tiết dịch vụ & Tiền công -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-wrench"></i> 1. Chi tiết dịch vụ & tiền công sửa chữa</h3>
            </div>
            <div class="card-content">
                <!-- Form thêm dịch vụ -->
                <form method="POST" action="index.php?c=repair&a=addService" style="background: var(--second-bg-color, #f8f9fa); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="ma_psc" value="<?= $psc['ma_psc'] ?>">
                    <div class="row" style="align-items: flex-end;">
                        <div class="col-12 col-m-5">
                            <label style="font-weight: 600; font-size: 0.9rem;">
                                <i class="fas fa-wrench text-primary"></i> Dịch vụ / Tiền công: <span style="color:red">*</span>
                            </label>
                            <input type="text" 
                                   name="ten_dv" 
                                   id="input_service" 
                                   class="form-control" 
                                   list="service_list" 
                                   placeholder="Nhập tên dịch vụ..." 
                                   autocomplete="off" 
                                   required 
                                   oninput="onServiceInputChange(this.value)">
                            <datalist id="service_list">
                                <?php foreach ($allServices as $s): ?>
                                    <option value="<?= htmlspecialchars($s['ten_dv']) ?>">
                                        Giá chuẩn: <?= number_format((float)$s['gia_tieu_chuan']) ?> đ
                                    </option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-12 col-m-2">
                            <label style="font-weight: 600; font-size: 0.9rem;">Số lần / Lượng:</label>
                            <input type="number" name="so_luong" value="1" min="1" class="form-control" required>
                        </div>
                        <div class="col-12 col-m-3">
                            <label style="font-weight: 600; font-size: 0.9rem;">Đơn giá tiền công (đ):</label>
                            <input type="number" name="don_gia" id="service_price" class="form-control" placeholder="Tự động hoặc tự nhập...">
                        </div>
                        <div class="col-12 col-m-2">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-plus"></i> Thêm dịch vụ
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Bảng danh sách dịch vụ đã thêm -->
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Nội dung công việc / Dịch vụ</th>
                                <th>Số lần</th>
                                <th>Đơn giá</th>
                                <th>Thành tiền</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($services)): ?>
                                <tr><td colspan="6" style="text-align: center; color: #747d8c;">Chưa có dịch vụ nào được ghi nhận cho phiếu này.</td></tr>
                            <?php else: ?>
                                <?php $stt = 1; $totalService = 0; ?>
                                <?php foreach ($services as $sv): ?>
                                    <?php $totalService += (float)$sv['thanh_tien']; ?>
                                    <tr>
                                        <td><?= $stt++ ?></td>
                                        <td><b><?= htmlspecialchars($sv['ten_dv']) ?></b></td>
                                        <td><?= $sv['so_luong'] ?></td>
                                        <td><?= number_format((float)$sv['don_gia']) ?> đ</td>
                                        <td style="font-weight: 600; color: #4834d4;"><?= number_format((float)$sv['thanh_tien']) ?> đ</td>
                                        <td>
                                            <a href="index.php?c=repair&a=deleteService&id=<?= $sv['ma_ctdv'] ?>" 
                                               onclick="return confirm('Bạn có chắc chắn muốn xóa dịch vụ này khỏi phiếu?')"
                                               class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Xóa
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr style="background: var(--second-bg-color, #f1f2f6); font-weight: 700;">
                                    <td colspan="4" style="text-align: right;">Tổng tiền công dịch vụ:</td>
                                    <td colspan="2" style="color: #4834d4; font-size: 1.05rem;"><?= number_format($totalService) ?> đ</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Phần 2: Chi tiết vật tư phụ tùng thay thế -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-cogs"></i> 2. Chi tiết vật tư phụ tùng thay thế</h3>
            </div>
            <div class="card-content">
                <!-- Form thêm phụ tùng -->
                <form method="POST" action="index.php?c=repair&a=addPart" style="background: var(--second-bg-color, #f8f9fa); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="ma_psc" value="<?= $psc['ma_psc'] ?>">
                    <div class="row" style="align-items: flex-end;">
                        <div class="col-12 col-m-5">
                            <label style="font-weight: 600; font-size: 0.9rem;">
                                <i class="fas fa-boxes text-success"></i> Phụ tùng thay thế: <span style="color:red">*</span>
                            </label>
                            <input type="text" 
                                   name="ten_pt" 
                                   id="input_part" 
                                   class="form-control" 
                                   list="part_list" 
                                   placeholder="Nhập tên phụ tùng..." 
                                   autocomplete="off" 
                                   required 
                                   oninput="onPartInputChange(this.value)">
                            <datalist id="part_list">
                                <?php foreach ($allParts as $p): ?>
                                    <option value="<?= htmlspecialchars($p['ten_pt']) ?>">
                                        Kho: <?= $p['so_luong_ton'] ?> <?= $p['don_vi_tinh'] ?> | Giá bán: <?= number_format((float)$p['gia_ban']) ?> đ
                                    </option>
                                <?php endforeach; ?>
                            </datalist>
                            <small id="part_stock_hint" style="font-size: 0.8rem; margin-top: 3px; display: block;"></small>
                        </div>
                        <div class="col-12 col-m-2">
                            <label style="font-weight: 600; font-size: 0.9rem;">Số lượng dùng:</label>
                            <input type="number" name="so_luong" value="1" min="1" class="form-control" required>
                        </div>
                        <div class="col-12 col-m-3">
                            <label style="font-weight: 600; font-size: 0.9rem;">Đơn giá bán phụ tùng (đ):</label>
                            <input type="number" name="don_gia" id="part_price" class="form-control" placeholder="Tự động theo phụ tùng">
                        </div>
                        <div class="col-12 col-m-2">
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-plus"></i> Xuất phụ tùng
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Bảng danh sách phụ tùng đã dùng -->
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tên phụ tùng</th>
                                <th>Đơn vị tính</th>
                                <th>Số lượng</th>
                                <th>Đơn giá</th>
                                <th>Thành tiền</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($parts)): ?>
                                <tr><td colspan="7" style="text-align: center; color: #747d8c;">Chưa có vật tư phụ tùng nào được xuất cho phiếu này.</td></tr>
                            <?php else: ?>
                                <?php $stt = 1; $totalParts = 0; ?>
                                <?php foreach ($parts as $pt): ?>
                                    <?php $totalParts += (float)$pt['thanh_tien']; ?>
                                    <tr>
                                        <td><?= $stt++ ?></td>
                                        <td><b><?= htmlspecialchars($pt['ten_pt']) ?></b></td>
                                        <td><?= htmlspecialchars($pt['don_vi_tinh']) ?></td>
                                        <td><?= $pt['so_luong'] ?></td>
                                        <td><?= number_format((float)$pt['don_gia']) ?> đ</td>
                                        <td style="font-weight: 600; color: #2ed573;"><?= number_format((float)$pt['thanh_tien']) ?> đ</td>
                                        <td>
                                            <a href="index.php?c=repair&a=deletePart&id=<?= $pt['ma_ctpt'] ?>" 
                                               onclick="return confirm('Xóa phụ tùng này sẽ hoàn trả số lượng về kho. Bạn có chắc chắn?')"
                                               class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Xóa & hoàn kho
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr style="background: var(--second-bg-color, #f1f2f6); font-weight: 700;">
                                    <td colspan="5" style="text-align: right;">Tổng tiền phụ tùng:</td>
                                    <td colspan="2" style="color: #2ed573; font-size: 1.05rem;"><?= number_format($totalParts) ?> đ</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$sumServices = 0;
foreach ($services as $s) {
    $sumServices += (float)$s['thanh_tien'];
}
$sumParts = 0;
foreach ($parts as $pt) {
    $sumParts += (float)$pt['thanh_tien'];
}
$tongChiPhi = (float)$psc['tong_tien'];
if ($tongChiPhi == 0 && ($sumServices + $sumParts) > 0) {
    $tongChiPhi = $sumServices + $sumParts;
}
$tienNoXe = (float)$psc['tien_no'];
$daThanhToan = ($psc['trang_thai'] === 'DaThanhToan' || !empty($invoice));
?>

<!-- Phần 3: Quyết toán chi phí & Hóa đơn thu tiền tích hợp (Hợp nhất 2 trong 1) -->
<div class="row" id="section-payment" style="margin-top: 10px;">
    <div class="col-12">
        <div class="card" style="border: 2px solid <?= $daThanhToan ? '#10b981' : '#2563eb' ?>; border-radius: 12px; box-shadow: 0 6px 20px rgba(0,0,0,0.06);">
            <div class="card-header" style="background: <?= $daThanhToan ? 'linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%)' : 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)' ?>; border-bottom: 1px solid <?= $daThanhToan ? '#a7f3d0' : '#bfdbfe' ?>; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; padding: 16px 20px;">
                <h3 style="margin: 0; color: <?= $daThanhToan ? '#065f46' : '#1e40af' ?>; display: flex; align-items: center; gap: 10px; font-size: 1.15rem;">
                    <i class="fas <?= $daThanhToan ? 'fa-check-circle' : 'fa-cash-register' ?>"></i>
                    <span>3. Quyết toán viện phí &amp; Hóa đơn thu tiền (Hợp nhất)</span>
                </h3>
                <div>
                    <?php if ($daThanhToan): ?>
                        <span class="badge badge-success" style="font-size: 0.95rem; padding: 7px 15px; font-weight: 700;">
                            <i class="fas fa-check-double"></i> ĐÃ THANH TOÁN XONG
                        </span>
                    <?php else: ?>
                        <span class="badge badge-warning" style="font-size: 0.95rem; padding: 7px 15px; font-weight: 700;">
                            <i class="fas fa-clock"></i> CHỜ THU TIỀN QUYẾT TOÁN
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-content" style="padding: 24px;">
                <!-- Tóm tắt chi phí 4 ô trực quan -->
                <div class="row" style="margin-bottom: 22px;">
                    <div class="col-12 col-m-3" style="margin-bottom: 10px;">
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                            <div style="font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 700;">1. Tiền công dịch vụ</div>
                            <div style="font-size: 1.35rem; font-weight: 800; color: #1e293b; margin-top: 4px;"><?= number_format($sumServices) ?> đ</div>
                        </div>
                    </div>
                    <div class="col-12 col-m-3" style="margin-bottom: 10px;">
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                            <div style="font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 700;">2. Tiền vật tư phụ tùng</div>
                            <div style="font-size: 1.35rem; font-weight: 800; color: #1e293b; margin-top: 4px;"><?= number_format($sumParts) ?> đ</div>
                        </div>
                    </div>
                    <div class="col-12 col-m-3" style="margin-bottom: 10px;">
                        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px 16px;">
                            <div style="font-size: 0.8rem; color: #1d4ed8; text-transform: uppercase; font-weight: 700;">Tổng chi phí phiếu sửa chữa</div>
                            <div style="font-size: 1.35rem; font-weight: 800; color: #1e40af; margin-top: 4px;"><?= number_format($tongChiPhi) ?> đ</div>
                        </div>
                    </div>
                    <div class="col-12 col-m-3" style="margin-bottom: 10px;">
                        <div style="background: <?= $tienNoXe > 0 ? '#fef2f2' : '#f0fdf4' ?>; border: 1px solid <?= $tienNoXe > 0 ? '#fecaca' : '#bbf7d0' ?>; border-radius: 8px; padding: 14px 16px;">
                            <div style="font-size: 0.8rem; color: <?= $tienNoXe > 0 ? '#b91c1c' : '#15803d' ?>; text-transform: uppercase; font-weight: 700;">Tiền nợ hiện tại của xe</div>
                            <div style="font-size: 1.35rem; font-weight: 800; color: <?= $tienNoXe > 0 ? '#dc2626' : '#16a34a' ?>; margin-top: 4px;"><?= number_format($tienNoXe) ?> đ</div>
                        </div>
                    </div>
                </div>

                <?php if ($daThanhToan && !empty($invoice)): ?>
                    <!-- KHỐI HÓA ĐƠN ĐÃ THANH TOÁN -->
                    <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 10px; padding: 22px; margin-bottom: 10px;">
                        <div class="row" style="align-items: center;">
                            <div class="col-12 col-m-8">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                    <span style="font-size: 1.4rem; font-weight: 800; color: #166534;">
                                        <i class="fas fa-file-invoice"></i> <?= htmlspecialchars($invoice['ma_hd_code'] ?? ('#HD' . $invoice['ma_hd'])) ?>
                                    </span>
                                    <span class="badge badge-success" style="font-size: 0.85rem;">
                                        <?= ($invoice['phuong_thuc'] === 'ChuyenKhoan') ? 'Chuyển khoản (VietQR)' : 'Tiền mặt' ?>
                                    </span>
                                </div>
                                <p style="margin: 4px 0; color: #374151; font-size: 0.95rem;">
                                    <b>Số tiền đã thu:</b> <span style="font-size: 1.25rem; font-weight: 800; color: #15803d;"><?= number_format((float)$invoice['tong_tien']) ?> đ</span>
                                </p>
                                <p style="margin: 4px 0; color: #4b5563; font-size: 0.9rem;">
                                    <b>Thời gian quyết toán:</b> <?= date('d/m/Y H:i', strtotime($invoice['ngay_thanh_toan'])) ?> &nbsp;|&nbsp;
                                    <b>Người thu tiền:</b> <?= htmlspecialchars($invoice['ten_nv_thu'] ?? 'Thu ngân Gara Pro') ?>
                                </p>
                                <p style="margin: 4px 0; color: #4b5563; font-size: 0.9rem;">
                                    <b>Tình trạng nợ xe:</b> <span style="color: #16a34a; font-weight: 700;">Đã thanh toán đủ (0 đ)</span>
                                </p>
                            </div>
                            <div class="col-12 col-m-4" style="text-align: right; display: flex; flex-direction: column; gap: 8px;">
                                <a href="index.php?c=repair&a=print&id=<?= $psc['ma_psc'] ?>" target="_blank" onclick="window.open(this.href, 'print_repair', 'width=1050,height=850,scrollbars=yes'); return false;" class="btn btn-success" style="font-weight: 700; padding: 10px 16px;">
                                    <i class="fas fa-print"></i> In Phiếu &amp; Hóa đơn hợp nhất
                                </a>
                                <a href="index.php?c=invoice&a=print&id=<?= $invoice['ma_hd'] ?>" target="_blank" onclick="window.open(this.href, 'print_inv', 'width=950,height=850,scrollbars=yes'); return false;" class="btn btn-secondary" style="font-size: 0.88rem;">
                                    <i class="fas fa-file-invoice-dollar"></i> In riêng hóa đơn bán lẻ
                                </a>
                                <a href="index.php?c=invoice&a=downloadPdf&id=<?= $invoice['ma_hd'] ?>" class="btn btn-primary" style="font-size: 0.88rem;">
                                    <i class="fas fa-file-pdf"></i> Tải hóa đơn PDF
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- FORM THU TIỀN TRỰC TIẾP TẠI CHỖ (HỢP NHẤT KHÔNG CẦN CHUYỂN TRANG) -->
                    <form method="POST" action="index.php?c=repair&a=pay" style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 22px;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="ma_psc" value="<?= $psc['ma_psc'] ?>">

                        <div style="font-weight: 700; color: #1e293b; font-size: 1.05rem; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-coins text-warning"></i>
                            <span>Biểu mẫu thu tiền &amp; Xuất hóa đơn nghiệm thu xe</span>
                        </div>

                        <div class="row">
                            <div class="col-12 col-m-6">
                                <label style="font-weight: 700; font-size: 0.92rem; color: #1e293b;">
                                    Số tiền thanh toán (VNĐ): <span style="color:red">*</span>
                                </label>
                                <?php
                                $suggestAmount = ($tienNoXe > 0) ? min($tongChiPhi > 0 ? $tongChiPhi : $tienNoXe, $tienNoXe) : $tongChiPhi;
                                if ($suggestAmount <= 0 && $tienNoXe > 0) {
                                    $suggestAmount = $tienNoXe;
                                }
                                ?>
                                <input type="number" 
                                       name="so_tien_thu" 
                                       id="input_so_tien_thu" 
                                       class="form-control" 
                                       style="font-size: 1.25rem; font-weight: 800; color: #1e40af; padding: 10px 14px;" 
                                       value="<?= (int)$suggestAmount ?>" 
                                       min="1" 
                                       max="<?= (int)$tienNoXe > 0 ? (int)$tienNoXe : 999999999 ?>" 
                                       required
                                       oninput="updateQrAmount(this.value)">

                                <div style="display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap;">
                                    <?php if ($tongChiPhi > 0): ?>
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="setPaymentAmount(<?= (int)$tongChiPhi ?>)">
                                            Đúng tiền phiếu (<?= number_format($tongChiPhi) ?> đ)
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($tienNoXe > 0 && $tienNoXe != $tongChiPhi): ?>
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="setPaymentAmount(<?= (int)$tienNoXe ?>)">
                                            Toàn bộ nợ xe (<?= number_format($tienNoXe) ?> đ)
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <small style="color: #64748b; font-size: 0.8rem; display: block; margin-top: 6px;">
                                    * Theo quy định Gara: Số tiền thu không vượt quá tổng nợ của xe (<?= number_format($tienNoXe) ?> đ).
                                </small>
                            </div>

                            <div class="col-12 col-m-6">
                                <label style="font-weight: 700; font-size: 0.92rem; color: #1e293b; margin-bottom: 8px; display: block;">
                                    Phương thức thanh toán: <span style="color:red">*</span>
                                </label>
                                <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                                    <label style="flex: 1; border: 2px solid #cbd5e1; border-radius: 8px; padding: 12px; cursor: pointer; display: flex; align-items: center; gap: 10px; background: #ffffff;" id="label-method-cash">
                                        <input type="radio" name="phuong_thuc" value="TienMat" checked onchange="togglePaymentMethod('TienMat')">
                                        <div>
                                            <div style="font-weight: 700; color: #1e293b;"><i class="fas fa-money-bill-wave text-success"></i> Tiền mặt</div>
                                            <small style="color: #64748b; font-size: 0.78rem;">Thu ngân nhận tiền trực tiếp</small>
                                        </div>
                                    </label>
                                    <label style="flex: 1; border: 2px solid #cbd5e1; border-radius: 8px; padding: 12px; cursor: pointer; display: flex; align-items: center; gap: 10px; background: #ffffff;" id="label-method-bank">
                                        <input type="radio" name="phuong_thuc" value="ChuyenKhoan" onchange="togglePaymentMethod('ChuyenKhoan')">
                                        <div>
                                            <div style="font-weight: 700; color: #1e293b;"><i class="fas fa-qrcode text-primary"></i> Chuyển khoản</div>
                                            <small style="color: #64748b; font-size: 0.78rem;">Quét mã VietQR chuyển khoản</small>
                                        </div>
                                    </label>
                                </div>

                                <!-- Box VietQR hiển thị khi chọn Chuyển khoản -->
                                <div id="vietqr-box" style="display: none; background: #ffffff; border: 1.5px dashed #3b82f6; border-radius: 8px; padding: 12px; margin-bottom: 12px; text-align: center;">
                                    <div style="font-size: 0.85rem; font-weight: 700; color: #1e40af; margin-bottom: 6px;">
                                        <i class="fas fa-qrcode"></i> Quét mã VietQR để thanh toán nhanh
                                    </div>
                                    <div style="display: flex; justify-content: center; align-items: center; gap: 14px; flex-wrap: wrap;">
                                        <img id="vietqr-img" 
                                             src="https://api.vietqr.io/image/970422-0825893162-compact2.jpg?amount=<?= (int)$suggestAmount ?>&addInfo=<?= urlencode('Thanh toan PSC ' . $psc['ma_psc'] . ' ' . $psc['bien_so']) ?>&accountName=GARAGE%20PRO" 
                                             alt="Mã QR thanh toán VietQR" 
                                             style="max-width: 140px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                        <div style="text-align: left; font-size: 0.82rem; color: #334155;">
                                            <div>Ngân hàng: <b>MBBank (Quân Đội)</b></div>
                                            <div>Số tài khoản: <b style="color: #1e40af; font-size: 0.95rem;">0825.893.162</b></div>
                                            <div>Chủ tài khoản: <b>GARAGE PRO HÀ NỘI</b></div>
                                            <div>Nội dung: <b style="color: #059669;">PSC <?= $psc['ma_psc'] ?> <?= htmlspecialchars($psc['bien_so']) ?></b></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px; border-top: 1px solid #e2e8f0; padding-top: 15px;">
                            <button type="submit" 
                                    class="btn btn-success" 
                                    style="padding: 12px 28px; font-size: 1.05rem; font-weight: 800; display: inline-flex; align-items: center; gap: 10px;"
                                    onclick="if(!confirm('Xác nhận thu ' + Number(document.getElementById('input_so_tien_thu').value).toLocaleString() + ' đ và lập hóa đơn hoàn tất phiếu sửa chữa?')) return false; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Đang xử lý thanh toán...'; this.disabled=true; this.form.submit();">
                                <i class="fas fa-check-circle"></i> Xác nhận Thu tiền &amp; Lập hóa đơn
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function setPaymentAmount(val) {
    const input = document.getElementById('input_so_tien_thu');
    if (input) {
        input.value = val;
        updateQrAmount(val);
    }
}

function updateQrAmount(val) {
    const qrImg = document.getElementById('vietqr-img');
    if (qrImg) {
        const amount = parseInt(val) || 0;
        qrImg.src = `https://api.vietqr.io/image/970422-0825893162-compact2.jpg?amount=${amount}&addInfo=<?= urlencode('Thanh toan PSC ' . $psc['ma_psc'] . ' ' . $psc['bien_so']) ?>&accountName=GARAGE%20PRO`;
    }
}

function togglePaymentMethod(method) {
    const qrBox = document.getElementById('vietqr-box');
    const labelCash = document.getElementById('label-method-cash');
    const labelBank = document.getElementById('label-method-bank');

    if (method === 'ChuyenKhoan') {
        if (qrBox) qrBox.style.display = 'block';
        if (labelBank) {
            labelBank.style.borderColor = '#2563eb';
            labelBank.style.backgroundColor = '#eff6ff';
        }
        if (labelCash) {
            labelCash.style.borderColor = '#cbd5e1';
            labelCash.style.backgroundColor = '#ffffff';
        }
    } else {
        if (qrBox) qrBox.style.display = 'none';
        if (labelCash) {
            labelCash.style.borderColor = '#16a34a';
            labelCash.style.backgroundColor = '#f0fdf4';
        }
        if (labelBank) {
            labelBank.style.borderColor = '#cbd5e1';
            labelBank.style.backgroundColor = '#ffffff';
        }
    }
}
</script>

<script>
// Dữ liệu dịch vụ cho tính năng gợi ý & tự động điền đơn giá
const servicePriceMap = {
    <?php foreach ($allServices as $s): ?>
        <?= json_encode($s['ten_dv']) ?>: <?= (float)$s['gia_tieu_chuan'] ?>,
    <?php endforeach; ?>
};

function onServiceInputChange(val) {
    const trimmed = val.trim();
    const priceInput = document.getElementById('service_price');
    if (servicePriceMap[trimmed] !== undefined) {
        priceInput.value = servicePriceMap[trimmed];
    }
}

// Dữ liệu phụ tùng cho tính năng gợi ý & kiểm tra kho
const partDataMap = {
    <?php foreach ($allParts as $p): ?>
        <?= json_encode($p['ten_pt']) ?>: {
            id: <?= (int)$p['ma_pt'] ?>,
            price: <?= (float)$p['gia_ban'] ?>,
            stock: <?= (int)$p['so_luong_ton'] ?>,
            unit: <?= json_encode($p['don_vi_tinh']) ?>
        },
    <?php endforeach; ?>
};

function onPartInputChange(val) {
    const trimmed = val.trim();
    const priceInput = document.getElementById('part_price');
    const hint = document.getElementById('part_stock_hint');
    if (partDataMap[trimmed] !== undefined) {
        const item = partDataMap[trimmed];
        priceInput.value = item.price;
        hint.innerHTML = `<span style="color: #059669; font-weight: 600;"><i class="fas fa-check"></i> Kho còn: ${item.stock} ${item.unit} | Đơn giá: ${item.price.toLocaleString()} đ</span>`;
    } else {
        hint.innerHTML = '';
    }
}
</script>

