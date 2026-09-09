<?php
class Controller {
    public function model($model) {
        require_once '../app/models/' . $model . '.php';
        return new $model();
    }

    public function view($view, $data = []) {
        extract($data);
        require_once '../app/views/layouts/header.php';
        require_once '../app/views/layouts/sidebar.php';
        require_once '../app/views/' . $view . '.php';
        require_once '../app/views/layouts/footer.php';
    }

    public function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}