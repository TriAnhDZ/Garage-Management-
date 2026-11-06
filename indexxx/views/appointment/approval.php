<div class="row">
    <div class="col-12">
        <div class="card" style="background: linear-gradient(135deg, #0c2461 0%, #1e3799 100%); color: #ffffff; padding: 22px 25px; margin-bottom: 22px; border-radius: 10px; border: none; box-shadow: 0 4px 15px rgba(12, 36, 97, 0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h2 style="margin: 0 0 6px; font-size: 1.55rem; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-clipboard-check" style="color: #2ed573;"></i>
                        <span>DANH SÁCH DUYỆT XE & TIẾP NHẬN SỬA CHỮA (KTV)</span>
                    </h2>
                    <p style="margin: 0; opacity: 0.9; font-size: 0.92rem;">
                        Quản lý tiếp nhận phương tiện và điều phối khoang sửa chữa.
                    </p>
                </div>
                <div style="display: flex; gap: 8px;">
                    <a href="index.php?c=appointment&a=index" class="btn btn-sm btn-outline-light" style="font-weight: 600; padding: 8px 16px; border: 1.5px solid #ffffff; border-radius: 6px; color: #ffffff;">
                        <i class="fas fa-list"></i> Tất cả lịch hẹn
                    </a>
                    <a href="index.php?c=reception&a=create" class="btn btn-sm btn-success" style="font-weight: 700; padding: 8px 18px; border-radius: 6px; box-shadow: 0 4px 10px rgba(46, 213, 115, 0.4);">
                        <i class="fas fa-plus-circle"></i> Tiếp nhận xe mới
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3 Thẻ thống kê nhanh cho KTV -->
<div class="row">
    <div class="col-12 col-m-4">
        <div class="stats-card <?= count($pendingAppointments) > 0 ? 'bg-warning' : 'bg-success' ?>" style="margin-bottom: 20px;">
            <div class="stats-icon"><i class="fas fa-bell"></i></div>
            <div class="stats-info">
                <h3><?= count($pendingAppointments) ?> xe</h3>
                <p>Xe khách đặt đang chờ KTV duyệt</p>
            </div>
        </div>
    </div>
    <div class="col-12 col-m-4">
        <div class="stats-card bg-primary" style="margin-bottom: 20px;">
            <div class="stats-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="stats-info">
                <h3><?= $approvedCount ?> xe</h3>
                <p>Lịch hẹn đã duyệt tiếp nhận</p>
            </div>
        </div>
    </div>
    <div class="col-12 col-m-4">
        <div class="stats-card <?= ($todayReceptionCount < $dailyLimit) ? 'bg-success' : 'bg-danger' ?>" style="margin-bottom: 20px;">
            <div class="stats-icon"><i class="fas fa-tachometer-alt"></i></div>
            <div class="stats-info">
                <h3><?= $todayReceptionCount ?> / <?= $dailyLimit ?> xe</h3>
                <p>Công suất tiếp nhận xưởng hôm nay</p>
            </div>
        </div>
    </div>
</div>

<!-- Khối 1: Bảng danh sách xe chờ KTV duyệt ngay -->
<div class="row" style="margin-bottom: 25px;">
    <div class="col-12">
        <div class="card" style="border-top: 4px solid #f39c12;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <h3 style="margin: 0; color: #ffffff; font-size: 1.2rem; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-car-side" style="color: #f39c12;"></i>
                    <span>Xe đang chờ Kỹ thuật viên duyệt & nhận xe</span>
                    <span class="badge badge-warning" style="font-size: 0.85rem; padding: 4px 10px; border-radius: 12px; font-weight: 700;">
                        <?= count($pendingAppointments) ?> yêu cầu
                    </span>
                </h3>
                <div>
                    <a href="index.php?c=appointment&a=approval" class="btn btn-sm btn-outline-primary" title="Làm mới danh sách">
                        <i class="fas fa-sync-alt"></i> Làm mới
                    </a>
                </div>
            </div>

            <div class="card-content">
                <?php if (empty($pendingAppointments)): ?>
                    <div style="text-align: center; padding: 45px 20px; color: #cbd5e1;">
                        <i class="fas fa-check-circle" style="font-size: 3rem; color: #10b981; margin-bottom: 15px; display: block;"></i>
                        <h4 style="color: #ffffff; font-weight: 700; margin: 0 0 8px; font-size: 1.15rem;">Tuyệt vời! Hiện không còn xe nào đang chờ duyệt.</h4>
                        <p style="margin: 0; font-size: 0.95rem; color: #94a3b8;">Tất cả các lịch hẹn khách đặt đều đã được KTV tiếp nhận hoặc xử lý hoàn tất.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table-custom table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">Mã LH</th>
                                    <th>Thời gian hẹn</th>
                                    <th>Phương tiện</th>
                                    <th>Khách hàng</th>
                                    <th>Số điện thoại</th>
                                    <th>Nội dung yêu cầu / Triệu chứng</th>
                                    <th>Trạng thái</th>
                                    <th style="min-width: 250px; text-align: center;">Thao tác KTV</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingAppointments as $pa): ?>
                                    <tr>
                                        <td><b style="color: #60a5fa; font-size: 1.05rem;">#<?= $pa['ma_lh'] ?></b></td>
                                        <td>
                                            <b style="color: #a5b4fc;">
                                                <i class="far fa-clock"></i> 
                                                <?= (!empty($pa['ngay_hen']) && $pa['ngay_hen'] !== '0000-00-00 00:00:00') ? date('d/m/Y H:i', strtotime($pa['ngay_hen'])) : 'Hôm nay' ?>
                                            </b>
                                        </td>
                                        <td>
                                            <span class="badge badge-info" style="font-size: 0.95rem; font-weight: 700; letter-spacing: 0.5px;">
                                                <?= htmlspecialchars($pa['bien_so']) ?>
                                            </span>
                                            <div style="font-size: 0.85rem; color: #94a3b8; margin-top: 3px; font-weight: 600;">
                                                <?= htmlspecialchars($pa['hang_xe']) ?> <?= htmlspecialchars($pa['dong_xe'] ?? '') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700; color: #ffffff;">
                                                <?= htmlspecialchars($pa['ten_chu_xe']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="tel:<?= htmlspecialchars($pa['sdt']) ?>" style="color: #38bdf8; font-weight: 600; text-decoration: none;">
                                                <i class="fas fa-phone-alt" style="font-size: 0.8rem;"></i> <?= htmlspecialchars($pa['sdt']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div style="max-width: 280px; font-size: 0.88rem; color: #e2e8f0;">
                                                <?= htmlspecialchars($pa['ghi_chu'] ?: 'Yêu cầu kiểm tra & bảo dưỡng xe') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-warning" style="font-weight: 700; font-size: 0.82rem; padding: 4px 8px;">
                                                <i class="fas fa-hourglass-half"></i> Chờ KTV duyệt
                                            </span>
                                        </td>
                                        <td style="text-align: center; white-space: nowrap;">
                                            <a href="index.php?c=appointment&a=approveAndRepair&id=<?= $pa['ma_lh'] ?>" class="btn btn-sm btn-success" style="font-weight: 600; padding: 6px 14px;" title="Duyệt và tiếp nhận xe vào sửa chữa">
                                                <i class="fas fa-check"></i> Duyệt & Nhận xe
                                            </a>
                                            <a href="index.php?c=appointment&a=downloadPdf&id=<?= $pa['ma_lh'] ?>" class="btn btn-sm btn-outline-info" title="In phiếu hẹn PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <a href="index.php?c=appointment&a=updateStatus&id=<?= $pa['ma_lh'] ?>&status=DaHuy" class="btn btn-sm btn-outline-danger" title="Từ chối / Hủy lịch hẹn" onclick="return confirm('Bạn có chắc chắn muốn hủy lịch hẹn #<?= $pa['ma_lh'] ?>?')">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Khối 2: Nhật ký thao tác KTV & Tiếp nhận xe (System Activity Log) -->
<div class="row">
    <div class="col-12">
        <div class="card" style="border-top: 4px solid #10b981;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <h3 style="margin: 0; color: #ffffff; font-size: 1.2rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-history" style="color: #10b981;"></i>
                    <span>Nhật ký duyệt xe & Thao tác kỹ thuật gần đây</span>
                </h3>
                <small style="color: #94a3b8; font-size: 0.88rem;">Ghi nhận minh bạch lịch sử duyệt xe, tiếp nhận xưởng và cập nhật trạng thái</small>
            </div>

            <div class="card-content">
                <?php if (empty($logs)): ?>
                    <div style="text-align: center; color: #cbd5e1; padding: 25px 15px;">
                        Chưa có lịch sử thao tác nào được ghi nhận.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table-custom" style="font-size: 0.92rem;">
                            <thead>
                                <tr>
                                    <th style="width: 155px; color: #38bdf8;">Thời gian</th>
                                    <th style="width: 170px; color: #38bdf8;">Người thực hiện</th>
                                    <th style="width: 190px; color: #38bdf8;">Hành động</th>
                                    <th style="color: #38bdf8;">Chi tiết nội dung thao tác</th>
                                    <th style="width: 110px; text-align: center; color: #38bdf8;">Địa chỉ IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $l): ?>
                                    <tr>
                                        <td style="color: #cbd5e1; font-size: 0.88rem; font-weight: 500;">
                                            <i class="far fa-clock" style="color: #60a5fa;"></i> <?= date('d/m/Y H:i:s', strtotime($l['thoi_gian'])) ?>
                                        </td>
                                        <td>
                                            <b style="color: #ffffff; font-size: 0.95rem;"><?= htmlspecialchars($l['ho_ten']) ?></b>
                                            <div style="margin-top: 3px;">
                                                <span class="badge badge-secondary" style="font-size: 0.75rem; padding: 2px 7px;">
                                                    <?= htmlspecialchars($l['vai_tro']) ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                                $actionClass = 'badge-primary';
                                                if (strpos($l['hanh_dong'], 'Duyệt') !== false) $actionClass = 'badge-success';
                                                if (strpos($l['hanh_dong'], 'Hủy') !== false || strpos($l['hanh_dong'], 'Xóa') !== false) $actionClass = 'badge-danger';
                                            ?>
                                            <span class="badge <?= $actionClass ?>" style="font-size: 0.82rem; font-weight: 700; padding: 4px 10px;">
                                                <?= htmlspecialchars($l['hanh_dong']) ?>
                                            </span>
                                        </td>
                                        <td style="color: #f1f5f9; font-size: 0.92rem; line-height: 1.55; font-weight: 500;">
                                            <?= htmlspecialchars($l['chi_tiet']) ?>
                                        </td>
                                        <td style="text-align: center; color: #cbd5e1; font-size: 0.85rem; font-family: monospace; font-weight: 600;">
                                            <?= htmlspecialchars($l['dia_chi_ip'] ?: '127.0.0.1') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

