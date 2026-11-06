<?php
$selectedPtId = (int)($_GET['ma_pt'] ?? 0);
?>
<div class="row">
    <div class="col-12 col-m-6" style="margin: 0 auto;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-exchange-alt"></i> Điều chuyển vật tư giữa các kho</h3>
            </div>
            <div class="card-content">
                <form method="POST" action="index.php?c=warehouse&a=storeTransfer">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="ma_pt">Chọn phụ tùng cần điều chuyển: <span style="color:red">*</span></label>
                        <select name="ma_pt" id="ma_pt" class="form-control" required onchange="onPartChange()">
                            <option value="">-- Chọn phụ tùng --</option>
                            <?php foreach ($parts as $p): ?>
                                <option value="<?= $p['ma_pt'] ?>" 
                                        data-kho="<?= $p['ma_kho'] ?>" 
                                        data-stock="<?= $p['so_luong_ton'] ?>" 
                                        data-unit="<?= $p['don_vi_tinh'] ?>"
                                        <?= ($p['ma_pt'] == $selectedPtId) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['ten_pt']) ?> [Kho hiện tại: <?= htmlspecialchars($p['ten_kho'] ?? 'Kho Tổng') ?> | Tồn: <?= $p['so_luong_ton'] ?> <?= $p['don_vi_tinh'] ?>]
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="tu_kho">Từ kho nguồn: <span style="color:red">*</span></label>
                                <select name="tu_kho" id="tu_kho" class="form-control" required>
                                    <?php foreach ($warehouses as $w): ?>
                                        <option value="<?= $w['ma_kho'] ?>"><?= htmlspecialchars($w['ten_kho']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="den_kho">Đến kho đích: <span style="color:red">*</span></label>
                                <select name="den_kho" id="den_kho" class="form-control" required>
                                    <option value="">-- Chọn kho đích --</option>
                                    <?php foreach ($warehouses as $w): ?>
                                        <option value="<?= $w['ma_kho'] ?>"><?= htmlspecialchars($w['ten_kho']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="so_luong">Số lượng chuyển: <span style="color:red">*</span></label>
                        <input type="number" name="so_luong" id="so_luong" class="form-control" value="1" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="ghi_chu">Lý do / Ghi chú điều chuyển:</label>
                        <textarea name="ghi_chu" id="ghi_chu" class="form-control" rows="3" placeholder="Điều chuyển bổ sung vật tư cho ca bảo dưỡng hoặc phân bổ lại kho..."></textarea>
                    </div>

                    <div style="margin-top: 25px; display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-check"></i> Xác nhận điều chuyển kho
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

<script>
function onPartChange() {
    const sel = document.getElementById('ma_pt');
    const opt = sel.options[sel.selectedIndex];
    const currentKho = opt.getAttribute('data-kho');
    if (currentKho) {
        document.getElementById('tu_kho').value = currentKho;
    }
}
document.addEventListener("DOMContentLoaded", onPartChange);
</script>

