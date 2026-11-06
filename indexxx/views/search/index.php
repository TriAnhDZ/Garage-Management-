<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="margin: 0;">
                        <i class="fas <?= !empty($isCustomer) ? 'fa-car' : 'fa-search' ?>"></i>
                        <?= !empty($isCustomer) ? 'Phương tiện của tôi' : 'Tra cứu hồ sơ & Lịch sử phương tiện' ?>
                    </h3>
                    <small style="color: #747d8c;">
                        <?= !empty($isCustomer) ? 'Danh sách và trạng thái xe của bạn tại Garage Pro' : 'Tìm kiếm thông tin xe, chủ xe và lịch sử sửa chữa theo nhiều tiêu chí' ?>
                    </small>
                </div>
                <?php if (!empty($isCustomer)): ?>
                    <a href="index.php?c=appointment&a=create" class="btn btn-sm btn-success">
                        <i class="fas fa-calendar-plus"></i> Đặt lịch hẹn mới
                    </a>
                <?php endif; ?>
            </div>

            <div class="card-content">
                <!-- Bộ lọc tra cứu -->
                <form method="GET" action="index.php" style="background: var(--second-bg-color, #f8f9fa); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <input type="hidden" name="c" value="search">
                    <input type="hidden" name="a" value="index">
                    <div class="row" style="align-items: flex-end;">
                        <div class="col-12 <?= !empty($isCustomer) ? 'col-m-5' : 'col-m-3' ?>">
                            <label style="font-weight: 600; margin-bottom: 4px; display: block;">Biển số xe:</label>
                            <input type="text" name="bien_so" class="form-control" value="<?= htmlspecialchars($bienSo ?? '') ?>" placeholder="VD: 30A-123.45">
                        </div>
                        <div class="col-12 <?= !empty($isCustomer) ? 'col-m-4' : 'col-m-3' ?>">
                            <label style="font-weight: 600; margin-bottom: 4px; display: block;">Hiệu xe:</label>
                            <input type="text" name="hang_xe" list="search_brands_list" class="form-control" value="<?= htmlspecialchars($hangXe ?? '') ?>" placeholder="Tất cả hoặc nhập hiệu xe..." autocomplete="off">
                            <datalist id="search_brands_list">
                                <?php foreach ($brands as $b): ?>
                                    <option value="<?= htmlspecialchars($b) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <?php if (empty($isCustomer)): ?>
                            <div class="col-12 col-m-3">
                                <label style="font-weight: 600; margin-bottom: 4px; display: block;">Tên chủ xe:</label>
                                <input type="text" name="ten_khach" class="form-control" value="<?= htmlspecialchars($tenKhach ?? '') ?>" placeholder="VD: Nguyễn Văn...">
                            </div>
                        <?php endif; ?>
                        <div class="col-12 <?= !empty($isCustomer) ? 'col-m-3' : 'col-m-3' ?>">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tìm kiếm</button>
                            <a href="index.php?c=search&a=index" class="btn btn-secondary"><i class="fas fa-redo"></i> Đặt lại</a>
                        </div>
                    </div>
                </form>

                <!-- Kết quả tra cứu -->
                <div class="table-responsive">
                    <table class="table-custom table-hover">
                        <thead>
                            <tr>
                                <th>Biển số xe</th>
                                <th>Hiệu xe / Dòng xe</th>
                                <?php if (empty($isCustomer)): ?>
                                    <th>Chủ xe</th>
                                    <th>Điện thoại</th>
                                <?php else: ?>
                                    <th>Năm sản xuất</th>
                                <?php endif; ?>
                                <th>Lượt sửa</th>
                                <th>Tình trạng nợ</th>
                                <th style="min-width: 150px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($cars)): ?>
                                <tr>
                                    <td colspan="<?= empty($isCustomer) ? '7' : '6' ?>" style="text-align: center; padding: 30px; color: #747d8c;">
                                        <i class="fas fa-car-side" style="font-size: 2.2rem; margin-bottom: 10px; display: block; opacity: 0.4;"></i>
                                        <?= !empty($isCustomer) ? 'Bạn chưa có phương tiện nào trong hệ thống hoặc không khớp từ khóa tìm kiếm.' : 'Không tìm thấy phương tiện nào phù hợp với bộ lọc.' ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($cars as $car): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-info" style="font-size: 0.95rem; font-weight: 700; letter-spacing: 0.5px;">
                                                <?= htmlspecialchars($car['bien_so']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <b><?= htmlspecialchars($car['hang_xe']) ?></b> <?= htmlspecialchars($car['dong_xe'] ?? '') ?>
                                        </td>
                                        <?php if (empty($isCustomer)): ?>
                                            <td><b><?= htmlspecialchars($car['ten_chu_xe'] ?? '') ?></b></td>
                                            <td><?= htmlspecialchars($car['sdt'] ?? '') ?></td>
                                        <?php else: ?>
                                            <td><?= htmlspecialchars($car['nam_san_xuat'] ?? 'Không rõ') ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <span class="badge badge-secondary"><?= $car['so_lan_sua'] ?> lượt</span>
                                        </td>
                                        <td>
                                            <?php if ($car['tien_no'] > 0): ?>
                                                <span class="badge badge-warning" style="font-size: 0.85rem;">
                                                    Còn nợ: <?= number_format((float)$car['tien_no']) ?> đ
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-success" style="font-size: 0.85rem;">
                                                    <i class="fas fa-check"></i> Đã tất toán
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="white-space: nowrap;">
                                            <a href="index.php?c=search&a=detail&id=<?= $car['ma_xe'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-history"></i> Lịch sử sửa
                                            </a>
                                            <?php if (!empty($isCustomer)): ?>
                                                <a href="index.php?c=appointment&a=create&bien_so=<?= urlencode($car['bien_so']) ?>" class="btn btn-sm btn-success" title="Đặt lịch hẹn bảo dưỡng">
                                                    <i class="fas fa-calendar-plus"></i> Đặt hẹn
                                                </a>
                                            <?php elseif (!empty($isStaff) && $car['tien_no'] > 0): ?>
                                                <a href="index.php?c=invoice&a=create&bien_so=<?= urlencode($car['bien_so']) ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-dollar-sign"></i> Thu tiền
                                                </a>
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
