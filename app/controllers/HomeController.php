<?php

class HomeController {
    public function index() {
        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/home.php';
        require_once BACKEND_ROOT . '/app/views/layout/footer.php';
    }
}
