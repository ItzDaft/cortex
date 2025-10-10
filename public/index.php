<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BACKEND_ROOT', dirname(__DIR__));
require_once BACKEND_ROOT . '/config/app.php';

// =========================================
// Archivos esenciales (modelos, helpers)
// =========================================
$includes = [
    '/config/database.php',
    '/app/models/Usuario.php',
    '/app/models/Resumen.php',
    '/app/models/AreaTematica.php',
    '/app/models/Pago.php',
    '/app/models/Revision.php',
    '/app/models/Extenso.php',
    '/app/models/EvaluacionExtenso.php',
    '/app/helpers/MailHelper.php',
    '/app/helpers/CSRFHelper.php',
];

foreach ($includes as $file) {
    $fullPath = BACKEND_ROOT . $file;
    if (file_exists($fullPath)) {
        require_once $fullPath;
    } else {
        die("Archivo esencial no encontrado: $fullPath");
    }
}

// =========================================
// Validación CSRF para POST
// =========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = null;

    if (!empty($_POST['csrf_token'])) {
        $token = $_POST['csrf_token'];
    } else {
        $datos = json_decode(file_get_contents('php://input'), true);
        if (isset($datos['csrf_token'])) {
            $token = $datos['csrf_token'];
        }
    }

    if (!CSRFHelper::verifyToken($token)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Error de seguridad (token CSRF inválido). Recargue la página.'
        ]);
        exit();
    }
}

// =========================================
// Procesar la URL y router MVC
// =========================================
$urlParam = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$urlSegments = explode('/', filter_var($urlParam, FILTER_SANITIZE_URL));

// Determinar controlador y método
$controladorNombre = !empty($urlSegments[0]) ? ucwords($urlSegments[0]) . 'Controller' : 'HomeController';
$controladorArchivo = BACKEND_ROOT . '/app/controllers/' . $controladorNombre . '.php';

// Verificar existencia del controlador
if (!file_exists($controladorArchivo)) {
    http_response_code(404);
    die(json_encode(['error' => 'Controlador no encontrado: ' . $controladorNombre]));
}

// Incluir controlador
require_once $controladorArchivo;

// Verificar existencia de clase
if (!class_exists($controladorNombre)) {
    http_response_code(404);
    die(json_encode(['error' => 'Clase del controlador no encontrada: ' . $controladorNombre]));
}

// Instanciar controlador
$controlador = new $controladorNombre;

// Determinar método y parámetros
$metodoNombre = !empty($urlSegments[1]) ? $urlSegments[1] : 'index';
$params = count($urlSegments) > 2 ? array_slice($urlSegments, 2) : [];

// Ejecutar método
if (!method_exists($controlador, $metodoNombre)) {
    http_response_code(404);
    die(json_encode(['error' => 'Método no encontrado: ' . $metodoNombre]));
}

call_user_func_array([$controlador, $metodoNombre], $params);
