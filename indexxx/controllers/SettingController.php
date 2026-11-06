<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/SettingModel.php';

class SettingController extends BaseController {
    private SettingModel $settingModel;

    public function __construct() {
        parent::__construct();
        $this->settingModel = new SettingModel();
    }

    public function index(): void {
        $this->requireRole(['QuanLy']);
        $rules = $this->settingModel->getAllRules();
        $this->render('setting/index', ['rules' => $rules]);
    }

    public function update(): void {
        $this->requireRole(['QuanLy']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id    = (int)($_POST['ma_tham_so'] ?? 0);
            $value = (float)($_POST['gia_tri'] ?? 0);
            $note  = trim($_POST['ghi_chu'] ?? '');

            if ($this->settingModel->updateRule($id, $value, $note)) {
                $this->setFlash('success', 'Cập nhật quy định / tham số thành công!');
            } else {
                $this->setFlash('danger', 'Cập nhật tham số thất bại.');
            }
        }
        $this->redirect('index.php?c=setting&a=index');
    }

    public function store(): void {
        $this->requireRole(['QuanLy']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name  = trim($_POST['ten_tham_so'] ?? '');
            $value = (float)($_POST['gia_tri'] ?? 0);
            $note  = trim($_POST['ghi_chu'] ?? '');

            if (!empty($name)) {
                $this->settingModel->addRule($name, $value, $note);
                $this->setFlash('success', "Thêm tham số '{$name}' thành công!");
            } else {
                $this->setFlash('danger', 'Tên tham số không được để trống.');
            }
        }
        $this->redirect('index.php?c=setting&a=index');
    }

    public function delete(): void {
        $this->requireRole(['QuanLy']);
        $id = (int)($_GET['id'] ?? 0);
        if ($this->settingModel->deleteRule($id)) {
            $this->setFlash('success', 'Đã xóa tham số quy định.');
        } else {
            $this->setFlash('danger', 'Xóa tham số thất bại.');
        }
        $this->redirect('index.php?c=setting&a=index');
    }
}

