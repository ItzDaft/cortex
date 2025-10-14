<?php

class HomeController {
    public function index() {
        $root = dirname(dirname(__DIR__)); 

    require_once $root . '/app/views/layout/header.php';
    require_once $root . '/app/views/home.php';
    require_once $root . '/app/views/layout/footer.php';
    }
}
