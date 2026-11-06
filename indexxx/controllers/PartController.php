<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/PartModel.php';
require_once __DIR__ . '/../models/WarehouseModel.php';

class PartController extends BaseController {
    private PartModel $partModel;
    private WarehouseModel $warehouseModel;

    public function __construct() {
        parent::__construct();
        $this->partModel = new PartModel();
        $this->warehouseModel = new WarehouseModel();
    }

    public function index(): void {
        $this->requireRole(['ThuKho', 'KyThuat', 'QuanLy']);
        $selectedKho = !empty($_GET['kho']) ? (int)$_GET['kho'] : null;
        $keyword = trim($_GET['keyword'] ?? '');
        $status = trim($_GET['status'] ?? '');

        $warehouses = $this->warehouseModel->getWarehouseStockSummary();
        $allParts = $this->partModel->getAllParts();
        $parts = $this->partModel->getAllParts($selectedKho);
        $lowStock = $this->partModel->getLowStockParts($selectedKho);

        // Lọc theo từ khóa tìm kiếm nếu có
        if (!empty($keyword)) {
            $parts = array_filter($parts, function($p) use ($keyword) {
                return (stripos($p['ten_pt'], $keyword) !== false)
                    || (stripos($p['ten_ncc'], $keyword) !== false)
                    || (stripos((string)$p['ma_pt'], $keyword) !== false);
            });
        }

        // Lọc theo trạng thái tồn kho nếu có
        if ($status === 'low_stock') {
            $parts = array_filter($parts, function($p) {
                return ((int)$p['so_luong_ton'] <= (int)$p['muc_canh_bao']);
            });
        }

        $this->render('part/index', [
            'parts'       => $parts,
            'allParts'    => $allParts,
            'lowStock'    => $lowStock,
            'warehouses'  => $warehouses,
            'selectedKho' => $selectedKho,
            'keyword'     => $keyword,
            'status'      => $status
        ]);
    }

    public function create(): void {
        $this->requireRole(['ThuKho', 'QuanLy']);
        $suppliers = $this->partModel->getAllSuppliers();
        $warehouses = $this->warehouseModel->getAllWarehouses();
        $this->render('part/create', [
            'suppliers'  => $suppliers,
            'warehouses' => $warehouses
        ]);
    }

    public function store(): void {
        $this->requireRole(['ThuKho', 'QuanLy']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name         = trim($_POST['ten_pt'] ?? '');
            $maNcc        = (int)($_POST['ma_ncc'] ?? 0);
            $maKho        = (int)($_POST['ma_kho'] ?? 1);
            $unit         = trim($_POST['don_vi_tinh'] ?? 'Cái');
            $importPrice  = (float)($_POST['gia_nhap'] ?? 0);
            $sellPrice    = !empty($_POST['gia_ban']) ? (float)$_POST['gia_ban'] : null;
            $initStock    = max(0, (int)($_POST['so_luong_ton'] ?? 0));
            $warningLevel = max(1, (int)($_POST['muc_canh_bao'] ?? 5));

            if (empty($name) || $maNcc <= 0 || $importPrice <= 0) {
                $this->setFlash('danger', 'Vui lòng điền đầy đủ Tên phụ tùng, Nhà cung cấp và Giá nhập hợp lệ.');
                $this->redirect('index.php?c=part&a=create');
            }

            $res = $this->partModel->createPart($name, $maNcc, $unit, $importPrice, $sellPrice, $initStock, $warningLevel, $maKho);
            if ($res['success']) {
                $this->setFlash('success', $res['message']);
                $this->redirect('index.php?c=part&a=index');
            } else {
                $this->setFlash('danger', $res['message']);
                $this->redirect('index.php?c=part&a=create');
            }
        }
    }

    public function edit(): void {
        $this->requireRole(['ThuKho', 'QuanLy']);
        $id = (int)($_GET['id'] ?? 0);
        $part = $this->partModel->getPartById($id);
        if (!$part) {
            $this->setFlash('danger', 'Không tìm thấy phụ tùng');
            $this->redirect('index.php?c=part&a=index');
        }
        $suppliers = $this->partModel->getAllSuppliers();
        $warehouses = $this->warehouseModel->getAllWarehouses();
        $this->render('part/edit', [
            'part'       => $part, 
            'suppliers'  => $suppliers,
            'warehouses' => $warehouses
        ]);
    }

    public function update(): void {
        $this->requireRole(['ThuKho', 'QuanLy']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id           = (int)($_POST['ma_pt'] ?? 0);
            $name         = trim($_POST['ten_pt'] ?? '');
            $maNcc        = (int)($_POST['ma_ncc'] ?? 0);
            $maKho        = (int)($_POST['ma_kho'] ?? 1);
            $unit         = trim($_POST['don_vi_tinh'] ?? 'Cái');
            $importPrice  = (float)($_POST['gia_nhap'] ?? 0);
            $sellPrice    = (float)($_POST['gia_ban'] ?? 0);
            $warningLevel = max(1, (int)($_POST['muc_canh_bao'] ?? 5));

            if ($this->partModel->updatePart($id, $name, $maNcc, $unit, $importPrice, $sellPrice, $warningLevel, $maKho)) {
                $this->setFlash('success', "Cập nhật phụ tùng '{$name}' thành công!");
            } else {
                $this->setFlash('danger', 'Cập nhật thất bại.');
            }
            $this->redirect('index.php?c=part&a=index');
        }
    }

    public function delete(): void {
        $this->requireRole(['ThuKho', 'QuanLy']);
        $id = (int)($_GET['id'] ?? 0);
        if ($this->partModel->deletePart($id)) {
            $this->setFlash('success', 'Đã xóa phụ tùng khỏi danh mục.');
        } else {
            $this->setFlash('danger', 'Không thể xóa phụ tùng vì đã có lịch sử trong phiếu sửa chữa!');
        }
        $this->redirect('index.php?c=part&a=index');
    }

    public function import(): void {
        $this->requireRole(['ThuKho', 'QuanLy']);
        $parts = $this->partModel->getAllParts();
        $suppliers = $this->partModel->getAllSuppliers();
        require_once __DIR__ . '/../models/WarehouseModel.php';
        $warehouseModel = new WarehouseModel();
        $warehouses = $warehouseModel->getAllWarehouses();
        $this->render('part/import', [
            'parts'      => $parts,
            'suppliers'  => $suppliers,
            'warehouses' => $warehouses
        ]);
    }

    public function storeImport(): void {
        $this->requireRole(['ThuKho', 'QuanLy']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $partInput   = trim($_POST['ten_pt'] ?? '');
            if (empty($partInput) && !empty($_POST['ma_pt'])) {
                $partInput = (int)$_POST['ma_pt'];
            }
            $quantity    = (int)($_POST['so_luong'] ?? 0);
            $importPrice = (float)($_POST['gia_nhap'] ?? 0);
            $note        = trim($_POST['ghi_chu'] ?? '');
            $unit        = trim($_POST['don_vi_tinh'] ?? 'Cái');
            $supplierId  = (int)($_POST['ma_ncc'] ?? 1);
            $warehouseId = (int)($_POST['ma_kho'] ?? 1);
            $maNv        = (int)($_SESSION['user']['ma_nv'] ?? 1);

            $res = $this->partModel->importStock($partInput, $quantity, $importPrice, $maNv, $note, $unit, $supplierId, $warehouseId);
            if ($res['success']) {
                $this->setFlash('success', $res['message']);
                $this->redirect('index.php?c=part&a=index');
            } else {
                $this->setFlash('danger', $res['message']);
                $this->redirect('index.php?c=part&a=import');
            }
        }
    }

    public function history(): void {
        $this->requireRole(['ThuKho', 'QuanLy']);
        $history = $this->partModel->getImportHistory();
        $this->render('part/history', ['history' => $history]);
    }
}

