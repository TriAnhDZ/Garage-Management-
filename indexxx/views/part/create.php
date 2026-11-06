<div class="row">
    <div class="col-12 col-m-6" style="margin: 0 auto;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-plus-circle"></i> Thêm loại phụ tùng mới</h3>
            </div>
            <div class="card-content">
                <form method="POST" action="index.php?c=part&a=store">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="ten_pt">Tên phụ tùng linh kiện: <span style="color:red">*</span></label>
                        <input type="text" name="ten_pt" id="ten_pt" class="form-control" placeholder="VD: Lọc gió điều hòa Denso" required>
                    </div>

                    <div class="row">
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="ma_ncc">Nhà cung cấp: <span style="color:red">*</span></label>
                                <select name="ma_ncc" id="ma_ncc" class="form-control" required>
                                    <option value="">-- Chọn nhà cung cấp --</option>
                                    <?php foreach ($suppliers as $ncc): ?>
                                        <option value="<?= $ncc['ma_ncc'] ?>"><?= htmlspecialchars($ncc['ten_ncc']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="ma_kho">Lưu trữ tại Kho vật tư: <span style="color:red">*</span></label>
                                <select name="ma_kho" id="ma_kho" class="form-control" required>
                                    <?php foreach ($warehouses as $wh): ?>
                                        <option value="<?= $wh['ma_kho'] ?>"><?= htmlspecialchars($wh['ten_kho']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="don_vi_tinh">Đơn vị tính:</label>
                                <input type="text" name="don_vi_tinh" id="don_vi_tinh" class="form-control" value="Cái" required>
                            </div>
                        </div>
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="muc_canh_bao">Mức cảnh báo tồn ít:</label>
                                <input type="number" name="muc_canh_bao" id="muc_canh_bao" class="form-control" value="5" min="1" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="gia_nhap">Giá nhập kho (VNĐ): <span style="color:red">*</span></label>
                                <input type="number" name="gia_nhap" id="gia_nhap" class="form-control" placeholder="150000" min="0" step="5000" required>
                            </div>
                        </div>
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="gia_ban">Giá bán xuất xưởng (VNĐ):</label>
                                <input type="number" name="gia_ban" id="gia_ban" class="form-control" placeholder="Tự động tính theo tỉ lệ nếu trống" min="0" step="5000">
                                <small style="color: #747d8c;">Nếu để trống sẽ tự tính theo quy định tỉ lệ %</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="so_luong_ton">Số lượng tồn kho ban đầu:</label>
                        <input type="number" name="so_luong_ton" id="so_luong_ton" class="form-control" value="0" min="0">
                    </div>

                    <div style="margin-top: 25px; display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu thông tin phụ tùng
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

