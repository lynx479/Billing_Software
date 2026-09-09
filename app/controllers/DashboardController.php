<?php
class DashboardController extends Controller {
    public function index() {
        $reportModel = $this->model('Report');

        $metrics = $reportModel->getDashboardSummary();
        $charts  = $reportModel->getDashboardCharts();

        $this->view('dashboard/index', [
            'metrics' => $metrics,
            'charts'  => $charts
        ]);
    }
}