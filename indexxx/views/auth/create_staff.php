<div class="row">
    <div class="col-12 col-m-5">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-user-plus"></i> Tạo tài khoản nhân viên mới</h3>
            </div>
            <div class="card-content">
                <form method="POST" action="index.php?c=auth&a=storeStaff">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>Họ và tên nhân viên: <span style="color:red">*</span></label>
                        <input type="text" name="ho_ten" class="form-control" placeholder="Nguyễn Văn Kỹ Thuật" required>
                    </div>

                    <div class="form-group">
                        <label>Số điện thoại: <span style="color:red">*</span></label>
                        <input type="text" name="sdt" class="form-control" placeholder="0901234567" required>
                    </div>

                    <div class="form-group">
                        <label>Email đăng nhập: <span style="color:red">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="nv_kythuat@gara.vn" required>
                    </div>

                    <div class="form-group">
                        <label>Mật khẩu ban đầu: <span style="color:red">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="123456" required>
                    </div>

                    <div class="form-group">
                        <label>Phân quyền vai trò: <span style="color:red">*</span></label>
                        <select name="vai_tro" class="form-control" required>
                            <option value="TiepNhan">Tiếp nhận & Thu ngân (TiepNhan)</option>
                            <option value="KyThuat">Kỹ thuật viên / Thợ máy (KyThuat)</option>
                            <option value="ThuKho">Thủ kho phụ tùng (ThuKho)</option>
                            <option value="QuanLy">Quản lý điều hành (QuanLy)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Chức vụ:</label>
                        <input type="text" name="chuc_vu" class="form-control" placeholder="Kỹ thuật viên máy gầm">
                    </div>

                    <div class="form-group">
                        <label>Lương cơ bản (VNĐ):</label>
                        <input type="number" name="luong_co_ban" class="form-control" value="10000000" step="500000">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-plus-circle"></i> Tạo tài khoản nhân viên
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-m-7">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-users-cog"></i> Danh sách nhân viên gara</h3>
            </div>
            <div class="card-content">
                <div class="table-responsive">
                    <table class="table-custom table-hover">
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Họ tên</th>
                                <th>Email / SĐT</th>
                                <th>Vai trò</th>
                                <th>Chức vụ</th>
                                <th>Lương</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($staffList)): ?>
                                <tr><td colspan="6" style="text-align: center;">Chưa có dữ liệu nhân viên</td></tr>
                            <?php else: ?>
                                <?php foreach ($staffList as $st): ?>
                                    <tr>
                                        <td>#<?= $st['ma_nv'] ?></td>
                                        <td><b><?= htmlspecialchars($st['ho_ten']) ?></b></td>
                                        <td>
                                            <?= htmlspecialchars($st['email']) ?><br>
                                            <small><?= htmlspecialchars($st['sdt']) ?></small>
                                        </td>
                                        <td>
                                            <?php
                                                $badgeClass = 'badge-primary';
                                                if ($st['vai_tro'] === 'QuanLy') $badgeClass = 'badge-danger';
                                                if ($st['vai_tro'] === 'TiepNhan') $badgeClass = 'badge-info';
                                                if ($st['vai_tro'] === 'KyThuat') $badgeClass = 'badge-warning';
                                                if ($st['vai_tro'] === 'ThuKho') $badgeClass = 'badge-secondary';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= $st['vai_tro'] ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($st['chuc_vu']) ?></td>
                                        <td><?= number_format((float)$st['luong_co_ban']) ?> đ</td>
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

