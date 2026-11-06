<div class="row">
    <div class="col-12 col-m-8" style="margin: 0 auto;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-hand-holding-usd"></i> Lập phiếu thu tiền</h3>
            </div>
            <div class="card-content">
                <!-- Bước 1: Tìm kiếm xe theo biển số -->
                <form method="GET" action="index.php" style="margin-bottom: 25px;">
                    <input type="hidden" name="c" value="invoice">
                    <input type="hidden" name="a" value="create">
                    <label style="font-weight: 600;">Bước 1: Nhập hoặc chọn biển số xe cần thu tiền:</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" name="bien_so" class="form-control" 
                               value="<?= htmlspecialchars($bienSo) ?>" 
                               placeholder="VD: 30A-123.45 hoặc 43A-678.90" 
                               style="text-transform: uppercase;" required>
                        <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                            <i class="fas fa-search"></i> Kiểm tra nợ
                        </button>
                    </div>
                </form>

                <?php if ($paymentInfo): ?>
                    <?php 
                        $car = $paymentInfo['car']; 
                        $repairs = $paymentInfo['repairs']; 
                    ?>
                    <div style="background: var(--second-bg-color, #f8f9fa); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <h4 style="margin-top: 0; color: #4834d4;"><i class="fas fa-info-circle"></i> Thông tin chủ xe & Xe</h4>
                        <div class="row">
                            <div class="col-12 col-m-6">
                                <p style="margin: 3px 0;">Chủ xe: <b><?= htmlspecialchars($car['ten_chu_xe']) ?></b></p>
                                <p style="margin: 3px 0;">Số điện thoại: <b><?= htmlspecialchars($car['sdt']) ?></b></p>
                                <p style="margin: 3px 0;">Địa chỉ: <?= htmlspecialchars($car['dia_chi'] ?? 'Chưa cập nhật') ?></p>
                            </div>
                            <div class="col-12 col-m-6">
                                <p style="margin: 3px 0;">Biển số: <span class="badge badge-info" style="font-size: 1rem;"><?= htmlspecialchars($car['bien_so']) ?></span></p>
                                <p style="margin: 3px 0;">Hiệu xe: <b><?= htmlspecialchars($car['hang_xe']) ?></b> <?= htmlspecialchars($car['dong_xe'] ?? '') ?></p>
                                <p style="margin: 3px 0;">
                                    Tiền nợ hiện tại: <b style="color: #eb4d4b; font-size: 1.25rem;"><?= number_format((float)$car['tien_no']) ?> đ</b>
                                </p>
                            </div>
                        </div>
                    </div>

                    <?php if ($car['tien_no'] <= 0): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Xe này hiện <b>không có nợ</b> (Tiền nợ = 0 đ). Không cần lập phiếu thu tiền!
                        </div>
                    <?php else: ?>
                        <!-- Bước 2: Form thu tiền -->
                        <form method="POST" action="index.php?c=invoice&a=store" onsubmit="return validatePayment(<?= (float)$car['tien_no'] ?>)">
                            <?= csrf_field() ?>
                            <h4 style="margin-top: 0; color: #2ed573;"><i class="fas fa-receipt"></i> Bước 2: Nhập thông tin thanh toán</h4>
                            
                            <div class="form-group">
                                <label for="ma_psc">Chọn phiếu sửa chữa cần thanh toán: <span style="color:red">*</span></label>
                                <select name="ma_psc" id="ma_psc" class="form-control" required>
                                    <?php foreach ($repairs as $r): ?>
                                        <option value="<?= $r['ma_psc'] ?>">
                                            Phiếu #<?= $r['ma_psc'] ?> - Ngày <?= date('d/m/Y', strtotime($r['ngay_lap'])) ?> - Tổng tiền: <?= number_format((float)$r['tong_tien']) ?> đ [<?= $r['trang_thai'] ?>]
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="so_tien_thu">Số tiền thu (VNĐ): <span style="color:red">*</span></label>
                                <input type="number" name="so_tien_thu" id="so_tien_thu" 
                                       class="form-control" 
                                       value="<?= (float)$car['tien_no'] ?>" 
                                       max="<?= (float)$car['tien_no'] ?>" 
                                       min="1000" step="1000" required>
                                <small style="color: #747d8c;">
                                    * Quy định Gara: Số tiền thu không được vượt quá số tiền nợ hiện tại (Tối đa: <?= number_format((float)$car['tien_no']) ?> đ).
                                </small>
                            </div>

                            <div class="form-group">
                                <label style="font-weight: 700; color: #1e293b; margin-bottom: 8px; display: block;">
                                    <i class="fas fa-credit-card text-primary"></i> Phương thức thanh toán: <span style="color:red">*</span>
                                </label>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 12px;">
                                    <label style="display: flex; align-items: center; gap: 12px; padding: 14px 18px; border: 2px solid #22c55e; border-radius: 10px; background: #f0fdf4; cursor: pointer; transition: all 0.2s;" id="label_tienmat">
                                        <input type="radio" name="phuong_thuc" value="TienMat" checked onchange="togglePaymentBox(this.value)" style="transform: scale(1.3);">
                                        <div>
                                            <div style="font-weight: 700; color: #15803d; font-size: 1rem;"><i class="fas fa-money-bill-wave"></i> Tiền mặt</div>
                                            <div style="font-size: 0.8rem; color: #166534;">Khách nộp tiền mặt trực tiếp tại quầy</div>
                                        </div>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 12px; padding: 14px 18px; border: 2px solid #cbd5e1; border-radius: 10px; background: #ffffff; cursor: pointer; transition: all 0.2s;" id="label_chuyenkhoan">
                                        <input type="radio" name="phuong_thuc" value="ChuyenKhoan" onchange="togglePaymentBox(this.value)" style="transform: scale(1.3);">
                                        <div>
                                            <div style="font-weight: 700; color: #1e40af; font-size: 1rem;"><i class="fas fa-university"></i> Chuyển khoản</div>
                                            <div style="font-size: 0.8rem; color: #475569;">Quét mã VietQR hoặc chuyển STK</div>
                                        </div>
                                    </label>
                                </div>

                                <!-- Hộp thông tin tài khoản ngân hàng khi chọn Chuyển khoản -->
                                <div id="bank_transfer_box" style="display: none; background: #eff6ff; border: 1.5px dashed #3b82f6; border-radius: 10px; padding: 16px; margin-top: 10px;">
                                    <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                                        <div style="flex: 1; min-width: 250px;">
                                            <div style="font-weight: 700; color: #1e40af; margin-bottom: 6px;"><i class="fas fa-qrcode text-primary"></i> Thông tin tài khoản Garage Pro:</div>
                                            <div style="font-size: 0.9rem; color: #334155; line-height: 1.6;">
                                                • Ngân hàng: <b>MB Bank (Ngân hàng Quân Đội)</b><br>
                                                • Số tài khoản: <b style="color: #2563eb; font-size: 1.1rem; letter-spacing: 1px;">0825893162</b><br>
                                                • Chủ tài khoản: <b>GARAGE PRO HA NOI</b><br>
                                                • Nội dung CK: <b style="color: #b91c1c;">TT PSC<?= $psc['ma_psc'] ?> <?= htmlspecialchars($car['bien_so']) ?></b>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top: 25px; display: flex; gap: 10px;">
                                <button type="submit" class="btn btn-success" style="font-weight: 600;">
                                    <i class="fas fa-check-circle"></i> Xác nhận lập phiếu thu tiền
                                </button>
                                <a href="index.php?c=invoice&a=index" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Quay lại
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function validatePayment(maxDebt) {
    const amount = parseFloat(document.getElementById('so_tien_thu').value);
    if (isNaN(amount) || amount <= 0) {
        alert('Vui lòng nhập số tiền thu hợp lệ lớn hơn 0!');
        return false;
    }
    if (amount > maxDebt) {
        alert('Theo quy định: Số tiền thu (' + amount.toLocaleString('vi-VN') + ' đ) không được vượt quá số tiền nợ (' + maxDebt.toLocaleString('vi-VN') + ' đ)!');
        return false;
    }
    return true;
}

function togglePaymentBox(val) {
    const box = document.getElementById('bank_transfer_box');
    const lblTm = document.getElementById('label_tienmat');
    const lblCk = document.getElementById('label_chuyenkhoan');
    if (val === 'ChuyenKhoan') {
        if (box) box.style.display = 'block';
        if (lblCk) { lblCk.style.borderColor = '#2563eb'; lblCk.style.background = '#eff6ff'; }
        if (lblTm) { lblTm.style.borderColor = '#cbd5e1'; lblTm.style.background = '#ffffff'; }
    } else {
        if (box) box.style.display = 'none';
        if (lblTm) { lblTm.style.borderColor = '#22c55e'; lblTm.style.background = '#f0fdf4'; }
        if (lblCk) { lblCk.style.borderColor = '#cbd5e1'; lblCk.style.background = '#ffffff'; }
    }
}
</script>

