<div class="row">
    <div class="col-12 col-m-6" style="margin: 0 auto;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-plus-circle"></i> Thêm dịch vụ / Loại tiền công mới</h3>
            </div>
            <div class="card-content">
                <form method="POST" action="index.php?c=service&a=store">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="ten_dv">Tên dịch vụ kỹ thuật / Loại tiền công: <span style="color:red">*</span></label>
                        <input type="text" name="ten_dv" id="ten_dv" class="form-control" placeholder="VD: Cân mâm bấm chì 4 bánh" required>
                    </div>

                    <div class="form-group">
                        <label for="mo_ta">Mô tả quy trình / Hạng mục:</label>
                        <textarea name="mo_ta" id="mo_ta" class="form-control" rows="3" placeholder="Mô tả các bước thực hiện của dịch vụ..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="gia_tieu_chuan">Đơn giá tiêu chuẩn (VNĐ): <span style="color:red">*</span></label>
                        <input type="number" name="gia_tieu_chuan" id="gia_tieu_chuan" class="form-control" placeholder="350000" min="0" step="10000" required>
                    </div>

                    <div class="form-group">
                        <label for="trang_thai">Trạng thái áp dụng:</label>
                        <select name="trang_thai" id="trang_thai" class="form-control">
                            <option value="KhaDung">Khả dụng (Áp dụng ngay)</option>
                            <option value="TamDung">Tạm dừng (Chưa cung cấp)</option>
                        </select>
                    </div>

                    <div style="margin-top: 25px; display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu dịch vụ
                        </button>
                        <a href="index.php?c=service&a=index" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

