<div class="row">
    <!-- Thông tin xe & Chủ sở hữu -->
    <div class="col-12 col-m-4">
        <div class="card">
            <div class="card-header">
                <h3 style="margin: 0;"><i class="fas fa-car"></i> Hồ sơ phương tiện</h3>
            </div>
            <div class="card-content">
                <div style="text-align: center; margin-bottom: 20px;">
                    <i class="fas fa-car-side" style="font-size: 3rem; color: #4834d4;"></i>
                    <h2 style="margin: 10px 0 5px;"><?= htmlspecialchars($car['bien_so']) ?></h2>
                    <span class="badge badge-primary" style="font-size: 0.95rem;"><?= htmlspecialchars($car['hang_xe']) ?> <?= htmlspecialchars($car['dong_xe'] ?? '') ?></span>
                </div>

                <div style="border-top: 1px solid rgba(0,0,0,0.08); padding-top: 15px;">
                    <p style="margin: 8px 0;"><b>Năm sản xuất:</b> <?= htmlspecialchars($car['nam_san_xuat'] ?? 'Không rõ') ?></p>
                    <?php if (empty($isCustomer)): ?>
                        <p style="margin: 8px 0;"><b>Số khung:</b> <?= htmlspecialchars($car['so_khung'] ?? 'Chưa cập nhật') ?></p>
                        <p style="margin: 8px 0;"><b>Số máy:</b> <?= htmlspecialchars($car['so_may'] ?? 'Chưa cập nhật') ?></p>
                        <p style="margin: 8px 0;"><b>Chủ xe:</b> <?= htmlspecialchars($car['ten_chu_xe']) ?></p>
                        <p style="margin: 8px 0;"><b>Điện thoại:</b> <?= htmlspecialchars($car['sdt']) ?></p>
                        <p style="margin: 8px 0;"><b>Địa chỉ:</b> <?= htmlspecialchars($car['dia_chi'] ?? 'Chưa có') ?></p>
                    <?php endif; ?>
                    <div style="margin-top: 15px; padding: 12px; background: rgba(235, 77, 75, 0.08); border-radius: 6px; border-left: 4px solid #eb4d4b;">
                        Tiền nợ hiện tại: <b style="color: #eb4d4b; font-size: 1.15rem;"><?= number_format((float)$car['tien_no']) ?> đ</b>
                    </div>
                </div>

                <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 8px;">
                    <?php if (!empty($isCustomer)): ?>
                        <a href="index.php?c=appointment&a=create&bien_so=<?= urlencode($car['bien_so']) ?>" class="btn btn-success btn-block">
                            <i class="fas fa-calendar-plus"></i> Đặt lịch sửa xe này
                        </a>
                    <?php elseif (!empty($isStaff) && $car['tien_no'] > 0): ?>
                        <a href="index.php?c=invoice&a=create&bien_so=<?= urlencode($car['bien_so']) ?>" class="btn btn-success btn-block">
                            <i class="fas fa-dollar-sign"></i> Lập phiếu thu tiền
                        </a>
                    <?php endif; ?>
                    <a href="index.php?c=search&a=index" class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Lịch sử sửa chữa của xe -->
    <div class="col-12 col-m-8">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <h3 style="margin: 0;"><i class="fas fa-history"></i> Lịch sử sửa chữa & Bảo dưỡng</h3>
                <?php if (!empty($isStaff)): ?>
                    <a href="index.php?c=repair&a=create" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Lập phiếu sửa mới
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-content">
                <div class="table-responsive">
                    <table class="table-custom table-hover">
                        <thead>
                            <tr>
                                <th>Mã phiếu</th>
                                <th>Ngày tiếp nhận</th>
                                <th>Tổng chi phí</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($repairs)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 30px; color: #747d8c;">
                                        <i class="fas fa-tools" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.4;"></i>
                                        Phương tiện này chưa có lịch sử sửa chữa nào.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($repairs as $r): ?>
                                    <tr>
                                        <td><b>#<?= $r['ma_psc'] ?></b></td>
                                        <td><?= date('d/m/Y H:i', strtotime($r['ngay_lap'])) ?></td>
                                        <td style="font-weight: 700; color: #4834d4;"><?= number_format((float)$r['tong_tien']) ?> đ</td>
                                        <td>
                                            <?php
                                                $badge = 'badge-secondary';
                                                $stText = $r['trang_thai'];
                                                if ($r['trang_thai'] === 'TiepNhan') { $badge = 'badge-info'; $stText = 'Tiếp nhận'; }
                                                if ($r['trang_thai'] === 'DangSuaChua') { $badge = 'badge-warning'; $stText = 'Đang sửa chữa'; }
                                                if ($r['trang_thai'] === 'HoanThanh') { $badge = 'badge-primary'; $stText = 'Đã hoàn thành'; }
                                                if ($r['trang_thai'] === 'DaThanhToan') { $badge = 'badge-success'; $stText = 'Đã thanh toán'; }
                                            ?>
                                            <span class="badge <?= $badge ?>"><?= $stText ?></span>
                                        </td>
                                        <td style="white-space: nowrap;">
                                            <?php if (!empty($isStaff)): ?>
                                                <a href="index.php?c=repair&a=detail&id=<?= $r['ma_psc'] ?>" class="btn btn-sm btn-primary" title="Xem chi tiết kỹ thuật">
                                                    <i class="fas fa-wrench"></i> Kỹ thuật
                                                </a>
                                            <?php endif; ?>
                                            <a href="index.php?c=repair&a=print&id=<?= $r['ma_psc'] ?>" target="_blank" class="btn btn-sm <?= !empty($isCustomer) ? 'btn-primary' : 'btn-secondary' ?>" title="Xem phiếu sửa / Hóa đơn">
                                                <i class="fas fa-file-invoice"></i> <?= !empty($isCustomer) ? 'Xem phiếu sửa' : 'In' ?>
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
