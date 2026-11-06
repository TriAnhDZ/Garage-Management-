<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-calendar-check" style="color: #4834d4;"></i>
                        <span><?= (!empty($isCustomer)) ? 'Lịch hẹn sửa chữa của tôi' : 'Quản lý lịch hẹn' ?></span>
                    </h3>
                    <small style="color: #747d8c;">
                        <?= (!empty($isCustomer)) ? 'Theo dõi trạng thái lịch hẹn và tiến độ phương tiện' : 'Quản lý và tiếp nhận lịch hẹn của khách hàng' ?>
                    </small>
                </div>

                <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                    <?php if (empty($isCustomer)): ?>
                        <!-- Lọc trạng thái cho KTV -->
                        <div style="display: flex; gap: 6px;">
                            <a href="index.php?c=appointment&a=index" class="btn btn-sm <?= empty($currentStatus) ? 'btn-primary' : 'btn-secondary' ?>">Tất cả</a>
                            <a href="index.php?c=appointment&a=index&status=ChoXacNhan" class="btn btn-sm <?= ($currentStatus === 'ChoXacNhan') ? 'btn-warning' : 'btn-secondary' ?>">
                                ⚡ Chờ KTV duyệt
                            </a>
                            <a href="index.php?c=appointment&a=index&status=DaXacNhan" class="btn btn-sm <?= ($currentStatus === 'DaXacNhan') ? 'btn-primary' : 'btn-secondary' ?>">Đã duyệt</a>
                            <a href="index.php?c=appointment&a=index&status=HoanThanh" class="btn btn-sm <?= ($currentStatus === 'HoanThanh') ? 'btn-success' : 'btn-secondary' ?>">Hoàn tất</a>
                        </div>
                    <?php endif; ?>

                    <a href="index.php?c=appointment&a=create" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Đặt lịch hẹn mới
                    </a>
                </div>
            </div>

            <div class="card-content">
                <div class="table-responsive">
                    <table class="table-custom table-hover">
                        <thead>
                            <tr>
                                <th>Mã hẹn</th>
                                <th>Thời gian hẹn</th>
                                <th>Biển số xe</th>
                                <th>Hiệu xe</th>
                                <th>Khách hàng</th>
                                <th>Số điện thoại</th>
                                <th>Ghi chú yêu cầu</th>
                                <th>Trạng thái</th>
                                <th style="min-width: 200px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($appointments)): ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 35px; color: #747d8c;">
                                        <i class="fas fa-calendar-times" style="font-size: 2rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                                        Hiện tại chưa có lịch hẹn nào<?= !empty($currentStatus) ? " ở trạng thái này" : "" ?>.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($appointments as $a): ?>
                                    <tr>
                                        <td><b>#<?= $a['ma_lh'] ?></b></td>
                                        <td><b style="color: #4834d4;"><?= !empty($a['ngay_hen']) && $a['ngay_hen'] !== '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($a['ngay_hen'])) : 'Linh hoạt' ?></b></td>
                                        <td><span class="badge badge-info" style="font-size: 0.95rem; font-weight: 700;"><?= htmlspecialchars($a['bien_so']) ?></span></td>
                                        <td><?= htmlspecialchars($a['hang_xe'] ?? '') ?></td>
                                        <td><b><?= htmlspecialchars($a['ten_chu_xe'] ?? 'Khách đặt') ?></b></td>
                                        <td><span style="color: #1a73e8;"><?= htmlspecialchars($a['sdt'] ?? '') ?></span></td>
                                        <td><small><?= htmlspecialchars($a['ghi_chu'] ?? 'Không có') ?></small></td>
                                        <td>
                                            <?php
                                                $badge = 'badge-secondary';
                                                $textStatus = $a['trang_thai'];
                                                if ($a['trang_thai'] === 'ChoXacNhan') { $badge = 'badge-warning'; $textStatus = 'Chờ KTV duyệt'; }
                                                if ($a['trang_thai'] === 'DaXacNhan') { $badge = 'badge-primary'; $textStatus = 'Đã duyệt nhận xe'; }
                                                if ($a['trang_thai'] === 'HoanThanh') { $badge = 'badge-success'; $textStatus = 'Đã hoàn tất'; }
                                                if ($a['trang_thai'] === 'DaHuy') { $badge = 'badge-danger'; $textStatus = 'Đã hủy'; }
                                            ?>
                                            <span class="badge <?= $badge ?>"><?= $textStatus ?></span>
                                        </td>

                                        <td style="white-space: nowrap;">
                                            <?php if (!empty($isCustomer)): ?>
                                                <!-- GIAO DIỆN KHÁCH HÀNG -->
                                                <a href="index.php?c=appointment&a=downloadPdf&id=<?= $a['ma_lh'] ?>" class="btn btn-sm btn-outline-primary" title="Tải phiếu hẹn PDF">
                                                    <i class="fas fa-file-pdf"></i> Tải PDF
                                                </a>
                                                <?php if ($a['trang_thai'] === 'ChoXacNhan'): ?>
                                                    <a href="index.php?c=appointment&a=updateStatus&id=<?= $a['ma_lh'] ?>&status=DaHuy" class="btn btn-sm btn-secondary" onclick="return confirm('Bạn có chắc muốn hủy lịch hẹn này?')" title="Hủy lịch hẹn">
                                                        <i class="fas fa-times"></i> Hủy hẹn
                                                    </a>
                                                <?php else: ?>
                                                    <a href="index.php?c=search&a=index&keyword=<?= urlencode($a['bien_so']) ?>" class="btn btn-sm btn-info" title="Xem tiến trình sửa chữa">
                                                        <i class="fas fa-search"></i> Tra cứu xe
                                                    </a>
                                                <?php endif; ?>

                                            <?php else: ?>
                                                <!-- GIAO DIỆN KỸ THUẬT VIÊN (KTV) DUYỆT -->
                                                <a href="index.php?c=appointment&a=downloadPdf&id=<?= $a['ma_lh'] ?>" class="btn btn-sm btn-outline-info" title="Tải phiếu hẹn PDF">
                                                    <i class="fas fa-file-pdf"></i> PDF
                                                </a>
                                                <?php if ($a['trang_thai'] === 'ChoXacNhan'): ?>
                                                    <a href="index.php?c=appointment&a=approveAndRepair&id=<?= $a['ma_lh'] ?>" class="btn btn-sm btn-success" style="font-weight: 600; padding: 5px 12px;" title="Duyệt và tiếp nhận xe vào sửa chữa">
                                                        <i class="fas fa-check"></i> Duyệt & Nhận xe
                                                    </a>
                                                    <a href="index.php?c=email&a=openWeb&type=appointment&id=<?= $a['ma_lh'] ?>" target="_blank" class="btn btn-sm btn-danger" title="Mở thư trên Gmail">
                                                        <i class="fab fa-google"></i> Gmail
                                                    </a>
                                                    <a href="index.php?c=appointment&a=updateStatus&id=<?= $a['ma_lh'] ?>&status=DaHuy" class="btn btn-sm btn-secondary" title="Hủy lịch hẹn" onclick="return confirm('Bạn có chắc muốn hủy lịch hẹn này?')">
                                                        <i class="fas fa-times"></i>
                                                    </a>

                                                <?php elseif ($a['trang_thai'] === 'DaXacNhan'): ?>
                                                    <a href="index.php?c=appointment&a=approveAndRepair&id=<?= $a['ma_lh'] ?>" class="btn btn-sm btn-primary" title="Xem phiếu sửa chữa">
                                                        <i class="fas fa-tools"></i> Vào sửa chữa
                                                    </a>
                                                    <a href="index.php?c=email&a=openWeb&type=appointment&id=<?= $a['ma_lh'] ?>" target="_blank" class="btn btn-sm btn-danger" title="Mở thư trên Gmail">
                                                        <i class="fab fa-google"></i> Gmail
                                                    </a>
                                                    <a href="index.php?c=appointment&a=updateStatus&id=<?= $a['ma_lh'] ?>&status=HoanThanh" class="btn btn-sm btn-info" title="Đánh dấu hoàn thành lịch hẹn">
                                                        <i class="fas fa-check"></i> Xong
                                                    </a>

                                                <?php else: ?>
                                                    <a href="index.php?c=email&a=openWeb&type=appointment&id=<?= $a['ma_lh'] ?>" target="_blank" class="btn btn-sm btn-danger" title="Mở Gmail Web">
                                                        <i class="fab fa-google"></i> Gmail Web
                                                    </a>
                                                <?php endif; ?>
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
    </div>
</div>
