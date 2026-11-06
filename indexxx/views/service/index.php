<div class="row">
    <!-- Cột 1: Danh mục dịch vụ / Tiền công -->
    <div class="col-12 col-m-7">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="margin: 0;"><i class="fas fa-wrench"></i> Danh mục Dịch vụ & Tiền công</h3>
                    <small style="color: #747d8c;">
                        Hiện có: <b><?= count($services) ?></b> / <b><?= $maxServices ?></b> loại tối đa theo quy định
                    </small>
                </div>
                <div>
                    <a href="index.php?c=service&a=create" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Thêm dịch vụ
                    </a>
                </div>
            </div>

            <div class="card-content">
                <div class="table-responsive">
                    <table class="table-custom table-hover">
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Tên dịch vụ / Tiền công</th>
                                <th>Đơn giá chuẩn</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($services)): ?>
                                <tr><td colspan="5" style="text-align: center;">Chưa có dịch vụ nào</td></tr>
                            <?php else: ?>
                                <?php foreach ($services as $sv): ?>
                                    <tr>
                                        <td>#<?= $sv['ma_dv'] ?></td>
                                        <td>
                                            <b><?= htmlspecialchars($sv['ten_dv']) ?></b><br>
                                            <small style="color: #747d8c;"><?= htmlspecialchars($sv['mo_ta'] ?? '') ?></small>
                                        </td>
                                        <td style="font-weight: 600; color: #4834d4;"><?= number_format((float)$sv['gia_tieu_chuan']) ?> đ</td>
                                        <td>
                                            <span class="badge <?= ($sv['trang_thai'] === 'KhaDung') ? 'badge-success' : 'badge-secondary' ?>">
                                                <?= ($sv['trang_thai'] === 'KhaDung') ? 'Khả dụng' : 'Tạm dừng' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="index.php?c=service&a=edit&id=<?= $sv['ma_dv'] ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="index.php?c=service&a=delete&id=<?= $sv['ma_dv'] ?>" 
                                               onclick="return confirm('Bạn có chắc muốn xóa hoặc tạm dừng dịch vụ này?')"
                                               class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
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

    <!-- Cột 2: Hiệu xe -->
    <div class="col-12 col-m-5">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0;"><i class="fas fa-tags"></i> Danh mục Hiệu xe</h3>
                    <small style="color: #747d8c;">
                        Hiện có: <b><?= count($brands) ?></b> / <b><?= $maxBrands ?></b> hiệu xe tối đa
                    </small>
                </div>
            </div>

            <div class="card-content">
                <!-- Form thêm hiệu xe mới -->
                <form method="POST" action="index.php?c=service&a=addBrand" style="margin-bottom: 20px;">
                    <?= csrf_field() ?>
                    <label style="font-weight: 600; font-size: 0.9rem;">Thêm hiệu xe mới:</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" name="ten_hieu_xe" class="form-control" placeholder="VD: Subaru, Lexus, Audi..." required>
                        <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                            <i class="fas fa-plus"></i> Thêm
                        </button>
                    </div>
                </form>

                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    <?php foreach ($brands as $b): ?>
                        <span class="badge badge-info" style="font-size: 0.95rem; padding: 6px 12px;">
                            <i class="fas fa-car"></i> <?= htmlspecialchars($b) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

