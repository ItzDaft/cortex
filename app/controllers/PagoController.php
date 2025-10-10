<?php

class PagoController {


    public function index() {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('usuario/login');
            //header('Location: /backend/public/usuario/login');
            //exit;
        }
        CSRFHelper::generateToken();
        $pagos = Pago::obtenerPorUsuario($_SESSION['usuario_id']);
        
        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/pago/index.php';
        require_once BACKEND_ROOT . '/app/views/layout/footer.php';
    }

    public function subirComprobanteExistente() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401); echo json_encode(['error' => 'Acceso no autorizado.']); return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); echo json_encode(['error' => 'Método no permitido.']); return;
        }
        if (empty($_POST['pago_id']) || !isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400); echo json_encode(['error' => 'Faltan datos o hubo un error con el archivo.']); return;
        }

        $archivo = $_FILES['comprobante'];
        $tiposPermitidos = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!in_array(mime_content_type($archivo['tmp_name']), $tiposPermitidos)) {
            http_response_code(400); echo json_encode(['error' => 'Formato de archivo no permitido.']); return;
        }

        $directorioSubida = BACKEND_ROOT . '/uploads/pagos/';
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreUnico = 'pago_' . $_POST['pago_id'] . '_' . time() . '.' . $extension;
        $rutaDestino = $directorioSubida . $nombreUnico;

        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            if (Pago::registrarComprobante((int)$_POST['pago_id'], $nombreUnico)) {
                http_response_code(200);
                echo json_encode(['mensaje' => 'Comprobante subido con éxito.']);
            } else {
                http_response_code(500); echo json_encode(['error' => 'Error al registrar el comprobante en la base de datos.']);
            }
        } else {
            http_response_code(500); echo json_encode(['error' => 'No se pudo guardar el archivo subido.']);
        }
    }


    public function subirComprobante() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401); echo json_encode(['error' => 'Acceso no autorizado.']); return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); echo json_encode(['error' => 'Método no permitido.']); return;
        }
        if (empty($_POST['monto']) || empty($_POST['tipo_pago']) || !isset($_FILES['comprobante'])) {
            http_response_code(400); echo json_encode(['error' => 'Faltan datos del pago o el archivo del comprobante.']); return;
        }
        if ($_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400); echo json_encode(['error' => 'Error al subir el archivo. Código: ' . $_FILES['comprobante']['error']]); return;
        }

        $archivo = $_FILES['comprobante'];
        $directorioSubida = BACKEND_ROOT . '/uploads/pagos/';
        $tiposPermitidos = ['image/jpeg', 'image/png', 'application/pdf'];
        $tipoArchivo = mime_content_type($archivo['tmp_name']);
        if (!in_array($tipoArchivo, $tiposPermitidos)) {
            http_response_code(400); echo json_encode(['error' => 'Formato de archivo no permitido. Solo se aceptan JPG, PNG y PDF.']); return;
        }

        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreUnico = 'pago_' . $_SESSION['usuario_id'] . '_' . time() . '.' . $extension;
        $rutaDestino = $directorioSubida . $nombreUnico;

        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            $pago = new Pago();
            $pago->usuario_id = $_SESSION['usuario_id'];
            $pago->monto = (float)$_POST['monto'];
            $pago->tipo_pago = $_POST['tipo_pago'];
            $pago->comprobante_ruta = $nombreUnico;
            $pago->estatus_pago = 'Pendiente';

            if ($pago->guardar()) {
                http_response_code(201); echo json_encode(['mensaje' => 'Comprobante subido con éxito. Su pago está en revisión.']);
            } else {
                http_response_code(500); echo json_encode(['error' => 'Error al guardar la información del pago en la base de datos.']);
            }
        } else {
            http_response_code(500); echo json_encode(['error' => 'No se pudo mover el archivo subido.']);
        }
    }

    /**
     * (API) Devuelve el estado del pago del usuario logueado.
     */
    public function verMiPago() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401); echo json_encode(['error' => 'Acceso no autorizado.']); return;
        }
        $pago = Pago::buscarPorUsuarioId($_SESSION['usuario_id']);
        if ($pago) {
            http_response_code(200); echo json_encode($pago);
        } else {
            http_response_code(404); echo json_encode(['mensaje' => 'Aún no ha registrado ningún pago.']);
        }
    }
}