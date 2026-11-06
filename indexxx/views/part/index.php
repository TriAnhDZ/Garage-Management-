<?php
    $totalWarehouses = count($warehouses ?? []);
    $totalSkuAll = count($allParts ?? $parts ?? []);
    $totalStockAll = (int)array_sum(array_column($warehouses ?? [], 'tong_ton_kho'));
    $totalValueAll = (float)array_sum(array_column($warehouses ?? [], 'tong_gia_tri_nhap'));
    $totalLowStock = count($lowStock ?? []);

    $selectedWarehouseName = null;
    if (!empty($selectedKho)) {
        foreach ($warehouses as $w) {
            if ($w['ma_kho'] == $selectedKho) {
                $selectedWarehouseName = $w['ten_kho'];
                break;
            }
        }
    }

    $partThumbnails = [
        1 => 'assets/images/parts/part_oil_castrol.jpg',
        2 => 'assets/images/parts/part_filter_toyota.jpg',
        3 => 'assets/images/parts/part_brake_mazda.jpg',
        4 => 'assets/images/parts/part_sparkplug_ngk.jpg',
        5 => 'assets/images/parts/part_tire_michelin.jpg',
        6 => 'assets/images/parts/part_battery_gs.jpg',
    ];
?>

<!-- 1. TIÊU ĐỀ & THANH CÔNG CỤ TẬP TRUNG -->
<div class="row" style="margin-bottom: 20px;">
    <div class="col-12">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; background: #ffffff; padding: 20px 24px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
            <div>
                <h2 style="margin: 0 0 5px; font-size: 1.45rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #ffffff; font-size: 1.2rem;">
                        <i class="fas fa-boxes"></i>
                    </span>
                    <span>Kho &amp; Vật Tư Phụ Tùng</span>
                </h2>
                <p style="margin: 0; color: #64748b; font-size: 0.9rem;">
                    Hệ thống quản lý thống nhất: Danh mục phụ tùng, kiểm soát định mức an toàn, nhập kho &amp; luân chuyển đa kho
                </p>
            </div>

            <!-- Các nút nghiệp vụ chính -->
            <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                <a href="index.php?c=part&a=import" class="btn btn-warning" style="font-weight: 700; box-shadow: 0 2px 6px rgba(245, 158, 11, 0.3);">
                    <i class="fas fa-truck-loading"></i> Nhập kho
                </a>
                <a href="index.php?c=warehouse&a=transfer" class="btn btn-primary" style="font-weight: 700; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3);">
                    <i class="fas fa-exchange-alt"></i> Điều chuyển kho
                </a>
                <a href="index.php?c=part&a=create" class="btn btn-success" style="font-weight: 700;">
                    <i class="fas fa-plus"></i> Thêm phụ tùng
                </a>
                <a href="index.php?c=warehouse&a=create" class="btn btn-outline-primary" style="font-weight: 600;">
                    <i class="fas fa-warehouse"></i> Thêm kho mới
                </a>
                <div class="dropdown" style="display: inline-block;">
                    <a href="index.php?c=warehouse&a=history" class="btn btn-secondary" style="font-weight: 600;" title="Xem lịch sử điều chuyển giữa các kho">
                        <i class="fas fa-history"></i> Nhật ký chuyển
                    </a>
                </div>
                <a href="index.php?c=part&a=history" class="btn btn-secondary" style="font-weight: 600;" title="Xem lịch sử nhập kho">
                    <i class="fas fa-file-invoice"></i> Lịch sử nhập
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 2. THỐNG KÊ TỔNG QUAN HỆ THỐNG KHO (KPI STATS) -->
<div class="row" style="margin-bottom: 22px;">
    <!-- Tổng kho -->
    <div class="col-12 col-m-6 col-m-3">
        <div class="card" style="border-left: 5px solid #2563eb; margin-bottom: 0;">
            <div class="card-content" style="padding: 18px 20px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Hệ Thống Kho</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #1e40af; margin-top: 4px;">
                        <?= $totalWarehouses ?> <span style="font-size: 0.9rem; font-weight: 600; color: #64748b;">kho xưởng</span>
                    </div>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                    <i class="fas fa-warehouse"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tổng mặt hàng -->
    <div class="col-12 col-m-6 col-m-3">
        <div class="card" style="border-left: 5px solid #059669; margin-bottom: 0;">
            <div class="card-content" style="padding: 18px 20px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Danh Mục Phụ Tùng</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #065f46; margin-top: 4px;">
                        <?= $totalSkuAll ?> <span style="font-size: 0.9rem; font-weight: 600; color: #64748b;">loại</span>
                    </div>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                    <i class="fas fa-cogs"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tổng tồn kho -->
    <div class="col-12 col-m-6 col-m-3">
        <div class="card" style="border-left: 5px solid #d97706; margin-bottom: 0;">
            <div class="card-content" style="padding: 18px 20px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Tổng Tồn Kho</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #92400e; margin-top: 4px;">
                        <?= number_format($totalStockAll) ?> <span style="font-size: 0.9rem; font-weight: 600; color: #64748b;">đơn vị</span>
                    </div>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #fffbeb; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                    <i class="fas fa-cubes"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tổng giá trị vốn tồn -->
    <div class="col-12 col-m-6 col-m-3">
        <div class="card" style="border-left: 5px solid #7c3aed; margin-bottom: 0;">
            <div class="card-content" style="padding: 18px 20px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Giá Trị Vốn Tồn</div>
                    <div style="font-size: 1.45rem; font-weight: 800; color: #5b21b6; margin-top: 4px;">
                        <?= number_format($totalValueAll) ?> <span style="font-size: 0.82rem; font-weight: 700;">VNĐ</span>
                    </div>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #f5f3ff; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3. HỆ THỐNG CÁC KHO TRỰC QUAN (CARDS CHỌN KHO NHANH) -->
<div class="row" style="margin-bottom: 12px;">
    <div class="col-12">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div style="font-weight: 800; font-size: 1.05rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-map-marked-alt text-primary"></i> Danh sách các kho trực thuộc (Nhấn để lọc phụ tùng theo kho):
            </div>
            <?php if (!empty($selectedKho)): ?>
                <a href="index.php?c=part&a=index" class="btn btn-sm btn-outline-secondary" style="font-weight: 700;">
                    <i class="fas fa-times-circle"></i> Xem tất cả các kho
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Card: Tất cả kho -->
    <div class="col-12 col-m-6 col-m-3" style="margin-bottom: 15px;">
        <div class="card" style="margin-bottom: 0; border: 2px solid <?= empty($selectedKho) ? '#2563eb' : '#e2e8f0' ?>; background: <?= empty($selectedKho) ? '#eff6ff' : '#ffffff' ?>; transition: all 0.2s; border-radius: 12px;">
            <div class="card-content" style="padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <b style="font-size: 1rem; color: #1e3a8a;">🌐 TẤT CẢ CÁC KHO</b>
                    <?php if (empty($selectedKho)): ?>
                        <span class="badge badge-primary" style="font-size: 0.75rem;">Đang chọn</span>
                    <?php endif; ?>
                </div>
                <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 12px;">
                    Tổng hợp toàn bộ phụ tùng trong toàn bộ hệ thống Gara
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; padding: 6px 10px; background: rgba(0,0,0,0.03); border-radius: 6px; margin-bottom: 12px;">
                    <span>Tổng tồn: <b><?= number_format($totalStockAll) ?></b></span>
                    <span>Mặt hàng: <b><?= $totalSkuAll ?></b></span>
                </div>
                <a href="index.php?c=part&a=index" class="btn btn-sm btn-block <?= empty($selectedKho) ? 'btn-primary' : 'btn-outline-primary' ?>" style="font-weight: 700; border-radius: 6px;">
                    <i class="fas fa-list"></i> Xem toàn bộ phụ tùng
                </a>
            </div>
        </div>
    </div>

    <!-- Cards từng kho cụ thể -->
    <?php foreach ($warehouses as $wh): ?>
        <?php 
            $isActive = ($selectedKho == $wh['ma_kho']); 
            $cardBorder = $isActive ? '#2563eb' : '#e2e8f0';
            $cardBg = $isActive ? '#eff6ff' : '#ffffff';
        ?>
        <div class="col-12 col-m-6 col-m-3" style="margin-bottom: 15px;">
            <div class="card" style="margin-bottom: 0; border: 2px solid <?= $cardBorder ?>; background: <?= $cardBg ?>; transition: all 0.2s; border-radius: 12px;">
                <div class="card-content" style="padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                        <h4 style="margin: 0; font-size: 0.98rem; font-weight: 700; color: #0f172a; line-height: 1.3;">
                            <?= htmlspecialchars($wh['ten_kho']) ?>
                        </h4>
                        <a href="index.php?c=warehouse&a=edit&id=<?= $wh['ma_kho'] ?>" style="color: #94a3b8; font-size: 0.85rem; padding: 2px 5px;" title="Cài đặt kho">
                            <i class="fas fa-cog"></i>
                        </a>
                    </div>
                    <div style="font-size: 0.78rem; color: #64748b; margin-bottom: 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($wh['dia_chi'] ?? '') ?>">
                        <i class="fas fa-map-marker-alt text-danger"></i> <?= htmlspecialchars($wh['dia_chi'] ?? 'Tại xưởng chính') ?>
                    </div>
                    <div style="font-size: 0.84rem; padding: 8px 10px; background: rgba(0,0,0,0.03); border-radius: 6px; margin-bottom: 12px; line-height: 1.5;">
                        <div style="display: flex; justify-content: space-between;">
                            <span>Mặt hàng:</span> <b><?= $wh['so_mat_hang'] ?> loại</b>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Số lượng tồn:</span> <b style="color: #059669;"><?= number_format($wh['tong_ton_kho']) ?> cái/bộ</b>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Vốn tồn:</span> <b style="color: #2563eb;"><?= number_format($wh['tong_gia_tri_nhap']) ?> đ</b>
                        </div>
                    </div>
                    <a href="index.php?c=part&a=index&kho=<?= $wh['ma_kho'] ?>" 
                       class="btn btn-sm btn-block <?= $isActive ? 'btn-primary' : 'btn-outline-primary' ?>" 
                       style="font-weight: 700; border-radius: 6px;">
                        <i class="fas <?= $isActive ? 'fa-check' : 'fa-filter' ?>"></i> <?= $isActive ? 'Đang lọc kho này' : 'Lọc theo kho này' ?>
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- 4. BANNER CẢNH BÁO TỒN KHO AN TOÀN -->
<?php if (!empty($lowStock)): ?>
    <div class="row" style="margin-bottom: 18px;">
        <div class="col-12">
            <div style="background: linear-gradient(135deg, #fffbeb, #fef3c7); border: 1.5px solid #f59e0b; border-radius: 12px; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: #f59e0b; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div style="font-weight: 800; color: #92400e; font-size: 1rem;">
                            Cảnh báo tồn kho an toàn: Có <?= count($lowStock) ?> mặt hàng phụ tùng đang chạm hoặc dưới ngưỡng tối thiểu!
                        </div>
                        <div style="font-size: 0.85rem; color: #b45309; margin-top: 2px;">
                            Các mặt hàng này cần được lập kế hoạch nhập bổ sung để không làm gián đoạn tiến độ tiếp nhận &amp; sửa chữa xe cho khách.
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <a href="index.php?c=part&a=index<?= !empty($selectedKho) ? '&kho='.$selectedKho : '' ?>&status=low_stock" class="btn btn-sm btn-warning" style="font-weight: 700;">
                        <i class="fas fa-eye"></i> Xem riêng hàng sắp hết
                    </a>
                    <a href="index.php?c=part&a=import" class="btn btn-sm btn-success" style="font-weight: 700;">
                        <i class="fas fa-truck-loading"></i> Nhập hàng ngay
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- 5. BỘ LỌC TÌM KIẾM NHANH PHỤ TÙNG -->
<div class="row" style="margin-bottom: 18px;">
    <div class="col-12">
        <div class="card" style="margin-bottom: 0;">
            <div class="card-content" style="padding: 16px 20px;">
                <form method="GET" action="index.php" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                    <input type="hidden" name="c" value="part">
                    <input type="hidden" name="a" value="index">

                    <!-- Tìm theo từ khóa -->
                    <div style="flex: 1; min-width: 240px;">
                        <div style="position: relative;">
                            <input type="text" name="keyword" class="form-control" 
                                   value="<?= htmlspecialchars($keyword ?? '') ?>" 
                                   placeholder="🔍 Tìm theo tên phụ tùng, nhà cung cấp, mã số...">
                        </div>
                    </div>

                    <!-- Chọn kho -->
                    <div style="min-width: 220px;">
                        <select name="kho" class="form-control">
                            <option value="">🏢 Tất cả các kho</option>
                            <?php foreach ($warehouses as $w): ?>
                                <option value="<?= $w['ma_kho'] ?>" <?= ($selectedKho == $w['ma_kho']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($w['ten_kho']) ?> (<?= $w['so_mat_hang'] ?> loại)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Chọn trạng thái -->
                    <div style="min-width: 170px;">
                        <select name="status" class="form-control">
                            <option value="">📊 Tất cả trạng thái</option>
                            <option value="low_stock" <?= (($status ?? '') === 'low_stock') ? 'selected' : '' ?>>⚠️ Sắp hết hàng</option>
                        </select>
                    </div>

                    <!-- Nút thao tác lọc -->
                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-primary" style="font-weight: 700;">
                            <i class="fas fa-search"></i> Lọc dữ liệu
                        </button>
                        <?php if (!empty($keyword) || !empty($selectedKho) || !empty($status)): ?>
                            <a href="index.php?c=part&a=index" class="btn btn-secondary" title="Xóa bộ lọc">
                                <i class="fas fa-redo"></i> Đặt lại
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 6. BẢNG DANH MỤC PHỤ TÙNG & QUẢN LÝ TỒN KHO TẬP TRUNG -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <h3 style="margin: 0; display: flex; align-items: center; gap: 8px; font-size: 1.15rem;">
                    <i class="fas fa-boxes text-primary"></i> 
                    <span>Danh sách vật tư phụ tùng</span>
                    <?php if (!empty($selectedWarehouseName)): ?>
                        <span class="badge badge-info" style="font-size: 0.85rem; font-weight: 700;">
                            Đang lọc: <?= htmlspecialchars($selectedWarehouseName) ?>
                        </span>
                    <?php else: ?>
                        <span class="badge badge-secondary" style="font-size: 0.85rem;">Toàn hệ thống (<?= count($parts) ?> mặt hàng)</span>
                    <?php endif; ?>
                    <?php if (($status ?? '') === 'low_stock'): ?>
                        <span class="badge badge-danger" style="font-size: 0.85rem;">⚠️ Chỉ hiển thị sắp hết hàng</span>
                    <?php endif; ?>
                </h3>

                <div style="font-size: 0.88rem; color: #64748b;">
                    Hiển thị <b><?= count($parts) ?></b> phụ tùng
                </div>
            </div>

            <div class="card-content">
                <div class="table-responsive">
                    <table class="table-custom table-hover">
                        <thead>
                            <tr>
                                <th style="width: 55px; text-align: center;">Mã</th>
                                <th style="width: 65px; text-align: center;">Hình Ảnh</th>
                                <th>Tên Phụ Tùng &amp; Quy Cách</th>
                                <th>Kho Lưu Trữ</th>
                                <th>Nhà Cung Cấp</th>
                                <th style="width: 70px; text-align: center;">ĐVT</th>
                                <th style="text-align: right;">Giá Nhập (Vốn)</th>
                                <th style="text-align: right;">Giá Bán Xuất</th>
                                <th style="text-align: center; width: 110px;">Số Lượng Tồn</th>
                                <th style="text-align: center; width: 85px;">Ngưỡng Báo</th>
                                <th style="width: 190px; text-align: center;">Thao Tác Nhanh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($parts)): ?>
                                <tr>
                                    <td colspan="11" style="text-align: center; padding: 35px 20px; color: #64748b;">
                                        <i class="fas fa-box-open" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                                        Không tìm thấy mặt hàng phụ tùng nào phù hợp với bộ lọc hiện tại.<br>
                                        <a href="index.php?c=part&a=import" class="btn btn-sm btn-warning" style="margin-top: 12px; font-weight: 700;">
                                            <i class="fas fa-plus"></i> Nhập kho mặt hàng mới
                                        </a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($parts as $p): ?>
                                    <?php 
                                        $isLow = ((int)$p['so_luong_ton'] <= (int)$p['muc_canh_bao']); 
                                        $khoTen = $p['ten_kho'] ?? 'Kho Tổng Phụ Tùng Trung Tâm';
                                        $khoId = (int)($p['ma_kho'] ?? 1);
                                        $thumb = $partThumbnails[$p['ma_pt']] ?? 'assets/images/parts/part_oil_castrol.jpg';
                                    ?>
                                    <tr style="<?= $isLow ? 'background-color: rgba(239, 68, 68, 0.05);' : '' ?>">
                                        <td style="text-align: center; font-weight: 700; color: #64748b;">
                                            #<?= $p['ma_pt'] ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($p['ten_pt']) ?>" style="width: 44px; height: 44px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; display: inline-block;">
                                        </td>
                                        <td>
                                            <div style="font-weight: 700; color: #0f172a; font-size: 0.96rem;">
                                                <?= htmlspecialchars($p['ten_pt']) ?>
                                            </div>
                                            <?php if ($isLow): ?>
                                                <span class="badge badge-danger" style="font-size: 0.72rem; padding: 2px 7px; margin-top: 3px;">
                                                    <i class="fas fa-exclamation-circle"></i> Sắp hết hàng
                                                </span>
                                            <?php else: ?>
                                                <small style="color: #64748b; font-size: 0.78rem;">Tồn an toàn</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-info" style="font-size: 0.8rem; padding: 4px 9px;">
                                                <i class="fas fa-warehouse"></i> <?= htmlspecialchars($khoTen) ?>
                                            </span>
                                        </td>
                                        <td style="color: #475569; font-size: 0.88rem;">
                                            <?= htmlspecialchars($p['ten_ncc']) ?>
                                        </td>
                                        <td style="text-align: center; font-weight: 600;">
                                            <?= htmlspecialchars($p['don_vi_tinh']) ?>
                                        </td>
                                        <td style="text-align: right; color: #64748b; font-size: 0.92rem;">
                                            <?= number_format((float)$p['gia_nhap']) ?> đ
                                        </td>
                                        <td style="text-align: right; font-weight: 800; color: #1e40af; font-size: 0.95rem;">
                                            <?= number_format((float)$p['gia_ban']) ?> đ
                                        </td>
                                        <td style="text-align: center;">
                                            <span style="font-size: 1.15rem; font-weight: 800; color: <?= $isLow ? '#dc2626' : '#15803d' ?>;">
                                                <?= $p['so_luong_ton'] ?>
                                            </span>
                                        </td>
                                        <td style="text-align: center; color: #64748b; font-weight: 600;">
                                            <?= $p['muc_canh_bao'] ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <div style="display: flex; gap: 4px; justify-content: center; flex-wrap: wrap;">
                                                <!-- Nhập thêm kho -->
                                                <a href="index.php?c=part&a=import&name=<?= urlencode($p['ten_pt']) ?>" 
                                                   class="btn btn-sm btn-warning" 
                                                   style="padding: 3px 8px; font-size: 0.76rem; font-weight: 700;" 
                                                   title="Nhập thêm phụ tùng này">
                                                    <i class="fas fa-truck-loading"></i> Nhập
                                                </a>

                                                <!-- Điều chuyển kho -->
                                                <a href="index.php?c=warehouse&a=transfer&part_id=<?= $p['ma_pt'] ?>" 
                                                   class="btn btn-sm btn-primary" 
                                                   style="padding: 3px 8px; font-size: 0.76rem; font-weight: 700;" 
                                                   title="Chuyển sang kho khác">
                                                    <i class="fas fa-exchange-alt"></i> Chuyển
                                                </a>

                                                <!-- Sửa thông tin -->
                                                <a href="index.php?c=part&a=edit&id=<?= $p['ma_pt'] ?>" 
                                                   class="btn btn-sm btn-info" 
                                                   style="padding: 3px 8px; font-size: 0.76rem;" 
                                                   title="Sửa quy cách & giá">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <!-- Xóa -->
                                                <a href="index.php?c=part&a=delete&id=<?= $p['ma_pt'] ?>" 
                                                   onclick="return confirm('Bạn có chắc chắn muốn xóa mặt hàng [<?= htmlspecialchars(addslashes($p['ten_pt'])) ?>] khỏi danh mục?')" 
                                                   class="btn btn-sm btn-danger" 
                                                   style="padding: 3px 8px; font-size: 0.76rem;" 
                                                   title="Xóa phụ tùng">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
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
