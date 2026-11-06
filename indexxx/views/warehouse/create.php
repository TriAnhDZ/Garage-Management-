<div class="row">
    <div class="col-12 col-m-6" style="margin: 0 auto;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-plus-circle"></i> Thêm kho vật tư mới</h3>
            </div>
            <div class="card-content">
                <form method="POST" action="index.php?c=warehouse&a=store">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="ten_kho">Tên kho vật tư: <span style="color:red">*</span></label>
                        <input type="text" name="ten_kho" id="ten_kho" class="form-control" placeholder="VD: Kho Sơn & Hóa Chất Phụ Trợ" required>
                    </div>

                    <div class="form-group">
                        <label for="dia_chi">Vị trí / Địa chỉ kho:</label>
                        <input type="text" name="dia_chi" id="dia_chi" class="form-control" placeholder="VD: Khu E - Nhà xưởng 2, Tầng trệt">
                    </div>

                    <div class="form-group">
                        <label for="sdt">Số điện thoại liên hệ kho:</label>
                        <input type="text" name="sdt" id="sdt" class="form-control" placeholder="0901111225">
                    </div>

                    <div class="form-group">
                        <label for="mo_ta">Mô tả đặc thù bảo quản:</label>
                        <textarea name="mo_ta" id="mo_ta" class="form-control" rows="3" placeholder="Chứa các loại sơn sấy, sơn lót, chất đóng rắn, dung môi pha chế..."></textarea>
                    </div>

                    <div style="margin-top: 25px; display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Tạo kho vật tư
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

