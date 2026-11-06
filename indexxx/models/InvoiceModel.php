<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/CarModel.php';

class InvoiceModel extends BaseModel {
    private CarModel $carModel;

    public function __construct() {
        parent::__construct();
        $this->carModel = new CarModel();
    }

    /**
     * Định dạng mã hóa đơn theo quy tắc:
     * #HD + [2 số đầu của biển số xe] + [4 số giờ phút HHmm] + [3 số lượng đơn trong ngày reset theo ngày 001..999]
     * Ví dụ: Xe 30H-888.88 thanh toán lúc 12:59, đơn thứ 2 trong ngày -> #HD301259002
     */
    public function formatInvoiceCode(array $inv, bool $withHash = true): string {
        // 1. Lấy 2 chữ số đầu tiên của biển số xe khách
        $platePrefix = '00';
        $bienSo = $inv['bien_so'] ?? '';
        if (preg_match('/(\d{2})/', $bienSo, $m)) {
            $platePrefix = $m[1];
        }

        // 2. Lấy 4 chữ số cho giờ và phút (HHmm)
        $timeStr = !empty($inv['ngay_thanh_toan']) ? date('Hi', strtotime($inv['ngay_thanh_toan'])) : date('Hi');

        // 3. Lấy 3 chữ số cho số lượng đơn được reset theo từng ngày (001..999)
        $ngayThanhToan = $inv['ngay_thanh_toan'] ?? date('Y-m-d H:i:s');
        $maHd = (int)($inv['ma_hd'] ?? 0);
        $sql = "SELECT COUNT(*) FROM hoa_don WHERE DATE(ngay_thanh_toan) = DATE(?) AND ma_hd <= ?";
        $dailyCount = (int)$this->fetchColumn($sql, [$ngayThanhToan, $maHd]);
        if ($dailyCount <= 0) {
            $dailyCount = 1;
        }
        $dailyStr = str_pad((string)$dailyCount, 3, '0', STR_PAD_LEFT);

        return ($withHash ? '#HD' : 'HD') . $platePrefix . $timeStr . $dailyStr;
    }

    public function getAllInvoices(?string $keyword = null): array {
        $sql = "SELECT hd.*, 
                       psc.ma_xe, psc.tong_tien AS tong_tien_sua,
                       x.bien_so, x.hang_xe, x.dong_xe,
                       kh.ma_kh, nd_kh.ho_ten AS ten_chu_xe, nd_kh.sdt,
                       nd_nv.ho_ten AS ten_nv_thu
                FROM hoa_don hd
                JOIN phieu_sua_chua psc ON hd.ma_psc = psc.ma_psc
                JOIN xe x ON psc.ma_xe = x.ma_xe
                JOIN khach_hang kh ON x.ma_kh = kh.ma_kh
                JOIN nguoi_dung nd_kh ON kh.ma_nd = nd_kh.ma_nd
                LEFT JOIN nhan_vien nv ON hd.ma_nv = nv.ma_nv
                LEFT JOIN nguoi_dung nd_nv ON nv.ma_nd = nd_nv.ma_nd
                WHERE 1=1";
        $params = [];

        if (!empty($keyword)) {
            $sql .= " AND (x.bien_so LIKE ? OR nd_kh.ho_ten LIKE ? OR hd.ma_hd = ?)";
            $params[] = "%{$keyword}%";
            $params[] = "%{$keyword}%";
            $params[] = is_numeric($keyword) ? (int)$keyword : 0;
        }

        $sql .= " ORDER BY hd.ma_hd DESC";
        $rows = $this->fetchAll($sql, $params);
        foreach ($rows as &$r) {
            $r['ma_hd_code'] = $this->formatInvoiceCode($r, true);
        }
        return $rows;
    }

    public function getInvoicesByCustomerId(int $maKh): array {
        $sql = "SELECT hd.*, 
                       psc.ma_xe, psc.tong_tien AS tong_tien_sua,
                       x.bien_so, x.hang_xe, x.dong_xe,
                       nd_nv.ho_ten AS ten_nv_thu
                FROM hoa_don hd
                JOIN phieu_sua_chua psc ON hd.ma_psc = psc.ma_psc
                JOIN xe x ON psc.ma_xe = x.ma_xe
                LEFT JOIN nhan_vien nv ON hd.ma_nv = nv.ma_nv
                LEFT JOIN nguoi_dung nd_nv ON nv.ma_nd = nd_nv.ma_nd
                WHERE x.ma_kh = ?
                ORDER BY hd.ma_hd DESC";
        $rows = $this->fetchAll($sql, [$maKh]);
        foreach ($rows as &$r) {
            $r['ma_hd_code'] = $this->formatInvoiceCode($r, true);
        }
        return $rows;
    }

    public function getInvoiceById(int $id): ?array {
        $sql = "SELECT hd.*, 
                       psc.ma_xe, psc.ngay_lap AS ngay_lap_psc, psc.tong_tien AS tong_tien_sua,
                       x.bien_so, x.hang_xe, x.dong_xe, x.nam_san_xuat, x.tien_no,
                       kh.ma_kh, nd_kh.ho_ten AS ten_chu_xe, nd_kh.sdt AS sdt_chu_xe, nd_kh.email AS email_chu_xe, kh.dia_chi,
                       nd_nv.ho_ten AS ten_nv_thu, nv.chuc_vu
                FROM hoa_don hd
                JOIN phieu_sua_chua psc ON hd.ma_psc = psc.ma_psc
                JOIN xe x ON psc.ma_xe = x.ma_xe
                JOIN khach_hang kh ON x.ma_kh = kh.ma_kh
                JOIN nguoi_dung nd_kh ON kh.ma_nd = nd_kh.ma_nd
                LEFT JOIN nhan_vien nv ON hd.ma_nv = nv.ma_nv
                LEFT JOIN nguoi_dung nd_nv ON nv.ma_nd = nd_nv.ma_nd
                WHERE hd.ma_hd = ?";
        $inv = $this->fetchOne($sql, [$id]);
        if ($inv) {
            $inv['ma_hd_code'] = $this->formatInvoiceCode($inv, true);
        }
        return $inv;
    }

    /**
     * Lấy hóa đơn thanh toán theo mã phiếu sửa chữa
     */
    public function getInvoiceByRepairId(int $maPsc): ?array {
        $sql = "SELECT hd.*, 
                       psc.ma_xe, psc.ngay_lap AS ngay_lap_psc, psc.tong_tien AS tong_tien_sua,
                       x.bien_so, x.hang_xe, x.dong_xe, x.nam_san_xuat, x.tien_no,
                       kh.ma_kh, nd_kh.ho_ten AS ten_chu_xe, nd_kh.sdt AS sdt_chu_xe, nd_kh.email AS email_chu_xe, kh.dia_chi,
                       nd_nv.ho_ten AS ten_nv_thu, nv.chuc_vu
                FROM hoa_don hd
                JOIN phieu_sua_chua psc ON hd.ma_psc = psc.ma_psc
                JOIN xe x ON psc.ma_xe = x.ma_xe
                JOIN khach_hang kh ON x.ma_kh = kh.ma_kh
                JOIN nguoi_dung nd_kh ON kh.ma_nd = nd_kh.ma_nd
                LEFT JOIN nhan_vien nv ON hd.ma_nv = nv.ma_nv
                LEFT JOIN nguoi_dung nd_nv ON nv.ma_nd = nd_nv.ma_nd
                WHERE hd.ma_psc = ?
                ORDER BY hd.ma_hd DESC LIMIT 1";
        $inv = $this->fetchOne($sql, [$maPsc]);
        if ($inv) {
            $inv['ma_hd_code'] = $this->formatInvoiceCode($inv, true);
        }
        return $inv;
    }

    public function getCarPaymentInfo(string $bienSo): ?array {
        $car = $this->carModel->getCarByPlate($bienSo);
        if (!$car) {
            return null;
        }

        // Lấy danh sách phiếu sửa chữa chưa thanh toán hoặc gần đây
        $sqlPsc = "SELECT * FROM phieu_sua_chua WHERE ma_xe = ? ORDER BY ma_psc DESC";
        $repairs = $this->fetchAll($sqlPsc, [$car['ma_xe']]);

        return [
            'car' => $car,
            'repairs' => $repairs
        ];
    }

    public function createInvoice(int $maPsc, int $maNv, float $soTienThu, string $phuongThuc = 'TienMat'): array {
        if ($soTienThu <= 0) {
            return ['success' => false, 'message' => 'Số tiền thu phải lớn hơn 0!'];
        }

        $psc = $this->fetchOne("SELECT psc.*, x.tien_no, x.bien_so FROM phieu_sua_chua psc JOIN xe x ON psc.ma_xe = x.ma_xe WHERE psc.ma_psc = ?", [$maPsc]);
        if (!$psc) {
            return ['success' => false, 'message' => 'Phiếu sửa chữa không tồn tại!'];
        }

        $tienNo = (float)$psc['tien_no'];
        // Quy định Gara: Số tiền thu không được vượt quá số tiền nợ
        if ($soTienThu > $tienNo && $tienNo > 0) {
            return [
                'success' => false,
                'message' => "Số tiền thu (" . number_format($soTienThu) . " đ) vượt quá tiền nợ hiện tại của xe (" . number_format($tienNo) . " đ)!"
            ];
        }

        $this->beginTransaction();
        try {
            // 1. Tạo hóa đơn
            $sqlHd = "INSERT INTO hoa_don (ma_psc, ma_nv, ngay_thanh_toan, tong_tien, phuong_thuc, trang_thai) 
                      VALUES (?, ?, NOW(), ?, ?, 'DaThanhToan')";
            $this->execute($sqlHd, [$maPsc, $maNv, $soTienThu, $phuongThuc]);
            $maHd = (int)$this->lastInsertId();

            // 2. Trừ tiền nợ của xe
            $this->execute("UPDATE xe SET tien_no = GREATEST(0, tien_no - ?) WHERE ma_xe = ?", [$soTienThu, $psc['ma_xe']]);

            // 3. Cập nhật trạng thái phiếu sửa chữa thành Đã thanh toán
            $this->execute("UPDATE phieu_sua_chua SET trang_thai = 'DaThanhToan' WHERE ma_psc = ?", [$maPsc]);

            $this->commit();

            $invNew = $this->getInvoiceById($maHd);
            $maHdCode = $invNew['ma_hd_code'] ?? "#HD{$maHd}";

            return [
                'success' => true,
                'ma_hd' => $maHd,
                'ma_hd_code' => $maHdCode,
                'message' => "Lập phiếu thu thành công " . number_format($soTienThu) . " đ cho xe {$psc['bien_so']}! Mã hóa đơn: {$maHdCode}"
            ];
        } catch (Exception $e) {
            $this->rollBack();
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    /**
     * Cập nhật phương thức thanh toán của hóa đơn (Tiền mặt hoặc Chuyển khoản)
     */
    public function updatePaymentMethod(int $maHd, string $phuongThuc): bool {
        if (!in_array($phuongThuc, ['TienMat', 'ChuyenKhoan'], true)) {
            return false;
        }
        $stmt = $this->db->prepare("UPDATE hoa_don SET phuong_thuc = ? WHERE ma_hd = ?");
        return $stmt->execute([$phuongThuc, $maHd]);
    }
}

