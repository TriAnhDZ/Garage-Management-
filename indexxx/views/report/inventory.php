<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <h3><i class="fas fa-warehouse"></i> Báo cáo xuất nhập tồn kho phụ tùng</h3>
                <!-- Chọn tháng và năm -->
                <form method="GET" action="index.php" style="display: flex; gap: 8px; align-items: center;">
                    <input type="hidden" name="c" value="report">
                    <input type="hidden" name="a" value="inventory">
                    
                    <label style="font-weight: 600; margin: 0;">Tháng:</label>
                    <select name="month" class="form-control" style="width: 80px; padding: 4px 8px;">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= ($month == $m) ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endfor; ?>
                    </select>

                    <label style="font-weight: 600; margin: 0;">Năm:</label>
                    <select name="year" class="form-control" style="width: 100px; padding: 4px 8px;">
                        <?php for ($y = 2023; $y <= 2027; $y++): ?>
                            <option value="<?= $y ?>" <?= ($year == $y) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>

                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-sync-alt"></i> Xem báo cáo
                    </button>
                </form>
            </div>

            <div class="card-content">
                <div class="table-responsive">
                    <table class="table-custom table-hover">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tên vật tư phụ tùng</th>
                                <th>Đơn vị tính</th>
                                <th>Tồn đầu kỳ</th>
                                <th>Phát sinh nhập (+)</th>
                                <th>Phát sinh xuất (-)</th>
                                <th>Tồn cuối kỳ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($report['items'])): ?>
                                <tr><td colspan="7" style="text-align: center; padding: 25px;">Chưa có dữ liệu tồn kho trong tháng này.</td></tr>
                            <?php else: ?>
                                <?php $stt = 1; ?>
                                <?php foreach ($report['items'] as $item): ?>
                                    <tr>
                                        <td><?= $stt++ ?></td>
                                        <td><b><?= htmlspecialchars($item['ten_pt']) ?></b></td>
                                        <td><?= htmlspecialchars($item['don_vi_tinh']) ?></td>
                                        <td><span class="badge badge-secondary"><?= $item['ton_dau'] ?></span></td>
                                        <td><span class="badge badge-success">+<?= $item['phat_sinh_nhap'] ?></span></td>
                                        <td><span class="badge badge-danger">-<?= $item['phat_sinh_xuat'] ?></span></td>
                                        <td>
                                            <b style="font-size: 1.1rem; color: #4834d4;">
                                                <?= $item['ton_cuoi'] ?>
                                            </b>
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

