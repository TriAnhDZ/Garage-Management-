<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <h3><i class="fas fa-history"></i> Lịch sử các phiếu nhập kho phụ tùng</h3>
                <div style="display: flex; gap: 8px;">
                    <a href="index.php?c=part&a=import" class="btn btn-warning">
                        <i class="fas fa-truck-loading"></i> Nhập kho mới
                    </a>
                    <a href="index.php?c=part&a=index" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại kho &amp; phụ tùng
                    </a>
                </div>
            </div>

            <div class="card-content">
                <div class="table-responsive">
                    <table class="table-custom table-hover">
                        <thead>
                            <tr>
                                <th>Mã PN</th>
                                <th>Thời gian nhập</th>
                                <th>Tên phụ tùng</th>
                                <th>Nhà cung cấp</th>
                                <th>Số lượng</th>
                                <th>Đơn giá nhập</th>
                                <th>Tổng tiền</th>
                                <th>Thủ kho tiếp nhận</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($history)): ?>
                                <tr><td colspan="9" style="text-align: center; padding: 25px;">Chưa có phiếu nhập kho nào.</td></tr>
                            <?php else: ?>
                                <?php foreach ($history as $h): ?>
                                    <tr>
                                        <td><b>#<?= $h['ma_pn'] ?></b></td>
                                        <td><?= date('d/m/Y H:i', strtotime($h['ngay_nhap'])) ?></td>
                                        <td><b><?= htmlspecialchars($h['ten_pt']) ?></b></td>
                                        <td><?= htmlspecialchars($h['ten_ncc']) ?></td>
                                        <td><span class="badge badge-info"><?= $h['so_luong'] ?> <?= htmlspecialchars($h['don_vi_tinh']) ?></span></td>
                                        <td><?= number_format((float)$h['don_gia_nhap']) ?> đ</td>
                                        <td style="font-weight: 700; color: #eb4d4b;"><?= number_format((float)$h['thanh_tien']) ?> đ</td>
                                        <td><?= htmlspecialchars($h['ten_nv']) ?></td>
                                        <td><?= htmlspecialchars($h['ghi_chu'] ?? '-') ?></td>
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

