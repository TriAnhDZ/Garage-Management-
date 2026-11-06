<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <h3><i class="fas fa-file-invoice-dollar"></i> Danh sách phiếu thu tiền / Hóa đơn</h3>
                <a href="index.php?c=invoice&a=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Lập phiếu thu tiền mới
                </a>
            </div>

            <div class="card-content">
                <!-- Bộ tìm kiếm -->
                <form method="GET" action="index.php" style="margin-bottom: 20px;">
                    <input type="hidden" name="c" value="invoice">
                    <input type="hidden" name="a" value="index">
                    <div class="row" style="align-items: flex-end;">
                        <div class="col-12 col-m-6">
                            <label>Tìm theo biển số xe, tên khách hàng hoặc mã hóa đơn:</label>
                            <input type="text" name="keyword" class="form-control" value="<?= htmlspecialchars($keyword ?? '') ?>" placeholder="VD: 30A-123.45 hoặc Tuấn...">
                        </div>
                        <div class="col-12 col-m-4">
                            <button type="submit" class="btn btn-info"><i class="fas fa-search"></i> Tìm kiếm</button>
                            <a href="index.php?c=invoice&a=index" class="btn btn-secondary"><i class="fas fa-redo"></i> Đặt lại</a>
                        </div>
                    </div>
                </form>

                <!-- Bảng danh sách hóa đơn -->
                <div class="table-responsive">
                    <table class="table-custom table-hover">
                        <thead>
                            <tr>
                                <th>Mã HĐ</th>
                                <th>Mã phiếu sửa</th>
                                <th>Biển số xe</th>
                                <th>Chủ xe / SĐT</th>
                                <th>Ngày thu</th>
                                <th>Số tiền thu</th>
                                <th>Phương thức</th>
                                <th>Thu ngân</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($invoices)): ?>
                                <tr><td colspan="9" style="text-align: center; padding: 25px;">Chưa có phiếu thu nào được lập.</td></tr>
                            <?php else: ?>
                                <?php foreach ($invoices as $inv): ?>
                                    <tr>
                                        <td><b class="text-primary"><?= htmlspecialchars($inv['ma_hd_code'] ?? ('#HD' . $inv['ma_hd'])) ?></b></td>
                                        <td><a href="index.php?c=repair&a=detail&id=<?= $inv['ma_psc'] ?>">#<?= $inv['ma_psc'] ?></a></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($inv['bien_so']) ?></span></td>
                                        <td>
                                            <b><?= htmlspecialchars($inv['ten_chu_xe']) ?></b><br>
                                            <small><?= htmlspecialchars($inv['sdt']) ?></small>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($inv['ngay_thanh_toan'])) ?></td>
                                        <td style="font-weight: 700; color: #2ed573; font-size: 1.05rem;">
                                            <?= number_format((float)$inv['tong_tien']) ?> đ
                                        </td>
                                        <td>
                                            <?php if ($inv['phuong_thuc'] === 'ChuyenKhoan'): ?>
                                                <span class="badge badge-primary" style="font-size: 0.85rem; padding: 5px 10px;">
                                                    <i class="fas fa-university"></i> Chuyển khoản
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-success" style="font-size: 0.85rem; padding: 5px 10px;">
                                                    <i class="fas fa-money-bill-wave"></i> Tiền mặt
                                                </span>
                                            <?php endif; ?>
                                            <div style="margin-top: 4px;">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                        style="padding: 2px 7px; font-size: 0.74rem; font-weight: 600; border-radius: 4px;"
                                                        onclick="openEditPaymentModal(<?= (int)$inv['ma_hd'] ?>, '<?= htmlspecialchars($inv['phuong_thuc']) ?>', '<?= htmlspecialchars($inv['ma_hd_code'] ?? ('#HD' . $inv['ma_hd'])) ?>')"
                                                        title="Chỉnh sửa phương thức thanh toán">
                                                    <i class="fas fa-edit"></i> Đổi
                                                </button>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($inv['ten_nv_thu'] ?? 'Thu ngân') ?></td>
                                        <td>
                                            <a href="index.php?c=invoice&a=print&id=<?= $inv['ma_hd'] ?>" target="_blank" onclick="window.open(this.href, 'print_invoice', 'width=1050,height=850,scrollbars=yes'); return false;" class="btn btn-sm btn-primary">
                                                <i class="fas fa-print"></i> Xem & In
                                            </a>
                                            <a href="index.php?c=email&a=openWeb&type=invoice&id=<?= $inv['ma_hd'] ?>" target="_blank" class="btn btn-sm btn-danger" title="Đưa sang web Gmail (mail.google.com) với nội dung soạn sẵn để bấm Gửi ngay">
                                                <i class="fab fa-google"></i> Mở Gmail Web
                                            </a>
                                            <a href="index.php?c=email&a=sendInvoice&id=<?= $inv['ma_hd'] ?>" class="btn btn-sm btn-success" title="Gửi hóa đơn tự động kèm PDF" onclick="this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Đang gửi...'; this.style.pointerEvents='none'; this.style.opacity='0.7'; return true;">
                                                <i class="fas fa-paper-plane"></i> Gửi tự động
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Chỉnh sửa phương thức thanh toán -->
<div id="editPaymentModal" style="display: none; position: fixed; z-index: 9999; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
    <div style="background: #ffffff; width: 100%; max-width: 440px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); overflow: hidden; margin: 20px;">
        <div style="background: #1e293b; color: #ffffff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-exchange-alt text-primary"></i> Đổi Phương Thức Thanh Toán
            </h4>
            <button type="button" onclick="closeEditPaymentModal()" style="background: none; border: none; color: #fff; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="index.php?c=invoice&a=updatePaymentMethod" style="padding: 20px;">
            <?= csrf_field() ?>
            <input type="hidden" name="ma_hd" id="modal_ma_hd" value="">
            <p style="margin: 0 0 15px; color: #475569; font-size: 0.95rem;">
                Hóa đơn: <b id="modal_hd_code" class="text-primary">#HD</b>
            </p>
            <div class="form-group">
                <label style="font-weight: 700; color: #1e293b; margin-bottom: 8px; display: block;">Chọn phương thức mới:</label>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <label style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer;">
                        <input type="radio" name="phuong_thuc" id="modal_pt_tienmat" value="TienMat" style="transform: scale(1.2);">
                        <span style="font-weight: 700; color: #15803d;"><i class="fas fa-money-bill-wave"></i> Tiền mặt</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer;">
                        <input type="radio" name="phuong_thuc" id="modal_pt_chuyenkhoan" value="ChuyenKhoan" style="transform: scale(1.2);">
                        <span style="font-weight: 700; color: #1e40af;"><i class="fas fa-university"></i> Chuyển khoản ngân hàng</span>
                    </label>
                </div>
            </div>
            <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeEditPaymentModal()" class="btn btn-secondary">Hủy</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditPaymentModal(maHd, currentPt, hdCode) {
    document.getElementById('modal_ma_hd').value = maHd;
    document.getElementById('modal_hd_code').innerText = hdCode;
    if (currentPt === 'ChuyenKhoan') {
        document.getElementById('modal_pt_chuyenkhoan').checked = true;
    } else {
        document.getElementById('modal_pt_tienmat').checked = true;
    }
    const modal = document.getElementById('editPaymentModal');
    modal.style.display = 'flex';
}
function closeEditPaymentModal() {
    document.getElementById('editPaymentModal').style.display = 'none';
}
window.addEventListener('click', function(e) {
    const modal = document.getElementById('editPaymentModal');
    if (e.target === modal) {
        modal.style.display = 'none';
    }
});
</script>

