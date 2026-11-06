<?php
require_once __DIR__ . '/BaseModel.php';

class ReportModel extends BaseModel {

    public function getRevenueReport(int $month, int $year): array {
        // 1. Báo cáo doanh thu trên tổng khách hàng
        $sqlCustomers = "SELECT kh.ma_kh,
                                nd.ho_ten AS ten_khach_hang,
                                nd.sdt,
                                nd.email,
                                GROUP_CONCAT(DISTINCT CONCAT(x.bien_so, ' (', x.hang_xe, ')') SEPARATOR ', ') AS danh_sach_xe,
                                COUNT(DISTINCT psc.ma_psc) AS so_luot_sua,
                                COALESCE(SUM(psc.tong_tien), 0) AS thanh_tien
                         FROM phieu_sua_chua psc
                         JOIN xe x ON psc.ma_xe = x.ma_xe
                         JOIN khach_hang kh ON x.ma_kh = kh.ma_kh
                         JOIN nguoi_dung nd ON kh.ma_nd = nd.ma_nd
                         WHERE MONTH(psc.ngay_lap) = ? AND YEAR(psc.ngay_lap) = ?
                         GROUP BY kh.ma_kh, nd.ho_ten, nd.sdt, nd.email
                         ORDER BY thanh_tien DESC";
        $customerDetails = $this->fetchAll($sqlCustomers, [$month, $year]);

        $totalRevenue = 0;
        $totalRepairs = 0;
        foreach ($customerDetails as $c) {
            $totalRevenue += (float)$c['thanh_tien'];
            $totalRepairs += (int)$c['so_luot_sua'];
        }
        $totalCustomers = count($customerDetails);
        $avgPerCustomer = $totalCustomers > 0 ? round($totalRevenue / $totalCustomers) : 0;

        foreach ($customerDetails as &$c) {
            $c['ti_le'] = $totalRevenue > 0 ? round(((float)$c['thanh_tien'] / $totalRevenue) * 100, 2) : 0;
        }
        unset($c);

        // 2. Chi tiết theo hiệu xe (bổ trợ tham khảo)
        $sqlBrands = "SELECT x.hang_xe, 
                             COUNT(DISTINCT psc.ma_psc) AS so_luot_sua,
                             COALESCE(SUM(psc.tong_tien), 0) AS thanh_tien
                      FROM phieu_sua_chua psc
                      JOIN xe x ON psc.ma_xe = x.ma_xe
                      WHERE MONTH(psc.ngay_lap) = ? AND YEAR(psc.ngay_lap) = ?
                      GROUP BY x.hang_xe
                      ORDER BY thanh_tien DESC";
        $brandDetails = $this->fetchAll($sqlBrands, [$month, $year]);
        foreach ($brandDetails as &$b) {
            $b['ti_le'] = $totalRevenue > 0 ? round(((float)$b['thanh_tien'] / $totalRevenue) * 100, 2) : 0;
        }
        unset($b);

        return [
            'month'            => $month,
            'year'             => $year,
            'total_revenue'    => $totalRevenue,
            'total_repairs'    => $totalRepairs,
            'total_customers'  => $totalCustomers,
            'avg_per_customer' => $avgPerCustomer,
            'customer_details' => $customerDetails,
            'brand_details'    => $brandDetails,
            'details'          => $customerDetails
        ];
    }

    public function getInventoryReport(int $month, int $year): array {
        // Báo cáo nhập xuất tồn theo tháng và năm
        $sql = "SELECT pt.ma_pt, pt.ten_pt, pt.don_vi_tinh, pt.so_luong_ton, pt.gia_nhap, pt.gia_ban,
                       COALESCE(nhap.tong_nhap, 0) AS phat_sinh_nhap,
                       COALESCE(xuat.tong_xuat, 0) AS phat_sinh_xuat
                FROM phu_tung pt
                LEFT JOIN (
                    SELECT ctn.ma_pt, SUM(ctn.so_luong) AS tong_nhap
                    FROM chi_tiet_nhap ctn
                    JOIN phieu_nhap pn ON ctn.ma_pn = pn.ma_pn
                    WHERE MONTH(pn.ngay_nhap) = ? AND YEAR(pn.ngay_nhap) = ?
                    GROUP BY ctn.ma_pt
                ) nhap ON pt.ma_pt = nhap.ma_pt
                LEFT JOIN (
                    SELECT ctpt.ma_pt, SUM(ctpt.so_luong) AS tong_xuat
                    FROM chi_tiet_phu_tung ctpt
                    JOIN phieu_sua_chua psc ON ctpt.ma_psc = psc.ma_psc
                    WHERE MONTH(psc.ngay_lap) = ? AND YEAR(psc.ngay_lap) = ?
                    GROUP BY ctpt.ma_pt
                ) xuat ON pt.ma_pt = xuat.ma_pt
                ORDER BY pt.ten_pt ASC";

        $rows = $this->fetchAll($sql, [$month, $year, $month, $year]);

        $result = [];
        foreach ($rows as $r) {
            $tonCuoi = (int)$r['so_luong_ton'];
            $nhap = (int)$r['phat_sinh_nhap'];
            $xuat = (int)$r['phat_sinh_xuat'];
            // Ước tính tồn đầu = tồn cuối - nhập + xuất
            $tonDau = max(0, $tonCuoi - $nhap + $xuat);

            $result[] = [
                'ma_pt' => $r['ma_pt'],
                'ten_pt' => $r['ten_pt'],
                'don_vi_tinh' => $r['don_vi_tinh'],
                'ton_dau' => $tonDau,
                'phat_sinh_nhap' => $nhap,
                'phat_sinh_xuat' => $xuat,
                'ton_cuoi' => $tonCuoi,
                'gia_nhap' => $r['gia_nhap']
            ];
        }

        return [
            'month' => $month,
            'year' => $year,
            'items' => $result
        ];
    }

    public function getDashboardStats(): array {
        $today = date('Y-m-d');
        $curMonth = (int)date('m');
        $curYear = (int)date('Y');

        // 1. Số xe tiếp nhận hôm nay
        $receptionCount = (int)($this->fetchOne(
            "SELECT COUNT(*) AS cnt FROM phieu_sua_chua WHERE DATE(ngay_lap) = ?",
            [$today]
        )['cnt'] ?? 0);

        // 2. Số phiếu đang sửa chữa
        $repairingCount = (int)($this->fetchOne(
            "SELECT COUNT(*) AS cnt FROM phieu_sua_chua WHERE trang_thai IN ('DangKiemTra', 'ChoKhachDuyet', 'DangSuaChua')"
        )['cnt'] ?? 0);

        // 3. Doanh thu tháng này
        $monthlyRevenue = (float)($this->fetchOne(
            "SELECT COALESCE(SUM(tong_tien), 0) AS total FROM hoa_don WHERE MONTH(ngay_thanh_toan) = ? AND YEAR(ngay_thanh_toan) = ?",
            [$curMonth, $curYear]
        )['total'] ?? 0);

        // 4. Số phụ tùng cảnh báo tồn kho thấp
        $lowStockCount = (int)($this->fetchOne(
            "SELECT COUNT(*) AS cnt FROM phu_tung WHERE so_luong_ton <= muc_canh_bao"
        )['cnt'] ?? 0);

        // 5. Phiếu sửa chữa gần đây (5 phiếu)
        $recentRepairs = $this->fetchAll(
            "SELECT psc.*, x.bien_so, x.hang_xe, nd.ho_ten AS ten_chu_xe
             FROM phieu_sua_chua psc
             JOIN xe x ON psc.ma_xe = x.ma_xe
             JOIN khach_hang kh ON x.ma_kh = kh.ma_kh
             JOIN nguoi_dung nd ON kh.ma_nd = nd.ma_nd
             ORDER BY psc.ma_psc DESC LIMIT 5"
        );

        // 6. Lịch hẹn chờ KTV duyệt
        $pendingAppointments = $this->fetchAll(
            "SELECT lh.*, x.bien_so, x.hang_xe, nd.ho_ten AS ten_chu_xe, nd.sdt
             FROM lich_hen lh
             JOIN xe x ON lh.ma_xe = x.ma_xe
             JOIN khach_hang kh ON lh.ma_kh = kh.ma_kh
             JOIN nguoi_dung nd ON kh.ma_nd = nd.ma_nd
             WHERE lh.trang_thai = 'ChoXacNhan'
             ORDER BY lh.ma_lh DESC LIMIT 5"
        );

        return [
            'today_receptions'          => $receptionCount,
            'repairing_count'           => $repairingCount,
            'monthly_revenue'           => $monthlyRevenue,
            'low_stock_count'           => $lowStockCount,
            'recent_repairs'            => $recentRepairs,
            'pending_appointments'      => $pendingAppointments,
            'pending_appointment_count' => count($pendingAppointments)
        ];
    }
}

