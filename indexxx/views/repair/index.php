<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <h3 style="margin: 0;"><i class="fas fa-clipboard-list text-primary"></i> Quản lý phiếu dịch vụ &amp; Sửa chữa ô tô</h3>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <a href="index.php?c=repair&a=create&type=SuaChua" class="btn btn-primary" style="font-weight: 700;">
                        <i class="fas fa-wrench"></i> + Phiếu sửa chữa
                    </a>
                    <a href="index.php?c=repair&a=create&type=DoChe" class="btn btn-warning" style="font-weight: 700; background: #d97706; border-color: #d97706; color: #fff;">
                        <i class="fas fa-cogs"></i> + Phiếu linh kiện/Độ xe
                    </a>
                    <a href="index.php?c=repair&a=create&type=RuaXe" class="btn btn-info" style="font-weight: 700;">
                        <i class="fas fa-spray-can"></i> + Phiếu rửa xe/Spa
                    </a>
                </div>
            </div>

            <div class="card-content">
                <!-- Tabs chuyển đổi nhanh 3 loại phiếu -->
                <div class="repair-type-tabs" style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; flex-wrap: wrap;">
                    <a href="index.php?c=repair&a=index<?= !empty($currentStatus) ? '&status='.$currentStatus : '' ?>" 
                       class="type-tab-btn <?= empty($currentType) ? 'active' : '' ?>">
                        <i class="fas fa-th-large"></i> Tất cả các phiếu
                    </a>
                    <a href="index.php?c=repair&a=index&type=SuaChua<?= !empty($currentStatus) ? '&status='.$currentStatus : '' ?>" 
                       class="type-tab-btn tab-suachua <?= ($currentType === 'SuaChua') ? 'active' : '' ?>">
                        <i class="fas fa-wrench"></i> 1. Phiếu sửa chữa &amp; bảo dưỡng
                    </a>
                    <a href="index.php?c=repair&a=index&type=DoChe<?= !empty($currentStatus) ? '&status='.$currentStatus : '' ?>" 
                       class="type-tab-btn tab-doche <?= ($currentType === 'DoChe') ? 'active' : '' ?>">
                        <i class="fas fa-cogs"></i> 2. Thay thế linh kiện &amp; Độ xe
                    </a>
                    <a href="index.php?c=repair&a=index&type=RuaXe<?= !empty($currentStatus) ? '&status='.$currentStatus : '' ?>" 
                       class="type-tab-btn tab-ruaxe <?= ($currentType === 'RuaXe') ? 'active' : '' ?>">
                        <i class="fas fa-spray-can"></i> 3. Phiếu rửa xe &amp; Car Spa
                    </a>
                </div>

                <!-- Bộ lọc & Tìm kiếm -->
                <form method="GET" action="index.php" style="margin-bottom: 20px;">
                    <input type="hidden" name="c" value="repair">
                    <input type="hidden" name="a" value="index">
                    <?php if (!empty($currentType)): ?>
                        <input type="hidden" name="type" value="<?= htmlspecialchars($currentType) ?>">
                    <?php endif; ?>
                    <div class="row" style="align-items: flex-end;">
                        <div class="col-12 col-m-4">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Tìm theo biển số, tên khách hoặc mã phiếu:</label>
                                <input type="text" name="keyword" class="form-control" value="<?= htmlspecialchars($keyword ?? '') ?>" placeholder="VD: 30A-123.45 hoặc Tuấn...">
                            </div>
                        </div>
                        <div class="col-12 col-m-4">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Trạng thái tiến độ:</label>
                                <select name="status" class="form-control">
                                    <option value="">-- Tất cả trạng thái --</option>
                                    <option value="TiepNhan" <?= ($currentStatus === 'TiepNhan') ? 'selected' : '' ?>>Tiếp nhận</option>
                                    <option value="DangKiemTra" <?= ($currentStatus === 'DangKiemTra') ? 'selected' : '' ?>>Đang kiểm tra</option>
                                    <option value="ChoKhachDuyet" <?= ($currentStatus === 'ChoKhachDuyet') ? 'selected' : '' ?>>Chờ khách duyệt</option>
                                    <option value="DangSuaChua" <?= ($currentStatus === 'DangSuaChua') ? 'selected' : '' ?>>Đang sửa chữa</option>
                                    <option value="HoanThanh" <?= ($currentStatus === 'HoanThanh') ? 'selected' : '' ?>>Hoàn thành</option>
                                    <option value="DaThanhToan" <?= ($currentStatus === 'DaThanhToan') ? 'selected' : '' ?>>Đã thanh toán</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-m-4">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-filter"></i> Lọc danh sách
                            </button>
                            <a href="index.php?c=repair&a=index<?= !empty($currentType) ? '&type='.$currentType : '' ?>" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Đặt lại
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Bảng danh sách phiếu sửa -->
                <div class="table-responsive">
                    <table class="table-custom table-hover">
                        <thead>
                            <tr>
                                <th>Mã phiếu</th>
                                <th>Phân loại phiếu</th>
                                <th>Biển số xe</th>
                                <th>Hiệu xe</th>
                                <th>Chủ xe / SĐT</th>
                                <th>Ngày lập</th>
                                <th>Tổng chi phí</th>
                                <th>Tiền nợ</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($repairs)): ?>
                                <tr><td colspan="10" style="text-align: center; padding: 30px; color: #64748b;">Không có phiếu nào phù hợp với điều kiện tìm kiếm.</td></tr>
                            <?php else: ?>
                                <?php foreach ($repairs as $r): ?>
                                    <?php 
                                    $tInfo = RepairModel::getTicketTypeInfo($r['loai_phieu'] ?? 'SuaChua');
                                    ?>
                                    <tr>
                                        <td><b>#<?= $r['ma_psc'] ?></b></td>
                                        <td>
                                            <span class="badge" style="background: <?= $tInfo['bg_color'] ?>; color: <?= $tInfo['color'] ?>; border: 1px solid <?= $tInfo['border_color'] ?>; font-size: 0.8rem; padding: 4px 8px; font-weight: 700;">
                                                <i class="<?= $tInfo['icon'] ?>"></i> <?= $tInfo['short_title'] ?>
                                            </span>
                                        </td>
                                        <td><span class="badge badge-info" style="font-weight: 800; font-size: 0.9rem;"><?= htmlspecialchars($r['bien_so']) ?></span></td>
                                        <td><?= htmlspecialchars($r['hang_xe']) ?> <?= htmlspecialchars($r['dong_xe'] ?? '') ?></td>
                                        <td>
                                            <b><?= htmlspecialchars($r['ten_chu_xe']) ?></b><br>
                                            <small><?= htmlspecialchars($r['sdt_chu_xe']) ?></small>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($r['ngay_lap'])) ?></td>
                                        <td style="font-weight: 700; color: #1e40af;"><?= number_format((float)$r['tong_tien']) ?> đ</td>
                                        <td style="font-weight: 700; color: <?= (float)$r['tien_no'] > 0 ? '#dc2626' : '#16a34a' ?>;">
                                            <?= number_format((float)$r['tien_no']) ?> đ
                                        </td>
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
                                            <a href="index.php?c=repair&a=detail&id=<?= $r['ma_psc'] ?>" class="btn btn-sm btn-primary" title="Xem chi tiết, vật tư & thanh toán">
                                                <i class="fas fa-edit"></i> Chi tiết
                                            </a>
                                            <?php if ($r['trang_thai'] !== 'DaThanhToan' && ((float)$r['tong_tien'] > 0 || (float)$r['tien_no'] > 0)): ?>
                                                <a href="index.php?c=repair&a=detail&id=<?= $r['ma_psc'] ?>#section-payment" class="btn btn-sm btn-success" title="Quyết toán &amp; Thu tiền trực tiếp">
                                                    <i class="fas fa-file-invoice-dollar"></i> Thu tiền
                                                </a>
                                            <?php endif; ?>
                                            <a href="index.php?c=repair&a=print&id=<?= $r['ma_psc'] ?>" target="_blank" onclick="window.open(this.href, 'print_repair', 'width=1050,height=850,scrollbars=yes'); return false;" class="btn btn-sm btn-secondary" title="In phiếu & Hóa đơn hợp nhất">
                                                <i class="fas fa-print"></i>
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

<style>
.type-tab-btn {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.88rem;
    font-weight: 700;
    text-decoration: none;
    color: #475569;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    transition: all 0.2s ease;
}
.type-tab-btn:hover {
    background: #e2e8f0;
    color: #1e293b;
}
.type-tab-btn.active {
    background: #1e40af;
    color: #ffffff;
    border-color: #1e40af;
    box-shadow: 0 2px 8px rgba(30, 64, 175, 0.25);
}
.type-tab-btn.tab-suachua.active {
    background: #1e40af;
    border-color: #1e40af;
}
.type-tab-btn.tab-doche.active {
    background: #d97706;
    border-color: #d97706;
}
.type-tab-btn.tab-ruaxe.active {
    background: #0284c7;
    border-color: #0284c7;
}
</style>

