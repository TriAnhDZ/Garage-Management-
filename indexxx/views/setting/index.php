<?php
$paramLabels = [
    'So xe sua chua toi da'  => 'Số xe sửa chữa tối đa / ngày',
    'Ti le don gia vtpt'      => 'Tỷ lệ đơn giá bán phụ tùng (%)',
    'So hieu xe'             => 'Số lượng hiệu xe tối đa',
    'So loai vat tu'         => 'Số loại vật tư phụ tùng tối đa',
    'So loai tien cong'      => 'Số loại tiền công tối đa',
    'gmail_smtp_user'        => 'Tài khoản Gmail gửi đi',
    'gmail_smtp_pass'        => 'Mật khẩu ứng dụng Gmail',
    'gmail_smtp_sender_name' => 'Tên hiển thị người gửi Gmail',
    'gmail_smtp_status'      => 'Trạng thái kết nối Gmail'
];
?>
<div class="row">
    <!-- Cột 1: Danh sách quy định hiện hành -->
    <div class="col-12 col-m-8">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <h3 style="margin: 0;"><i class="fas fa-sliders-h"></i> Bảng quy định & Tham số vận hành Gara</h3>
                <a href="index.php?c=email&a=config" class="btn btn-sm btn-outline-primary" style="background:#fff; color:#4834d4; border:1px solid #4834d4;">
                    <i class="fab fa-google"></i> Cấu hình tài khoản Gmail
                </a>
            </div>
            <div class="card-content">
                <p style="color: #747d8c; margin-top: 0;">
                    Các quy định dưới đây điều chỉnh trực tiếp các ràng buộc nghiệp vụ trong hệ thống như: số xe tiếp nhận tối đa trong ngày, tỉ lệ đơn giá bán phụ tùng, số lượng tối đa các loại tiền công & vật tư.
                </p>

                <div class="table-responsive">
                    <table class="table-custom table-hover">
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Tên quy định / Tham số</th>
                                <th>Giá trị thiết lập</th>
                                <th>Mô tả / Ý nghĩa</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rules as $r): ?>
                                <?php
                                    $displayName = $paramLabels[$r['ten_tham_so']] ?? $r['ten_tham_so'];
                                    $isSmtp = str_starts_with($r['ten_tham_so'], 'gmail_smtp_');
                                ?>
                                <tr>
                                    <td>#<?= $r['ma_tham_so'] ?></td>
                                    <td>
                                        <b><?= htmlspecialchars($displayName) ?></b><br>
                                        <small style="color: #747d8c;"><code><?= htmlspecialchars($r['ten_tham_so']) ?></code></small>
                                    </td>
                                    <td>
                                        <?php if ($r['ten_tham_so'] === 'gmail_smtp_pass'): ?>
                                            <span class="badge badge-secondary">••••••••••••••••</span>
                                        <?php elseif ($isSmtp): ?>
                                            <span class="badge badge-info"><?= htmlspecialchars($r['ghi_chu'] ?? '') ?></span>
                                        <?php else: ?>
                                            <b style="color: #4834d4; font-size: 1.15rem;">
                                                <?= (float)$r['gia_tri'] ?>
                                                <?php if ($r['ten_tham_so'] === 'Ti le don gia vtpt'): ?> % <?php endif; ?>
                                            </b>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= htmlspecialchars($r['ghi_chu'] ?? '') ?></small></td>
                                    <td>
                                        <?php if ($isSmtp): ?>
                                            <a href="index.php?c=email&a=config" class="btn btn-sm btn-info" title="Cài đặt Gmail">
                                                <i class="fas fa-cog"></i> Cấu hình
                                            </a>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-primary" onclick="editRule(<?= htmlspecialchars(json_encode(array_merge($r, ['display_name' => $displayName]))) ?>)">
                                                <i class="fas fa-edit"></i> Sửa giá trị
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Cột 2: Form chỉnh sửa / thêm mới tham số -->
    <div class="col-12 col-m-4">
        <div class="card">
            <div class="card-header">
                <h3 id="form_title"><i class="fas fa-edit"></i> Cập nhật tham số</h3>
            </div>
            <div class="card-content">
                <form method="POST" action="index.php?c=setting&a=update" id="ruleForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="ma_tham_so" id="rule_id" value="">

                    <div class="form-group">
                        <label>Tên quy định / tham số:</label>
                        <input type="text" id="rule_name_display" class="form-control" readonly style="background: rgba(0,0,0,0.05); font-weight: 600;">
                        <input type="hidden" name="ten_tham_so" id="rule_name">
                    </div>

                    <div class="form-group">
                        <label>Giá trị quy định mới: <span style="color:red">*</span></label>
                        <input type="number" step="any" name="gia_tri" id="rule_val" class="form-control" required placeholder="Nhập giá trị số...">
                    </div>

                    <div class="form-group">
                        <label>Ghi chú diễn giải:</label>
                        <textarea name="ghi_chu" id="rule_note" class="form-control" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-warning btn-block" id="btn_submit">
                        <i class="fas fa-save"></i> Lưu cập nhật quy định
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editRule(rule) {
    document.getElementById('rule_id').value = rule.ma_tham_so;
    document.getElementById('rule_name').value = rule.ten_tham_so;
    document.getElementById('rule_name_display').value = rule.display_name || rule.ten_tham_so;
    document.getElementById('rule_val').value = rule.gia_tri;
    document.getElementById('rule_note').value = rule.ghi_chu || '';
    document.getElementById('form_title').innerHTML = '<i class="fas fa-edit"></i> Chỉnh sửa: ' + (rule.display_name || rule.ten_tham_so);
    document.getElementById('rule_val').focus();
}

// Mặc định chọn dòng đầu tiên để sửa
document.addEventListener("DOMContentLoaded", function() {
    <?php if (!empty($rules)): ?>
        editRule(<?= json_encode(array_merge($rules[0], ['display_name' => $paramLabels[$rules[0]['ten_tham_so']] ?? $rules[0]['ten_tham_so']])) ?>);
    <?php endif; ?>
});
</script>
