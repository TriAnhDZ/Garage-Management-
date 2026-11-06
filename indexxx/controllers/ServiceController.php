<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ServiceModel.php';
require_once __DIR__ . '/../models/CarModel.php';
require_once __DIR__ . '/../models/SettingModel.php';

class ServiceController extends BaseController {
    private ServiceModel $serviceModel;
    private CarModel $carModel;
    private SettingModel $settingModel;

    public function __construct() {
        parent::__construct();
        $this->serviceModel = new ServiceModel();
        $this->carModel = new CarModel();
        $this->settingModel = new SettingModel();
    }

    public function index(): void {
        $this->requireRole(['KyThuat', 'TiepNhan', 'QuanLy']);
        $services = $this->serviceModel->getAllServices();
        $brands   = $this->carModel->getAllBrands();
        $maxBrands = (int)$this->settingModel->getRuleValue('So hieu xe', 20);
        $maxServices = (int)$this->settingModel->getRuleValue('So loai tien cong', 50);

        $this->render('service/index', [
            'services'    => $services,
            'brands'      => $brands,
            'maxBrands'   => $maxBrands,
            'maxServices' => $maxServices
        ]);
    }

    public function create(): void {
        $this->requireRole(['KyThuat', 'QuanLy']);
        $this->render('service/create');
    }

    public function store(): void {
        $this->requireRole(['KyThuat', 'QuanLy']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name        = trim($_POST['ten_dv'] ?? '');
            $description = trim($_POST['mo_ta'] ?? '');
            $price       = (float)($_POST['gia_tieu_chuan'] ?? 0);
            $status      = trim($_POST['trang_thai'] ?? 'KhaDung');

            if (empty($name) || $price < 0) {
                $this->setFlash('danger', 'Vui lòng nhập tên dịch vụ và đơn giá hợp lệ.');
                $this->redirect('index.php?c=service&a=create');
            }

            $res = $this->serviceModel->createService($name, $description, $price, $status);
            if ($res['success']) {
                $this->setFlash('success', $res['message']);
                $this->redirect('index.php?c=service&a=index');
            } else {
                $this->setFlash('danger', $res['message']);
                $this->redirect('index.php?c=service&a=create');
            }
        }
    }

    public function edit(): void {
        $this->requireRole(['KyThuat', 'QuanLy']);
        $id = (int)($_GET['id'] ?? 0);
        $service = $this->serviceModel->getServiceById($id);
        if (!$service) {
            $this->setFlash('danger', 'Không tìm thấy dịch vụ');
            $this->redirect('index.php?c=service&a=index');
        }
        $this->render('service/edit', ['service' => $service]);
    }

    public function update(): void {
        $this->requireRole(['KyThuat', 'QuanLy']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id          = (int)($_POST['ma_dv'] ?? 0);
            $name        = trim($_POST['ten_dv'] ?? '');
            $description = trim($_POST['mo_ta'] ?? '');
            $price       = (float)($_POST['gia_tieu_chuan'] ?? 0);
            $status      = trim($_POST['trang_thai'] ?? 'KhaDung');

            if ($this->serviceModel->updateService($id, $name, $description, $price, $status)) {
                $this->setFlash('success', "Cập nhật dịch vụ '{$name}' thành công!");
            } else {
                $this->setFlash('danger', 'Cập nhật dịch vụ thất bại.');
            }
            $this->redirect('index.php?c=service&a=index');
        }
    }

    public function delete(): void {
        $this->requireRole(['KyThuat', 'QuanLy']);
        $id = (int)($_GET['id'] ?? 0);
        if ($this->serviceModel->deleteService($id)) {
            $this->setFlash('success', 'Đã xử lý dịch vụ thành công!');
        } else {
            $this->setFlash('danger', 'Xóa dịch vụ thất bại.');
        }
        $this->redirect('index.php?c=service&a=index');
    }

    public function addBrand(): void {
        $this->requireRole(['TiepNhan', 'QuanLy']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $brand = trim($_POST['ten_hieu_xe'] ?? '');
            $brands = $this->carModel->getAllBrands();
            $maxBrands = (int)$this->settingModel->getRuleValue('So hieu xe', 20);

            if (empty($brand)) {
                $this->setFlash('danger', 'Tên hiệu xe không được để trống.');
            } elseif (count($brands) >= $maxBrands) {
                $this->setFlash('danger', "Số lượng hiệu xe đã đạt tối đa ({$maxBrands}) theo quy định!");
            } else {
                $this->setFlash('success', "Hiệu xe '{$brand}' đã sẵn sàng sử dụng trong tiếp nhận xe!");
            }
        }
        $this->redirect('index.php?c=service&a=index');
    }
}

