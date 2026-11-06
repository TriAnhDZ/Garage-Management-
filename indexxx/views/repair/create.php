<div class="row">
    <div class="col-12 col-m-8" style="margin: 0 auto;">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <h3 style="margin: 0;"><i class="fas fa-file-signature text-primary"></i> Khởi tạo phiếu dịch vụ &amp; Thu tiền</h3>
                <span class="badge badge-primary" style="font-size: 0.85rem;">Quy trình Garage Pro ERP</span>
            </div>
            <div class="card-content">
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px 16px; margin-bottom: 22px; font-size: 0.9rem; color: #1e40af; line-height: 1.5;">
                    <i class="fas fa-info-circle"></i> <b>Phân loại dịch vụ tích hợp:</b> Chọn đúng loại phiếu bên dưới để hệ thống áp dụng mẫu biên bản, gợi ý dịch vụ/vật tư phù hợp và thực hiện <b>quyết toán thu tiền trực tiếp</b> tại quầy.
                </div>

                <form method="POST" action="index.php?c=repair&a=store">
                    <?= csrf_field() ?>

                    <!-- Chọn loại phiếu: 3 phân nhóm -->
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label style="font-weight: 700; font-size: 0.95rem; color: #1e293b; margin-bottom: 10px; display: block;">
                            1. Chọn loại phiếu dịch vụ: <span style="color:red">*</span>
                        </label>
                        <div class="row">
                            <!-- Option 1: Phiếu sửa chữa -->
                            <div class="col-12 col-m-4" style="margin-bottom: 10px;">
                                <label class="ticket-type-card <?= ($selectedType === 'SuaChua') ? 'active' : '' ?>" id="card-type-SuaChua" onclick="selectTicketType('SuaChua')">
                                    <input type="radio" name="loai_phieu" value="SuaChua" <?= ($selectedType === 'SuaChua') ? 'checked' : '' ?> style="display: none;">
                                    <div class="card-type-icon" style="background: #dbeafe; color: #1e40af;">
                                        <i class="fas fa-wrench"></i>
                                    </div>
                                    <div class="card-type-title">Phiếu sửa chữa</div>
                                    <div class="card-type-sub">Bảo dưỡng định kỳ, sửa máy, gầm, phanh, điện lạnh</div>
                                </label>
                            </div>

                            <!-- Option 2: Phiếu thay thế linh kiện / độ chế -->
                            <div class="col-12 col-m-4" style="margin-bottom: 10px;">
                                <label class="ticket-type-card <?= ($selectedType === 'DoChe') ? 'active' : '' ?>" id="card-type-DoChe" onclick="selectTicketType('DoChe')">
                                    <input type="radio" name="loai_phieu" value="DoChe" <?= ($selectedType === 'DoChe') ? 'checked' : '' ?> style="display: none;">
                                    <div class="card-type-icon" style="background: #fef3c7; color: #d97706;">
                                        <i class="fas fa-cogs"></i>
                                    </div>
                                    <div class="card-type-title">Thay linh kiện &amp; Độ xe</div>
                                    <div class="card-type-sub">Nâng cấp phụ tùng, độ đèn bi, màn hình, phuộc, đồ chơi</div>
                                </label>
                            </div>

                            <!-- Option 3: Phiếu rửa xe -->
                            <div class="col-12 col-m-4" style="margin-bottom: 10px;">
                                <label class="ticket-type-card <?= ($selectedType === 'RuaXe') ? 'active' : '' ?>" id="card-type-RuaXe" onclick="selectTicketType('RuaXe')">
                                    <input type="radio" name="loai_phieu" value="RuaXe" <?= ($selectedType === 'RuaXe') ? 'checked' : '' ?> style="display: none;">
                                    <div class="card-type-icon" style="background: #e0f2fe; color: #0284c7;">
                                        <i class="fas fa-spray-can"></i>
                                    </div>
                                    <div class="card-type-title">Phiếu rửa xe &amp; Spa</div>
                                    <div class="card-type-sub">Rửa bọt tuyết, hút bụi, dọn khoang máy, nội thất, đánh bóng</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Chọn phương tiện -->
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label for="ma_xe" style="font-weight: 700; font-size: 0.95rem; color: #1e293b;">
                            2. Chọn xe thực hiện: <span style="color:red">*</span>
                        </label>
                        <select name="ma_xe" id="ma_xe" class="form-control" style="font-size: 1rem; padding: 10px 14px;" required>
                            <option value="">-- Chọn xe theo Biển số - Chủ xe - Hiệu xe --</option>
                            <?php foreach ($cars as $car): ?>
                                <option value="<?= $car['ma_xe'] ?>">
                                    <?= htmlspecialchars($car['bien_so']) ?> - <?= htmlspecialchars($car['hang_xe']) ?> <?= htmlspecialchars($car['dong_xe'] ?? '') ?> (Chủ xe: <?= htmlspecialchars($car['ten_chu_xe']) ?> - SĐT: <?= htmlspecialchars($car['sdt']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-top: 30px; display: flex; gap: 12px; flex-wrap: wrap; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                        <button type="submit" class="btn btn-primary" style="padding: 11px 26px; font-weight: 700; font-size: 1rem;">
                            <i class="fas fa-check-circle"></i> Khởi tạo phiếu ngay
                        </button>
                        <a href="index.php?c=reception&a=create" class="btn btn-info" style="padding: 11px 18px;">
                            <i class="fas fa-car"></i> Tiếp nhận xe mới
                        </a>
                        <a href="index.php?c=repair&a=index" class="btn btn-secondary" style="padding: 11px 18px;">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.ticket-type-card {
    display: block;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 18px 14px;
    cursor: pointer;
    background: #ffffff;
    transition: all 0.2s ease;
    text-align: center;
    height: 100%;
}
.ticket-type-card:hover {
    border-color: #94a3b8;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.ticket-type-card.active {
    border-color: #2563eb;
    background: #f8fafc;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.15);
}
.ticket-type-card.active#card-type-DoChe {
    border-color: #d97706;
    background: #fffbeb;
    box-shadow: 0 4px 14px rgba(217, 119, 6, 0.15);
}
.ticket-type-card.active#card-type-RuaXe {
    border-color: #0284c7;
    background: #f0f9ff;
    box-shadow: 0 4px 14px rgba(2, 132, 199, 0.15);
}
.card-type-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    margin: 0 auto 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
}
.card-type-title {
    font-weight: 800;
    font-size: 0.95rem;
    color: #0f172a;
    margin-bottom: 4px;
}
.card-type-sub {
    font-size: 0.78rem;
    color: #64748b;
    line-height: 1.35;
}
</style>

<script>
function selectTicketType(type) {
    document.querySelectorAll('.ticket-type-card').forEach(c => c.classList.remove('active'));
    const targetCard = document.getElementById('card-type-' + type);
    if (targetCard) {
        targetCard.classList.add('active');
        const radio = targetCard.querySelector('input[type=radio]');
        if (radio) radio.checked = true;
    }
}
</script>

