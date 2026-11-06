<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/RepairModel.php';
require_once __DIR__ . '/../models/CarModel.php';
require_once __DIR__ . '/../models/ServiceModel.php';
require_once __DIR__ . '/../models/PartModel.php';
require_once __DIR__ . '/../models/InvoiceModel.php';

class RepairController extends BaseController {
    private RepairModel $repairModel;
    private CarModel $carModel;
    private ServiceModel $serviceModel;
    private PartModel $partModel;
    private InvoiceModel $invoiceModel;

    public function __construct() {
        parent::__construct();
        $this->repairModel = new RepairModel();
        $this->carModel = new CarModel();
        $this->serviceModel = new ServiceModel();
        $this->partModel = new PartModel();
        $this->invoiceModel = new InvoiceModel();
    }

    public function index(): void {
        $this->requireRole(['TiepNhan', 'KyThuat', 'QuanLy']);
        $status = $_GET['status'] ?? null;
        $keyword = $_GET['keyword'] ?? null;

        $repairs = $this->repairModel->getAllRepairs($status, $keyword);
        $this->render('repair/index', [
            'repairs' => $repairs,
            'currentStatus' => $status,
            'keyword' => $keyword
        ]);
    }

    public function create(): void {
        $this->requireRole(['TiepNhan', 'KyThuat', 'QuanLy']);
        $cars = $this->carModel->getAllCars();
        $this->render('repair/create', ['cars' => $cars]);
    }

    public function store(): void {
        $this->requireRole(['TiepNhan', 'KyThuat', 'QuanLy']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maXe = (int)($_POST['ma_xe'] ?? 0);
            $maNv = (int)($_SESSION['user']['ma_nv'] ?? 1);

            if ($maXe <= 0) {
                $this->setFlash('danger', 'Vui lòng chọn xe cần lập phiếu sửa chữa.');
                $this->redirect('index.php?c=repair&a=create');
            }

            $maPsc = $this->repairModel->createRepair($maXe, $maNv);
            $this->setFlash('success', "Khởi tạo phiếu sửa chữa #{$maPsc} thành công!");
            $this->redirect('index.php?c=repair&a=detail&id=' . $maPsc);
        }
        $this->redirect('index.php?c=repair&a=create');
    }

    public function detail(): void {
        $this->requireRole(['TiepNhan', 'KyThuat', 'QuanLy']);
        $id = (int)($_GET['id'] ?? 0);
        $psc = $this->repairModel->getRepairById($id);

        if (!$psc) {
            $this->setFlash('danger', 'Không tìm thấy phiếu sửa chữa.');
            $this->redirect('index.php?c=repair&a=index');
        }

        $services = $this->repairModel->getServicesByRepairId($id);
        $parts = $this->repairModel->getPartsByRepairId($id);
        $allServices = $this->serviceModel->getAllServices(true);
        $allParts = $this->partModel->getAllParts();
        $invoice = $this->invoiceModel->getInvoiceByRepairId($id);

        $this->render('repair/detail', [
            'psc'         => $psc,
            'services'    => $services,
            'parts'       => $parts,
            'allServices' => $allServices,
            'allParts'    => $allParts,
            'invoice'     => $invoice
        ]);
    }

    public function addService(): void {
        $this->requireRole(['TiepNhan', 'KyThuat', 'QuanLy']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maPsc   = (int)($_POST['ma_psc'] ?? 0);
            $serviceInput = trim($_POST['ten_dv'] ?? '');
            if (empty($serviceInput) && !empty($_POST['ma_dv'])) {
                $serviceInput = (int)$_POST['ma_dv'];
            }
            $soLuong = max(1, (int)($_POST['so_luong'] ?? 1));
            $donGia  = (isset($_POST['don_gia']) && $_POST['don_gia'] !== '') ? (float)$_POST['don_gia'] : null;

            $res = $this->repairModel->addServiceItem($maPsc, $serviceInput, $soLuong, $donGia);
            if ($res['success']) {
                $this->setFlash('success', $res['message']);
            } else {
                $this->setFlash('danger', $res['message']);
            }
            $this->redirect('index.php?c=repair&a=detail&id=' . $maPsc);
        }
    }

    public function deleteService(): void {
        $this->requireRole(['TiepNhan', 'KyThuat', 'QuanLy']);
        $maCtdv = (int)($_GET['id'] ?? 0);
        $res = $this->repairModel->deleteServiceItem($maCtdv);
        if ($res['success']) {
            $this->setFlash('success', $res['message']);
            $this->redirect('index.php?c=repair&a=detail&id=' . $res['ma_psc']);
        } else {
            $this->setFlash('danger', $res['message']);
            $this->redirect('index.php?c=repair&a=index');
        }
    }

    public function addPart(): void {
        $this->requireRole(['TiepNhan', 'KyThuat', 'ThuKho', 'QuanLy']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maPsc   = (int)($_POST['ma_psc'] ?? 0);
            $partInput = trim($_POST['ten_pt'] ?? '');
            if (empty($partInput) && !empty($_POST['ma_pt'])) {
                $partInput = (int)$_POST['ma_pt'];
            }
            $soLuong = max(1, (int)($_POST['so_luong'] ?? 1));
            $donGia  = (isset($_POST['don_gia']) && $_POST['don_gia'] !== '') ? (float)$_POST['don_gia'] : null;

            $res = $this->repairModel->addPartItem($maPsc, $partInput, $soLuong, $donGia);
            if ($res['success']) {
                $this->setFlash('success', $res['message']);
            } else {
                $this->setFlash('danger', $res['message']);
            }
            $this->redirect('index.php?c=repair&a=detail&id=' . $maPsc);
        }
    }

    public function deletePart(): void {
        $this->requireRole(['TiepNhan', 'KyThuat', 'ThuKho', 'QuanLy']);
        $maCtpt = (int)($_GET['id'] ?? 0);
        $res = $this->repairModel->deletePartItem($maCtpt);
        if ($res['success']) {
            $this->setFlash('success', $res['message']);
            $this->redirect('index.php?c=repair&a=detail&id=' . $res['ma_psc']);
        } else {
            $this->setFlash('danger', $res['message']);
            $this->redirect('index.php?c=repair&a=index');
        }
    }

    public function updateStatus(): void {
        $this->requireRole(['TiepNhan', 'KyThuat', 'QuanLy']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maPsc = (int)($_POST['ma_psc'] ?? 0);
            $status = trim($_POST['trang_thai'] ?? '');

            if ($this->repairModel->updateStatus($maPsc, $status)) {
                $emailNotice = "";
                if ($status === 'HoanThanh') {
                    // Tự động tạo file PDF và gửi email cho khách hàng khi xe hoàn thành!
                    require_once __DIR__ . '/../models/EmailService.php';
                    $emailService = new EmailService();
                    $sendRes = $emailService->sendRepairCompletedNotice($maPsc);
                    if ($sendRes['success']) {
                        $emailNotice = " " . $sendRes['message'];
                    }
                }
                $this->setFlash('success', "Cập nhật trạng thái phiếu #{$maPsc} thành '{$status}' thành công!{$emailNotice}");
            } else {
                $this->setFlash('danger', 'Cập nhật trạng thái thất bại.');
            }
            $this->redirect('index.php?c=repair&a=detail&id=' . $maPsc);
        }
    }

    /**
     * Thu tiền và quyết toán thanh toán trực tiếp ngay trên Phiếu Sửa Chữa (Hợp nhất 2 trong 1)
     */
    public function pay(): void {
        $this->requireRole(['TiepNhan', 'KyThuat', 'QuanLy']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maPsc      = (int)($_POST['ma_psc'] ?? 0);
            $soTienThu  = (float)($_POST['so_tien_thu'] ?? 0);
            $phuongThuc = trim($_POST['phuong_thuc'] ?? 'TienMat');
            $maNv       = (int)($_SESSION['user']['ma_nv'] ?? 1);

            if ($maPsc <= 0 || $soTienThu <= 0) {
                $this->setFlash('danger', 'Vui lòng nhập số tiền thanh toán hợp lệ (lớn hơn 0).');
                $this->redirect('index.php?c=repair&a=detail&id=' . $maPsc);
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
                    // Tránh gián đoạn nếu lỗi SMTP
                }

                $this->setFlash('success', "Thanh toán thành công! Đã tạo hóa đơn {$res['ma_hd_code']} và hoàn tất phiếu sửa chữa #{$maPsc}.{$emailNotice}");
            } else {
                $this->setFlash('danger', $res['message']);
            }
            $this->redirect('index.php?c=repair&a=detail&id=' . $maPsc);
        }
        $this->redirect('index.php?c=repair&a=index');
    }

    public function downloadPdf(): void {
        $this->requireRole(['TiepNhan', 'KyThuat', 'QuanLy', 'KhachHang']);
        $id = (int)($_GET['id'] ?? 0);
        $psc = $this->repairModel->getRepairById($id);
        if (!$psc) {
            die('Không tìm thấy phiếu sửa chữa');
        }

        $user = $this->getCurrentUser();
        $role = $user['vai_tro'] ?? '';
        if ($role === 'KhachHang') {
            $maKh = (int)($user['ma_kh'] ?? 0);
            if ((int)$psc['ma_kh'] !== $maKh) {
                die('Bạn không có quyền tải phiếu sửa chữa này!');
            }
        }

        require_once __DIR__ . '/../models/PdfService.php';
        $pdfService = new PdfService();
        $pdfPath = $pdfService->generateRepairPdf($id);

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

    public function print(): void {
        $this->requireRole(['TiepNhan', 'KyThuat', 'QuanLy', 'KhachHang']);
        $id = (int)($_GET['id'] ?? 0);
        $psc = $this->repairModel->getRepairById($id);
        if (!$psc) {
            die('Không tìm thấy phiếu sửa chữa');
        }

        $user = $this->getCurrentUser();
        $role = $user['vai_tro'] ?? '';
        $isStaff = in_array($role, ['QuanLy', 'NhanVien', 'TiepNhan', 'KyThuat', 'ThuKho'], true);
        if ($role === 'KhachHang') {
            $maKh = (int)($user['ma_kh'] ?? 0);
            if ((int)$psc['ma_kh'] !== $maKh) {
                die('Bạn không có quyền truy cập phiếu sửa chữa này!');
            }
        }

        $services = $this->repairModel->getServicesByRepairId($id);
        $parts = $this->repairModel->getPartsByRepairId($id);
        $invoice = $this->invoiceModel->getInvoiceByRepairId($id);

        $this->render('repair/print', [
            'psc'      => $psc,
            'services' => $services,
            'parts'    => $parts,
            'invoice'  => $invoice,
            'isStaff'  => $isStaff
        ], 'none');
    }
}

