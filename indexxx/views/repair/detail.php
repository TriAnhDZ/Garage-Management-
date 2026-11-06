<div class="row">
    <!-- Header thông tin phiếu sửa chữa -->
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h2 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                        <span>Phiếu sửa chữa #<?= $psc['ma_psc'] ?></span>
                        <span class="badge badge-info" style="font-size: 1.1rem;"><?= htmlspecialchars($psc['bien_so']) ?></span>
                    </h2>
                    <small style="color: #747d8c;">
                        Khởi tạo lúc: <?= date('d/m/Y H:i', strtotime($psc['ngay_lap'])) ?> | Kỹ thuật viên phụ trách: <b><?= htmlspecialchars($psc['ten_nv'] ?? 'Chưa chỉ định') ?></b>
                    </small>
                </div>
                <div class="card-actions" style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <a href="index.php?c=repair&a=print&id=<?= $psc['ma_psc'] ?>" target="_blank" onclick="window.open(this.href, 'print_repair', 'width=1050,height=850,scrollbars=yes'); return false;" class="btn btn-secondary">
                        <i class="fas fa-print"></i> In phiếu
                    </a>
                    <a href="index.php?c=repair&a=downloadPdf&id=<?= $psc['ma_psc'] ?>" class="btn btn-primary">
                        <i class="fas fa-file-pdf"></i> Tải PDF
                    </a>
                    <?php if ($psc['trang_thai'] === 'HoanThanh' || $psc['trang_thai'] === 'DaThanhToan'): ?>
                        <a href="index.php?c=email&a=sendCompletion&id=<?= $psc['ma_psc'] ?>" class="btn btn-info" id="btnSendRepairEmail" title="Gửi email tự động qua hệ thống kèm file PDF phiếu sửa chữa" onclick="if(!confirm('Hệ thống sẽ tạo file PDF và tự động gửi Gmail thông báo tới khách hàng. Bạn có muốn gửi ngay?')) return false; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Đang gửi Gmail...'; this.style.pointerEvents='none'; this.style.opacity='0.7'; return true;">
                            <i class="fas fa-paper-plane"></i> Tự động gửi Gmail (Kèm PDF)
                        </a>
                        <a href="index.php?c=email&a=openWeb&type=repair&id=<?= $psc['ma_psc'] ?>" target="_blank" class="btn btn-danger" title="Mở trực tiếp trên web Gmail (mail.google.com) với nội dung soạn sẵn để báo khách nhận xe">
                            <i class="fab fa-google"></i> Mở Gmail Web
                        </a>
                    <?php endif; ?>
                    <?php if ($psc['tien_no'] > 0): ?>
                        <a href="index.php?c=invoice&a=create&bien_so=<?= urlencode($psc['bien_so']) ?>" class="btn btn-success">
                            <i class="fas fa-file-invoice-dollar"></i> Lập phiếu thu tiền
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-content">
                <div class="row">
                    <div class="col-12 col-m-4">
                        <p style="margin: 4px 0;"><b>Chủ xe:</b> <?= htmlspecialchars($psc['ten_chu_xe']) ?></p>
                        <p style="margin: 4px 0;"><b>Điện thoại:</b> <?= htmlspecialchars($psc['sdt_chu_xe']) ?></p>
                        <p style="margin: 4px 0;"><b>Địa chỉ:</b> <?= htmlspecialchars($psc['dia_chi'] ?? 'Chưa có') ?></p>
                    </div>
                    <div class="col-12 col-m-4">
                        <p style="margin: 4px 0;"><b>Hiệu xe:</b> <?= htmlspecialchars($psc['hang_xe']) ?> <?= htmlspecialchars($psc['dong_xe'] ?? '') ?></p>
                        <p style="margin: 4px 0;"><b>Năm sản xuất:</b> <?= htmlspecialchars($psc['nam_san_xuat'] ?? 'Không rõ') ?></p>
                        <p style="margin: 4px 0;"><b>Nợ hiện tại của xe:</b> <b style="color: #eb4d4b; font-size: 1.1rem;"><?= number_format((float)$psc['tien_no']) ?> đ</b></p>
                    </div>
                    <div class="col-12 col-m-4" style="background-color: var(--second-bg-color, #f8f9fa); padding: 15px; border-radius: 8px;">
                        <div style="font-size: 0.9rem; color: #747d8c;">Tổng chi phí phiếu sửa chữa:</div>
                        <div style="font-size: 1.8rem; font-weight: 700; color: #4834d4; margin-bottom: 10px;">
                            <?= number_format((float)$psc['tong_tien']) ?> đ
                        </div>
                        
                        <!-- Cập nhật trạng thái phiếu -->
                        <form method="POST" action="index.php?c=repair&a=updateStatus" style="display: flex; gap: 8px;" onsubmit="var b=this.querySelector('button[type=submit]'); if(b){b.innerHTML='<i class=\'fas fa-spinner fa-spin\'>
                    <?= csrf_field() ?></i> Lưu'; b.disabled=true;}">
                            <input type="hidden" name="ma_psc" value="<?= $psc['ma_psc'] ?>">
                            <select name="trang_thai" class="form-control" style="font-size: 0.85rem; padding: 4px 8px;">
                                <option value="TiepNhan" <?= ($psc['trang_thai'] === 'TiepNhan') ? 'selected' : '' ?>>1. Tiếp nhận</option>
                                <option value="DangKiemTra" <?= ($psc['trang_thai'] === 'DangKiemTra') ? 'selected' : '' ?>>2. Đang kiểm tra</option>
                                <option value="ChoKhachDuyet" <?= ($psc['trang_thai'] === 'ChoKhachDuyet') ? 'selected' : '' ?>>3. Chờ khách duyệt</option>
                                <option value="DangSuaChua" <?= ($psc['trang_thai'] === 'DangSuaChua') ? 'selected' : '' ?>>4. Đang sửa chữa</option>
                                <option value="HoanThanh" <?= ($psc['trang_thai'] === 'HoanThanh') ? 'selected' : '' ?>>5. Hoàn thành</option>
                                <option value="DaThanhToan" <?= ($psc['trang_thai'] === 'DaThanhToan') ? 'selected' : '' ?>>6. Đã thanh toán</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">Lưu</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Phần 1: Chi tiết dịch vụ & Tiền công -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-wrench"></i> 1. Chi tiết dịch vụ & tiền công sửa chữa</h3>
            </div>
            <div class="card-content">
                <!-- Form thêm dịch vụ -->
                <form method="POST" action="index.php?c=repair&a=addService" style="background: var(--second-bg-color, #f8f9fa); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="ma_psc" value="<?= $psc['ma_psc'] ?>">
                    <div class="row" style="align-items: flex-end;">
                        <div class="col-12 col-m-5">
                            <label style="font-weight: 600; font-size: 0.9rem;">
                                <i class="fas fa-wrench text-primary"></i> Dịch vụ / Tiền công: <span style="color:red">*</span>
                            </label>
                            <input type="text" 
                                   name="ten_dv" 
                                   id="input_service" 
                                   class="form-control" 
                                   list="service_list" 
                                   placeholder="Nhập tên dịch vụ..." 
                                   autocomplete="off" 
                                   required 
                                   oninput="onServiceInputChange(this.value)">
                            <datalist id="service_list">
                                <?php foreach ($allServices as $s): ?>
                                    <option value="<?= htmlspecialchars($s['ten_dv']) ?>">
                                        Giá chuẩn: <?= number_format((float)$s['gia_tieu_chuan']) ?> đ
                                    </option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-12 col-m-2">
                            <label style="font-weight: 600; font-size: 0.9rem;">Số lần / Lượng:</label>
                            <input type="number" name="so_luong" value="1" min="1" class="form-control" required>
                        </div>
                        <div class="col-12 col-m-3">
                            <label style="font-weight: 600; font-size: 0.9rem;">Đơn giá tiền công (đ):</label>
                            <input type="number" name="don_gia" id="service_price" class="form-control" placeholder="Tự động hoặc tự nhập...">
                        </div>
                        <div class="col-12 col-m-2">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-plus"></i> Thêm dịch vụ
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Bảng danh sách dịch vụ đã thêm -->
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Nội dung công việc / Dịch vụ</th>
                                <th>Số lần</th>
                                <th>Đơn giá</th>
                                <th>Thành tiền</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($services)): ?>
                                <tr><td colspan="6" style="text-align: center; color: #747d8c;">Chưa có dịch vụ nào được ghi nhận cho phiếu này.</td></tr>
                            <?php else: ?>
                                <?php $stt = 1; $totalService = 0; ?>
                                <?php foreach ($services as $sv): ?>
                                    <?php $totalService += (float)$sv['thanh_tien']; ?>
                                    <tr>
                                        <td><?= $stt++ ?></td>
                                        <td><b><?= htmlspecialchars($sv['ten_dv']) ?></b></td>
                                        <td><?= $sv['so_luong'] ?></td>
                                        <td><?= number_format((float)$sv['don_gia']) ?> đ</td>
                                        <td style="font-weight: 600; color: #4834d4;"><?= number_format((float)$sv['thanh_tien']) ?> đ</td>
                                        <td>
                                            <a href="index.php?c=repair&a=deleteService&id=<?= $sv['ma_ctdv'] ?>" 
                                               onclick="return confirm('Bạn có chắc chắn muốn xóa dịch vụ này khỏi phiếu?')"
                                               class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Xóa
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr style="background: var(--second-bg-color, #f1f2f6); font-weight: 700;">
                                    <td colspan="4" style="text-align: right;">Tổng tiền công dịch vụ:</td>
                                    <td colspan="2" style="color: #4834d4; font-size: 1.05rem;"><?= number_format($totalService) ?> đ</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Phần 2: Chi tiết vật tư phụ tùng thay thế -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-cogs"></i> 2. Chi tiết vật tư phụ tùng thay thế</h3>
            </div>
            <div class="card-content">
                <!-- Form thêm phụ tùng -->
                <form method="POST" action="index.php?c=repair&a=addPart" style="background: var(--second-bg-color, #f8f9fa); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="ma_psc" value="<?= $psc['ma_psc'] ?>">
                    <div class="row" style="align-items: flex-end;">
                        <div class="col-12 col-m-5">
                            <label style="font-weight: 600; font-size: 0.9rem;">
                                <i class="fas fa-boxes text-success"></i> Phụ tùng thay thế: <span style="color:red">*</span>
                            </label>
                            <input type="text" 
                                   name="ten_pt" 
                                   id="input_part" 
                                   class="form-control" 
                                   list="part_list" 
                                   placeholder="Nhập tên phụ tùng..." 
                                   autocomplete="off" 
                                   required 
                                   oninput="onPartInputChange(this.value)">
                            <datalist id="part_list">
                                <?php foreach ($allParts as $p): ?>
                                    <option value="<?= htmlspecialchars($p['ten_pt']) ?>">
                                        Kho: <?= $p['so_luong_ton'] ?> <?= $p['don_vi_tinh'] ?> | Giá bán: <?= number_format((float)$p['gia_ban']) ?> đ
                                    </option>
                                <?php endforeach; ?>
                            </datalist>
                            <small id="part_stock_hint" style="font-size: 0.8rem; margin-top: 3px; display: block;"></small>
                        </div>
                        <div class="col-12 col-m-2">
                            <label style="font-weight: 600; font-size: 0.9rem;">Số lượng dùng:</label>
                            <input type="number" name="so_luong" value="1" min="1" class="form-control" required>
                        </div>
                        <div class="col-12 col-m-3">
                            <label style="font-weight: 600; font-size: 0.9rem;">Đơn giá bán phụ tùng (đ):</label>
                            <input type="number" name="don_gia" id="part_price" class="form-control" placeholder="Tự động theo phụ tùng">
                        </div>
                        <div class="col-12 col-m-2">
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-plus"></i> Xuất phụ tùng
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Bảng danh sách phụ tùng đã dùng -->
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tên phụ tùng</th>
                                <th>Đơn vị tính</th>
                                <th>Số lượng</th>
                                <th>Đơn giá</th>
                                <th>Thành tiền</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($parts)): ?>
                                <tr><td colspan="7" style="text-align: center; color: #747d8c;">Chưa có vật tư phụ tùng nào được xuất cho phiếu này.</td></tr>
                            <?php else: ?>
                                <?php $stt = 1; $totalParts = 0; ?>
                                <?php foreach ($parts as $pt): ?>
                                    <?php $totalParts += (float)$pt['thanh_tien']; ?>
                                    <tr>
                                        <td><?= $stt++ ?></td>
                                        <td><b><?= htmlspecialchars($pt['ten_pt']) ?></b></td>
                                        <td><?= htmlspecialchars($pt['don_vi_tinh']) ?></td>
                                        <td><?= $pt['so_luong'] ?></td>
                                        <td><?= number_format((float)$pt['don_gia']) ?> đ</td>
                                        <td style="font-weight: 600; color: #2ed573;"><?= number_format((float)$pt['thanh_tien']) ?> đ</td>
                                        <td>
                                            <a href="index.php?c=repair&a=deletePart&id=<?= $pt['ma_ctpt'] ?>" 
                                               onclick="return confirm('Xóa phụ tùng này sẽ hoàn trả số lượng về kho. Bạn có chắc chắn?')"
                                               class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Xóa & hoàn kho
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr style="background: var(--second-bg-color, #f1f2f6); font-weight: 700;">
                                    <td colspan="5" style="text-align: right;">Tổng tiền phụ tùng:</td>
                                    <td colspan="2" style="color: #2ed573; font-size: 1.05rem;"><?= number_format($totalParts) ?> đ</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Dữ liệu dịch vụ cho tính năng gợi ý & tự động điền đơn giá
const servicePriceMap = {
    <?php foreach ($allServices as $s): ?>
        <?= json_encode($s['ten_dv']) ?>: <?= (float)$s['gia_tieu_chuan'] ?>,
    <?php endforeach; ?>
};

function onServiceInputChange(val) {
    const trimmed = val.trim();
    const priceInput = document.getElementById('service_price');
    if (servicePriceMap[trimmed] !== undefined) {
        priceInput.value = servicePriceMap[trimmed];
    }
}

// Dữ liệu phụ tùng cho tính năng gợi ý & kiểm tra kho
const partDataMap = {
    <?php foreach ($allParts as $p): ?>
        <?= json_encode($p['ten_pt']) ?>: {
            id: <?= (int)$p['ma_pt'] ?>,
            price: <?= (float)$p['gia_ban'] ?>,
            stock: <?= (int)$p['so_luong_ton'] ?>,
            unit: <?= json_encode($p['don_vi_tinh']) ?>
        },
    <?php endforeach; ?>
};

function onPartInputChange(val) {
    const trimmed = val.trim();
    const priceInput = document.getElementById('part_price');
    const hint = document.getElementById('part_stock_hint');
    if (partDataMap[trimmed] !== undefined) {
        const item = partDataMap[trimmed];
        priceInput.value = item.price;
        hint.innerHTML = `<span style="color: #059669; font-weight: 600;"><i class="fas fa-check"></i> Kho còn: ${item.stock} ${item.unit} | Đơn giá: ${item.price.toLocaleString()} đ</span>`;
    } else {
        hint.innerHTML = '';
    }
}
</script>

