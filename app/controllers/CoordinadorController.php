<?php

class CoordinadorController {

    private $rolesPermitidos = ['Coordinador', 'Administrador'];

    /**
     * Middleware de autorización para verificar el rol del usuario.
     * @return bool True si el usuario tiene permiso, false si no.
     */
    private function autorizar(): bool {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('usuario/login');
            return false;
        }

        $rolesUsuario = Usuario::obtenerRoles($_SESSION['usuario_id']);
        if (empty(array_intersect($this->rolesPermitidos, $rolesUsuario))) {
            redirect('');
            return false;
        }
        return true;
    }


    public function listarPendientesDeValidacion() {
        header('Content-Type: application/json');
        if (!$this->autorizar()) return;

        $pdo = Database::conectar();
        $sql = "SELECT r.*, u.nombre_completo as autor_nombre, a.nombre_area 
                FROM resumenes r
                JOIN usuarios u ON r.autor_id = u.id
                JOIN areas_tematicas a ON r.area_id = a.id
                WHERE r.estatus = 'Pendiente de Asignacion'";
        $stmt = $pdo->query($sql);

        echo json_encode($stmt->fetchAll());
    }

  
public function validarArea() {
    header('Content-Type: application/json');
    if (!$this->autorizar()) return; // Se asume que tienes un método autorizarApi()

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido.']);
        return;
    }

    $datos = json_decode(file_get_contents('php://input'), true);

    if (empty($datos['resumen_id']) || empty($datos['area_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Se requiere el ID del resumen y el ID del área.']);
        return;
    }
    
    // --- NUEVA LÓGICA DE ASIGNACIÓN AUTOMÁTICA ---
    // 1. Encontrar un Coordinador de Área disponible para el área seleccionada
    $coordinadorDeArea = Usuario::buscarRevisorDisponiblePorArea($datos['area_id']);

    if (!$coordinadorDeArea) {
        http_response_code(409); // Conflict
        echo json_encode(['error' => 'No hay Coordinadores de Área disponibles para el área seleccionada.']);
        return;
    }

    // 2. Usar una transacción para asegurar la integridad de los datos
    $pdo = Database::conectar();
    try {
        $pdo->beginTransaction();

        // 2a. Actualizar el estatus y el área del resumen
        $sql_update = "UPDATE resumenes SET area_id = :area_id, estatus = 'En Revision' WHERE id = :resumen_id";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute(['area_id' => $datos['area_id'], 'resumen_id' => $datos['resumen_id']]);

        // 2b. Crear la nueva revisión (asignación)
        $revision = new Revision();
        $revision->resumen_id = $datos['resumen_id'];
        $revision->revisor_id = $coordinadorDeArea['id'];
        $revision->veredicto = 'Pendiente';
        $revision->guardar();

        $pdo->commit();
        http_response_code(200);
        echo json_encode(['mensaje' => 'Resumen validado y asignado a un Coordinador de Área.']);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo asignar el resumen.']);
    }
}
    
    /**
     * Muestra el panel principal del coordinador con la lista de resúmenes a validar.
     */
public function dashboard() {
    if (!$this->autorizar()) return;

    $todosLosResumenes = Resumen::obtenerTodosConDetalles();

    $resumenesPendientes = [];
    $resumenesHistorial = [];

    foreach ($todosLosResumenes as $resumen) {
        if ($resumen['estatus'] === 'Pendiente de Asignacion') {
            $resumenesPendientes[] = $resumen;
        } else {
            $resumenesHistorial[] = $resumen;
        }
    }

    require_once BACKEND_ROOT . '/app/views/layout/header.php';
    require_once BACKEND_ROOT . '/app/views/coordinador/dashboard.php';
    require_once BACKEND_ROOT . '/app/views/layout/footer.php';
}

    /**
     * Muestra la página para validar un resumen específico.
     * @param int $id El ID del resumen a validar.
     */
    public function validar($id) {
        if (!$this->autorizar()) return;
        CSRFHelper::generateToken();

        $resumen = Resumen::buscarPorId($id);
        $areas = AreaTematica::obtenerTodas();

        if (!$resumen) {
            echo "Resumen no encontrado.";
            redirect('coordinador/dashboard');
            return;
        }
    $esEditable=($resumen['estatus'] === 'Pendiente de Asignacion');
    $view_resumen = $resumen;
    $view_areas = $areas;
        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/coordinador/validar.php';
        require_once BACKEND_ROOT . '/app/views/layout/footer.php';
    }   
}