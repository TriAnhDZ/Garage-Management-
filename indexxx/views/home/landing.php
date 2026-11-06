<?php
/**
 * GARA PRO - Cổng Giới Thiệu Dịch Vụ & Trang Khách Hàng Cao Cấp
 * Hệ thống Quản lý Dịch vụ Gara Ô Tô Chuyên Nghiệp
 */

$isCustomer = !empty($isCustomer);
$currentUser = $currentUser ?? null;
$myCars = $myCars ?? [];
$myAppointments = $myAppointments ?? [];
$myRepairs = $myRepairs ?? [];
$services = $services ?? [];
$parts = $parts ?? [];
$brands = $brands ?? [];
$lookupPlate = $lookupPlate ?? '';
$carProgress = $carProgress ?? null;

// Hình ảnh thực tế cho các dịch vụ sửa chữa
$serviceImages = [
    1 => 'assets/images/services/service_oil.jpg',    // Thay dầu động cơ & lọc dầu
    2 => 'assets/images/services/service_wheel.jpg',  // Cân chỉnh góc đặt bánh xe
    3 => 'assets/images/services/service_brake.jpg',  // Bảo dưỡng phanh 4 bánh
    4 => 'assets/images/services/service_ac.jpg',     // Vệ sinh dàn lạnh điều hòa bằng nội soi
    5 => 'assets/images/services/service_paint.jpg',  // Sơn dặm vá cản trước / sau
];

// Hình ảnh thực tế cho các vật tư phụ tùng linh kiện
$partImages = [
    1 => 'assets/images/parts/part_oil_castrol.jpg',    // Dầu nhớt Castrol Magnatec 5W-30
    2 => 'assets/images/parts/part_filter_toyota.jpg',  // Lọc nhớt Toyota Vios chính hãng
    3 => 'assets/images/parts/part_brake_mazda.jpg',    // Má phanh trước Ceramic Mazda CX-5
    4 => 'assets/images/parts/part_sparkplug_ngk.jpg',  // Bugi Iridium NGK Laser
    5 => 'assets/images/parts/part_tire_michelin.jpg',  // Lốp Michelin Primacy 4
    6 => 'assets/images/parts/part_battery_gs.jpg',     // Bình ắc quy GS 12V-60Ah
];

// Lập bản đồ phiếu sửa chữa mới nhất theo từng xe
$latestRepairsByCar = [];
foreach ($myRepairs as $r) {
    if (!isset($latestRepairsByCar[$r['ma_xe']])) {
        $latestRepairsByCar[$r['ma_xe']] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GARA PRO HÀ NỘI - Trung Tâm Dịch Vụ & Bảo Dưỡng Ô Tô Chuyên Nghiệp</title>
    
    <link rel="stylesheet" href="assets/fontawesomefree/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;0,900;1,400&display=swap" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    
    <style>
        :root {
            --primary-accent: #1e40af;
            --primary-hover: #1d4ed8;
            --secondary-accent: #10b981;
            --secondary-hover: #059669;
            --dark-navy: #0f172a;
            --navy-surface: #1e293b;
            --slate-gray: #475569;
            --light-bg: #eef2f6;
            --card-border: #cbd5e1;
        }
        
        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            max-width: 100vw;
            overflow-x: hidden;
            font-family: 'Roboto', 'Segoe UI', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
            background-color: var(--light-bg);
            color: #1e293b;
            line-height: 1.6;
        }

        input, button, select, textarea {
            font-family: 'Roboto', 'Segoe UI', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
        }

        /* Top Navigation Bar */
        .public-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 74px;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 36px;
            z-index: 1000;
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }

        .public-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: #ffffff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
        }

        .brand-text {
            display: flex;
            flex-direction: column;
        }

        .brand-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--dark-navy);
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .brand-subtitle {
            font-size: 0.72rem;
            color: var(--slate-gray);
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .public-menu {
            display: flex;
            align-items: center;
            gap: 20px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .public-menu a {
            text-decoration: none;
            color: #475569;
            font-weight: 600;
            font-size: 0.92rem;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .public-menu a:hover {
            color: var(--primary-accent);
            background: #f1f5f9;
        }

        .menu-badge {
            background: #ef4444;
            color: #ffffff;
            font-size: 0.72rem;
            padding: 2px 7px;
            border-radius: 10px;
            font-weight: 700;
        }

        /* User Header Actions */
        .auth-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .customer-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f1f5f9;
            padding: 6px 14px 6px 8px;
            border-radius: 30px;
            border: 1.5px solid #cbd5e1;
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
        }

        .customer-pill:hover {
            background: #e2e8f0;
            border-color: #94a3b8;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--primary-accent);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .user-name {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--dark-navy);
            max-width: 140px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .btn-logout-main {
            padding: 8px 18px;
            background: #fee2e2;
            color: #dc2626;
            border: 1.5px solid #f87171;
            border-radius: 24px;
            font-size: 0.88rem;
            font-weight: 800;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.15);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-logout-main:hover {
            background: #ef4444;
            color: #ffffff;
            border-color: #ef4444;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35);
        }

        /* Floating User Dropdown Menu */
        .landing-user-dropdown {
            display: none;
            position: absolute;
            top: 52px;
            right: 0;
            width: 250px;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
            border: 1px solid #e2e8f0;
            padding: 10px;
            z-index: 1050;
            animation: fadeInDown 0.2s ease;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dropdown-user-header {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 8px;
        }

        .dropdown-nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            color: #334155;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 600;
            transition: all 0.15s ease;
        }

        .dropdown-nav-item:hover {
            background: #f1f5f9;
            color: #1e40af;
        }

        .dropdown-nav-logout {
            margin-top: 6px;
            background: #fee2e2;
            color: #dc2626 !important;
            border: 1px solid #fca5a5;
            font-weight: 700;
        }

        .dropdown-nav-logout:hover {
            background: #ef4444 !important;
            color: #ffffff !important;
            border-color: #ef4444;
        }

        /* Hero Banner Section */
        .hero-section {
            margin-top: 74px;
            background: linear-gradient(135deg, #090d16 0%, #0f172a 45%, #1e293b 100%);
            color: #ffffff;
            padding: 75px 36px 95px;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 500px;
            height: 100%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.15) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-content {
            max-width: 1180px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(30, 64, 175, 0.35);
            border: 1px solid rgba(96, 165, 250, 0.3);
            color: #93c5fd;
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .hero-title {
            font-size: 2.7rem;
            font-weight: 800;
            line-height: 1.2;
            margin: 0 0 16px;
            letter-spacing: -0.8px;
        }

        .hero-title span {
            background: linear-gradient(90deg, #60a5fa 0%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-desc {
            font-size: 1.12rem;
            color: #94a3b8;
            max-width: 780px;
            margin: 0 0 35px;
            line-height: 1.65;
        }

        .hero-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn-hero {
            padding: 13px 28px;
            font-size: 0.98rem;
            font-weight: 700;
            border-radius: 30px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .btn-hero-primary {
            background: #10b981;
            color: #ffffff;
        }

        .btn-hero-primary:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }

        .btn-hero-outline {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(5px);
        }

        .btn-hero-outline:hover {
            background: #ffffff;
            color: var(--dark-navy);
            transform: translateY(-2px);
        }

        /* VIP Customer Dashboard Container */
        .vip-customer-container {
            max-width: 1200px;
            margin: -45px auto 40px;
            padding: 0 20px;
            position: relative;
            z-index: 10;
        }

        .vip-panel {
            background: #ffffff;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 12px 35px rgba(15, 23, 42, 0.08);
            border: 2px solid var(--card-border);
            margin-bottom: 35px;
        }

        .vip-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 18px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .vip-panel-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .vip-panel-title h3 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--dark-navy);
        }

        .vip-panel-title p {
            margin: 2px 0 0;
            color: var(--slate-gray);
            font-size: 0.88rem;
        }

        /* Summary Stat Chips */
        .stat-chips-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 18px;
            margin-bottom: 30px;
        }

        .stat-chip {
            background: #ffffff;
            border: 2px solid var(--card-border);
            border-radius: 12px;
            padding: 18px 22px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.03);
            transition: all 0.2s ease;
        }

        .stat-chip:hover {
            border-color: #94a3b8;
            box-shadow: 0 6px 18px rgba(0,0,0,0.06);
            transform: translateY(-2px);
        }

        .chip-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .chip-icon.blue { background: #eff6ff; color: #2563eb; }
        .chip-icon.amber { background: #fffbeb; color: #d97706; }
        .chip-icon.green { background: #ecfdf5; color: #059669; }
        .chip-icon.red { background: #fef2f2; color: #dc2626; }

        .chip-content h4 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--dark-navy);
        }

        .chip-content p {
            margin: 2px 0 0;
            font-size: 0.85rem;
            color: var(--slate-gray);
            font-weight: 600;
        }

        /* Vehicle Cards Grid */
        .cars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 24px;
        }

        .car-card {
            background: #ffffff;
            border: 2px solid var(--card-border);
            border-radius: 16px;
            padding: 24px;
            transition: all 0.25s ease;
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.06);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .car-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 30px rgba(37, 99, 235, 0.14);
            border-color: #2563eb;
        }

        /* License Plate Styling */
        .plate-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            border: 2px solid #0f172a;
            color: #0f172a;
            font-family: 'Roboto', 'Segoe UI', Arial, sans-serif;
            font-weight: 900;
            font-size: 1.15rem;
            letter-spacing: 1px;
            padding: 5px 15px;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .car-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .car-brand-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--dark-navy);
            margin: 6px 0 2px;
        }

        .car-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .car-status-pill.repairing { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .car-status-pill.finished { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .car-status-pill.idle { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }

        /* Khung thông tin xe dạng bảng tương phản cao */
        .car-meta-list {
            margin: 16px 0 20px;
            padding: 14px 18px;
            background: #f1f5f9;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            font-size: 0.9rem;
        }

        .car-meta-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 0;
            border-bottom: 1px solid #cbd5e1;
            color: #334155;
            font-weight: 600;
        }

        .car-meta-item:last-child {
            border-bottom: none;
        }

        .car-meta-item b {
            color: #0f172a;
            font-weight: 800;
        }

        .car-card-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .btn-card-action {
            flex: 1;
            min-width: 140px;
            padding: 10px 14px;
            font-size: 0.85rem;
            font-weight: 800;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1.5px solid transparent;
        }

        .btn-pdf-download {
            background: #1e40af;
            color: #ffffff !important;
            border-color: #1e40af;
            box-shadow: 0 2px 8px rgba(30, 64, 175, 0.25);
        }

        .btn-pdf-download:hover {
            background: #1d4ed8;
            border-color: #1d4ed8;
            transform: translateY(-1px);
        }

        .btn-card-print {
            background: #ffffff;
            color: #1e293b !important;
            border-color: #94a3b8;
        }

        .btn-card-print:hover {
            background: #f1f5f9;
            color: #0f172a !important;
            border-color: #64748b;
        }

        .btn-card-book {
            background: #059669;
            color: #ffffff !important;
            border-color: #059669;
            box-shadow: 0 2px 8px rgba(5, 150, 105, 0.25);
        }

        .btn-card-book:hover {
            background: #047857;
            border-color: #047857;
            transform: translateY(-1px);
        }

        /* Bảng dữ liệu tương phản cao (High Contrast Table) */
        .table-responsive {
            display: block;
            width: 100%;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            box-shadow: 0 6px 22px rgba(15, 23, 42, 0.08);
            margin-bottom: 20px;
        }

        .table-custom {
            width: 100%;
            min-width: 580px;
            border-collapse: collapse;
            margin-bottom: 0;
            background: #ffffff;
        }

        .table-custom thead th {
            background: #0f172a !important;
            color: #ffffff !important;
            font-weight: 800;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 14px 18px;
            border: none;
            white-space: nowrap;
        }

        .table-custom tbody tr {
            border-bottom: 1.5px solid #e2e8f0;
            transition: background-color 0.15s ease;
        }

        .table-custom tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .table-custom tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .table-custom tbody tr:hover {
            background-color: #e0e7ff !important;
        }

        .table-custom tbody td {
            padding: 13px 18px;
            color: #0f172a;
            font-size: 0.92rem;
            font-weight: 600;
            vertical-align: middle;
        }

        /* Generic Section Headers */
        .landing-section {
            padding: 70px 24px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-badge {
            display: inline-block;
            background: #e0e7ff;
            color: #3730a3;
            font-weight: 700;
            font-size: 0.8rem;
            padding: 5px 14px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .section-header h2 {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--dark-navy);
            margin: 0 0 12px;
            letter-spacing: -0.5px;
        }

        .section-header p {
            color: var(--slate-gray);
            margin: 0 auto;
            font-size: 1.05rem;
            max-width: 700px;
        }

        /* Facilities & Showcase Cards */
        .showcase-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
            gap: 25px;
        }

        .showcase-card {
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 28px 24px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        }

        .showcase-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }

        .showcase-icon {
            width: 58px;
            height: 58px;
            border-radius: 14px;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: var(--primary-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 20px;
        }

        .showcase-title {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--dark-navy);
            margin: 0 0 10px;
        }

        .showcase-desc {
            color: #64748b;
            font-size: 0.92rem;
            line-height: 1.6;
            margin: 0;
        }

        /* 4 Golden Guarantees */
        .guarantees-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-radius: 20px;
            padding: 45px 36px;
            color: #ffffff;
            margin-top: 50px;
            border: 1px solid #334155;
        }

        .guarantees-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .guarantee-item {
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        .guarantee-icon {
            font-size: 1.6rem;
            color: #34d399;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .guarantee-text h4 {
            margin: 0 0 6px;
            font-size: 1.05rem;
            font-weight: 700;
            color: #f8fafc;
        }

        .guarantee-text p {
            margin: 0;
            color: #94a3b8;
            font-size: 0.88rem;
            line-height: 1.5;
        }

        /* Services Grid & Image Cards */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
        }

        .service-card {
            background: #ffffff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            border: 1px solid var(--card-border);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 14px 30px rgba(0,0,0,0.1);
        }

        .service-card:hover .service-img-wrapper img {
            transform: scale(1.08);
        }

        .service-img-wrapper {
            position: relative;
            height: 180px;
            overflow: hidden;
            background: #0f172a;
        }

        .service-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .service-img-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(15, 23, 42, 0.78);
            color: #38bdf8;
            backdrop-filter: blur(4px);
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            border: 1px solid rgba(56, 189, 248, 0.3);
        }

        .service-body {
            padding: 22px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            justify-content: space-between;
        }

        .service-icon {
            font-size: 1.4rem;
            color: var(--primary-accent);
            margin-bottom: 8px;
            display: inline-block;
        }

        .service-card h3 {
            margin: 0 0 8px;
            font-size: 1.15rem;
            color: var(--dark-navy);
            font-weight: 700;
        }

        .service-desc {
            color: var(--slate-gray);
            font-size: 0.88rem;
            line-height: 1.55;
            margin: 0 0 15px;
            flex-grow: 1;
        }

        .service-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px dashed #e2e8f0;
            padding-top: 14px;
            margin-top: 10px;
        }

        .service-price {
            font-size: 1.25rem;
            font-weight: 800;
            color: #059669;
        }

        /* Lưới linh kiện phụ tùng có hình ảnh (Parts Showcase) */
        .parts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 22px;
            margin-bottom: 30px;
        }

        .part-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid var(--card-border);
            padding: 18px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            position: relative;
        }

        .part-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.08);
            border-color: #93c5fd;
        }

        .part-card:hover .part-img-container img {
            transform: scale(1.06);
        }

        .part-img-container {
            width: 100%;
            height: 160px;
            border-radius: 10px;
            overflow: hidden;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            position: relative;
        }

        .part-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .part-brand-tag {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(255, 255, 255, 0.92);
            color: #1e3a8a;
            font-size: 0.72rem;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .part-card-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 6px;
            line-height: 1.4;
            min-height: 44px;
        }

        .part-card-meta {
            font-size: 0.82rem;
            color: #64748b;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .part-card-price {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1e40af;
            margin-bottom: 12px;
        }

        /* Workshop Photo Gallery */
        .workshop-gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }

        .workshop-photo-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            background: #ffffff;
            border: 1px solid var(--card-border);
            transition: all 0.3s ease;
        }

        .workshop-photo-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }

        .workshop-photo-card:hover img {
            transform: scale(1.05);
        }

        .workshop-photo-card img {
            width: 100%;
            height: 190px;
            object-fit: cover;
            display: block;
            transition: transform 0.4s ease;
        }

        .workshop-photo-caption {
            padding: 14px 16px;
        }

        .workshop-photo-caption h4 {
            margin: 0 0 4px;
            font-size: 0.98rem;
            font-weight: 700;
            color: #0f172a;
        }

        .workshop-photo-caption p {
            margin: 0;
            font-size: 0.82rem;
            color: #64748b;
            line-height: 1.45;
        }

        /* 5-Step Standardized Workflow */
        .workflow-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .workflow-step {
            background: #ffffff;
            padding: 26px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            border: 1px solid var(--card-border);
            text-align: center;
            position: relative;
        }

        .workflow-number {
            width: 46px;
            height: 46px;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.25rem;
            margin: 0 auto 16px;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
        }

        .workflow-step h4 {
            margin: 0 0 8px;
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark-navy);
        }

        .workflow-step p {
            color: var(--slate-gray);
            font-size: 0.85rem;
            margin: 0;
            line-height: 1.5;
        }

        /* Booking Form */
        .booking-card {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--card-border);
            padding: 40px;
            max-width: 950px;
            margin: 0 auto;
        }

        .btn-quick-brand {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #334155;
            padding: 4px 10px;
            border-radius: 14px;
            font-size: 0.78rem;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.15s ease-in-out;
        }

        .btn-quick-brand:hover {
            background: var(--primary-accent);
            color: #ffffff;
            border-color: var(--primary-accent);
        }

        /* Public Footer */
        .public-footer {
            background: #090d16;
            color: #94a3b8;
            padding: 60px 36px 30px;
            border-top: 1px solid #1e293b;
        }

        .footer-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 35px;
            margin-bottom: 40px;
        }

        .footer-col h4 {
            color: #ffffff;
            margin: 0 0 18px;
            font-size: 1.15rem;
            font-weight: 700;
        }

        .footer-col p {
            margin: 8px 0;
            font-size: 0.92rem;
            line-height: 1.6;
        }

        .footer-bottom {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
            border-top: 1px solid #1e293b;
            padding-top: 25px;
            font-size: 0.88rem;
            color: #64748b;
        }

        @media (max-width: 992px) {
            .public-menu { display: none; }
            .hero-title { font-size: 2.1rem; }
            .public-navbar { padding: 0 20px; }
            .hero-section { padding: 60px 20px 80px; }
            .process-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            /* Tối ưu thanh điều hướng Top Navbar trên điện thoại */
            .public-navbar {
                padding: 0 12px;
                height: 60px;
            }
            .brand-icon {
                width: 36px;
                height: 36px;
                font-size: 1.1rem;
            }
            .brand-title {
                font-size: 1.1rem;
            }
            .brand-subtitle {
                display: none !important;
            }
            .public-auth {
                gap: 6px;
            }
            .btn-auth {
                padding: 6px 10px;
                font-size: 0.78rem;
                letter-spacing: 0;
            }
            .user-profile-pill {
                padding: 4px 8px;
                gap: 6px;
            }
            .user-name {
                max-width: 85px;
                font-size: 0.82rem;
            }
            .btn-logout-main {
                padding: 5px 10px;
                font-size: 0.78rem;
                gap: 4px;
            }

            /* Tối ưu Hero Section không bị tụt lề sâu */
            .hero-section {
                padding: 25px 12px 35px !important;
                margin-top: 60px;
            }
            .hero-tag {
                font-size: 0.75rem;
                padding: 4px 12px;
                margin-bottom: 12px;
            }
            .hero-title {
                font-size: 1.55rem !important;
                line-height: 1.3;
                margin-bottom: 12px;
            }
            .hero-desc {
                font-size: 0.88rem;
                margin-bottom: 20px;
            }
            .hero-actions {
                flex-direction: column;
                width: 100%;
                gap: 10px;
            }
            .btn-hero {
                width: 100%;
                justify-content: center;
                text-align: center;
                padding: 12px 14px;
                font-size: 0.95rem;
            }

            /* Tối ưu VIP Customer Dashboard */
            .vip-customer-container {
                margin: -20px auto 20px !important;
                padding: 0 10px !important;
            }
            .vip-panel {
                padding: 14px 10px !important;
                border-radius: 12px !important;
                margin-bottom: 20px !important;
            }
            .vip-panel-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
                padding-bottom: 12px;
                margin-bottom: 16px;
            }
            .vip-stats-grid {
                grid-template-columns: 1fr !important;
                gap: 10px !important;
            }
            .vip-cars-grid {
                grid-template-columns: 1fr !important;
                gap: 12px !important;
            }
            .car-vip-card {
                padding: 14px !important;
            }

            /* Tối ưu các phần Section và Form Đặt lịch hẹn */
            .landing-section {
                padding: 30px 10px !important;
            }
            .section-header {
                margin-bottom: 24px;
            }
            .section-header h2 {
                font-size: 1.45rem !important;
            }
            .section-header p {
                font-size: 0.88rem !important;
            }
            .booking-card {
                padding: 16px 12px !important;
                border-radius: 12px !important;
            }
            .booking-row {
                flex-direction: column;
            }
            .btn-submit-booking {
                width: 100% !important;
                padding: 13px !important;
                font-size: 1rem;
            }

            /* Tra cứu hồ sơ & thẻ dịch vụ */
            .lookup-card-container {
                padding: 16px 12px !important;
                border-radius: 12px !important;
            }
            .stat-chips-row {
                grid-template-columns: 1fr !important;
                gap: 10px !important;
            }
            .showcase-grid {
                grid-template-columns: 1fr !important;
                gap: 14px !important;
            }
            .process-grid {
                grid-template-columns: 1fr !important;
                gap: 14px !important;
            }
            .footer-grid {
                grid-template-columns: 1fr !important;
                gap: 25px !important;
            }
            .footer-section {
                padding: 35px 12px 20px !important;
            }

            /* Bảng dữ liệu trượt ngang mượt mà trên mobile */
            .table-responsive {
                border-radius: 8px;
                margin-bottom: 15px;
            }
            .table-custom th, .table-custom td {
                padding: 10px 8px !important;
                font-size: 0.82rem !important;
            }
        }
    </style>
</head>
<body>

    <!-- 1. Top Navbar Chuẩn Nhận Diện Thương Hiệu -->
    <header class="public-navbar">
        <a href="index.php" class="public-brand">
            <div class="brand-icon">
                <i class="fas fa-car-side"></i>
            </div>
            <div class="brand-text">
                <span class="brand-title">GARAGE PRO</span>
                <span class="brand-subtitle">Trung Tâm Dịch Vụ Ô Tô Hà Nội</span>
            </div>
        </a>

        <ul class="public-menu">
            <li><a href="#gioi-thieu"><i class="fas fa-building"></i> Giới thiệu Gara</a></li>
            <?php if ($isCustomer): ?>
                <li>
                    <a href="#xe-cua-toi">
                        <i class="fas fa-car"></i> Xe của tôi 
                        <?php if (count($myCars) > 0): ?>
                            <span class="menu-badge"><?= count($myCars) ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="#lich-hen-cua-toi">
                        <i class="fas fa-calendar-check"></i> Lịch hẹn
                        <?php if (count($myAppointments) > 0): ?>
                            <span class="menu-badge" style="background:#2563eb;"><?= count($myAppointments) ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endif; ?>
            <li><a href="#dich-vu"><i class="fas fa-wrench"></i> Dịch vụ & Giá</a></li>
            <li><a href="#quy-trinh"><i class="fas fa-stream"></i> Quy trình 5 bước</a></li>
            <li><a href="#tra-cuu"><i class="fas fa-search"></i> Tra cứu xe</a></li>
            <li><a href="#dat-lich"><i class="fas fa-calendar-alt"></i> Đặt lịch hẹn</a></li>
            <li><a href="#lien-he"><i class="fas fa-phone-alt"></i> Liên hệ</a></li>
        </ul>

        <div class="auth-actions">
            <?php if ($isCustomer && $currentUser): ?>
                <!-- Wrapper chứa Profile Pill & Dropdown -->
                <div style="position: relative;" id="customerDropdownWrapper">
                    <div class="customer-pill" onclick="toggleLandingUserMenu(event)" title="Nhấn để xem menu tài khoản nhanh">
                        <div class="user-avatar">
                            <?= strtoupper(mb_substr($currentUser['ho_ten'] ?? 'U', 0, 1, 'UTF-8')) ?>
                        </div>
                        <div>
                            <div class="user-name"><?= htmlspecialchars($currentUser['ho_ten']) ?></div>
                            <div style="font-size: 0.72rem; color: #10b981; font-weight: 700;"><i class="fas fa-check-circle"></i> Khách hàng</div>
                        </div>
                        <i class="fas fa-chevron-down" style="font-size: 0.75rem; color: #64748b; margin-left: 2px;"></i>
                    </div>

                    <!-- Menu Dropdown của Khách hàng -->
                    <div class="landing-user-dropdown" id="landingUserDropdown">
                        <div class="dropdown-user-header">
                            <div style="font-weight: 800; color: var(--dark-navy); font-size: 0.95rem;"><?= htmlspecialchars($currentUser['ho_ten']) ?></div>
                            <div style="font-size: 0.8rem; color: var(--slate-gray); word-break: break-all;"><?= htmlspecialchars($currentUser['email'] ?? '') ?></div>
                        </div>
                        <a href="#xe-cua-toi" class="dropdown-nav-item" onclick="closeLandingDropdown()">
                            <i class="fas fa-car" style="color: #2563eb; width: 18px;"></i>
                            <span>Xe của tôi (<?= count($myCars) ?> xe)</span>
                        </a>
                        <a href="#lich-hen-cua-toi" class="dropdown-nav-item" onclick="closeLandingDropdown()">
                            <i class="fas fa-calendar-check" style="color: #059669; width: 18px;"></i>
                            <span>Lịch hẹn của tôi (<?= count($myAppointments) ?>)</span>
                        </a>
                        <a href="index.php?c=auth&a=profile" class="dropdown-nav-item">
                            <i class="fas fa-id-card" style="color: #7c3aed; width: 18px;"></i>
                            <span>Hồ sơ & Đổi mật khẩu</span>
                        </a>
                        <a href="index.php?c=auth&a=logout" class="dropdown-nav-item dropdown-nav-logout" title="Đăng xuất khỏi hệ thống">
                            <i class="fas fa-sign-out-alt" style="width: 18px;"></i>
                            <span>Đăng xuất ngay</span>
                        </a>
                    </div>
                </div>

                <!-- Nút Đăng Xuất To Nổi Bật 1-Click Ngay Cạnh -->
                <a href="index.php?c=auth&a=logout" class="btn-logout-main" title="Bấm vào đây để đăng xuất ngay lập tức">
                    <i class="fas fa-sign-out-alt"></i> <span>Đăng xuất</span>
                </a>
            <?php else: ?>
                <a href="index.php?c=auth&a=login" class="btn btn-outline-primary" style="font-weight: 700; border-radius: 20px; padding: 7px 18px;">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập
                </a>
                <a href="index.php?c=auth&a=register" class="btn btn-primary" style="font-weight: 700; border-radius: 20px; padding: 7px 18px;">
                    <i class="fas fa-user-plus"></i> Đăng ký
                </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Thông báo Flash từ hệ thống -->
    <?php if (!empty($_SESSION['flash'])): ?>
        <?php 
            $f = $_SESSION['flash']; 
            unset($_SESSION['flash']); 
            $alertType = ($f['type'] === 'error') ? 'danger' : $f['type'];
        ?>
        <div style="max-width: 1000px; margin: 90px auto -60px; padding: 0 20px; position: relative; z-index: 100;">
            <div class="alert alert-<?= $alertType ?>" id="message_container" style="box-shadow: 0 8px 25px rgba(0,0,0,0.12); border-radius: 10px; font-weight: 500;">
                <i class="fas <?= ($alertType === 'success') ? 'fa-check-circle' : 'fa-info-circle' ?>"></i>
                <?= htmlspecialchars($f['message']) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- 2. Hero Showcase Banner -->
    <section class="hero-section">
        <div class="hero-content">
            <div class="hero-flex-wrapper" style="display: flex; align-items: center; justify-content: space-between; gap: 40px; flex-wrap: wrap;">
                <div style="flex: 1 1 540px; min-width: 300px;">
                    <?php if ($isCustomer && $currentUser): ?>
                        <div class="hero-tag">
                            <i class="fas fa-gem" style="color: #fbbf24;"></i> CỔNG DỊCH VỤ KHÁCH HÀNG THÂN THIẾT (VIP)
                        </div>
                        <h1 class="hero-title">
                            Kính chào Quý khách, <span><?= htmlspecialchars($currentUser['ho_ten']) ?></span>!
                        </h1>
                        <p class="hero-desc">
                            Chào mừng bạn quay trở lại với Trung tâm Dịch vụ Kỹ thuật Ô tô <b>GARA PRO HÀ NỘI</b>. Hệ thống sẵn sàng đồng hành cùng xế yêu của bạn: theo dõi tiến độ sửa chữa trực tuyến, tải phiếu kết quả & hóa đơn PDF (1 trang) đính kèm chữ ký đóng dấu xác nhận.
                        </p>
                        <div class="hero-actions">
                            <a href="#xe-cua-toi" class="btn-hero btn-hero-primary">
                                <i class="fas fa-car"></i> Quản lý <?= count($myCars) ?> xe của tôi
                            </a>
                            <a href="#dat-lich" class="btn-hero btn-hero-outline">
                                <i class="fas fa-calendar-plus"></i> Đặt lịch hẹn bảo dưỡng mới
                            </a>
                            <a href="#gioi-thieu" class="btn-hero btn-hero-outline">
                                <i class="fas fa-info-circle"></i> Giới thiệu về Garage Pro
                            </a>
                            <a href="tel:0825893162" class="btn-hero btn-hero-outline" style="border-color: #34d399; color: #34d399;">
                                <i class="fas fa-phone-alt"></i> Hotline 24/7: 0825.893.162
                            </a>
                        </div>
                    <?php else: ?>
                        <h1 class="hero-title">
                            DỊCH VỤ SỬA CHỮA & CHĂM SÓC XE <span>CHUYÊN NGHIỆP TẠI HÀ NỘI</span>
                        </h1>
                        <p class="hero-desc">
                            Garage Pro Hà Nội - Tọa lạc tại 123 Đường Cầu Giấy, Hà Nội. Quy mô 2.000m² với 10 khoang dịch vụ hiện đại, cầu nâng thủy lực chuẩn châu Âu, máy chẩn đoán OBD-II đa năng, cam kết 100% phụ tùng chính hãng và tự động xuất hóa đơn PDF qua Gmail.
                        </p>
                        <div class="hero-actions">
                            <a href="#dat-lich" class="btn-hero btn-hero-primary">
                                <i class="fas fa-calendar-check"></i> Đặt lịch hẹn bảo dưỡng ngay
                            </a>
                            <a href="#tra-cuu" class="btn-hero btn-hero-outline">
                                <i class="fas fa-search-location"></i> Tra cứu tiến độ xe đang sửa
                            </a>
                            <a href="#gioi-thieu" class="btn-hero btn-hero-outline">
                                <i class="fas fa-building"></i> Tìm hiểu về Garage Pro
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Thẻ Hình Ảnh Xưởng Thực Tế Nổi Bật -->
                <div style="flex: 1 1 420px; max-width: 500px; width: 100%;">
                    <div style="position: relative; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 45px rgba(0,0,0,0.45); border: 1.5px solid rgba(255,255,255,0.15);">
                        <img src="assets/images/services/hero_workshop.jpg" alt="Khoang xưởng dịch vụ Garage Pro" style="width: 100%; height: 320px; object-fit: cover; display: block;">
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 20px; background: linear-gradient(to top, rgba(15, 23, 42, 0.95), transparent);">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                                <span style="background: #2563eb; color: #fff; font-size: 0.75rem; font-weight: 800; padding: 3px 8px; border-radius: 6px;">XƯỞNG KỸ THUẬT</span>
                                <span style="color: #38bdf8; font-size: 0.82rem; font-weight: 600;"><i class="fas fa-certificate"></i> Chuẩn Châu Âu</span>
                            </div>
                            <h4 style="margin: 0; font-size: 1.1rem; color: #ffffff; font-weight: 700;">Hệ Thống 10 Khoang Sửa Chữa Hiện Đại</h4>
                            <p style="margin: 4px 0 0; font-size: 0.85rem; color: #cbd5e1;">Cầu nâng thủy lực cao cấp & máy chẩn đoán ECU đa năng</p>
                        </div>
                        <div style="position: absolute; top: 16px; right: 16px; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(8px); padding: 8px 14px; border-radius: 30px; border: 1px solid rgba(255,255,255,0.2); display: flex; align-items: center; gap: 8px; color: #fbbf24; font-size: 0.82rem; font-weight: 700;">
                            <i class="fas fa-star"></i> 5.000+ Lượt Xe Tin Cậy
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. DÀNH RIÊNG CHO KHÁCH HÀNG: CỔNG QUẢN LÝ XE & LỊCH HẸN (VIP HUB) -->
    <?php if ($isCustomer): ?>
        <div class="vip-customer-container" id="xe-cua-toi">
            <!-- Panel Quản lý Xe Của Tôi -->
            <div class="vip-panel">
                <div class="vip-panel-header">
                    <div class="vip-panel-title">
                        <div style="font-size: 2rem; color: #2563eb;"><i class="fas fa-warehouse"></i></div>
                        <div>
                            <h3>GARAGE CỦA TÔI (PHƯƠNG TIỆN ĐÃ ĐĂNG KÝ)</h3>
                            <p>Theo dõi tình trạng xe, lịch sử sửa chữa và tải phiếu kết quả PDF 1 trang đính kèm mộc xác nhận</p>
                        </div>
                    </div>
                    <div>
                        <a href="#dat-lich" class="btn btn-success" style="font-weight: 700; border-radius: 8px;">
                            <i class="fas fa-plus-circle"></i> Đặt lịch hẹn cho xe
                        </a>
                    </div>
                </div>

                <!-- Thống kê nhanh theo phong cách thẻ thẻ gọn gàng -->
                <?php
                    $totalCars = count($myCars);
                    $totalDebt = array_sum(array_column($myCars, 'tien_no'));
                    $pendingAppointments = count(array_filter($myAppointments, fn($a) => $a['trang_thai'] === 'ChoXacNhan'));
                ?>
                <div class="stat-chips-row">
                    <div class="stat-chip">
                        <div class="chip-icon blue"><i class="fas fa-car-alt"></i></div>
                        <div class="chip-content">
                            <h4><?= $totalCars ?> Xe</h4>
                            <p>Phương tiện trong hồ sơ</p>
                        </div>
                    </div>
                    <div class="stat-chip">
                        <div class="chip-icon amber"><i class="fas fa-clock"></i></div>
                        <div class="chip-content">
                            <h4><?= $pendingAppointments ?> Lịch</h4>
                            <p>Lịch hẹn chờ KTV duyệt</p>
                        </div>
                    </div>
                    <div class="stat-chip">
                        <div class="chip-icon <?= ($totalDebt > 0) ? 'red' : 'green' ?>">
                            <i class="fas <?= ($totalDebt > 0) ? 'fa-wallet' : 'fa-check-circle' ?>"></i>
                        </div>
                        <div class="chip-content">
                            <h4 style="color: <?= ($totalDebt > 0) ? '#dc2626' : '#059669' ?>;">
                                <?= number_format((float)$totalDebt) ?> đ
                            </h4>
                            <p><?= ($totalDebt > 0) ? 'Công nợ cần thanh toán' : 'Công nợ (Đã thanh toán đủ)' ?></p>
                        </div>
                    </div>
                </div>

                <!-- Lưới danh sách xe -->
                <?php if (!empty($myCars)): ?>
                    <div class="cars-grid">
                        <?php foreach ($myCars as $c): ?>
                            <?php 
                                $repair = $latestRepairsByCar[$c['ma_xe']] ?? null;
                            ?>
                            <div class="car-card">
                                <div>
                                    <div class="car-card-header">
                                        <div class="plate-box">
                                            <?= htmlspecialchars($c['bien_so']) ?>
                                        </div>
                                        <div>
                                            <?php if ($repair): ?>
                                                <?php if (in_array($repair['trang_thai'], ['TiepNhan', 'DangKiemTra', 'DangSuaChua'])): ?>
                                                    <span class="car-status-pill repairing"><i class="fas fa-wrench"></i> Đang sửa chữa</span>
                                                <?php elseif ($repair['trang_thai'] === 'HoanThanh'): ?>
                                                    <span class="car-status-pill finished"><i class="fas fa-check-circle"></i> Sẵn sàng nhận xe</span>
                                                <?php else: ?>
                                                    <span class="car-status-pill idle"><i class="fas fa-check"></i> Đã xuất xưởng</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="car-status-pill idle"><i class="fas fa-car"></i> Hoạt động tốt</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="car-brand-title">
                                        <?= htmlspecialchars($c['hang_xe']) ?> <?= htmlspecialchars($c['dong_xe'] ?? '') ?>
                                    </div>
                                    <div style="font-size: 0.82rem; color: #64748b;">Năm sản xuất: <?= !empty($c['nam_sx']) ? htmlspecialchars($c['nam_sx']) : 'Tiêu chuẩn' ?></div>

                                    <div class="car-meta-list">
                                        <div class="car-meta-item">
                                            <span>Số lần sửa chữa:</span>
                                            <b><?= $c['so_lan_sua'] ?? 0 ?> lần</b>
                                        </div>
                                        <div class="car-meta-item">
                                            <span>Tiền công nợ:</span>
                                            <b style="color: <?= ($c['tien_no'] > 0) ? '#dc2626' : '#059669' ?>;">
                                                <?= number_format((float)$c['tien_no']) ?> đ
                                            </b>
                                        </div>
                                        <?php if ($repair): ?>
                                            <div class="car-meta-item" style="border-top: 1px dashed #cbd5e1; margin-top: 4px; padding-top: 6px;">
                                                <span>Phiếu sửa gần nhất:</span>
                                                <b>#PSC-<?= $repair['ma_psc'] ?> (<?= date('d/m/Y', strtotime($repair['ngay_lap'])) ?>)</b>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="car-card-actions">
                                    <?php if ($repair): ?>
                                        <a href="index.php?c=repair&a=downloadPdf&id=<?= $repair['ma_psc'] ?>" class="btn-card-action btn-pdf-download" title="Tải file PDF 1 trang có chữ ký & đóng dấu">
                                            <i class="fas fa-file-pdf"></i> Tải Phiếu sửa PDF (1 trang)
                                        </a>
                                        <a href="index.php?c=repair&a=print&id=<?= $repair['ma_psc'] ?>" target="_blank" class="btn-card-action btn-card-print" title="Xem bản in phiếu sửa">
                                            <i class="fas fa-print"></i> Xem bản in
                                        </a>
                                    <?php endif; ?>
                                    <button type="button" class="btn-card-action btn-card-book" onclick="selectCarForBooking('<?= htmlspecialchars($c['bien_so']) ?>', '<?= htmlspecialchars($c['hang_xe']) ?>')">
                                        <i class="fas fa-calendar-plus"></i> Đặt lịch xe này
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px 20px; background: #ffffff; border-radius: 12px; border: 2px dashed #cbd5e1;">
                        <i class="fas fa-car" style="font-size: 2.5rem; color: #94a3b8; margin-bottom: 12px;"></i>
                        <p style="color: #64748b; margin: 0 0 15px;">Quý khách chưa có phương tiện nào được ghi nhận trong hệ thống.</p>
                        <a href="#dat-lich" class="btn btn-primary" style="border-radius: 20px; font-weight: 700;">
                            <i class="fas fa-plus"></i> Đặt lịch hẹn và đăng ký xe đầu tiên
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Panel Lịch Hẹn Của Khách Hàng -->
            <div class="vip-panel" id="lich-hen-cua-toi">
                <div class="vip-panel-header">
                    <div class="vip-panel-title">
                        <div style="font-size: 2rem; color: #059669;"><i class="fas fa-calendar-check"></i></div>
                        <div>
                            <h3>LỊCH HẸN BẢO DƯỠNG CỦA QUÝ KHÁCH</h3>
                            <p>Danh sách các buổi hẹn bảo dưỡng trực tuyến và tải phiếu hẹn PDF 1 trang</p>
                        </div>
                    </div>
                </div>

                <?php if (!empty($myAppointments)): ?>
                    <div class="table-responsive">
                        <table class="table-custom table-hover">
                            <thead>
                                <tr>
                                    <th>Mã lịch</th>
                                    <th>Biển số xe</th>
                                    <th>Hiệu xe</th>
                                    <th>Thời gian hẹn</th>
                                    <th>Yêu cầu / Hiện tượng</th>
                                    <th>Trạng thái</th>
                                    <th style="text-align: right;">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myAppointments as $a): ?>
                                    <tr>
                                        <td><b>#LH-<?= $a['ma_lh'] ?></b></td>
                                        <td>
                                            <span class="plate-box" style="font-size: 0.95rem; padding: 2px 8px;">
                                                <?= htmlspecialchars($a['bien_so']) ?>
                                            </span>
                                        </td>
                                        <td><b><?= htmlspecialchars($a['hang_xe']) ?></b></td>
                                        <td>
                                            <i class="fas fa-clock" style="color: #64748b;"></i> 
                                            <?= date('d/m/Y H:i', strtotime($a['ngay_hen'])) ?>
                                        </td>
                                        <td style="color: #475569; max-width: 250px;">
                                            <?= !empty($a['ghi_chu']) ? htmlspecialchars($a['ghi_chu']) : 'Bảo dưỡng định kỳ' ?>
                                        </td>
                                        <td>
                                            <?php if ($a['trang_thai'] === 'ChoXacNhan'): ?>
                                                <span class="badge badge-warning"><i class="fas fa-spinner fa-spin"></i> Chờ KTV xác nhận</span>
                                            <?php elseif ($a['trang_thai'] === 'DaXacNhan'): ?>
                                                <span class="badge badge-info"><i class="fas fa-check"></i> Đã tiếp nhận xưởng</span>
                                            <?php elseif ($a['trang_thai'] === 'HoanThanh'): ?>
                                                <span class="badge badge-success"><i class="fas fa-check-double"></i> Đã hoàn thành</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Đã hủy</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="index.php?c=appointment&a=downloadPdf&id=<?= $a['ma_lh'] ?>" class="btn btn-sm btn-outline-primary" style="font-weight: 600;" title="Tải phiếu hẹn PDF 1 trang">
                                                <i class="fas fa-file-pdf"></i> Tải Phiếu hẹn PDF
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="color: #64748b; margin: 0; padding: 15px; background: #f8fafc; border-radius: 8px;">
                        Quý khách hiện chưa có lịch hẹn bảo dưỡng nào trong danh sách.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- 4. GIỚI THIỆU VỀ GARAGE PRO HÀ NỘI (SHOWCASE CƠ SỞ VẬT CHẤT & NĂNG LỰC) -->
    <section class="landing-section" id="gioi-thieu" style="<?= $isCustomer ? 'padding-top: 10px;' : '' ?>">
        <div class="section-header">
            <span class="section-badge">Hệ Thống Tiêu Chuẩn Châu Âu</span>
            <h2>GIỚI THIỆU VỀ GARAGE PRO HÀ NỘI</h2>
            <p>
                Trung tâm dịch vụ kỹ thuật và chăm sóc xe hơi toàn diện hàng đầu thủ đô, tọa lạc tại số <b>123 Đường Cầu Giấy, Quận Cầu Giấy, Hà Nội</b> với quy mô xưởng dịch vụ trên 2.000m².
            </p>
        </div>

        <div class="showcase-grid">
            <div class="showcase-card">
                <div class="showcase-icon"><i class="fas fa-warehouse"></i></div>
                <h3 class="showcase-title">10 Khoang Sửa Chữa Chuyên Sâu</h3>
                <p class="showcase-desc">
                    Phân khu khoa học: Khoang Bảo Dưỡng Nhanh 60 phút, Khoang Đại Tu Gầm Máy, Khoang Điện Tử - Điều Hòa và Phòng Đồng Sơn sấy hấp khép kín.
                </p>
            </div>
            <div class="showcase-card">
                <div class="showcase-icon"><i class="fas fa-arrows-alt-v"></i></div>
                <h3 class="showcase-title">Cầu Nâng Thủy Lực Châu Âu</h3>
                <p class="showcase-desc">
                    Hệ thống cầu nâng 2 trụ và 4 trụ thế hệ mới nhập khẩu nguyên chiếc, khóa an toàn thủy lực tự động, tải trọng tới 5.5 tấn, đáp ứng từ sedan, SUV đến xe thương mại.
                </p>
            </div>
            <div class="showcase-card">
                <div class="showcase-icon"><i class="fas fa-laptop-code"></i></div>
                <h3 class="showcase-title">Máy Chẩn Đoán OBD-II Đa Năng</h3>
                <p class="showcase-desc">
                    Trang bị máy quét mã lỗi điện tử chuyên sâu OBD-II thế hệ mới nhất, đọc và phân tích toàn bộ thông số ECU hộp đen cho hơn 50 hãng xe phổ biến và cao cấp.
                </p>
            </div>
            <div class="showcase-card">
                <div class="showcase-icon"><i class="fas fa-crosshairs"></i></div>
                <h3 class="showcase-title">Cân Thước Lái 3D & Sơn Đức</h3>
                <p class="showcase-desc">
                    Công nghệ đo và cân chỉnh độ chụm góc lái bằng tia hồng ngoại Hunter 3D, cùng buồng sơn sấy hồng ngoại tiêu chuẩn Đức sử dụng sơn gốc nước cao cấp.
                </p>
            </div>
        </div>

        <!-- HÌNH ẢNH THỰC TẾ XƯỞNG DỊCH VỤ & KHOANG KỸ THUẬT -->
        <div style="margin-top: 35px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-camera-retro" style="color: #2563eb;"></i> HÌNH ẢNH KHÔNG GIAN XƯỞNG & ĐỘI NGŨ KỸ THUẬT
                </h3>
                <span style="font-size: 0.85rem; color: #64748b;"><i class="fas fa-check-circle text-success"></i> Hình ảnh thực tế tại 123 Đường Cầu Giấy, Hà Nội</span>
            </div>

            <div class="workshop-gallery-grid">
                <div class="workshop-photo-card">
                    <img src="assets/images/services/hero_workshop.jpg" alt="Khoang sửa chữa ô tô hiện đại" loading="lazy">
                    <div class="workshop-photo-caption">
                        <h4>10 Khoang Dịch Vụ Chuẩn Châu Âu</h4>
                        <p>Cầu nâng thủy lực đồng bộ, trang thiết bị bảo hộ an toàn và sàn epoxy chuyên dụng.</p>
                    </div>
                </div>
                <div class="workshop-photo-card">
                    <img src="assets/images/services/hero_mechanic.jpg" alt="Kỹ thuật viên kiểm tra động cơ" loading="lazy">
                    <div class="workshop-photo-caption">
                        <h4>Đội Ngũ Kỹ Thuật Viên Tay Nghề Cao</h4>
                        <p>Được đào tạo bài bản từ các hãng xe lớn, chẩn đoán khoang máy và gầm tỉ mỉ, chính xác.</p>
                    </div>
                </div>
                <div class="workshop-photo-card">
                    <img src="assets/images/services/service_wheel.jpg" alt="Cân chỉnh độ chụm bánh xe" loading="lazy">
                    <div class="workshop-photo-caption">
                        <h4>Cân Thước Lái & Cân Mâm Bấm Chì</h4>
                        <p>Đo đạc góc đặt bánh xe bằng tia hồng ngoại 3D, cân bằng động mâm lốp triệt tiêu rung lắc.</p>
                    </div>
                </div>
                <div class="workshop-photo-card">
                    <img src="assets/images/services/service_paint.jpg" alt="Phòng sơn sấy hồng ngoại" loading="lazy">
                    <div class="workshop-photo-caption">
                        <h4>Phòng Sơn Sấy Hồng Ngoại Chuẩn Đức</h4>
                        <p>Phòng hấp nhiệt khép kín, lọc bụi tuần hoàn, pha màu vi tính chuẩn xác 100% màu sơn gốc.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="guarantees-banner">
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-shield-alt" style="font-size: 2rem; color: #34d399;"></i>
                <div>
                    <h3 style="margin: 0; font-size: 1.45rem; font-weight: 800;">4 CAM KẾT VÀNG TẠI GARAGE PRO HÀ NỘI</h3>
                    <p style="margin: 4px 0 0; color: #cbd5e1; font-size: 0.92rem;">Đặt sự an toàn, minh bạch và quyền lợi của Quý khách hàng lên hàng đầu</p>
                </div>
            </div>

            <div class="guarantees-grid">
                <div class="guarantee-item">
                    <div class="guarantee-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="guarantee-text">
                        <h4>100% Phụ Tùng Chính Hãng</h4>
                        <p>Nhập khẩu chính hãng từ Bosch, Denso, Michelin, Motul, GS. Đầy đủ tem chống hàng giả và bảo hành 12 - 24 tháng.</p>
                    </div>
                </div>
                <div class="guarantee-item">
                    <div class="guarantee-icon"><i class="fas fa-receipt"></i></div>
                    <div class="guarantee-text">
                        <h4>Báo Giá Minh Bạch Trước</h4>
                        <p>Kỹ thuật viên lập phiếu kiểm tra, báo giá chi tiết từng hạng mục và chỉ thi công sau khi nhận được sự đồng thuận của khách hàng.</p>
                    </div>
                </div>
                <div class="guarantee-item">
                    <div class="guarantee-icon"><i class="fas fa-envelope-open-text"></i></div>
                    <div class="guarantee-text">
                        <h4>Tự Động Gửi Gmail & PDF</h4>
                        <p>Mỗi giai đoạn tiếp nhận, hoàn thành sửa chữa và thanh toán đều tự động phát sinh phiếu PDF 1 trang gửi ngay vào hộp thư Gmail.</p>
                    </div>
                </div>
                <div class="guarantee-item">
                    <div class="guarantee-icon"><i class="fas fa-user-shield"></i></div>
                    <div class="guarantee-text">
                        <h4>KTV Lành Nghề >10 Năm</h4>
                        <p>Đội ngũ kỹ sư ô tô đào tạo chính quy, tay nghề cao, thái độ phục vụ tận tâm, cẩn trọng và chuẩn mực.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. HỘP TRA CỨU TIẾN ĐỘ XE TRỰC TUYẾN (LIVE TRACKER) -->
    <div class="lookup-card-container" id="tra-cuu" style="max-width: 1000px; margin: 40px auto; padding: 0 20px;">
        <div style="background: #ffffff; padding: 35px; border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,0.06); border: 1px solid var(--card-border);">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <div style="width: 44px; height: 44px; border-radius: 10px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                    <i class="fas fa-search-location"></i>
                </div>
                <div>
                    <h3 style="margin: 0; color: var(--dark-navy); font-size: 1.35rem; font-weight: 800;">Tra cứu tiến độ & tình trạng xe trực tuyến</h3>
                    <p style="color: var(--slate-gray); margin: 2px 0 0; font-size: 0.9rem;">
                        Nhập biển số xe (VD: <b>37B1-200611</b>, <b>51K-888.99</b>) để kiểm tra tình trạng sửa chữa, linh kiện thay thế và chi phí thời gian thực.
                    </p>
                </div>
            </div>

            <form method="GET" action="index.php">
                <div style="display: flex; gap: 12px; margin-top: 20px; flex-wrap: wrap;">
                    <input type="text" name="lookup_plate" class="form-control" 
                           value="<?= htmlspecialchars($lookupPlate ?? '') ?>" 
                           placeholder="Nhập biển số xe cần tra cứu (VD: 37B1-200611, 51K-888.99)..." 
                           style="font-size: 1.05rem; padding: 13px 18px; text-transform: uppercase; flex: 1; min-width: 250px; font-weight: 600;" required>
                    <button type="submit" class="btn btn-primary" style="padding: 13px 30px; font-weight: 700; border-radius: 8px; font-size: 1rem;">
                        <i class="fas fa-search"></i> Kiểm tra ngay
                    </button>
                </div>
            </form>

            <!-- Kết quả tra cứu chi tiết -->
            <?php if (!empty($carProgress)): ?>
                <?php 
                    $c = $carProgress['car'];
                    $repair = $carProgress['latestRepair'];
                    $svs = $carProgress['services'];
                    $pts = $carProgress['parts'];
                ?>
                <div style="margin-top: 28px; border-top: 2px dashed #e2e8f0; padding-top: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <span class="plate-box" style="font-size: 1.25rem;"><?= htmlspecialchars($c['bien_so']) ?></span>
                            <span style="font-weight: 800; font-size: 1.2rem; margin-left: 10px; color: var(--dark-navy);">
                                <?= htmlspecialchars($c['hang_xe']) ?> <?= htmlspecialchars($c['dong_xe'] ?? '') ?>
                            </span>
                            <div style="color: #64748b; font-size: 0.92rem; margin-top: 4px;">
                                Chủ xe: <b><?= htmlspecialchars($c['ten_chu_xe']) ?></b> - SĐT: <b><?= htmlspecialchars($c['sdt'] ?? '') ?></b>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 0.85rem; color: #64748b;">Công nợ hiện tại:</span><br>
                            <b style="color: <?= ($c['tien_no'] > 0) ? '#dc2626' : '#059669' ?>; font-size: 1.35rem;">
                                <?= number_format((float)$c['tien_no']) ?> đ
                            </b>
                        </div>
                    </div>

                    <?php if ($repair): ?>
                        <div style="margin-top: 22px; background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                                <div>
                                    <b style="color: #1e40af; font-size: 1.05rem;">Phiếu sửa chữa: #<?= $repair['ma_psc'] ?></b> 
                                    <span style="color: #64748b; font-size: 0.9rem;">(Tiếp nhận: <?= date('d/m/Y H:i', strtotime($repair['ngay_lap'])) ?>)</span>
                                </div>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <?php
                                        $badge = 'badge-secondary';
                                        $statusText = $repair['trang_thai'];
                                        if ($repair['trang_thai'] === 'TiepNhan') { $badge = 'badge-info'; $statusText = '1. Tiếp nhận'; }
                                        if ($repair['trang_thai'] === 'DangKiemTra') { $badge = 'badge-warning'; $statusText = '2. Đang kiểm tra'; }
                                        if ($repair['trang_thai'] === 'DangSuaChua') { $badge = 'badge-warning'; $statusText = '3. Đang sửa chữa'; }
                                        if ($repair['trang_thai'] === 'HoanThanh') { $badge = 'badge-primary'; $statusText = '4. Hoàn thành (Sẵn sàng nhận xe)'; }
                                        if ($repair['trang_thai'] === 'DaThanhToan') { $badge = 'badge-success'; $statusText = '5. Đã thanh toán & xuất xưởng'; }
                                    ?>
                                    <span class="badge <?= $badge ?>" style="font-size: 0.95rem; padding: 6px 14px;"><?= $statusText ?></span>
                                    <a href="index.php?c=repair&a=downloadPdf&id=<?= $repair['ma_psc'] ?>" class="btn btn-sm btn-outline-primary" style="font-weight: 700;">
                                        <i class="fas fa-file-pdf"></i> Tải PDF (1 trang)
                                    </a>
                                </div>
                            </div>

                            <!-- Chi tiết hạng mục -->
                            <?php if (!empty($svs) || !empty($pts)): ?>
                                <div class="table-responsive" style="margin-top: 12px;">
                                    <table class="table-custom table-hover">
                                        <thead>
                                            <tr>
                                                <th>Hạng mục thực hiện</th>
                                                <th>Loại</th>
                                                <th>Số lượng</th>
                                                <th style="text-align: right;">Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($svs as $s): ?>
                                                <tr>
                                                    <td><b><?= htmlspecialchars($s['ten_dv']) ?></b></td>
                                                    <td><span class="badge badge-secondary">Công thợ</span></td>
                                                    <td><?= $s['so_luong'] ?></td>
                                                    <td style="font-weight: 700; text-align: right; color: #0f172a;"><?= number_format((float)$s['thanh_tien']) ?> đ</td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php foreach ($pts as $p): ?>
                                                <tr>
                                                    <td><b><?= htmlspecialchars($p['ten_pt']) ?></b></td>
                                                    <td><span class="badge badge-primary">Phụ tùng</span></td>
                                                    <td><?= $p['so_luong'] ?> <?= htmlspecialchars($p['don_vi_tinh']) ?></td>
                                                    <td style="font-weight: 700; text-align: right; color: #0f172a;"><?= number_format((float)$p['thanh_tien']) ?> đ</td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <tr style="font-weight: bold; background: #e0e7ff;">
                                                <td colspan="3" style="text-align: right; font-size: 1.05rem; color: #1e3a8a;">TỔNG CỘNG PHÍ DỊCH VỤ:</td>
                                                <td style="color: #1e40af; font-size: 1.25rem; font-weight: 800; text-align: right;"><?= number_format((float)$repair['tong_tien']) ?> VNĐ</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p style="color: #64748b; margin: 0;">Xe đang trong quy trình kiểm tra sơ bộ ban đầu, kỹ thuật viên sẽ sớm cập nhật bảng kê chi tiết.</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p style="color: #64748b; margin-top: 15px;">Xe đã có hồ sơ trong hệ thống nhưng hiện không có phiếu sửa chữa nào đang thực hiện.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 6. DỊCH VỤ & BẢNG GIÁ NIÊM YẾT -->
    <section class="landing-section" id="dich-vu">
        <div class="section-header">
            <span class="section-badge">Bảng Giá Dịch Vụ Niêm Yết</span>
            <h2>DỊCH VỤ SỬA CHỮA & BẢNG GIÁ NIÊM YẾT</h2>
            <p>Bảng giá tiền công và dịch vụ kỹ thuật công khai, cam kết không phát sinh bất kỳ phụ phí nào</p>
        </div>

        <div class="services-grid">
            <?php foreach ($services as $s): ?>
                <?php 
                    $srvImg = $serviceImages[$s['ma_dv']] ?? 'assets/images/services/hero_workshop.jpg';
                ?>
                <div class="service-card">
                    <div class="service-img-wrapper">
                        <img src="<?= $srvImg ?>" alt="<?= htmlspecialchars($s['ten_dv']) ?>" loading="lazy">
                        <span class="service-img-badge"><i class="fas fa-certificate"></i> Chuẩn Châu Âu</span>
                    </div>
                    <div class="service-body">
                        <div>
                            <div class="service-icon"><i class="fas fa-wrench"></i></div>
                            <h3><?= htmlspecialchars($s['ten_dv']) ?></h3>
                            <p class="service-desc">
                                <?= htmlspecialchars($s['mo_ta'] ?? 'Dịch vụ kỹ thuật tiêu chuẩn chất lượng cao dành cho tất cả các dòng xe ô tô.') ?>
                            </p>
                        </div>
                        <div class="service-footer">
                            <div class="service-price">
                                <?= number_format((float)$s['gia_tieu_chuan']) ?> VNĐ
                            </div>
                            <a href="#dat-lich" class="btn btn-sm btn-primary" style="font-weight: 700; border-radius: 6px;">
                                <i class="fas fa-calendar-alt"></i> Đặt lịch
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- 7. QUY TRÌNH TIẾP NHẬN & SỬA CHỮA 5 BƯỚC -->
    <section style="background: #ffffff; padding: 75px 24px; border-top: 1px solid var(--card-border); border-bottom: 1px solid var(--card-border);" id="quy-trinh">
        <div style="max-width: 1200px; margin: 0 auto;">
            <div class="section-header">
                <span class="section-badge">Quy Trình Chuẩn Hóa</span>
                <h2>QUY TRÌNH TIẾP NHẬN & SỬA CHỮA 5 BƯỚC</h2>
                <p>Khách hàng luôn chủ động nắm bắt từng công đoạn chăm sóc xế yêu</p>
            </div>

            <div class="workflow-grid">
                <div class="workflow-step">
                    <div class="workflow-number">1</div>
                    <h4>Tiếp nhận xe</h4>
                    <p>Đăng ký lịch hẹn hoặc đón xe tại xưởng, kiểm tra hiện trạng và lập hồ sơ tiếp nhận nhanh chóng.</p>
                </div>
                <div class="workflow-step">
                    <div class="workflow-number">2</div>
                    <h4>Khám & Báo giá</h4>
                    <p>Kỹ thuật viên chẩn đoán OBD-II, lên bảng kê công thợ & vật tư phụ tùng để khách hàng phê duyệt.</p>
                </div>
                <div class="workflow-step">
                    <div class="workflow-number">3</div>
                    <h4>Sửa chữa & Xuất kho</h4>
                    <p>Thi công chuyên nghiệp tại khoang kỹ thuật, xuất linh kiện chính hãng từ kho tự động trừ tồn kho.</p>
                </div>
                <div class="workflow-step">
                    <div class="workflow-number">4</div>
                    <h4>KCS & Rửa xe</h4>
                    <p>Tổ trưởng kỹ thuật kiểm định chất lượng lần cuối (KCS), chạy thử, vệ sinh và rửa xe sạch sẽ.</p>
                </div>
                <div class="workflow-step">
                    <div class="workflow-number">5</div>
                    <h4>Giao xe & Bảo hành</h4>
                    <p>Bàn giao chìa khóa, xuất hóa đơn & phiếu bảo hành PDF tự động gửi thẳng vào Gmail khách hàng.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 8. FORM ĐẶT LỊCH HẸN BẢO DƯỠNG TRỰC TUYẾN -->
    <section class="landing-section" id="dat-lich">
        <div class="section-header">
            <span class="section-badge">Đặt Chỗ Trước - Tiết Kiệm Thời Gian</span>
            <h2>ĐẶT LỊCH HẸN SỬA CHỮA & BẢO DƯỠNG</h2>
            <p>Để lại thông tin, đội ngũ kỹ thuật Garage Pro sẽ chuẩn bị sẵn khoang máy và phụ tùng đón tiếp Quý khách</p>
        </div>

        <div class="booking-card">
            <?php if (!$isCustomer): ?>
                <div style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border: 1.5px solid #3b82f6; border-radius: 12px; padding: 14px 18px; margin-bottom: 22px; display: flex; align-items: center; gap: 14px;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div>
                        <div style="font-weight: 700; color: #1e3a8a; font-size: 0.96rem;">
                            Quý khách chưa đăng nhập? Cứ yên tâm điền thông tin phiếu hẹn!
                        </div>
                        <div style="font-size: 0.85rem; color: #1e40af; margin-top: 3px;">
                            Hệ thống sẽ <b>tự động lưu giữ toàn bộ thông tin phiếu</b> và đưa Quý khách đến bước tạo mật khẩu trong 30 giây để kích hoạt phiếu & nhận PDF có mộc dấu qua Gmail.
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 1.5px solid #22c55e; border-radius: 12px; padding: 12px 18px; margin-bottom: 22px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-user-check text-success" style="font-size: 1.3rem;"></i>
                        <span style="font-size: 0.92rem; color: #166534; font-weight: 600;">
                            Đang đặt lịch với tư cách khách hàng: <b><?= htmlspecialchars($currentUser['ho_ten']) ?></b> (<?= htmlspecialchars($currentUser['email']) ?>)
                        </span>
                    </div>
                    <span class="badge badge-success" style="font-size: 0.8rem; padding: 5px 12px;">Đã đăng nhập</span>
                </div>
            <?php endif; ?>
            <form method="POST" action="index.php?c=home&a=bookAppointment" id="bookingForm" onsubmit="return handleBookingSubmit(this);">
                <?= csrf_field() ?>
                <div class="row">
                    <div class="col-12 col-m-4">
                        <div class="form-group">
                            <label style="font-weight: 700; color: var(--dark-navy);"><i class="fas fa-user text-primary"></i> Họ và tên của Quý khách: <span style="color:red">*</span></label>
                            <input type="text" name="ten_khach" class="form-control" 
                                   value="<?= htmlspecialchars($currentUser['ho_ten'] ?? '') ?>" 
                                   placeholder="VD: Nguyễn Văn A" required>
                        </div>
                    </div>
                    <div class="col-12 col-m-4">
                        <div class="form-group">
                            <label style="font-weight: 700; color: var(--dark-navy);"><i class="fas fa-phone-alt text-primary"></i> Số điện thoại liên hệ: <span style="color:red">*</span></label>
                            <input type="text" name="sdt" class="form-control" 
                                   value="<?= htmlspecialchars($currentUser['sdt'] ?? '') ?>" 
                                   placeholder="VD: 0912345678" required>
                        </div>
                    </div>
                    <div class="col-12 col-m-4">
                        <div class="form-group">
                            <label style="font-weight: 700; color: var(--dark-navy);"><i class="fas fa-envelope text-primary"></i> Email (Gmail nhận Phiếu PDF): <span style="color:red">*</span></label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" 
                                   placeholder="VD: khachhang@gmail.com" required>
                            <small style="color: #64748b; font-size: 0.76rem;"><i class="fas fa-shield-alt text-success"></i> Tự động gửi file PDF phiếu hẹn kèm con dấu về Gmail này</small>
                        </div>
                    </div>

                    <?php if ($isCustomer && !empty($myCars)): ?>
                        <div class="col-12">
                            <div class="form-group" style="background: #f1f5f9; padding: 15px; border-radius: 10px; border: 1px solid #cbd5e1;">
                                <label style="font-weight: 700; color: #1e40af;"><i class="fas fa-magic"></i> Chọn nhanh xe từ hồ sơ của bạn:</label>
                                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 6px;">
                                    <?php foreach ($myCars as $c): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary" style="font-weight: 700; background: #ffffff;"
                                                onclick="selectCarForBooking('<?= htmlspecialchars($c['bien_so']) ?>', '<?= htmlspecialchars($c['hang_xe']) ?>')">
                                            🚗 <?= htmlspecialchars($c['bien_so']) ?> (<?= htmlspecialchars($c['hang_xe']) ?>)
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="col-12 col-m-6">
                        <div class="form-group">
                            <label style="font-weight: 700; color: var(--dark-navy);"><i class="fas fa-id-card text-primary"></i> Biển số xe: <span style="color:red">*</span></label>
                            <input type="text" id="landing_bien_so" name="bien_so" class="form-control" 
                                   placeholder="VD: 37B1-200611 hoặc 30A-123.45" style="text-transform: uppercase; font-weight: 700;" required>
                        </div>
                    </div>
                    <div class="col-12 col-m-6">
                        <div class="form-group">
                            <label style="font-weight: 700; color: var(--dark-navy);">
                                <i class="fas fa-car text-primary"></i> Hiệu xe (Hãng xe): <span style="color:red">*</span>
                            </label>
                            <input type="text" id="landing_hang_xe" name="hang_xe" list="landing_brands_list" class="form-control" placeholder="Nhập hiệu xe..." autocomplete="off" required>
                            <datalist id="landing_brands_list">
                                <?php foreach ($brands as $b): ?>
                                    <option value="<?= htmlspecialchars($b) ?>">
                                <?php endforeach; ?>
                            </datalist>
                            <div style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                                <small style="color: #64748b; font-size: 0.78rem;">Phổ biến:</small>
                                <?php 
                                    $quickBrands = ['Toyota', 'Hyundai', 'Kia', 'Honda', 'Mazda', 'Ford', 'VinFast', 'Mercedes-Benz', 'BMW', 'Mitsubishi'];
                                    foreach ($quickBrands as $qb): 
                                ?>
                                    <button type="button" class="btn-quick-brand" onclick="document.getElementById('landing_hang_xe').value='<?= htmlspecialchars($qb) ?>'; document.getElementById('landing_hang_xe').focus();">
                                        <?= htmlspecialchars($qb) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-m-6">
                        <div class="form-group">
                            <label style="font-weight: 700; color: var(--dark-navy);"><i class="fas fa-clock text-primary"></i> Thời gian Quý khách dự kiến đến gara: <span style="color:red">*</span></label>
                            <input type="datetime-local" id="landing_ngay_hen" name="ngay_hen" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-12 col-m-6">
                        <div class="form-group">
                            <label style="font-weight: 700; color: var(--dark-navy);"><i class="fas fa-clipboard-list text-primary"></i> Hiện tượng xe / Hạng mục yêu cầu:</label>
                            <textarea name="ghi_chu" class="form-control" rows="2" placeholder="VD: Thay dầu nhớt, kiểm tra độ chụm lái, bảo dưỡng phanh 4 bánh, vệ sinh lọc gió máy lạnh..."></textarea>
                        </div>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" id="btnSubmitBooking" class="btn btn-primary" style="padding: 14px 42px; font-size: 1.05rem; font-weight: 800; border-radius: 30px; box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3); transition: all 0.3s ease;">
                        <i class="fas fa-paper-plane mr-2"></i> GỬI YÊU CẦU ĐẶT LỊCH HẸN
                    </button>
                    <div id="bookingLoadingNotice" style="display: none; max-width: 650px; margin: 15px auto 0; padding: 14px 20px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; color: #1e3a8a; text-align: left; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.08);">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="font-size: 1.8rem; color: #2563eb;"><i class="fas fa-spinner fa-spin"></i></div>
                            <div>
                                <div style="font-weight: 800; font-size: 0.95rem; color: #1d4ed8;">Đang tạo Phiếu PDF & đồng bộ máy chủ Gmail...</div>
                                <div style="font-size: 0.82rem; color: #475569; margin-top: 2px;">
                                    Hệ thống đang xuất văn bản PDF chính thức (1 trang có dấu đỏ) và truyền dữ liệu bảo mật tới Gmail của bạn. Vui lòng đợi trong giây lát!
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- 9. KHO PHỤ TÙNG CHÍNH HÃNG -->
    <section class="landing-section" id="phu-tung" style="padding-top: 10px;">
        <div class="section-header">
            <span class="section-badge">Kho Linh Kiện Chuẩn</span>
            <h2>KHO VẬT TƯ & PHỤ TÙNG CHÍNH HÃNG</h2>
            <p>100% linh kiện chính ngạch có sẵn tại kho: Bosch, Denso, GS Battery, Castrol, Motul, Michelin</p>
        </div>

        <!-- Lưới thẻ hình ảnh phụ tùng sinh động -->
        <div class="parts-grid">
            <?php foreach ($parts as $p): ?>
                <?php 
                    $pImg = $partImages[$p['ma_pt']] ?? 'assets/images/parts/part_oil_castrol.jpg';
                    $brandName = $p['ten_ncc'] ?? 'Chính Hãng';
                ?>
                <div class="part-card">
                    <div>
                        <div class="part-img-container">
                            <img src="<?= $pImg ?>" alt="<?= htmlspecialchars($p['ten_pt']) ?>" loading="lazy">
                            <span class="part-brand-tag"><i class="fas fa-shield-alt"></i> <?= htmlspecialchars($brandName) ?></span>
                        </div>
                        <h4 class="part-card-title"><?= htmlspecialchars($p['ten_pt']) ?></h4>
                        <div class="part-card-meta">
                            <span>Đơn vị: <b><?= htmlspecialchars($p['don_vi_tinh']) ?></b></span>
                            <?php if ($p['so_luong_ton'] > 0): ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> Còn hàng (<?= $p['so_luong_ton'] ?>)</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Tạm hết hàng</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <div class="part-card-price">
                            <?= number_format((float)$p['gia_ban']) ?> đ
                        </div>
                        <a href="#dat-lich" class="btn btn-sm btn-outline-primary btn-block" style="font-weight: 700; border-radius: 6px; padding: 8px 12px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                            <i class="fas fa-tools"></i> Đặt lịch thay lắp phụ tùng
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Bảng kê tổng hợp đầy đủ -->
        <div style="margin-top: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #334155;">
                    <i class="fas fa-list-ul"></i> Bảng niêm yết chi tiết & tra cứu quy cách
                </h4>
                <span style="font-size: 0.85rem; color: #64748b;">Tổng cộng <b><?= count($parts) ?></b> mặt hàng trong kho</span>
            </div>
            <div class="table-responsive">
                <table class="table-custom table-hover">
                    <thead>
                        <tr>
                            <th style="width: 70px; text-align: center;">Hình ảnh</th>
                            <th>Tên phụ tùng</th>
                            <th>Đơn vị tính</th>
                            <th>Nhà cung cấp</th>
                            <th>Tình trạng kho</th>
                            <th style="text-align: right;">Giá niêm yết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($parts as $p): ?>
                            <?php 
                                $pImg = $partImages[$p['ma_pt']] ?? 'assets/images/parts/part_oil_castrol.jpg';
                            ?>
                            <tr>
                                <td style="text-align: center;">
                                    <img src="<?= $pImg ?>" alt="<?= htmlspecialchars($p['ten_pt']) ?>" style="width: 48px; height: 48px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;">
                                </td>
                                <td><b><?= htmlspecialchars($p['ten_pt']) ?></b></td>
                                <td><?= htmlspecialchars($p['don_vi_tinh']) ?></td>
                                <td><?= htmlspecialchars($p['ten_ncc']) ?></td>
                                <td>
                                    <?php if ($p['so_luong_ton'] > 0): ?>
                                        <span class="badge badge-success"><i class="fas fa-check"></i> Còn hàng (<?= $p['so_luong_ton'] ?>)</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Tạm hết hàng</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-weight: 800; color: #1e40af; font-size: 1.05rem; text-align: right;"><?= number_format((float)$p['gia_ban']) ?> đ</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- 10. Footer Thông Tin Liên Hệ & Cứu Hộ -->
    <footer class="public-footer" id="lien-he">
        <div class="footer-grid">
            <div class="footer-col">
                <div style="font-size: 1.45rem; font-weight: 800; color: #ffffff; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; background: #2563eb; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; color: #fff;">
                        <i class="fas fa-car-side"></i>
                    </div>
                    GARAGE PRO HÀ NỘI
                </div>
                <p style="line-height: 1.65; font-size: 0.92rem; color: #94a3b8;">
                    Trung tâm dịch vụ bảo dưỡng và sửa chữa ô tô công nghệ cao chuẩn mực tại thủ đô. Cam kết chất lượng, bảo hành uy tín và đồng hành an tâm trên mọi chặng đường.
                </p>
                <div style="margin-top: 15px; display: flex; gap: 10px;">
                    <a href="#" style="width: 36px; height: 36px; background: #1e293b; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none;"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" style="width: 36px; height: 36px; background: #1e293b; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none;"><i class="fab fa-youtube"></i></a>
                    <a href="#" style="width: 36px; height: 36px; background: #1e293b; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none;"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Trụ sở & Xưởng dịch vụ</h4>
                <p><i class="fas fa-map-marker-alt" style="color: #34d399;"></i> <b>123 Đường Cầu Giấy, Quận Cầu Giấy, Hà Nội</b></p>
                <p><i class="fas fa-phone-alt" style="color: #34d399;"></i> Hotline tư vấn: <b>0825.893.162</b></p>
                <p><i class="fas fa-envelope" style="color: #34d399;"></i> Email: <b>support@garapro.vn</b></p>
                <p><i class="fas fa-globe" style="color: #34d399;"></i> Website: <b>www.garapro.vn</b></p>
            </div>
            <div class="footer-col">
                <h4>Thời gian hoạt động</h4>
                <p><i class="fas fa-calendar-day" style="color: #60a5fa;"></i> Thứ 2 - Thứ 7: <b>07:30 - 18:00</b></p>
                <p><i class="fas fa-calendar-week" style="color: #60a5fa;"></i> Chủ Nhật: <b>08:00 - 16:30</b></p>
                <p style="color: #34d399; font-weight: 700; margin-top: 12px;">
                    <i class="fas fa-truck-pickup"></i> Đội cứu hộ khẩn cấp giao thông Hà Nội 24/24: 0825.893.162
                </p>
            </div>
        </div>

        <div class="footer-bottom">
            &copy; 2026 GARAGE PRO HÀ NỘI - Hệ thống Quản trị Dịch vụ Gara Ô Tô Chuyên Nghiệp.
        </div>
    </footer>

    <script>
        // Xử lý ẩn thông báo flash mượt mà
        const flashMsg = document.getElementById("message_container");
        if (flashMsg) {
            setTimeout(function() {
                flashMsg.style.transition = "opacity 0.6s ease";
                flashMsg.style.opacity = "0";
                setTimeout(function() { flashMsg.style.display = "none"; }, 600);
            }, 6000);
        }

        // Chọn nhanh xe để đặt lịch hẹn
        function selectCarForBooking(plate, brand) {
            const plateInput = document.getElementById('landing_bien_so');
            const brandInput = document.getElementById('landing_hang_xe');
            const dateInput = document.getElementById('landing_ngay_hen');

            if (plateInput) plateInput.value = plate;
            if (brandInput) brandInput.value = brand;

            // Cuộn mượt mà đến form đặt lịch
            const formSection = document.getElementById('dat-lich');
            if (formSection) {
                formSection.scrollIntoView({ behavior: 'smooth' });
                setTimeout(() => {
                    if (dateInput) dateInput.focus();
                }, 500);
            }
        }

        // Đóng/mở dropdown menu khách hàng ở góc trên bên phải
        function toggleLandingUserMenu(e) {
            e.stopPropagation();
            const menu = document.getElementById('landingUserDropdown');
            if (menu) {
                menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
            }
        }

        function closeLandingDropdown() {
            const menu = document.getElementById('landingUserDropdown');
            if (menu) menu.style.display = 'none';
        }

        // Tự động đóng menu khi bấm ra ngoài
        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('customerDropdownWrapper');
            const menu = document.getElementById('landingUserDropdown');
            if (menu && wrapper && !wrapper.contains(e.target)) {
                menu.style.display = 'none';
            }
        });

        // Xử lý loading khi bấm đặt lịch hẹn (gửi Gmail + xuất PDF ngầm)
        function handleBookingSubmit(form) {
            const btn = document.getElementById('btnSubmitBooking');
            const notice = document.getElementById('bookingLoadingNotice');
            if (btn) {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Đang gửi yêu cầu...';
                btn.style.opacity = '0.8';
                btn.style.pointerEvents = 'none';
            }
            if (notice) {
                notice.style.display = 'block';
            }
            return true;
        }
    </script>
</body>
</html>
