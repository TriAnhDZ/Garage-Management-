<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ReportModel.php';

class ReportController extends BaseController {
    private ReportModel $reportModel;

    public function __construct() {
        parent::__construct();
        $this->reportModel = new ReportModel();
    }

    public function revenue(): void {
        $this->requireRole(['QuanLy', 'TiepNhan']);
        $month = !empty($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
        $year  = !empty($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

        $report = $this->reportModel->getRevenueReport($month, $year);

        $this->render('report/revenue', [
            'report' => $report,
            'month'  => $month,
            'year'   => $year
        ]);
    }

    public function inventory(): void {
        $this->requireRole(['QuanLy', 'ThuKho']);
        $month = !empty($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
        $year  = !empty($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

        $report = $this->reportModel->getInventoryReport($month, $year);

        $this->render('report/inventory', [
            'report' => $report,
            'month'  => $month,
            'year'   => $year
        ]);
    }
}

