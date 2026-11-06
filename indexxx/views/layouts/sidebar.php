<?php
$role = $user['vai_tro'] ?? '';
$isAdmin = ($role === 'QuanLy');
$isStaff = in_array($role, ['NhanVien', 'TiepNhan', 'KyThuat', 'ThuKho', 'QuanLy'], true);
$isCustomer = ($role === 'KhachHang');
$currentC = $_GET['c'] ?? 'home';
$currentA = $_GET['a'] ?? 'index';
?>
<!-- Sidebar Navigation -->
<div class="sidebar" id="app-sidebar">
    <!-- Thanh điều khiển ghim / thu gọn menu -->
    <div class="sidebar-pin-banner" onclick="collapseSidebar()" title="Bấm để Ghim hoặc Thu gọn Menu">
        <div class="sidebar-pin-label">
            <i class="fas fa-bars"></i>
            <span>DANH MỤC HỆ THỐNG</span>
        </div>
        <div class="sidebar-pin-icon" title="Trạng thái Ghim / Thu gọn">
            <i class="fas fa-thumbtack"></i>
        </div>
    </div>

    <ul class="sidebar-nav">
        <!-- 1. Trang chủ -->
        <li class="sidebar-nav-item <?= ($currentC === 'home') ? 'active' : '' ?>">
            <a href="index.php?c=home&a=index" class="sidebar-nav-link" data-title="Trang chủ" title="Trang chủ">
                <div><i class="fas fa-tachometer-alt"></i></div>
                <span>Trang chủ</span>
            </a>
        </li>

        <?php if ($isCustomer): ?>
        <!-- Menu dành riêng cho Khách hàng (Đơn giản, gọn gàng) -->
        <li class="sidebar-nav-item <?= ($currentC === 'search') ? 'active' : '' ?>">
            <a href="index.php?c=search&a=index" class="sidebar-nav-link" data-title="Xe của tôi" title="Xe của tôi">
                <div><i class="fas fa-car"></i></div>
                <span>Xe của tôi</span>
            </a>
        </li>
        <li class="sidebar-nav-item <?= ($currentC === 'appointment' && $currentA !== 'create') ? 'active' : '' ?>">
            <a href="index.php?c=appointment&a=index" class="sidebar-nav-link" data-title="Lịch hẹn của tôi" title="Lịch hẹn của tôi">
                <div><i class="fas fa-calendar-check"></i></div>
                <span>Lịch hẹn của tôi</span>
            </a>
        </li>
        <li class="sidebar-nav-item <?= ($currentC === 'appointment' && $currentA === 'create') ? 'active' : '' ?>">
            <a href="index.php?c=appointment&a=create" class="sidebar-nav-link" data-title="Đặt lịch hẹn mới" title="Đặt lịch hẹn mới">
                <div><i class="fas fa-calendar-plus"></i></div>
                <span>Đặt lịch hẹn mới</span>
            </a>
        </li>
        <?php else: ?>
        <!-- 2. Tra cứu xe (Dành cho Quản lý & Nhân viên) -->
        <li class="sidebar-nav-item <?= ($currentC === 'search') ? 'active' : '' ?>">
            <a href="index.php?c=search&a=index" class="sidebar-nav-link" data-title="Tra cứu xe" title="Tra cứu xe">
                <div><i class="fas fa-search"></i></div>
                <span>Tra cứu xe</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if ($isStaff): ?>
        <?php
            // Đếm nhanh số lịch hẹn chờ KTV duyệt để hiển thị huy hiệu đỏ/cam
            if (!isset($pendingApprovalCountGlobal)) {
                try {
                    $dbSidebar = Database::getConnection();
                    $pendingApprovalCountGlobal = (int)$dbSidebar->query("SELECT COUNT(*) FROM lich_hen WHERE trang_thai = 'ChoXacNhan'")->fetchColumn();
                } catch (Exception $e) {
                    $pendingApprovalCountGlobal = 0;
                }
            }
        ?>
        <!-- 2b. Duyệt xe chờ sửa (Dành riêng cho Kỹ thuật viên & Tiếp nhận) -->
        <li class="sidebar-nav-item <?= ($currentC === 'appointment' && ($currentA === 'approval' || ($currentA === 'index' && ($_GET['status'] ?? '') === 'ChoXacNhan'))) ? 'active' : '' ?>">
            <a href="index.php?c=appointment&a=approval" class="sidebar-nav-link" data-title="Duyệt xe chờ sửa" title="Duyệt xe chờ sửa & Tiếp nhận (KTV)">
                <div><i class="fas fa-clipboard-check" style="color: #f39c12;"></i></div>
                <span>Duyệt xe chờ sửa</span>
                <?php if ($pendingApprovalCountGlobal > 0): ?>
                    <span class="badge" style="margin-left: auto; font-size: 0.72rem; padding: 2px 7px; border-radius: 10px; font-weight: 700; background: #e67e22; color: #ffffff;">
                        <?= $pendingApprovalCountGlobal ?>
                    </span>
                <?php endif; ?>
            </a>
        </li>

        <!-- 3. Tiếp nhận xe -->
        <li class="sidebar-nav-item <?= ($currentC === 'reception') ? 'active' : '' ?>">
            <a href="index.php?c=reception&a=index" class="sidebar-nav-link" data-title="Tiếp nhận xe" title="Tiếp nhận xe">
                <div><i class="fas fa-car-side"></i></div>
                <span>Tiếp nhận xe</span>
            </a>
        </li>

        <!-- 4. Lập phiếu sửa chữa -->
        <li class="sidebar-nav-item <?= ($currentC === 'repair') ? 'active' : '' ?>">
            <a href="index.php?c=repair&a=index" class="sidebar-nav-link" data-title="Phiếu sửa chữa" title="Phiếu sửa chữa">
                <div><i class="fas fa-tools"></i></div>
                <span>Phiếu sửa chữa</span>
            </a>
        </li>

        <!-- 5. Lập phiếu thu tiền -->
        <li class="sidebar-nav-item <?= ($currentC === 'invoice') ? 'active' : '' ?>">
            <a href="index.php?c=invoice&a=index" class="sidebar-nav-link" data-title="Phiếu thu tiền" title="Phiếu thu tiền">
                <div><i class="fas fa-file-invoice-dollar"></i></div>
                <span>Phiếu thu tiền</span>
            </a>
        </li>

        <!-- 6. Quản lý Kho & Vật tư phụ tùng (Gộp tối ưu thống nhất) -->
        <li class="sidebar-nav-item <?= in_array($currentC, ['part', 'warehouse'], true) ? 'active' : '' ?>">
            <a href="index.php?c=part&a=index" class="sidebar-nav-link" data-title="Kho & Phụ tùng" title="Kho & Vật tư phụ tùng">
                <div><i class="fas fa-boxes"></i></div>
                <span>Kho & Phụ tùng</span>
            </a>
        </li>

        <!-- 7. Quản lý dịch vụ / Tiền công -->
        <li class="sidebar-nav-item <?= ($currentC === 'service') ? 'active' : '' ?>">
            <a href="index.php?c=service&a=index" class="sidebar-nav-link" data-title="Tiền công & Dịch vụ" title="Tiền công & Dịch vụ">
                <div><i class="fas fa-wrench"></i></div>
                <span>Tiền công & Dịch vụ</span>
            </a>
        </li>

        <!-- 8. Lịch hẹn khách hàng -->
        <li class="sidebar-nav-item <?= ($currentC === 'appointment') ? 'active' : '' ?>">
            <a href="index.php?c=appointment&a=index" class="sidebar-nav-link" data-title="Lịch hẹn sửa chữa" title="Lịch hẹn sửa chữa">
                <div><i class="fas fa-calendar-check"></i></div>
                <span>Lịch hẹn sửa chữa</span>
            </a>
        </li>

        <!-- 8b. Hộp thư Gmail gửi khách -->
        <li class="sidebar-nav-item <?= ($currentC === 'email') ? 'active' : '' ?>">
            <a href="index.php?c=email&a=index" class="sidebar-nav-link" data-title="Hộp thư gửi khách" title="Hộp thư gửi khách">
                <div><i class="fas fa-envelope-open-text"></i></div>
                <span>Hộp thư gửi khách</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if ($isAdmin): ?>
        <!-- 9. Cập nhật tham số quy định -->
        <li class="sidebar-nav-item <?= ($currentC === 'setting') ? 'active' : '' ?>">
            <a href="index.php?c=setting&a=index" class="sidebar-nav-link" data-title="Cập nhật tham số" title="Cập nhật tham số">
                <div><i class="fas fa-sliders-h"></i></div>
                <span>Cập nhật tham số</span>
            </a>
        </li>

        <!-- 9b. Cấu hình Gmail SMTP -->
        <li class="sidebar-nav-item <?= ($currentC === 'email' && $currentA === 'config') ? 'active' : '' ?>">
            <a href="index.php?c=email&a=config" class="sidebar-nav-link" data-title="Cấu hình Gmail SMTP" title="Cấu hình Gmail SMTP">
                <div><i class="fab fa-google"></i></div>
                <span>Cấu hình Gmail SMTP</span>
            </a>
        </li>

        <!-- 10. Báo cáo doanh số -->
        <li class="sidebar-nav-item <?= ($currentC === 'report' && $currentA === 'revenue') ? 'active' : '' ?>">
            <a href="index.php?c=report&a=revenue" class="sidebar-nav-link" data-title="Doanh thu khách hàng" title="Doanh thu khách hàng">
                <div><i class="fas fa-chart-line"></i></div>
                <span>Doanh thu khách hàng</span>
            </a>
        </li>

        <!-- 11. Báo cáo tồn kho -->
        <li class="sidebar-nav-item <?= ($currentC === 'report' && $currentA === 'inventory') ? 'active' : '' ?>">
            <a href="index.php?c=report&a=inventory" class="sidebar-nav-link" data-title="Báo cáo tồn kho" title="Báo cáo tồn kho">
                <div><i class="fas fa-warehouse"></i></div>
                <span>Báo cáo tồn kho</span>
            </a>
        </li>

        <!-- 12. Quản lý nhân viên -->
        <li class="sidebar-nav-item <?= ($currentC === 'auth' && $currentA === 'createStaff') ? 'active' : '' ?>">
            <a href="index.php?c=auth&a=createStaff" class="sidebar-nav-link" data-title="Tài khoản nhân viên" title="Tài khoản nhân viên">
                <div><i class="fas fa-user-shield"></i></div>
                <span>Tài khoản nhân viên</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>
