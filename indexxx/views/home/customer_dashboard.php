<?php
$totalCars = count($myCars);
$totalDebt = array_sum(array_column($myCars, 'tien_no'));
$pendingAppointments = count(array_filter($myAppointments, fn($a) => $a['trang_thai'] === 'ChoXacNhan'));
$activeRepairs = count(array_filter($myRepairs, fn($r) => in_array($r['trang_thai'], ['TiepNhan', 'DangSuaChua'])));
?>

<!-- Banner Chào Mừng Thân Thiện -->
<div class="row">
    <div class="col-12">
        <div class="card" style="background: linear-gradient(135deg, #1e3799 0%, #0c2461 100%); color: #ffffff; padding: 22px 28px; margin-bottom: 22px; border-radius: 10px; border: none; box-shadow: 0 4px 15px rgba(12, 36, 97, 0.15);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h2 style="margin: 0 0 6px; font-size: 1.6rem; font-weight: 700;">
                        Xin chào, <?= htmlspecialchars($user['ho_ten']) ?>! 👋
                    </h2>
                    <p style="margin: 0; opacity: 0.9; font-size: 0.92rem;">
                        Chào mừng bạn đến với Cổng thông tin khách hàng Garage Pro tại Hà Nội. Quản lý xe và theo dõi tiến độ sửa chữa trực tuyến dễ dàng.
                    </p>
                </div>
                <div>
                    <a href="index.php?c=appointment&a=create" class="btn btn-success" style="font-weight: 700; padding: 10px 22px; border-radius: 6px; box-shadow: 0 4px 10px rgba(46, 213, 115, 0.3);">
                        <i class="fas fa-calendar-plus"></i> Đặt lịch hẹn mới
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3 Thẻ Chỉ Số Gọn Gàng -->
<div class="row">
    <div class="col-12 col-m-4">
        <div class="stats-card bg-primary" style="margin-bottom: 20px;">
            <div class="stats-icon"><i class="fas fa-car"></i></div>
            <div class="stats-info">
                <h3><?= $totalCars ?> xe</h3>
                <p>Phương tiện của bạn</p>
            </div>
        </div>
    </div>
    <div class="col-12 col-m-4">
        <div class="stats-card bg-warning" style="margin-bottom: 20px;">
            <div class="stats-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="stats-info">
                <h3><?= $pendingAppointments ?> lịch</h3>
                <p>Lịch hẹn chờ KTV duyệt</p>
            </div>
        </div>
    </div>
    <div class="col-12 col-m-4">
        <div class="stats-card <?= ($totalDebt > 0) ? 'bg-danger' : 'bg-success' ?>" style="margin-bottom: 20px;">
            <div class="stats-icon"><i class="fas <?= ($totalDebt > 0) ? 'fa-wallet' : 'fa-check-circle' ?>"></i></div>
            <div class="stats-info">
                <h3><?= number_format((float)$totalDebt) ?> đ</h3>
                <p><?= ($totalDebt > 0) ? 'Tổng công nợ cần thanh toán' : 'Công nợ (Đã thanh toán đủ)' ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Chi Tiết 2 Khối Chính: Xe Của Tôi & Lịch Hẹn / Tiến Độ -->
<div class="row">
    <!-- Cột 1: Danh sách xe của tôi -->
    <div class="col-12 col-m-5">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0;"><i class="fas fa-car" style="color: #4834d4;"></i> Xe của tôi</h3>
                <a href="index.php?c=search&a=index" class="btn btn-sm btn-outline-primary" style="border: 1px solid #4834d4; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem;">Xem tất cả</a>
            </div>
            <div class="card-content">
                <?php if (empty($myCars)): ?>
                    <div style="text-align: center; color: #747d8c; padding: 30px 15px;">
                        <i class="fas fa-car" style="font-size: 2.5rem; opacity: 0.3; margin-bottom: 10px; display: block;"></i>
                        <p style="margin: 0 0 12px; font-size: 0.95rem;">Bạn chưa đăng ký phương tiện nào.</p>
                        <a href="index.php?c=appointment&a=create" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Đặt lịch hẹn sửa xe ngay
                        </a>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <?php foreach ($myCars as $c): ?>
                            <div style="background: var(--second-bg-color, #f8f9fa); padding: 15px; border-radius: 8px; border-left: 4px solid #4834d4; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                    <div>
                                        <span class="badge badge-info" style="font-size: 0.95rem; font-weight: 700;"><?= htmlspecialchars($c['bien_so']) ?></span>
                                        <h4 style="margin: 6px 0 2px; font-size: 1rem;"><?= htmlspecialchars($c['hang_xe']) ?> <?= htmlspecialchars($c['dong_xe'] ?? '') ?></h4>
                                        <small style="color: #747d8c;">Năm SX: <?= htmlspecialchars($c['nam_san_xuat'] ?? 'Không rõ') ?></small>
                                    </div>
                                    <div style="text-align: right;">
                                        <?php if ($c['tien_no'] > 0): ?>
                                            <span class="badge badge-warning" style="font-size: 0.8rem;">Còn nợ: <?= number_format((float)$c['tien_no']) ?> đ</span>
                                        <?php else: ?>
                                            <span class="badge badge-success" style="font-size: 0.8rem;"><i class="fas fa-check"></i> Đã tất toán</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 8px; border-top: 1px solid rgba(0,0,0,0.06); padding-top: 10px; margin-top: 8px;">
                                    <a href="index.php?c=search&a=detail&id=<?= $c['ma_xe'] ?>" class="btn btn-sm btn-primary" style="flex: 1; text-align: center;">
                                        <i class="fas fa-history"></i> Lịch sử sửa
                                    </a>
                                    <a href="index.php?c=appointment&a=create&bien_so=<?= urlencode($c['bien_so']) ?>" class="btn btn-sm btn-success" style="flex: 1; text-align: center;">
                                        <i class="fas fa-calendar-plus"></i> Đặt lịch hẹn
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Cột 2: Lịch hẹn & Tiến độ sửa chữa -->
    <div class="col-12 col-m-7">
        <!-- Lịch hẹn gần đây -->
        <div class="card" style="margin-bottom: 22px;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0;"><i class="fas fa-calendar-alt" style="color: #4834d4;"></i> Lịch hẹn sửa chữa</h3>
                <a href="index.php?c=appointment&a=index" class="btn btn-sm btn-outline-primary" style="border: 1px solid #4834d4; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem;">Xem tất cả</a>
            </div>
            <div class="card-content">
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Mã hẹn</th>
                                <th>Thời gian</th>
                                <th>Biển số xe</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($myAppointments)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #747d8c; padding: 25px;">
                                        Bạn chưa có lịch hẹn nào. Nhấn <b>"Đặt lịch hẹn mới"</b> để được tiếp nhận nhanh nhất!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach (array_slice($myAppointments, 0, 5) as $ap): ?>
                                    <tr>
                                        <td><b>#<?= $ap['ma_lh'] ?></b></td>
                                        <td><b style="color: #4834d4;"><?= !empty($ap['ngay_hen']) && $ap['ngay_hen'] !== '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($ap['ngay_hen'])) : 'Linh hoạt' ?></b></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($ap['bien_so']) ?></span></td>
                                        <td>
                                            <?php
                                                $badge = 'badge-secondary';
                                                $stText = $ap['trang_thai'];
                                                if ($ap['trang_thai'] === 'ChoXacNhan') { $badge = 'badge-warning'; $stText = 'Chờ KTV duyệt'; }
                                                if ($ap['trang_thai'] === 'DaXacNhan') { $badge = 'badge-primary'; $stText = 'Đã duyệt'; }
                                                if ($ap['trang_thai'] === 'HoanThanh') { $badge = 'badge-success'; $stText = 'Hoàn thành'; }
                                                if ($ap['trang_thai'] === 'DaHuy') { $badge = 'badge-danger'; $stText = 'Đã hủy'; }
                                            ?>
                                            <span class="badge <?= $badge ?>"><?= $stText ?></span>
                                        </td>
                                        <td>
                                            <a href="index.php?c=appointment&a=downloadPdf&id=<?= $ap['ma_lh'] ?>" class="btn btn-sm btn-outline-primary" title="Tải phiếu hẹn PDF 1 trang">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                            <?php if ($ap['trang_thai'] === 'ChoXacNhan'): ?>
                                                <a href="index.php?c=appointment&a=updateStatus&id=<?= $ap['ma_lh'] ?>&status=DaHuy" class="btn btn-sm btn-secondary" onclick="return confirm('Bạn có chắc muốn hủy lịch hẹn này?')" title="Hủy lịch hẹn">
                                                    <i class="fas fa-times"></i> Hủy
                                                </a>
                                            <?php else: ?>
                                                <span style="color: #747d8c; font-size: 0.85rem;"><i class="fas fa-check"></i> Đã tiếp nhận</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Lịch sử sửa chữa & Tiến độ xe -->
        <div class="card">
            <div class="card-header">
                <h3 style="margin: 0;"><i class="fas fa-wrench" style="color: #4834d4;"></i> Tiến độ sửa chữa & Hồ sơ bảo dưỡng</h3>
            </div>
            <div class="card-content">
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Mã phiếu</th>
                                <th>Biển số xe</th>
                                <th>Ngày nhận xe</th>
                                <th>Chi phí</th>
                                <th>Tiến độ</th>
                                <th>Phiếu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($myRepairs)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #747d8c; padding: 25px;">
                                        Chưa có hồ sơ sửa chữa nào.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach (array_slice($myRepairs, 0, 5) as $rp): ?>
                                    <tr>
                                        <td><b>#<?= $rp['ma_psc'] ?></b></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($rp['bien_so']) ?></span></td>
                                        <td><?= date('d/m/Y', strtotime($rp['ngay_lap'])) ?></td>
                                        <td style="font-weight: 700; color: #4834d4;"><?= number_format((float)$rp['tong_tien']) ?> đ</td>
                                        <td>
                                            <?php
                                                $badge = 'badge-secondary';
                                                $stText = $rp['trang_thai'];
                                                if ($rp['trang_thai'] === 'TiepNhan') { $badge = 'badge-info'; $stText = 'Tiếp nhận'; }
                                                if ($rp['trang_thai'] === 'DangSuaChua') { $badge = 'badge-warning'; $stText = 'Đang sửa chữa'; }
                                                if ($rp['trang_thai'] === 'HoanThanh') { $badge = 'badge-primary'; $stText = 'Đã sửa xong'; }
                                                if ($rp['trang_thai'] === 'DaThanhToan') { $badge = 'badge-success'; $stText = 'Đã tất toán'; }
                                            ?>
                                            <span class="badge <?= $badge ?>"><?= $stText ?></span>
                                        </td>
                                        <td>
                                            <a href="index.php?c=repair&a=print&id=<?= $rp['ma_psc'] ?>" target="_blank" class="btn btn-sm btn-primary" title="Xem chi tiết phiếu sửa">
                                                <i class="fas fa-file-invoice"></i> Xem
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
