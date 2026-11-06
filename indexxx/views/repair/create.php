<div class="row">
    <div class="col-12 col-m-6" style="margin: 0 auto;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-file-signature"></i> Lập phiếu sửa chữa &amp; Thu tiền tích hợp</h3>
            </div>
            <div class="card-content">
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px 14px; margin-bottom: 18px; font-size: 0.88rem; color: #1e40af;">
                    <i class="fas fa-info-circle"></i> <b>Quy trình tích hợp 2 trong 1:</b> Sau khi khởi tạo phiếu, bạn có thể xuất vật tư, dịch vụ và thực hiện <b>thu tiền / xuất hóa đơn trực tiếp</b> ngay tại chỗ mà không cần chuyển qua trang phiếu thu riêng.
                </div>
                <form method="POST" action="index.php?c=repair&a=store">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="ma_xe">Chọn xe cần sửa chữa: <span style="color:red">*</span></label>
                        <select name="ma_xe" id="ma_xe" class="form-control" required>
                            <option value="">-- Chọn xe theo Biển số - Chủ xe --</option>
                            <?php foreach ($cars as $car): ?>
                                <option value="<?= $car['ma_xe'] ?>">
                                    <?= htmlspecialchars($car['bien_so']) ?> - <?= htmlspecialchars($car['hang_xe']) ?> (Chủ xe: <?= htmlspecialchars($car['ten_chu_xe']) ?> - <?= htmlspecialchars($car['sdt']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-top: 25px; display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Khởi tạo phiếu sửa chữa
                        </button>
                        <a href="index.php?c=reception&a=create" class="btn btn-info">
                            <i class="fas fa-plus"></i> Tiếp nhận xe mới chưa có trong hệ thống
                        </a>
                        <a href="index.php?c=repair&a=index" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

