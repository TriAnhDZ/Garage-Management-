<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/InvoiceModel.php';
require_once __DIR__ . '/../models/CarModel.php';
require_once __DIR__ . '/../models/RepairModel.php';

class InvoiceController extends BaseController {
    private InvoiceModel $invoiceModel;
    private CarModel $carModel;
    private RepairModel $repairModel;

    public function __construct() {
        parent::__construct();
        $this->invoiceModel = new InvoiceModel();
        $this->carModel = new CarModel();
        $this->repairModel = new RepairModel();
    }

    public function index(): void {
        $this->requireRole(['TiepNhan', 'QuanLy']);
        $keyword = $_GET['keyword'] ?? null;
        $invoices = $this->invoiceModel->getAllInvoices($keyword);

        $this->render('invoice/index', [
            'invoices' => $invoices,
            'keyword'  => $keyword
        ]);
    }

    public function create(): void {
        $this->requireRole(['TiepNhan', 'QuanLy']);
        $bienSo = trim($_GET['bien_so'] ?? '');
        $paymentInfo = null;

        if (!empty($bienSo)) {
            $paymentInfo = $this->invoiceModel->getCarPaymentInfo($bienSo);
            if (!$paymentInfo) {
                $this->setFlash('warning', "Không tìm thấy thông tin xe có biển số: {$bienSo}");
            }
        }

        $allCars = $this->carModel->getAllCars();

        $this->render('invoice/create', [
            'bienSo'      => $bienSo,
            'paymentInfo' => $paymentInfo,
            'allCars'     => $allCars
        ]);
    }

    public function store(): void {
        $this->requireRole(['TiepNhan', 'QuanLy']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maPsc      = (int)($_POST['ma_psc'] ?? 0);
            $soTienThu  = (float)($_POST['so_tien_thu'] ?? 0);
            $phuongThuc = trim($_POST['phuong_thuc'] ?? 'TienMat');
            $maNv       = (int)($_SESSION['user']['ma_nv'] ?? 1);

            if ($maPsc <= 0 || $soTienThu <= 0) {
                $this->setFlash('danger', 'Vui lòng chọn phiếu sửa chữa và nhập số tiền thu hợp lệ.');
                $this->redirect('index.php?c=invoice&a=create');
            }

            $res = $this->invoiceModel->createInvoice($maPsc, $maNv, $soTienThu, $phuongThuc);
            if ($res['success']) {
                $emailNotice = "";
                // Tự động gửi email biên nhận thanh toán kèm file PDF 1 trang chính thức cho khách hàng
                try {
                    require_once __DIR__ . '/../models/EmailService.php';
                    $emailService = new EmailService();
                    $emailRes = $emailService->sendInvoiceReceipt((int)$res['ma_hd']);
                    if (!empty($emailRes['success'])) {
                        $emailNotice = " " . $emailRes['message'];
                    }
                } catch (Exception $e) {
                    // Tránh gián đoạn nếu SMTP mạng tạm thời
                }

                $this->setFlash('success', $res['message'] . $emailNotice);
                $this->redirect('index.php?c=invoice&a=print&id=' . $res['ma_hd']);
            } else {
                $this->setFlash('danger', $res['message']);
                $this->redirect('index.php?c=invoice&a=create');
            }
        }
        $this->redirect('index.php?c=invoice&a=create');
    }

    /**
     * Chỉnh sửa phương thức thanh toán của hóa đơn (Tiền mặt / Chuyển khoản)
     */
    public function updatePaymentMethod(): void {
        $this->requireRole(['TiepNhan', 'QuanLy']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maHd       = (int)($_POST['ma_hd'] ?? 0);
            $phuongThuc = trim($_POST['phuong_thuc'] ?? '');
            $returnTo   = trim($_POST['return_to'] ?? '');

            if ($maHd > 0 && in_array($phuongThuc, ['TienMat', 'ChuyenKhoan'], true)) {
                if ($this->invoiceModel->updatePaymentMethod($maHd, $phuongThuc)) {
                    $ptText = ($phuongThuc === 'ChuyenKhoan') ? 'Chuyển khoản' : 'Tiền mặt';
                    $this->setFlash('success', "Đã cập nhật phương thức thanh toán của hóa đơn sang: <b>{$ptText}</b> thành công!");
                } else {
                    $this->setFlash('danger', 'Cập nhật phương thức thanh toán thất bại.');
                }
            } else {
                $this->setFlash('danger', 'Dữ liệu phương thức thanh toán không hợp lệ.');
            }

            if ($returnTo === 'print' && $maHd > 0) {
                $this->redirect('index.php?c=invoice&a=print&id=' . $maHd);
                return;
            }
        }
        $this->redirect('index.php?c=invoice&a=index');
    }

    public function print(): void {
        $this->requireRole(['TiepNhan', 'QuanLy', 'KhachHang']);
        $id = (int)($_GET['id'] ?? 0);
        $invoice = $this->invoiceModel->getInvoiceById($id);
        if (!$invoice) {
            die('Không tìm thấy hóa đơn');
        }

        $user = $this->getCurrentUser();
        $role = $user['vai_tro'] ?? '';
        $isStaff = in_array($role, ['QuanLy', 'NhanVien', 'TiepNhan', 'KyThuat', 'ThuKho'], true);
        if ($role === 'KhachHang') {
            $maKh = (int)($user['ma_kh'] ?? 0);
            if ((int)$invoice['ma_kh'] !== $maKh) {
                die('Bạn không có quyền truy cập hóa đơn này!');
            }
        }

        $services = $this->repairModel->getServicesByRepairId((int)$invoice['ma_psc']);
        $parts = $this->repairModel->getPartsByRepairId((int)$invoice['ma_psc']);

        $this->render('invoice/print', [
            'invoice'  => $invoice,
            'services' => $services,
            'parts'    => $parts,
            'isStaff'  => $isStaff
        ], 'none');
    }

    public function downloadPdf(): void {
        $this->requireRole(['TiepNhan', 'QuanLy', 'KhachHang']);
        $id = (int)($_GET['id'] ?? 0);
        $invoice = $this->invoiceModel->getInvoiceById($id);
        if (!$invoice) {
            die('Không tìm thấy hóa đơn');
        }

        $user = $this->getCurrentUser();
        $role = $user['vai_tro'] ?? '';
        if ($role === 'KhachHang') {
            $maKh = (int)($user['ma_kh'] ?? 0);
            if ((int)$invoice['ma_kh'] !== $maKh) {
                die('Bạn không có quyền tải hóa đơn này!');
            }
        }

        require_once __DIR__ . '/../models/PdfService.php';
        $pdfService = new PdfService();
        $pdfPath = $pdfService->generateInvoicePdf($id);

        if (file_exists($pdfPath)) {
            $filename = basename($pdfPath);
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($pdfPath));
            readfile($pdfPath);
            exit;
        } else {
            die('Không thể tạo file PDF');
        }
    }
}

