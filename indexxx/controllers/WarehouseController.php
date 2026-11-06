<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/WarehouseModel.php';
require_once __DIR__ . '/../models/PartModel.php';

class WarehouseController extends BaseController {
    private WarehouseModel $warehouseModel;
    private PartModel $partModel;

    public function __construct() {
        parent::__construct();
        $this->warehouseModel = new WarehouseModel();
        $this->partModel = new PartModel();
    }

    public function index(): void {
        $selectedKho = !empty($_GET['kho']) ? (int)$_GET['kho'] : null;
        $url = 'index.php?c=part&a=index' . ($selectedKho ? '&kho=' . $selectedKho : '');
        $this->redirect($url);
    }

    public function create(): void {
        $this->requireRole(['QuanLy', 'ThuKho']);
        $this->render('warehouse/create');
    }

    public function store(): void {
        $this->requireRole(['QuanLy', 'ThuKho']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name        = trim($_POST['ten_kho'] ?? '');
            $address     = trim($_POST['dia_chi'] ?? '');
            $phone       = trim($_POST['sdt'] ?? '');
            $description = trim($_POST['mo_ta'] ?? '');

            if (empty($name)) {
                $this->setFlash('danger', 'Tên kho vật tư không được để trống.');
                $this->redirect('index.php?c=warehouse&a=create');
            }

            $this->warehouseModel->createWarehouse($name, $address, $phone, $description);
            $this->setFlash('success', "Thêm kho vật tư '{$name}' thành công!");
            $this->redirect('index.php?c=part&a=index');
        }
    }

    public function edit(): void {
        $this->requireRole(['QuanLy', 'ThuKho']);
        $id = (int)($_GET['id'] ?? 0);
        $kho = $this->warehouseModel->getWarehouseById($id);
        if (!$kho) {
            $this->setFlash('danger', 'Không tìm thấy kho vật tư.');
            $this->redirect('index.php?c=part&a=index');
        }
        $this->render('warehouse/edit', ['kho' => $kho]);
    }

    public function update(): void {
        $this->requireRole(['QuanLy', 'ThuKho']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id          = (int)($_POST['ma_kho'] ?? 0);
            $name        = trim($_POST['ten_kho'] ?? '');
            $address     = trim($_POST['dia_chi'] ?? '');
            $phone       = trim($_POST['sdt'] ?? '');
            $description = trim($_POST['mo_ta'] ?? '');
            $status      = trim($_POST['trang_thai'] ?? 'HoatDong');

            if ($this->warehouseModel->updateWarehouse($id, $name, $address, $phone, $description, $status)) {
                $this->setFlash('success', "Cập nhật thông tin kho '{$name}' thành công!");
            } else {
                $this->setFlash('danger', 'Cập nhật kho thất bại.');
            }
            $this->redirect('index.php?c=part&a=index');
        }
    }

    public function delete(): void {
        $this->requireRole(['QuanLy']);
        $id = (int)($_GET['id'] ?? 0);
        if ($this->warehouseModel->deleteWarehouse($id)) {
            $this->setFlash('success', 'Đã xóa kho vật tư thành công.');
        } else {
            $this->setFlash('danger', 'Không thể xóa kho vì đang có chứa vật tư phụ tùng!');
        }
        $this->redirect('index.php?c=part&a=index');
    }

    public function transfer(): void {
        $this->requireRole(['ThuKho', 'QuanLy']);
        $warehouses = $this->warehouseModel->getAllWarehouses();
        $parts = $this->partModel->getAllParts();
        $selectedPtId = (int)($_GET['part_id'] ?? 0);
        $this->render('warehouse/transfer', [
            'warehouses'   => $warehouses,
            'parts'        => $parts,
            'selectedPtId' => $selectedPtId
        ]);
    }

    public function storeTransfer(): void {
        $this->requireRole(['ThuKho', 'QuanLy']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maPt     = (int)($_POST['ma_pt'] ?? 0);
            $fromKho  = (int)($_POST['tu_kho'] ?? 0);
            $toKho    = (int)($_POST['den_kho'] ?? 0);
            $quantity = (int)($_POST['so_luong'] ?? 1);
            $note     = trim($_POST['ghi_chu'] ?? '');
            $maNv     = (int)($_SESSION['user']['ma_nv'] ?? 1);

            $res = $this->warehouseModel->transferPart($maPt, $fromKho, $toKho, $quantity, $maNv, $note);
            if ($res['success']) {
                $this->setFlash('success', $res['message']);
                $this->redirect('index.php?c=warehouse&a=history');
            } else {
                $this->setFlash('danger', $res['message']);
                $this->redirect('index.php?c=warehouse&a=transfer');
            }
        }
    }

    public function history(): void {
        $this->requireRole(['ThuKho', 'QuanLy']);
        $history = $this->warehouseModel->getTransferHistory();
        $this->render('warehouse/history', ['history' => $history]);
    }
}

