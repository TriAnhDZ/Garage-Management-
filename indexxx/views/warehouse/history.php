<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <h3><i class="fas fa-history"></i> Nhật ký điều chuyển vật tư phụ tùng giữa các kho</h3>
                <div style="display: flex; gap: 8px;">
                    <a href="index.php?c=warehouse&a=transfer" class="btn btn-warning">
                        <i class="fas fa-exchange-alt"></i> Điều chuyển mới
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
                                <th>Mã CK</th>
                                <th>Thời gian chuyển</th>
                                <th>Tên phụ tùng</th>
                                <th>Số lượng</th>
                                <th>Từ kho nguồn</th>
                                <th>Đến kho đích</th>
                                <th>Người thực hiện</th>
                                <th>Lý do / Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($history)): ?>
                                <tr><td colspan="8" style="text-align: center; padding: 25px;">Chưa có lần điều chuyển kho nào được ghi nhận.</td></tr>
                            <?php else: ?>
                                <?php foreach ($history as $ck): ?>
                                    <tr>
                                        <td><b>#<?= $ck['ma_ck'] ?></b></td>
                                        <td><?= date('d/m/Y H:i', strtotime($ck['ngay_chuyen'])) ?></td>
                                        <td><b><?= htmlspecialchars($ck['ten_pt']) ?></b></td>
                                        <td><span class="badge badge-info"><?= $ck['so_luong'] ?> <?= htmlspecialchars($ck['don_vi_tinh']) ?></span></td>
                                        <td><span class="badge badge-secondary"><?= htmlspecialchars($ck['ten_tu_kho']) ?></span></td>
                                        <td><span class="badge badge-success"><?= htmlspecialchars($ck['ten_den_kho']) ?></span></td>
                                        <td><?= htmlspecialchars($ck['ten_nv']) ?></td>
                                        <td><?= htmlspecialchars($ck['ghi_chu'] ?? '-') ?></td>
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

