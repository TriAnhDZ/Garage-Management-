<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-envelope-open-text" style="color: #ea4335;"></i>
                        <span>Hộp thư Gmail gửi khách hàng (Email Logs)</span>
                    </h3>
                    <small style="color: #747d8c;">
                        Nhật ký các email thông báo, hóa đơn điện tử và xác nhận lịch hẹn đã gửi hoặc mở qua Google Gmail
                    </small>
                </div>

                <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                    <!-- Lọc theo loại email -->
                    <div style="display: flex; gap: 6px;">
                        <a href="index.php?c=email&a=index" class="btn btn-sm <?= empty($currentType) ? 'btn-primary' : 'btn-secondary' ?>">Tất cả</a>
                        <a href="index.php?c=email&a=index&type=HoaDon" class="btn btn-sm <?= ($currentType === 'HoaDon') ? 'btn-primary' : 'btn-secondary' ?>">Hóa đơn thu tiền</a>
                        <a href="index.php?c=email&a=index&type=HoanThanhXe" class="btn btn-sm <?= ($currentType === 'HoanThanhXe') ? 'btn-primary' : 'btn-secondary' ?>">Thông báo nhận xe</a>
                        <a href="index.php?c=email&a=index&type=LichHen" class="btn btn-sm <?= ($currentType === 'LichHen') ? 'btn-primary' : 'btn-secondary' ?>">Xác nhận lịch hẹn</a>
                    </div>

                    <!-- Nút cấu hình SMTP -->
                    <a href="index.php?c=email&a=config" class="btn btn-sm btn-outline-primary" style="background: #ffffff; color: #4834d4; border: 1px solid #4834d4;">
                        <i class="fas fa-cog"></i> Cấu hình Gmail SMTP
                    </a>
                </div>
            </div>

            <div class="card-content">
                <div class="table-responsive">
                    <table class="table-custom table-hover">
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Khách hàng nhận</th>
                                <th>Địa chỉ Gmail</th>
                                <th>Tiêu đề Email</th>
                                <th>Phân loại</th>
                                <th>Thời gian</th>
                                <th>Trạng thái</th>
                                <th style="min-width: 220px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($emails)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 30px;">
                                        Chưa có email nào trong nhật ký. Bạn có thể bấm <b>Gửi Gmail</b> hoặc <b>Mở Gmail Web</b> ở trang Hóa đơn hoặc Lịch hẹn!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($emails as $em): ?>
                                    <tr>
                                        <td>#<?= $em['ma_email'] ?></td>
                                        <td><b><?= htmlspecialchars($em['ten_nguoi_nhan']) ?></b></td>
                                        <td><span style="color: #4834d4; font-weight: 500;"><?= htmlspecialchars($em['email_nguoi_nhan']) ?></span></td>
                                        <td><b><?= htmlspecialchars($em['tieu_de']) ?></b></td>
                                        <td>
                                            <?php
                                                $badge = 'badge-secondary';
                                                if ($em['loai_email'] === 'HoaDon') $badge = 'badge-success';
                                                if ($em['loai_email'] === 'HoanThanhXe') $badge = 'badge-primary';
                                                if ($em['loai_email'] === 'LichHen') $badge = 'badge-warning';
                                            ?>
                                            <span class="badge <?= $badge ?>"><?= $em['loai_email'] ?></span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($em['ngay_gui'])) ?></td>
                                        <td>
                                            <?php if ($em['trang_thai'] === 'MoGmailWeb'): ?>
                                                <span class="badge badge-info" style="background: #e8f0fe; color: #1a73e8; border: 1px solid #d2e3fc;">
                                                    <i class="fab fa-google"></i> Mở Gmail Web
                                                </span>
                                            <?php elseif ($em['trang_thai'] === 'DaGui'): ?>
                                                <span class="badge badge-success"><i class="fas fa-check"></i> Đã gửi</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Thất bại</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="white-space: nowrap;">
                                            <!-- Mở Gmail Web trực tiếp -->
                                            <a href="index.php?c=email&a=openWeb&type=log&id=<?= $em['ma_email'] ?>" target="_blank" class="btn btn-sm btn-danger" title="Mở trực tiếp trên web Gmail (mail.google.com) với nội dung soạn sẵn">
                                                <i class="fab fa-google"></i> Mở Gmail Web
                                            </a>
                                            <!-- Xem preview giả lập -->
                                            <a href="index.php?c=email&a=preview&id=<?= $em['ma_email'] ?>" target="_blank" class="btn btn-sm btn-info" title="Xem giao diện hộp thư">
                                                <i class="fas fa-eye"></i> Xem thư
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
