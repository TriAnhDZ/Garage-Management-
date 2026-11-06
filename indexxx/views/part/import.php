<div class="row">
    <div class="col-12 col-m-8" style="margin: 0 auto;">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff; padding: 18px 24px; border-radius: 12px 12px 0 0;">
                <h3 style="margin:0; font-size: 1.25rem;"><i class="fas fa-truck-loading text-warning"></i> Lập phiếu nhập kho phụ tùng</h3>
                <p style="margin: 4px 0 0; font-size: 0.85rem; color: #94a3b8;">Nhập kho vật tư, phụ tùng thay thế</p>
            </div>
            <div class="card-content" style="padding: 24px;">
                <form method="POST" action="index.php?c=part&a=storeImport">
                    <?= csrf_field() ?>
                    <!-- Tên phụ tùng -->
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="ten_pt" style="font-weight: 700; font-size: 0.95rem; color: #1e293b;">
                            <i class="fas fa-box-open text-primary"></i> Tên phụ tùng / Vật tư: <span style="color:red">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="text" 
                                   name="ten_pt" 
                                   id="ten_pt" 
                                   class="form-control" 
                                   list="part_suggestions" 
                                   value="<?= htmlspecialchars($_GET['name'] ?? '') ?>"
                                   placeholder="Nhập tên phụ tùng..." 
                                   autocomplete="off" 
                                   required 
                                   style="font-size: 1rem; padding: 10px 14px;"
                                   oninput="onPartImportChange(this.value)">
                            <datalist id="part_suggestions">
                                <?php foreach ($parts as $p): ?>
                                    <option value="<?= htmlspecialchars($p['ten_pt']) ?>">
                                        Kho: <?= $p['so_luong_ton'] ?> <?= $p['don_vi_tinh'] ?> | Giá nhập: <?= number_format((float)$p['gia_nhap']) ?> đ
                                    </option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div id="part_status_hint" style="margin-top: 6px; font-size: 0.85rem;"></div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="so_luong" style="font-weight: 600;">Số lượng nhập thêm: <span style="color:red">*</span></label>
                                <input type="number" name="so_luong" id="so_luong" class="form-control" min="1" value="10" required>
                            </div>
                        </div>
                        <div class="col-12 col-m-6">
                            <div class="form-group">
                                <label for="gia_nhap" style="font-weight: 600;">Đơn giá nhập vào (VNĐ): <span style="color:red">*</span></label>
                                <input type="number" name="gia_nhap" id="gia_nhap" class="form-control" min="0" step="1000" placeholder="Nhập giá mua vào..." required>
                            </div>
                        </div>
                    </div>

                    <!-- Thiết lập quy cách phụ tùng khi gõ mới -->
                    <div id="new_part_fields" style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                        <div style="font-weight: 700; color: #1e40af; font-size: 0.9rem; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                            <i class="fas fa-sliders-h"></i> Thiết lập quy cách phụ tùng:
                        </div>
                        <div class="row">
                            <div class="col-12 col-m-4">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label for="don_vi_tinh" style="font-size: 0.85rem; font-weight: 600;">Đơn vị tính:</label>
                                    <input type="text" name="don_vi_tinh" id="don_vi_tinh" class="form-control" list="units_list" value="Cái" placeholder="Cái, Bộ, Lít...">
                                    <datalist id="units_list">
                                        <option value="Cái">
                                        <option value="Bộ">
                                        <option value="Lít">
                                        <option value="Bình">
                                        <option value="Can">
                                        <option value="Chai">
                                        <option value="Bộ 4 chiếc">
                                        <option value="Thùng">
                                        <option value="Hộp">
                                        <option value="Kg">
                                        <option value="Cuộn">
                                    </datalist>
                                </div>
                            </div>
                            <div class="col-12 col-m-4">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label for="ma_ncc" style="font-size: 0.85rem; font-weight: 600;">Nhà cung cấp:</label>
                                    <select name="ma_ncc" id="ma_ncc" class="form-control">
                                        <?php if (!empty($suppliers)): ?>
                                            <?php foreach ($suppliers as $ncc): ?>
                                                <option value="<?= $ncc['ma_ncc'] ?>"><?= htmlspecialchars($ncc['ten_ncc']) ?></option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="1">Nhà cung cấp chính hãng</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-m-4">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label for="ma_kho" style="font-size: 0.85rem; font-weight: 600;">Kho lưu trữ:</label>
                                    <select name="ma_kho" id="ma_kho" class="form-control">
                                        <?php if (!empty($warehouses)): ?>
                                            <?php foreach ($warehouses as $wh): ?>
                                                <option value="<?= $wh['ma_kho'] ?>"><?= htmlspecialchars($wh['ten_kho']) ?></option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="1">Kho Tổng Phụ Tùng Trung Tâm</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="ghi_chu" style="font-weight: 600;">Ghi chú phiếu nhập kho:</label>
                        <textarea name="ghi_chu" id="ghi_chu" class="form-control" rows="2" placeholder="Số hóa đơn VAT của NCC, chứng từ giao nhận, tình trạng kiện hàng..."></textarea>
                    </div>

                    <div style="margin-top: 25px; display: flex; gap: 12px; align-items: center;">
                        <button type="submit" class="btn btn-warning" style="padding: 10px 24px; font-weight: 700; font-size: 0.95rem;">
                            <i class="fas fa-check-circle"></i> Xác nhận nhập kho & Lưu tồn
                        </button>
                        <a href="index.php?c=part&a=index" class="btn btn-secondary" style="padding: 10px 18px;">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const existingPartsData = {
    <?php foreach ($parts as $p): ?>
        <?= json_encode($p['ten_pt']) ?>: {
            id: <?= (int)$p['ma_pt'] ?>,
            price: <?= (float)$p['gia_nhap'] ?>,
            unit: <?= json_encode($p['don_vi_tinh']) ?>,
            stock: <?= (int)$p['so_luong_ton'] ?>,
            nccId: <?= (int)$p['ma_ncc'] ?>,
            khoId: <?= (int)$p['ma_kho'] ?>
        },
    <?php endforeach; ?>
};

function onPartImportChange(val) {
    const trimmed = val.trim();
    const hint = document.getElementById('part_status_hint');
    const priceInput = document.getElementById('gia_nhap');
    const unitInput = document.getElementById('don_vi_tinh');
    const nccSelect = document.getElementById('ma_ncc');
    const khoSelect = document.getElementById('ma_kho');

    if (existingPartsData[trimmed]) {
        const item = existingPartsData[trimmed];
        priceInput.value = item.price;
        if (unitInput) unitInput.value = item.unit;
        if (nccSelect && item.nccId) nccSelect.value = item.nccId;
        if (khoSelect && item.khoId) khoSelect.value = item.khoId;

        hint.innerHTML = `<span style="color: #059669; font-weight: 600;">
            <i class="fas fa-check"></i> Kho hiện có: ${item.stock} ${item.unit} | Giá nhập cũ: ${item.price.toLocaleString()} đ
        </span>`;
    } else {
        hint.innerHTML = '';
    }
}

window.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('ten_pt');
    if (el && el.value) {
        onPartImportChange(el.value);
    }
});
</script>

