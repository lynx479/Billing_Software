<?php
class SettlementsController extends Controller {
    public function index() {
        $settlements = $this->model('Settlement')->getAllSettlements();
        $this->view('settlements/index', ['settlements' => $settlements]);
    }

    public function generate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model('Settlement')->generateSettlementForSeller(
                $_POST['seller_id'],
                $_POST['start_date'],
                $_POST['end_date']
            );
            header('Location: ' . APP_URL . '/settlements');
            exit;
        }
    }
}