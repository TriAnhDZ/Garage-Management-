<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="margin: 0;"><i class="fas fa-car"></i> Danh sách xe tiếp nhận hôm nay (<?= date('d/m/Y') ?>)</h3>
                    <small style="color: #747d8c;">
                        Đã tiếp nhận: <b><?= $todayCount ?></b> / <b><?= $dailyLimit ?></b> xe tối đa theo quy định
                    </small>
                </div>
                <div>
                    <?php if ($todayCount < $dailyLimit): ?>
                        <a href="index.php?c=reception&a=create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tiếp nhận xe mới
                        </a>
                    <?php else: ?>
                        <span class="badge badge-danger" style="padding: 8px 12px; font-size: 0.9rem;">
                            <i class="fas fa-ban"></i> Đã đạt giới hạn tiếp nhận hôm nay
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-content">
                <div class="table-responsive">
                    <table class="table-custom table-hover">
                        <thead>
                            <tr>
                                <th>Mã tiếp nhận</th>
                                <th>Biển số xe</th>
                                <th>Hiệu xe / Đời xe</th>
                                <th>Chủ xe</th>
                                <th>Số điện thoại</th>
                                <th>Địa chỉ</th>
                                <th>Thời gian</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($receptions)): ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 25px;">
                                        Hôm nay chưa tiếp nhận xe nào. Nhấn <b>"Tiếp nhận xe mới"</b> để lập phiếu tiếp nhận!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($receptions as $rc): ?>
                                    <tr>
                                        <td><b>#<?= $rc['ma_psc'] ?></b></td>
                                        <td><span class="badge badge-info" style="font-size: 0.95rem;"><?= htmlspecialchars($rc['bien_so']) ?></span></td>
                                        <td><b><?= htmlspecialchars($rc['hang_xe']) ?></b> <?= htmlspecialchars($rc['dong_xe'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($rc['ten_chu_xe']) ?></td>
                                        <td><?= htmlspecialchars($rc['sdt']) ?></td>
                                        <td><?= htmlspecialchars($rc['dia_chi'] ?? 'Chưa có') ?></td>
                                        <td><?= date('H:i:s', strtotime($rc['ngay_lap'])) ?></td>
                                        <td>
                                            <span class="badge badge-warning"><?= $rc['trang_thai'] ?></span>
                                        </td>
                                        <td>
                                            <a href="index.php?c=repair&a=detail&id=<?= $rc['ma_psc'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-tools"></i> Lập phiếu sửa
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

