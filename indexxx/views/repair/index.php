<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <h3><i class="fas fa-tools"></i> Danh sách phiếu sửa chữa xe</h3>
                <div style="display: flex; gap: 10px;">
                    <a href="index.php?c=repair&a=create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Lập phiếu sửa mới
                    </a>
                </div>
            </div>

            <div class="card-content">
                <!-- Bộ lọc & Tìm kiếm -->
                <form method="GET" action="index.php" style="margin-bottom: 20px;">
                    <input type="hidden" name="c" value="repair">
                    <input type="hidden" name="a" value="index">
                    <div class="row" style="align-items: flex-end;">
                        <div class="col-12 col-m-4">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Tìm theo biển số, tên khách hoặc mã phiếu:</label>
                                <input type="text" name="keyword" class="form-control" value="<?= htmlspecialchars($keyword ?? '') ?>" placeholder="VD: 30A-123.45 hoặc Tuấn...">
                            </div>
                        </div>
                        <div class="col-12 col-m-4">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Trạng thái tiến độ:</label>
                                <select name="status" class="form-control">
                                    <option value="">-- Tất cả trạng thái --</option>
                                    <option value="TiepNhan" <?= ($currentStatus === 'TiepNhan') ? 'selected' : '' ?>>Tiếp nhận</option>
                                    <option value="DangKiemTra" <?= ($currentStatus === 'DangKiemTra') ? 'selected' : '' ?>>Đang kiểm tra</option>
                                    <option value="ChoKhachDuyet" <?= ($currentStatus === 'ChoKhachDuyet') ? 'selected' : '' ?>>Chờ khách duyệt</option>
                                    <option value="DangSuaChua" <?= ($currentStatus === 'DangSuaChua') ? 'selected' : '' ?>>Đang sửa chữa</option>
                                    <option value="HoanThanh" <?= ($currentStatus === 'HoanThanh') ? 'selected' : '' ?>>Hoàn thành</option>
                                    <option value="DaThanhToan" <?= ($currentStatus === 'DaThanhToan') ? 'selected' : '' ?>>Đã thanh toán</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-m-4">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-filter"></i> Lọc danh sách
                            </button>
                            <a href="index.php?c=repair&a=index" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Đặt lại
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Bảng danh sách phiếu sửa -->
                <div class="table-responsive">
                    <table class="table-custom table-hover">
                        <thead>
                            <tr>
                                <th>Mã phiếu</th>
                                <th>Biển số xe</th>
                                <th>Hiệu xe</th>
                                <th>Chủ xe / SĐT</th>
                                <th>Ngày lập</th>
                                <th>Tổng chi phí</th>
                                <th>Tiền nợ</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($repairs)): ?>
                                <tr><td colspan="9" style="text-align: center; padding: 25px;">Không có phiếu sửa chữa nào phù hợp tiêu chí tìm kiếm.</td></tr>
                            <?php else: ?>
                                <?php foreach ($repairs as $r): ?>
                                    <tr>
                                        <td><b>#<?= $r['ma_psc'] ?></b></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($r['bien_so']) ?></span></td>
                                        <td><?= htmlspecialchars($r['hang_xe']) ?> <?= htmlspecialchars($r['dong_xe'] ?? '') ?></td>
                                        <td>
                                            <b><?= htmlspecialchars($r['ten_chu_xe']) ?></b><br>
                                            <small><?= htmlspecialchars($r['sdt_chu_xe']) ?></small>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($r['ngay_lap'])) ?></td>
                                        <td style="font-weight: 700; color: #eb4d4b;"><?= number_format((float)$r['tong_tien']) ?> đ</td>
                                        <td style="font-weight: 700; color: #ffa502;"><?= number_format((float)$r['tien_no']) ?> đ</td>
                                        <td>
                                            <?php
                                                $badge = 'badge-secondary';
                                                if ($r['trang_thai'] === 'TiepNhan') $badge = 'badge-info';
                                                if ($r['trang_thai'] === 'DangSuaChua') $badge = 'badge-warning';
                                                if ($r['trang_thai'] === 'HoanThanh') $badge = 'badge-primary';
                                                if ($r['trang_thai'] === 'DaThanhToan') $badge = 'badge-success';
                                            ?>
                                            <span class="badge <?= $badge ?>"><?= $r['trang_thai'] ?></span>
                                        </td>
                                        <td>
                                            <a href="index.php?c=repair&a=detail&id=<?= $r['ma_psc'] ?>" class="btn btn-sm btn-primary" title="Xem chi tiết & Thêm linh kiện">
                                                <i class="fas fa-edit"></i> Chi tiết
                                            </a>
                                            <a href="index.php?c=repair&a=print&id=<?= $r['ma_psc'] ?>" target="_blank" onclick="window.open(this.href, 'print_repair', 'width=1050,height=850,scrollbars=yes'); return false;" class="btn btn-sm btn-secondary" title="In phiếu">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

