<div class="row">
    <div class="col-12 col-m-6" style="margin: 0 auto;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-edit"></i> Chỉnh sửa phụ tùng: <?= htmlspecialchars($part['ten_pt']) ?></h3>
            </div>
            <div class="card-content">
                <form method="POST" action="index.php?c=part&a=update">
                    <?= csrf_field() ?>
                    <input type="hidden" name="ma_pt" value="<?= $part['ma_pt'] ?>">

                    <div class="form-group">
                        <label for="ten_pt">Tên phụ tùng linh kiện: <span style="color:red">*</span></label>
                        <input type="text" name="ten_pt" id="ten_pt" class="form-control" value="<?= htmlspecialchars($part['ten_pt']) ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="ma_ncc">Nhà cung cấp: <span style="color:red">*</span></label>
                                <select name="ma_ncc" id="ma_ncc" class="form-control" required>
                                    <?php foreach ($suppliers as $ncc): ?>
                                        <option value="<?= $ncc['ma_ncc'] ?>" <?= ($ncc['ma_ncc'] == $part['ma_ncc']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($ncc['ten_ncc']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="ma_kho">Lưu trữ tại Kho: <span style="color:red">*</span></label>
                                <select name="ma_kho" id="ma_kho" class="form-control" required>
                                    <?php foreach ($warehouses as $wh): ?>
                                        <option value="<?= $wh['ma_kho'] ?>" <?= ($wh['ma_kho'] == ($part['ma_kho'] ?? 1)) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($wh['ten_kho']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="don_vi_tinh">Đơn vị tính:</label>
                                <input type="text" name="don_vi_tinh" id="don_vi_tinh" class="form-control" value="<?= htmlspecialchars($part['don_vi_tinh']) ?>" required>
                            </div>
                        </div>
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="muc_canh_bao">Mức cảnh báo tồn ít:</label>
                                <input type="number" name="muc_canh_bao" id="muc_canh_bao" class="form-control" value="<?= $part['muc_canh_bao'] ?>" min="1" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="gia_nhap">Giá nhập kho (VNĐ): <span style="color:red">*</span></label>
                                <input type="number" name="gia_nhap" id="gia_nhap" class="form-control" value="<?= (float)$part['gia_nhap'] ?>" min="0" step="1000" required>
                            </div>
                        </div>
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="gia_ban">Giá bán xuất xưởng (VNĐ): <span style="color:red">*</span></label>
                                <input type="number" name="gia_ban" id="gia_ban" class="form-control" value="<?= (float)$part['gia_ban'] ?>" min="0" step="1000" required>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 25px; display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cập nhật thay đổi
                        </button>
                        <a href="index.php?c=part&a=index" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

