<div class="row">
    <div class="col-12 col-m-6" style="margin: 0 auto;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-calendar-plus"></i> Đặt lịch hẹn sửa chữa xe</h3>
            </div>
            <div class="card-content">
                <form method="POST" action="index.php?c=appointment&a=store">
                    <?= csrf_field() ?>
                    <?php if (!empty($cars)): ?>
                        <div class="form-group" id="existing_car_group">
                            <label for="ma_xe">Chọn xe đã có trong hệ thống: <span style="color:red">*</span></label>
                            <select name="ma_xe" id="ma_xe" class="form-control">
                                <option value="">-- Chọn xe theo biển số --</option>
                                <?php foreach ($cars as $car): ?>
                                    <option value="<?= $car['ma_xe'] ?>">
                                        <?= htmlspecialchars($car['bien_so']) ?> - <?= htmlspecialchars($car['hang_xe']) ?> <?= htmlspecialchars($car['dong_xe'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="margin: 12px 0;">
                            <label style="cursor: pointer; font-weight: 600; color: #4834d4;">
                                <input type="checkbox" id="toggle_new_car" name="is_new_car" value="1" onchange="toggleNewCarMode(this.checked)">
                                <i class="fas fa-plus-circle"></i> Đặt lịch cho phương tiện mới / Xe khác
                            </label>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="is_new_car" value="1">
                    <?php endif; ?>

                    <!-- Form nhập xe mới (hiển thị khi chưa có xe hoặc chọn Xe khác) -->
                    <div id="new_car_fields" style="<?= !empty($cars) ? 'display: none;' : '' ?> background: #f8f9fa; border: 1px dashed #cbd5e0; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                        <h4 style="margin: 0 0 12px; color: #2d3748; font-size: 0.95rem;">
                            <i class="fas fa-car"></i> THÔNG TIN XE MỚI:
                        </h4>
                        <div class="form-group">
                            <label for="new_bien_so">Biển số xe: <span style="color:red">*</span></label>
                            <input type="text" id="new_bien_so" name="new_bien_so" class="form-control" placeholder="VD: 30A-123.45" style="text-transform: uppercase;">
                        </div>

                        <div class="form-group">
                            <label for="new_hang_xe">Hiệu xe (Hãng xe): <span style="color:red">*</span></label>
                            <input type="text" id="new_hang_xe" name="new_hang_xe" list="appt_brands_list" class="form-control" placeholder="Nhập tên hiệu xe..." autocomplete="off">
                            <datalist id="appt_brands_list">
                                <?php if (!empty($brands)): ?>
                                    <?php foreach ($brands as $b): ?>
                                        <option value="<?= htmlspecialchars($b) ?>">
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </datalist>
                            <div style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 5px; align-items: center;">
                                <small style="color: #747d8c; font-size: 0.78rem;">Phổ biến:</small>
                                <?php 
                                    $quickBrands = ['Toyota', 'Hyundai', 'Kia', 'Honda', 'Mazda', 'Ford', 'VinFast', 'Mercedes-Benz', 'BMW', 'Mitsubishi'];
                                    foreach ($quickBrands as $qb): 
                                ?>
                                    <button type="button" class="btn-quick-brand" onclick="document.getElementById('new_hang_xe').value='<?= htmlspecialchars($qb) ?>'; document.getElementById('new_hang_xe').focus();">
                                        <?= htmlspecialchars($qb) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="new_dong_xe">Dòng xe (Model):</label>
                            <input type="text" id="new_dong_xe" name="new_dong_xe" class="form-control" placeholder="VD: Camry, Vios, CX-5, VF8, C200...">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="ngay_hen">Ngày & Giờ mong muốn đến gara: <span style="color:red">*</span></label>
                        <input type="datetime-local" name="ngay_hen" id="ngay_hen" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="ghi_chu">Mô tả hiện tượng xe / Yêu cầu bảo dưỡng:</label>
                        <textarea name="ghi_chu" id="ghi_chu" class="form-control" rows="4" placeholder="VD: Xe có tiếng kêu lạ ở bánh trước, cần thay nhớt và kiểm tra hệ thống phanh..."></textarea>
                    </div>

                    <div style="margin-top: 25px; display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Gửi yêu cầu đặt lịch hẹn
                        </button>
                        <a href="index.php?c=appointment&a=index" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleNewCarMode(isNew) {
    const newCarDiv = document.getElementById('new_car_fields');
    const existingGroup = document.getElementById('existing_car_group');
    const maXeSelect = document.getElementById('ma_xe');
    const newBienSo = document.getElementById('new_bien_so');
    const newHangXe = document.getElementById('new_hang_xe');

    if (isNew) {
        newCarDiv.style.display = 'block';
        if (existingGroup) existingGroup.style.display = 'none';
        if (maXeSelect) maXeSelect.removeAttribute('required');
        if (newBienSo) newBienSo.setAttribute('required', 'required');
        if (newHangXe) newHangXe.setAttribute('required', 'required');
    } else {
        newCarDiv.style.display = 'none';
        if (existingGroup) existingGroup.style.display = 'block';
        if (maXeSelect) maXeSelect.setAttribute('required', 'required');
        if (newBienSo) newBienSo.removeAttribute('required');
        if (newHangXe) newHangXe.removeAttribute('required');
    }
}
</script>

<style>
.btn-quick-brand {
    background: #edf2f7;
    border: 1px solid #cbd5e0;
    color: #2d3748;
    padding: 3px 9px;
    border-radius: 12px;
    font-size: 0.78rem;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.15s ease-in-out;
}
.btn-quick-brand:hover {
    background: #4834d4;
    color: #ffffff;
    border-color: #4834d4;
    transform: translateY(-1px);
}
</style>

