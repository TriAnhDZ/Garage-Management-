<div class="row">
    <div class="col-12 col-m-8" style="margin: 0 auto;">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3><i class="fas fa-car-side"></i> Phiếu tiếp nhận xe sửa chữa</h3>
                <span class="badge <?= ($todayCount < $dailyLimit) ? 'badge-success' : 'badge-danger' ?>">
                    Hôm nay: <?= $todayCount ?> / <?= $dailyLimit ?> xe
                </span>
            </div>

            <div class="card-content">
                <?php if ($todayCount >= $dailyLimit): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <b>Cảnh báo quy định:</b> Gara đã tiếp nhận đủ số lượng xe tối đa trong ngày (<?= $dailyLimit ?> xe) theo quy định hiện hành. Không thể tiếp nhận thêm xe mới trong ngày hôm nay!
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?c=reception&a=store">
                    <?= csrf_field() ?>
                    <h4 style="color: #4834d4; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; margin-top: 0;">
                        1. Thông tin chủ xe
                    </h4>
                    <div class="row">
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="ten_chu_xe">Tên chủ xe: <span style="color:red">*</span></label>
                                <input type="text" id="ten_chu_xe" name="ten_chu_xe" class="form-control" placeholder="Nguyễn Văn A" required>
                            </div>
                        </div>
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="sdt">Số điện thoại liên hệ: <span style="color:red">*</span></label>
                                <input type="text" id="sdt" name="sdt" class="form-control" placeholder="0912345678" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="dia_chi">Địa chỉ chủ xe:</label>
                                <input type="text" id="dia_chi" name="dia_chi" class="form-control" placeholder="Số nhà, tên đường, phường/xã...">
                            </div>
                        </div>
                    </div>

                    <h4 style="color: #4834d4; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; margin-top: 20px;">
                        2. Thông tin phương tiện tiếp nhận
                    </h4>
                    <div class="row">
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="bien_so">Biển số xe: <span style="color:red">*</span></label>
                                <input type="text" id="bien_so" name="bien_so" class="form-control" placeholder="VD: 30A-123.45" style="text-transform: uppercase;" required>
                            </div>
                        </div>
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="hang_xe">Hiệu xe (Hãng xe): <span style="color:red">*</span></label>
                                <input type="text" id="hang_xe" name="hang_xe" list="brands_list" class="form-control" placeholder="Nhập tên hiệu xe..." autocomplete="off" required>
                                <datalist id="brands_list">
                                    <?php foreach ($brands as $b): ?>
                                        <option value="<?= htmlspecialchars($b) ?>">
                                    <?php endforeach; ?>
                                </datalist>
                                <div style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 5px; align-items: center;">
                                    <small style="color: #747d8c; font-size: 0.78rem;">Phổ biến:</small>
                                    <?php 
                                        $quickBrands = ['Toyota', 'Hyundai', 'Kia', 'Honda', 'Mazda', 'Ford', 'VinFast', 'Mercedes-Benz', 'BMW', 'Mitsubishi'];
                                        foreach ($quickBrands as $qb): 
                                    ?>
                                        <button type="button" class="btn-quick-brand" onclick="setBrand('<?= htmlspecialchars($qb) ?>')">
                                            <?= htmlspecialchars($qb) ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="dong_xe">Dòng xe (Model):</label>
                                <input type="text" id="dong_xe" name="dong_xe" class="form-control" placeholder="Vios 1.5G, CX-5, Santa Fe...">
                            </div>
                        </div>
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="nam_san_xuat">Năm sản xuất:</label>
                                <input type="number" id="nam_san_xuat" name="nam_san_xuat" class="form-control" placeholder="2021" min="1990" max="2027">
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 25px; display: flex; gap: 12px;">
                        <button type="submit" class="btn btn-primary" <?= ($todayCount >= $dailyLimit) ? 'disabled' : '' ?>>
                            <i class="fas fa-check-circle"></i> Tiếp nhận xe & Khởi tạo phiếu sửa chữa
                        </button>
                        <a href="index.php?c=reception&a=index" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function setBrand(brand) {
    const input = document.getElementById('hang_xe');
    if (input) {
        input.value = brand;
        input.focus();
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

