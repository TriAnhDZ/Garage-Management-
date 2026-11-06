<!-- Stat Widgets -->
<div class="row">
    <!-- 1. Xe tiếp nhận hôm nay -->
    <div class="col-12 col-m-6 col-m-3">
        <div class="stats-card bg-primary">
            <div class="stats-icon"><i class="fas fa-car-side"></i></div>
            <div class="stats-info">
                <h3><?= $stats['today_receptions'] ?> / <?= $dailyLimit ?></h3>
                <p>Tiếp nhận hôm nay</p>
            </div>
        </div>
    </div>

    <!-- 2. Xe đang sửa chữa -->
    <div class="col-12 col-m-6 col-m-3">
        <div class="stats-card bg-warning">
            <div class="stats-icon"><i class="fas fa-tools"></i></div>
            <div class="stats-info">
                <h3><?= $stats['repairing_count'] ?></h3>
                <p>Phiếu đang sửa chữa</p>
            </div>
        </div>
    </div>

    <!-- 3. Doanh thu tháng -->
    <div class="col-12 col-m-6 col-m-3">
        <div class="stats-card bg-success">
            <div class="stats-icon"><i class="fas fa-wallet"></i></div>
            <div class="stats-info">
                <h3><?= number_format($stats['monthly_revenue'] / 1000000, 1) ?> Tr</h3>
                <p>Doanh thu tháng (<?= date('m/Y') ?>)</p>
            </div>
        </div>
    </div>

    <!-- 4. Cảnh báo tồn kho -->
    <div class="col-12 col-m-6 col-m-3">
        <div class="stats-card bg-danger">
            <div class="stats-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stats-info">
                <h3><?= $stats['low_stock_count'] ?></h3>
                <p>Phụ tùng sắp hết</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Shortcuts -->
<div class="row" style="margin-bottom: 20px;">
    <div class="col-12">
        <div class="card" style="padding: 15px 20px;">
            <div style="font-weight: 600; margin-bottom: 12px; color: #4834d4;">
                <i class="fas fa-bolt"></i> Thao tác nhanh hệ thống:
            </div>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <a href="index.php?c=appointment&a=approval" class="btn btn-warning" style="font-weight: 600;">
                    <i class="fas fa-clipboard-check"></i> Duyệt xe chờ sửa (KTV)
                </a>
                <a href="index.php?c=reception&a=create" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Tiếp nhận xe mới
                </a>
                <a href="index.php?c=repair&a=create" class="btn btn-info">
                    <i class="fas fa-file-signature"></i> Lập phiếu sửa chữa
                </a>
                <a href="index.php?c=invoice&a=create" class="btn btn-success">
                    <i class="fas fa-file-invoice-dollar"></i> Lập phiếu thu tiền
                </a>
                <a href="index.php?c=part&a=index" class="btn btn-secondary">
                    <i class="fas fa-boxes"></i> Kho vật tư phụ tùng
                </a>
                <a href="index.php?c=search&a=index" class="btn btn-secondary">
                    <i class="fas fa-search"></i> Tra cứu hồ sơ xe
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($stats['pending_appointments'])): ?>
<!-- Thông báo nổi bật: Lịch hẹn khách đặt chờ KTV duyệt ngay -->
<div class="row" style="margin-bottom: 20px;">
    <div class="col-12">
        <div class="card" style="border-left: 5px solid #2ed573; background: #f0fff4;">
            <div class="card-header" style="background: transparent; border: none; padding-bottom: 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                <h3 style="color: #22543d; margin: 0; font-size: 1.15rem; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-bell" style="color: #2ed573;"></i>
                    <span>Có <?= count($stats['pending_appointments']) ?> lịch hẹn khách vừa đặt đang chờ KTV duyệt!</span>
                </h3>
                <a href="index.php?c=appointment&a=approval" class="btn btn-sm btn-outline-primary" style="font-size: 0.82rem; font-weight: 700;">
                    <i class="fas fa-clipboard-check"></i> Xem trang duyệt xe (KTV)
                </a>
            </div>
            <div class="card-content" style="padding-top: 10px;">
                <div class="table-responsive">
                    <table class="table-custom" style="background: #ffffff; border-radius: 6px;">
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Khách hàng</th>
                                <th>Số điện thoại</th>
                                <th>Phương tiện</th>
                                <th>Thời gian hẹn</th>
                                <th>Yêu cầu</th>
                                <th>Thao tác KTV</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['pending_appointments'] as $pa): ?>
                                <tr>
                                    <td><b>#<?= $pa['ma_lh'] ?></b></td>
                                    <td><b><?= htmlspecialchars($pa['ten_chu_xe']) ?></b></td>
                                    <td><span style="color: #1a73e8; font-weight: 500;"><?= htmlspecialchars($pa['sdt']) ?></span></td>
                                    <td><span class="badge badge-info"><?= htmlspecialchars($pa['bien_so']) ?> (<?= htmlspecialchars($pa['hang_xe']) ?>)</span></td>
                                    <td><b style="color: #4834d4;"><?= !empty($pa['ngay_hen']) && $pa['ngay_hen'] !== '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($pa['ngay_hen'])) : 'Hôm nay' ?></b></td>
                                    <td><small><?= htmlspecialchars($pa['ghi_chu'] ?? 'Kiểm tra xe') ?></small></td>
                                    <td style="white-space: nowrap;">
                                        <a href="index.php?c=appointment&a=approveAndRepair&id=<?= $pa['ma_lh'] ?>" class="btn btn-sm btn-success" style="font-weight: 600; padding: 5px 12px;" title="Duyệt và tiếp nhận xe">
                                            <i class="fas fa-check"></i> Duyệt & Nhận xe
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recent Repairs Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3><i class="fas fa-history"></i> Phiếu sửa chữa gần đây</h3>
                <a href="index.php?c=repair&a=index" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
            </div>
            <div class="card-content">
                <div class="table-responsive">
                    <table class="table-custom table-hover">
                        <thead>
                            <tr>
                                <th>Mã phiếu</th>
                                <th>Biển số xe</th>
                                <th>Hãng xe</th>
                                <th>Chủ xe</th>
                                <th>Ngày tiếp nhận</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($stats['recent_repairs'])): ?>
                                <tr><td colspan="8" style="text-align: center;">Chưa có phiếu sửa chữa nào</td></tr>
                            <?php else: ?>
                                <?php foreach ($stats['recent_repairs'] as $r): ?>
                                    <tr>
                                        <td><b>#<?= $r['ma_psc'] ?></b></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($r['bien_so']) ?></span></td>
                                        <td><?= htmlspecialchars($r['hang_xe']) ?></td>
                                        <td><?= htmlspecialchars($r['ten_chu_xe']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($r['ngay_lap'])) ?></td>
                                        <td style="color: #eb4d4b; font-weight: 600;"><?= number_format((float)$r['tong_tien']) ?> đ</td>
                                        <td>
                                            <?php
                                                $badge = 'badge-secondary';
                                                if ($r['trang_thai'] === 'TiepNhan') $badge = 'badge-info';
                                                if ($r['trang_thai'] === 'DangSuaChua') $badge = 'badge-warning';
                                                if ($r['trang_thai'] === 'HoanThanh') $badge = 'badge-primary';
                                                if ($r['trang_thai'] === 'DaThanhToan') $badge = 'badge-success';
                                            ?>
                                            <span class="badge <?= $badge ?>"><?= $r['trang_thai'] ?></span>
                                        </td>
                                        <td>
                                            <a href="index.php?c=repair&a=detail&id=<?= $r['ma_psc'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> Chi tiết
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

