<div class="row">
    <div class="col-12 col-m-6" style="margin: 0 auto;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-edit"></i> Chỉnh sửa kho: <?= htmlspecialchars($kho['ten_kho']) ?></h3>
            </div>
            <div class="card-content">
                <form method="POST" action="index.php?c=warehouse&a=update">
                    <?= csrf_field() ?>
                    <input type="hidden" name="ma_kho" value="<?= $kho['ma_kho'] ?>">

                    <div class="form-group">
                        <label for="ten_kho">Tên kho vật tư: <span style="color:red">*</span></label>
                        <input type="text" name="ten_kho" id="ten_kho" class="form-control" value="<?= htmlspecialchars($kho['ten_kho']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="dia_chi">Vị trí / Địa chỉ kho:</label>
                        <input type="text" name="dia_chi" id="dia_chi" class="form-control" value="<?= htmlspecialchars($kho['dia_chi'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="sdt">Số điện thoại liên hệ kho:</label>
                        <input type="text" name="sdt" id="sdt" class="form-control" value="<?= htmlspecialchars($kho['sdt'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="mo_ta">Mô tả đặc thù bảo quản:</label>
                        <textarea name="mo_ta" id="mo_ta" class="form-control" rows="3"><?= htmlspecialchars($kho['mo_ta'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="trang_thai">Trạng thái vận hành:</label>
                        <select name="trang_thai" id="trang_thai" class="form-control">
                            <option value="HoatDong" <?= ($kho['trang_thai'] === 'HoatDong') ? 'selected' : '' ?>>Đang hoạt động</option>
                            <option value="TamDong" <?= ($kho['trang_thai'] === 'TamDong') ? 'selected' : '' ?>>Tạm dừng bảo trì</option>
                        </select>
                    </div>

                    <div style="margin-top: 25px; display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cập nhật thay đổi
                        </button>
                        <a href="index.php?c=part&a=index" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại kho &amp; phụ tùng
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

