<?php

class ResumenController {

 /**
     * Muestra el formulario para crear un nuevo resumen (GET)
     * o procesa la creación del resumen (POST).
     */

public function crear() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');

        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401); 
            echo json_encode(['error' => 'Acceso no autorizado. Debe iniciar sesión.']); 
            return;
        }

        $datos = json_decode(file_get_contents('php://input'), true);

        if (empty($datos['titulo']) || empty($datos['autor_principal']) || empty($datos['resumen_texto']) || empty($datos['area_id'])) {
            http_response_code(400); 
            echo json_encode(['error' => 'El título, autor principal, resumen y área son obligatorios.']); 
            return;
        }

        if (strlen($datos['resumen_texto']) > 1500) {
            http_response_code(400); 
            echo json_encode(['error' => 'El resumen no puede exceder los 1500 caracteres.']); 
            return;
        }
        
        $resumen = new Resumen();
        $resumen->autor_id = $_SESSION['usuario_id'];
        $resumen->autor_principal = trim($datos['autor_principal']);
        $resumen->titulo = trim($datos['titulo']);
        $resumen->coautores = trim($datos['coautores']) ?? null;
        $resumen->resumen_texto = trim($datos['resumen_texto']);
        $resumen->area_id = (int)$datos['area_id'];
        $resumen->estatus = 'Pendiente de Asignacion';
        $resumen->intento_envio = 1;
        $resumen->fecha_envio = date('Y-m-d H:i:s');
        $resumen->palabras_clave = trim($datos['palabras_clave']) ?? null;
        $resumen->adscripcion1 = trim($datos['adscripcion1']) ?? null;
        $resumen->adscripcion2 = trim($datos['adscripcion2']) ?? null;

        if ($resumen->guardar()) {
            http_response_code(201);
            echo json_encode(['mensaje' => 'Resumen enviado con éxito.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Ocurrió un error al enviar el resumen.']);
        }

    } else {
        CSRFHelper::generateToken();

        header('Content-Type: text/html');
        if (!isset($_SESSION['usuario_id'])) {
            redirect('usuario/login');
            //header('Location: /ccti2025/backend/public/usuario/login'); 
            exit;
        }
        $areas = AreaTematica::obtenerTodas();
        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/resumen/crear.php';
        require_once BACKEND_ROOT . '/app/views/layout/footer.php';
    }
}

public function misResumenes() {
    if (!isset($_SESSION['usuario_id'])) {
        redirect('usuario/login');
        //exit;
    }

    $autor_id = $_SESSION['usuario_id'];
    
    $pdo = Database::conectar();
    $sql = "SELECT r.*, p.id as pago_id, p.estatus_pago, a.nombre_area,
                   e.id as extenso_id, e.estatus_extenso, 
                   (SELECT MAX(ev.intento) FROM extenso_versiones ev WHERE ev.extenso_id = e.id) as extenso_intento
            FROM resumenes r
            LEFT JOIN pagos p ON r.id = p.resumen_id
            JOIN areas_tematicas a ON r.area_id = a.id
            LEFT JOIN extensos e ON r.id = e.resumen_id
            WHERE r.autor_id = :autor_id
            ORDER BY r.fecha_ultima_modificacion DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['autor_id' => $autor_id]);
    $resumenes = $stmt->fetchAll();
    $evaluaciones = EvaluacionExtenso::obtenerEvaluacionesParaAutor($autor_id);
    $comentariosPorExtenso = [];
    foreach ($evaluaciones as $eval) {
        $comentarios = trim($eval['observaciones_generales'] . "\n" . $eval['argumento_rechazo']);
        if (!empty($comentarios)) {
            $comentariosPorExtenso[$eval['extenso_id']][] = $comentarios;
        }
    }    
    
    require_once BACKEND_ROOT . '/app/views/layout/header.php';
    require_once BACKEND_ROOT . '/app/views/resumen/misResumenes.php';
    require_once BACKEND_ROOT . '/app/views/layout/footer.php';
}
    /**
     * Lista todos los resúmenes del autor que ha iniciado sesión.
     */
    public function listarPorAutor() {
        header('Content-Type: application/json');

        // 1. Autenticación
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Acceso no autorizado.']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido.']);
            return;
        }

        $autor_id = $_SESSION['usuario_id'];
        $resumenes = Resumen::buscarPorAutor($autor_id);

        http_response_code(200);
        echo json_encode($resumenes);
    }
    /**
 * Muestra la vista para editar y reenviar un resumen rechazado.
 * @param int $id El ID del resumen a reenviar.
 */
public function vistaReenviar($id) {

    if (!isset($_SESSION['usuario_id'])) {
        require('usuario/login');
        //header('Location: /backend/public/usuario/login');
        //exit;
    }

    $resumen = Resumen::buscarPorId($id);

    
    if (!$resumen || $resumen['autor_id'] != $_SESSION['usuario_id'] || $resumen['estatus'] !== 'Rechazado' || $resumen['intento_envio'] != 1) {
        redirect('resumen/misResumenes');
        exit;
    }
    

    $areas = AreaTematica::obtenerTodas();
    $view_resumen = $resumen;
    $view_areas = $areas;  
    require_once BACKEND_ROOT . '/app/views/layout/header.php';
    require_once BACKEND_ROOT . '/app/views/resumen/reenviar.php';
    require_once BACKEND_ROOT . '/app/views/layout/footer.php';
}

/**
 * (API) Procesa el reenvío de un resumen corregido.
 */
public function procesarReenvio($id) {
    header('Content-Type: application/json');
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401); echo json_encode(['error' => 'Acceso no autorizado.']); return;
    }

    $resumen = Resumen::buscarPorId($id);
    if (!$resumen || $resumen['autor_id'] != $_SESSION['usuario_id']) {
        http_response_code(403); echo json_encode(['error' => 'No tienes permiso para editar este resumen.']); return;
    }
    
    Revision::eliminarPorResumenId($id);
    // --------------------------------------------------------------------------

    $datos = json_decode(file_get_contents('php://input'), true);

    $resumenActualizado = new Resumen();
    $resumenActualizado->id = $id;
    $resumenActualizado->autor_id = $resumen['autor_id'];
    $resumenActualizado->autor_principal = trim($datos['autor_principal']);
    $resumenActualizado->titulo = trim($datos['titulo']);
    $resumenActualizado->coautores = trim($datos['coautores']);
    $resumenActualizado->resumen_texto = trim($datos['resumen_texto']);
    $resumenActualizado->area_id = (int)$datos['area_id'];
    $resumenActualizado->estatus = 'Pendiente de Asignacion'; 
    $resumenActualizado->intento_envio = 2; 
    $resumenActualizado->fecha_envio = date('Y-m-d H:i:s');
            
    $resumenActualizado->palabras_clave = trim($datos['palabras_clave']) ?? null;
    $resumenActualizado->adscripcion1 = trim($datos['adscripcion1']) ?? null;
    $resumenActualizado->adscripcion2 = trim($datos['adscripcion2']) ?? null;

    if ($resumenActualizado->guardar()) {
        echo json_encode(['mensaje' => 'Resumen reenviado con éxito para una nueva evaluación.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo reenviar el resumen.']);
    }
}
/**
 * Muestra la nueva página dedicada a la gestión de artículos extensos para el autor.
 */
public function misExtensos() {
    if (!isset($_SESSION['usuario_id'])) {
        redirect('usuario/login');
    }
    if (!in_array('Autor', $_SESSION['usuario_roles'])) {
        redirect(''); 
    }
    $resumenesParaExtenso = Resumen::buscarParaEnvioExtenso($_SESSION['usuario_id']);

    require_once BACKEND_ROOT . '/app/views/layout/header.php';
    require_once BACKEND_ROOT . '/app/views/resumen/misExtensos.php'; // La nueva vista que crearemos
    require_once BACKEND_ROOT . '/app/views/layout/footer.php';
}
}