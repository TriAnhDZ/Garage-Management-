<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/CarModel.php';
require_once __DIR__ . '/../models/SettingModel.php';

class ReceptionController extends BaseController {
    private CarModel $carModel;
    private SettingModel $settingModel;

    public function __construct() {
        parent::__construct();
        $this->carModel = new CarModel();
        $this->settingModel = new SettingModel();
    }

    public function index(): void {
        $this->requireRole(['TiepNhan', 'KyThuat', 'QuanLy']);
        $receptions = $this->carModel->getTodayReceptions();
        $todayCount = $this->carModel->getDailyReceptionCount();
        $dailyLimit = (int)$this->settingModel->getRuleValue('So xe sua chua toi da', 30);

        $this->render('reception/index', [
            'receptions' => $receptions,
            'todayCount' => $todayCount,
            'dailyLimit' => $dailyLimit
        ]);
    }

    public function create(): void {
        $this->requireRole(['TiepNhan', 'QuanLy']);
        $todayCount = $this->carModel->getDailyReceptionCount();
        $dailyLimit = (int)$this->settingModel->getRuleValue('So xe sua chua toi da', 30);
        $brands = $this->carModel->getAllBrands();

        $this->render('reception/create', [
            'todayCount' => $todayCount,
            'dailyLimit' => $dailyLimit,
            'brands'     => $brands
        ]);
    }

    public function store(): void {
        $this->requireRole(['TiepNhan', 'QuanLy']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tenChuXe = trim($_POST['ten_chu_xe'] ?? '');
            $sdt      = trim($_POST['sdt'] ?? '');
            $diaChi   = trim($_POST['dia_chi'] ?? '');
            $bienSo   = trim($_POST['bien_so'] ?? '');
            $hangXe   = trim($_POST['hang_xe'] ?? '');
            $dongXe   = trim($_POST['dong_xe'] ?? '');
            $namSx    = !empty($_POST['nam_san_xuat']) ? (int)$_POST['nam_san_xuat'] : null;
            $maNv     = (int)($_SESSION['user']['ma_nv'] ?? 1);

            if (empty($tenChuXe) || empty($sdt) || empty($bienSo) || empty($hangXe)) {
                $this->setFlash('danger', 'Vui lòng điền đầy đủ Tên chủ xe, Số điện thoại, Biển số và Hiệu xe.');
                $this->redirect('index.php?c=reception&a=create');
            }

            $res = $this->carModel->createReception($tenChuXe, $sdt, $diaChi, $bienSo, $hangXe, $dongXe, $namSx, $maNv);
            if ($res['success']) {
                $this->setFlash('success', $res['message']);
                $this->redirect('index.php?c=repair&a=detail&id=' . $res['ma_psc']);
            } else {
                $this->setFlash('danger', $res['message']);
                $this->redirect('index.php?c=reception&a=create');
            }
        }
        $this->redirect('index.php?c=reception&a=create');
    }
}

