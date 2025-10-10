<?php
define('BASE_URL', 'https://' . $_SERVER['HTTP_HOST'] . '/backend/public/');
function redirect($ruta) {
    header('Location: ' . BASE_URL . $ruta);
    exit;
}