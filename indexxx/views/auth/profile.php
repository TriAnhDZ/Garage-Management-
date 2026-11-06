<div class="row">
    <div class="col-12 col-m-6">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-id-card"></i> Thông tin cá nhân</h3>
            </div>
            <div class="card-content">
                <form method="POST" action="index.php?c=auth&a=updateProfile">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>Họ và tên:</label>
                        <input type="text" name="ho_ten" class="form-control" value="<?= htmlspecialchars($user['ho_ten']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại:</label>
                        <input type="text" name="sdt" class="form-control" value="<?= htmlspecialchars($user['sdt']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Vai trò:</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['vai_tro']) ?>" readonly style="background-color: rgba(0,0,0,0.05);">
                    </div>
                    <?php if (isset($user['dia_chi'])): ?>
                    <div class="form-group">
                        <label>Địa chỉ liên hệ:</label>
                        <input type="text" name="dia_chi" class="form-control" value="<?= htmlspecialchars($user['dia_chi'] ?? '') ?>">
                    </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Cập nhật thông tin
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-m-6">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-key"></i> Đổi mật khẩu</h3>
            </div>
            <div class="card-content">
                <form method="POST" action="index.php?c=auth&a=changePassword">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>Mật khẩu hiện tại:</label>
                        <input type="password" name="current_password" class="form-control" required placeholder="Nhập mật khẩu cũ...">
                    </div>
                    <div class="form-group">
                        <label>Mật khẩu mới:</label>
                        <input type="password" name="new_password" class="form-control" required placeholder="Nhập mật khẩu mới...">
                    </div>
                    <div class="form-group">
                        <label>Xác nhận mật khẩu mới:</label>
                        <input type="password" name="confirm_password" class="form-control" required placeholder="Nhập lại mật khẩu mới...">
                    </div>

                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-sync-alt"></i> Xác nhận đổi mật khẩu
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

