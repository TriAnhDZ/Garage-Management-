<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/CarModel.php';
require_once __DIR__ . '/../models/RepairModel.php';

class SearchController extends BaseController {
    private CarModel $carModel;
    private RepairModel $repairModel;

    public function __construct() {
        parent::__construct();
        $this->carModel = new CarModel();
        $this->repairModel = new RepairModel();
    }

    public function index(): void {
        $this->requireLogin();
        $user = $this->getCurrentUser();
        $role = $user['vai_tro'] ?? '';
        $isCustomer = ($role === 'KhachHang');
        $isStaff = in_array($role, ['QuanLy', 'NhanVien', 'TiepNhan', 'KyThuat', 'ThuKho'], true);
        $maKh = (int)($user['ma_kh'] ?? 0);

        $bienSo   = trim($_GET['bien_so'] ?? '');
        $hangXe   = trim($_GET['hang_xe'] ?? '');
        $tenKhach = trim($_GET['ten_khach'] ?? '');

        if ($isCustomer) {
            $cars = $this->carModel->searchCustomerCars($maKh, $bienSo, $hangXe);
        } else {
            $cars = $this->carModel->searchCars($bienSo, $hangXe, $tenKhach);
        }
        $brands = $this->carModel->getAllBrands();

        $this->render('search/index', [
            'cars'       => $cars,
            'bienSo'     => $bienSo,
            'hangXe'     => $hangXe,
            'tenKhach'   => $tenKhach,
            'brands'     => $brands,
            'isCustomer' => $isCustomer,
            'isStaff'    => $isStaff
        ]);
    }

    public function detail(): void {
        $this->requireLogin();
        $user = $this->getCurrentUser();
        $role = $user['vai_tro'] ?? '';
        $isCustomer = ($role === 'KhachHang');
        $isStaff = in_array($role, ['QuanLy', 'NhanVien', 'TiepNhan', 'KyThuat', 'ThuKho'], true);
        $maKh = (int)($user['ma_kh'] ?? 0);

        $maXe = (int)($_GET['id'] ?? 0);
        $car = $this->carModel->getCarById($maXe);
        if (!$car) {
            $this->setFlash('danger', 'Không tìm thấy thông tin xe.');
            $this->redirect('index.php?c=search&a=index');
        }

        // Nếu là khách hàng, chỉ được xem xe thuộc quyền sở hữu của mình
        if ($isCustomer && (int)$car['ma_kh'] !== $maKh) {
            $this->setFlash('danger', 'Bạn chỉ có quyền xem thông tin hồ sơ phương tiện của chính mình!');
            $this->redirect('index.php?c=search&a=index');
        }

        // Lấy toàn bộ lịch sử sửa chữa của xe
        $repairs = $this->repairModel->getAllRepairs(null, $car['bien_so']);

        $this->render('search/detail', [
            'car'        => $car,
            'repairs'    => $repairs,
            'isCustomer' => $isCustomer,
            'isStaff'    => $isStaff
        ]);
    }
}
