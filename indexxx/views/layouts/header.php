<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>Hệ Thống Quản Lý Gara Ô Tô</title>
    
    <!-- Chart.js & Font Awesome (Có thuộc tính SRI & Crossorigin bảo vệ CWE-345) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.css" integrity="sha512-/zs32ZEJh+/EO2N1b0PEdoA10JkdC3zJ8L5FTiQu82LR9S/rFi568MT024Ffc+g408N9dB0fObgkKjzddN51g==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="assets/fontawesomefree/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" crossorigin="anonymous">
    
    <!-- App CSS -->
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<?php
$sidebarCookie = $_COOKIE['sidebar_state'] ?? 'expanded';
$bodySidebarClass = ($sidebarCookie === 'collapsed') ? '' : 'sidebar-expand';
?>
<body class="overlay-scrollbar light <?= $bodySidebarClass ?>">
    <!-- Lớp phủ mờ (Backdrop) khi mở menu trên điện thoại -->
    <div id="sidebar-backdrop" class="sidebar-backdrop" onclick="collapseSidebar()"></div>

    <!-- Navbar Top -->
    <div class="navbar">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="javascript:void(0);" onclick="collapseSidebar()" id="sidebar-toggle-btn" title="Bấm để Ghim hoặc Thu gọn Menu (Đứng im)">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php" style="color: #2ed573; font-weight: 700; font-size: 1.15rem; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-car-side" style="font-size: 1.5rem;"></i>
                    <span>GARAGE PRO</span>
                </a>
            </li>
        </ul>

        <ul class="navbar-nav nav-right">
            <!-- Đổi giao diện sáng/tối -->
            <li class="nav-item mode">
                <a class="nav-link" href="javascript:void(0);" onclick="switchTheme()" title="Chuyển chế độ Sáng / Tối">
                    <i class="fas fa-moon dark-icon"></i>
                    <i class="fas fa-sun light-icon"></i>
                </a>
            </li>

            <!-- Avatar & Dropdown -->
            <li class="nav-item avt-wrapper" style="position: relative;">
                <div class="avt dropdown">
                    <div class="dropdown-toggle" data-toggle="user-menu" onclick="toggleUserDropdown(event)" style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 8px 12px; border-radius: 8px; transition: background 0.2s;">
                        <span style="font-size: 0.92rem; font-weight: 600;">
                            <?= htmlspecialchars($user['ho_ten'] ?? 'Tài khoản') ?>
                            <span class="badge badge-primary" style="margin-left: 4px;"><?= htmlspecialchars($user['vai_tro'] ?? 'Khách') ?></span>
                        </span>
                        <i class="fas fa-user-circle" style="font-size: 1.9rem; color: #4834d4;"></i>
                        <i class="fas fa-chevron-down" style="font-size: 0.75rem; color: #718096; margin-left: 2px;"></i>
                    </div>
                    <ul id="user-menu" class="dropdown-menu" style="min-width: 210px; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border: 1px solid #e2e8f0; padding: 6px;">
                        <li class="dropdown-menu-item" style="padding: 10px 14px; border-bottom: 1px solid #edf2f7; margin-bottom: 4px;">
                            <div style="font-weight: 700; color: #2d3748; font-size: 0.95rem;"><?= htmlspecialchars($user['ho_ten'] ?? 'Tài khoản') ?></div>
                            <div style="font-size: 0.8rem; color: #718096;"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                        </li>
                        <li class="dropdown-menu-item">
                            <a href="index.php?c=auth&a=profile" class="dropdown-menu-link" style="padding: 10px 14px; border-radius: 6px; display: flex; align-items: center; gap: 10px; font-weight: 600; color: #4a5568;">
                                <div style="color: #4834d4; width: 20px;"><i class="fas fa-id-card"></i></div>
                                <span>Trang cá nhân</span>
                            </a>
                        </li>
                        <li class="dropdown-menu-item">
                            <a href="index.php?c=auth&a=logout" class="dropdown-menu-link" style="padding: 10px 14px; border-radius: 6px; display: flex; align-items: center; gap: 10px; background: #fff5f5; color: #e53e3e; font-weight: 700; margin-top: 4px;">
                                <div style="color: #e53e3e; width: 20px;"><i class="fas fa-sign-out-alt"></i></div>
                                <span>Đăng xuất</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Nút Đăng xuất trực tiếp 1-click trên thanh điều hướng -->
            <li class="nav-item" style="display: flex; align-items: center; margin-left: 6px;">
                <a href="index.php?c=auth&a=logout" style="background: #fee2e2; color: #b91c1c; border: 1.5px solid #fca5a5; padding: 7px 16px; border-radius: 20px; font-weight: 700; font-size: 0.85rem; text-decoration: none; display: flex; align-items: center; gap: 6px; transition: all 0.2s;" onmouseover="this.style.background='#ef4444'; this.style.color='#fff';" onmouseout="this.style.background='#fee2e2'; this.style.color='#b91c1c';" title="Đăng xuất khỏi hệ thống">
                    <i class="fas fa-sign-out-alt"></i> <span>Đăng xuất</span>
                </a>
            </li>
        </ul>
    </div>

    <script>
        function toggleUserDropdown(e) {
            e.stopPropagation();
            const menu = document.getElementById('user-menu');
            if (menu) {
                menu.classList.toggle('dropdown-expand');
            }
        }
    </script>

