<?php

class ExtensoController {

    public function enviar($extenso_id) {
        if (!isset($_SESSION['usuario_id']) || !in_array('Autor', $_SESSION['usuario_roles'])) {
            redirect('');
        }

        CSRFHelper::generateToken();
        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/extenso/enviar.php';
        require_once BACKEND_ROOT . '/app/views/layout/footer.php';
    }

    public function procesarEnvio($extenso_id) {
        header('Content-Type: application/json');
        if (!isset($_SESSION['usuario_id']) || !in_array('Autor', $_SESSION['usuario_roles'])) {
            http_response_code(403); echo json_encode(['error' => 'Permisos insuficientes.']); return;
        }

        if (!isset($_FILES['archivo_extenso']) || $_FILES['archivo_extenso']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400); echo json_encode(['error' => 'No se recibió el archivo o hubo un error al subirlo.']); return;
        }
        
        $archivo = $_FILES['archivo_extenso'];
        $tipos_permitidos = [
            'application/pdf'
        ];

        if (!in_array(mime_content_type($archivo['tmp_name']), $tipos_permitidos)) {
            http_response_code(400); echo json_encode(['error' => 'Formato de archivo no permitido. Solo se aceptan PDF']); return;
        }

        $directorioSubida = BACKEND_ROOT . '/uploads/extensos/';
        if (!is_dir($directorioSubida)) { mkdir($directorioSubida, 0777, true); }

        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreUnico = 'extenso_' . $extenso_id . '_v1_' . time() . '.' . $extension;
        $rutaDestino = $directorioSubida . $nombreUnico;

        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            Extenso::agregarVersion($extenso_id, 1, $nombreUnico);
            Extenso::actualizarEstatus($extenso_id, 'Pendiente de Filtro'); 
            echo json_encode(['mensaje' => 'Artículo extenso enviado con éxito.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo guardar el archivo del extenso.']);
        }
    }
 /**
 * Muestra la página para que el autor suba una nueva versión de su extenso.
 */
public function reenviar($extenso_id) {
    if (!isset($_SESSION['usuario_id'])) { redirect(''); }

    $extenso = Extenso::obtenerDetallesParaAutor($extenso_id);

    CSRFHelper::generateToken();
    require_once BACKEND_ROOT . '/app/views/layout/header.php';
    require_once BACKEND_ROOT . '/app/views/extenso/reenviar.php';
    require_once BACKEND_ROOT . '/app/views/layout/footer.php';
}

/**
 * (API) Procesa la subida de una nueva versión del artículo extenso.
 */
public function procesarReenvio($extenso_id) {
    header('Content-Type: application/json');
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(403); echo json_encode(['error' => 'Permisos insuficientes.']); return;
    }

    if (!isset($_FILES['archivo_extenso']) || $_FILES['archivo_extenso']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400); echo json_encode(['error' => 'No se recibió el archivo.']); return;
    }
    $archivo = $_FILES['archivo_extenso'];
    $tipos_permitidos = ['application/pdf'];
    if (!in_array(mime_content_type($archivo['tmp_name']), $tipos_permitidos)) {
    http_response_code(400); 
    echo json_encode(['error' => 'Formato de archivo no permitido. Solo se aceptan archivos PDF.']); 
    return;
}
    $pdo = Database::conectar();
    try {
        $pdo->beginTransaction();

        EvaluacionExtenso::eliminarEvaluacionesAnteriores($extenso_id);

        $directorioSubida = BACKEND_ROOT . '/uploads/extensos/';
        $archivo = $_FILES['archivo_extenso'];
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $ultimoIntento = Extenso::obtenerUltimoIntento($extenso_id);
        $nuevoIntento = $ultimoIntento + 1;
        $nombreUnico = 'extenso_' . $extenso_id . '_v' . $nuevoIntento . '_' . time() . '.' . $extension;

        if (!move_uploaded_file($archivo['tmp_name'], $directorioSubida . $nombreUnico)) {
            throw new Exception('No se pudo guardar el archivo.');
        }

        Extenso::agregarVersion($extenso_id, $nuevoIntento, $nombreUnico);
        Extenso::actualizarEstatus($extenso_id, 'Pendiente de Filtro');
        $pdo->commit();
        echo json_encode(['mensaje' => 'Nueva versión enviada con éxito.']);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

}