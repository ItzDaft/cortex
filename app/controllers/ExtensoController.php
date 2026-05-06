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
        
        // Lógica de límite de fecha DESACTIVADA temporalmente.
        // Dejar comentado para reactivar después:
        /*
        // Calcular fecha límite para reenvío (15 días)
        $fechaLimite = Extenso::calcularFechaLimite($extenso_id);
        $diasRestantes = 0;
        $plazoVencido = false;

        if ($fechaLimite) {
            $limite = new DateTime($fechaLimite);
            $hoy = new DateTime();
            if ($hoy > $limite) {
                $plazoVencido = true;
            } else {
                $diasRestantes = $hoy->diff($limite)->days;
            }
        }
        */

        CSRFHelper::generateToken();
        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/extenso/reenviar.php';
        require_once BACKEND_ROOT . '/app/views/layout/footer.php';
    }

/**
 * (API) Procesa la subida de una nueva versión del artículo extenso.
 */
public function procesarReenvio($extenso_id) {
        // PREVENIR CUALQUIER SALIDA HTML POR ERRORES/WARNINGS
        ini_set('display_errors', 0);
        error_reporting(0);
        
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['usuario_id'])) {
                throw new Exception('Permisos insuficientes.');
            }
            
            // Validación por fecha límite DESACTIVADA temporalmente.
            // Dejar comentado para reactivar después:
            /*
            $fechaLimite = Extenso::calcularFechaLimite($extenso_id);
            if ($fechaLimite && new DateTime() > new DateTime($fechaLimite)) {
                throw new Exception('El plazo de 15 días para enviar correcciones ha vencido.');
            }
            */

            if (!isset($_FILES['archivo_extenso']) || $_FILES['archivo_extenso']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No se recibió el archivo o hubo un error en la subida.');
            }
            
            $archivo = $_FILES['archivo_extenso'];
            $tipos_permitidos = ['application/pdf'];
            
            // Validar MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $archivo['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $tipos_permitidos)) {
                throw new Exception('Formato de archivo no permitido. Solo se aceptan archivos PDF.');
            }

            $pdo = Database::conectar();
            $pdo->beginTransaction();

            $idVersionAnterior = Extenso::obtenerIdUltimaVersion($extenso_id);

            $directorioSubida = BACKEND_ROOT . '/uploads/extensos/';
            if (!is_dir($directorioSubida)) {
                mkdir($directorioSubida, 0777, true);
            }

            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            $ultimoIntento = Extenso::obtenerUltimoIntento($extenso_id);
            $nuevoIntento = $ultimoIntento + 1;
            $nombreUnico = 'extenso_' . $extenso_id . '_v' . $nuevoIntento . '_' . time() . '.' . $extension;
            $rutaCompleta = $directorioSubida . $nombreUnico;

            if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                throw new Exception('No se pudo guardar el archivo físico en el servidor.');
            }

            Extenso::agregarVersion($extenso_id, $nuevoIntento, $nombreUnico);

            // Replicar asignación de revisores para la nueva versión si existía previa
            $idVersionNueva = Extenso::obtenerIdUltimaVersion($extenso_id);
            $asignacionReplicada = false;
            if ($idVersionAnterior && $idVersionNueva) {
                // Si se replica correctamente, el extenso pasa directo a En Revisión.
                $asignacionReplicada = EvaluacionExtenso::replicarAsignacion($idVersionAnterior, $idVersionNueva);
            }

            if ($asignacionReplicada) {
                Extenso::actualizarEstatus($extenso_id, 'En Revisión');
            } else {
                // Fallback para casos sin revisores previos o si no se pudo replicar.
                Extenso::actualizarEstatus($extenso_id, 'Pendiente de Filtro');
            }

            $pdo->commit();
            echo json_encode(['mensaje' => 'Nueva versión enviada con éxito.']);

        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            http_response_code(400); // 400 Bad Request para errores lógicos
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Formulario para subir el archivo final (versión para revista).
     */
    public function subirFinal($extenso_id) {
        if (!isset($_SESSION['usuario_id']) || !in_array('Autor', $_SESSION['usuario_roles'])) {
            redirect('');
        }

        $subir_final = Extenso::obtenerParaSubidaVersionFinal((int) $extenso_id, (int) $_SESSION['usuario_id']);
        if (!$subir_final) {
            redirect('');
        }

        CSRFHelper::generateToken();
        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/extenso/subir_final.php';
        require_once BACKEND_ROOT . '/app/views/layout/footer.php';
    }

    /**
     * Procesa la subida del archivo final (un solo archivo por extenso; reemplaza si ya existía).
     */
    public function procesarSubidaFinal($extenso_id) {
        ini_set('display_errors', 0);
        error_reporting(0);
        header('Content-Type: application/json');

        try {
            if (!isset($_SESSION['usuario_id']) || !in_array('Autor', $_SESSION['usuario_roles'])) {
                throw new Exception('Permisos insuficientes.');
            }

            $extenso_id = (int) $extenso_id;
            $ctx = Extenso::obtenerParaSubidaVersionFinal($extenso_id, (int) $_SESSION['usuario_id']);
            if (!$ctx) {
                throw new Exception('No puede subir la versión final para este artículo.');
            }

            if (!isset($_FILES['archivo_extenso']) || $_FILES['archivo_extenso']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No se recibió el archivo o hubo un error en la subida.');
            }

            $archivo = $_FILES['archivo_extenso'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $archivo['tmp_name']);
            finfo_close($finfo);

            $extPermitidas = ['doc', 'docx'];
            $mimesPermitidos = [
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/octet-stream'
            ];
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

            if (!in_array($extension, $extPermitidas, true) || !in_array($mime, $mimesPermitidos, true)) {
                throw new Exception('Formato de archivo no permitido. Solo se aceptan archivos .doc y .docx.');
            }

            $directorioSubida = BACKEND_ROOT . '/uploads/extensos_finales/';
            if (!is_dir($directorioSubida)) {
                mkdir($directorioSubida, 0777, true);
            }

            $nombreUnico = 'extenso_' . $extenso_id . '_final_' . time() . '.' . $extension;
            $rutaCompleta = $directorioSubida . $nombreUnico;

            if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                throw new Exception('No se pudo guardar el archivo en el servidor.');
            }

            if (!Extenso::guardarOReemplazarVersionFinal($extenso_id, $nombreUnico)) {
                if (is_file($rutaCompleta)) {
                    @unlink($rutaCompleta);
                }
                throw new Exception('No se pudo registrar el archivo en la base de datos.');
            }

            if (($ctx['estatus_extenso'] ?? '') === 'Corregir extenso final') {
                Extenso::actualizarEstatus($extenso_id, 'Aceptado Final');
            }

            echo json_encode(['mensaje' => 'Versión final enviada con éxito.']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

}