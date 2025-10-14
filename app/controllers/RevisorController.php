<?php

class RevisorController {

    private $rolesPermitidos = ['Coordinador de Area', 'Administrador'];

    private function autorizar(): bool {
        if (!isset($_SESSION['usuario_id'])) {
            //http_response_code(401);
            redirect('usuario/login');
            //echo json_encode(['error' => 'Acceso no autorizado.']);
            return false;
        }
        $rolesUsuario = Usuario::obtenerRoles($_SESSION['usuario_id']);
        if (empty(array_intersect($this->rolesPermitidos, $rolesUsuario))) {
            //http_response_code(403);
            redirect('');
            //echo json_encode(['error' => 'Permisos insuficientes.']);
            return false;
        }
        return true;
    }

    /**
     * Lista los resúmenes disponibles para el revisor logueado.
     */
    public function listarDisponibles() {
        header('Content-Type: application/json');
        if (!$this->autorizar()) return;

        $revisor = Usuario::buscarPorId($_SESSION['usuario_id']);

        if (empty($revisor['area_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'No tienes un área de especialización asignada.']);
            return;
        }

        $resumenes = Resumen::buscarDisponiblesPorArea($revisor['area_id'], $revisor['id']);
        echo json_encode($resumenes);
    }

    /**
     * Un revisor "reclama" un resumen para evaluarlo.
     */
    
    public function reclamarResumen() {
        header('Content-Type: application/json');
        if (!$this->autorizar()) return;
        
        $datos = json_decode(file_get_contents('php://input'), true);
        if (empty($datos['resumen_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Se requiere el ID del resumen.']);
            return;
        }
        
        $resumen_id = $datos['resumen_id'];
        $revisor_id = $_SESSION['usuario_id'];

        if (Revision::contarPorResumen($resumen_id) >= 1) {
            http_response_code(409); 
            echo json_encode(['error' => 'Este resumen ya tiene el máximo de revisores.']);
            return;
        }
        if (Revision::existe($resumen_id, $revisor_id)) {
            http_response_code(409);
            echo json_encode(['error' => 'Ya has reclamado este resumen anteriormente.']);
            return;
        }

    
        $revision = new Revision();
        $revision->resumen_id = $resumen_id;
        $revision->revisor_id = $revisor_id;
        $revision->veredicto = 'Pendiente';

        if ($revision->guardar()) {
            http_response_code(201);
            echo json_encode(['mensaje' => 'Resumen asignado para tu revisión.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo asignar el resumen.']);
        }
    }
    /**
     * Un revisor envía su evaluación final sobre un resumen.
     */
public function enviarEvaluacion() {
    header('Content-Type: application/json');
    if (!$this->autorizar()) return;
    
    $datos = json_decode(file_get_contents('php://input'), true);
    // . (la validación de datos se queda igual) .
    if (empty($datos['resumen_id']) || empty($datos['veredicto'])) {
        http_response_code(400); echo json_encode(['error' => 'Faltan datos en la evaluación.']); return;
    }
    $resumen_id = $datos['resumen_id'];
    $revisor_id = $_SESSION['usuario_id'];
    
    $revision_data = Revision::buscarPorResumenYRevisor($resumen_id, $revisor_id);
    if (!$revision_data) {
        http_response_code(404);
        echo json_encode(['error' => 'No se encontró una revisión asignada.']);
        return;
    }

    $revision = new Revision();
    $revision->id = $revision_data['id'];
    $revision->veredicto = $datos['veredicto'];
    $revision->comentarios = $datos['comentarios'];

    if ($revision->guardar()) {
        $nuevo_estatus = $datos['veredicto'];

        Resumen::actualizarEstatus($resumen_id, $nuevo_estatus);
        $resumen_data = Resumen::buscarPorId($resumen_id);
        $autor = Usuario::buscarPorId($resumen_data['autor_id']);

        if ($nuevo_estatus === 'Aceptado') {
            $roles_del_autor = Usuario::obtenerRoles($autor['id']);
            $monto=0;
            if(in_array('Asistente con Cartel', $roles_del_autor)){
                $monto=500.00;
            }elseif(in_array('Autor', $roles_del_autor)){
                $monto=1000.00;
                Extenso::crearParaResumen($resumen_id);
            }
            if($monto>0){
                Pago::crearPagoParaResumen($resumen_id, $autor['id'],$monto);

            }
        } 
        $asunto = ($nuevo_estatus === 'Aceptado') ? "¡Tu resumen ha sido Aceptado!" : "Actualización sobre tu resumen";
        $cuerpo = "<h1>Hola, {$autor['nombre_completo']}!</h1><p>Tu resumen titulado '<strong>{$resumen_data['titulo']}</strong>' ha sido evaluado con el veredicto: <strong>{$nuevo_estatus}</strong>.</p>";
        if ($nuevo_estatus === 'Aceptado') {
            $cuerpo .= "<p>Se ha generado la orden de pago correspondiente. Por favor, inicia sesión en el sistema para subir tu comprobante.</p>";
        } else {
            $cuerpo .= "<p>Comentarios: <em>" . htmlspecialchars($datos['comentarios']) . "</em></p>";
        }
        MailHelper::enviarCorreo($autor['correo'], $autor['nombre_completo'], $asunto, $cuerpo);

        http_response_code(200);
        echo json_encode(['mensaje' => 'Evaluación enviada con éxito.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo guardar la evaluación.']);
    }
}
    /**
     * Muestra el panel principal del revisor.
     */
 public function dashboard() {
    if (!$this->autorizar()) return;
    CSRFHelper::generateToken();

    $revisor = Usuario::buscarPorId($_SESSION['usuario_id']);

    if (empty($revisor['area_id'])) {
        echo "Error: No tienes un área de especialización asignada.";
        return;
    }

    $resumenesDisponibles = Resumen::buscarDisponiblesPorArea($revisor['area_id'], $revisor['id']);
    $revisionesAsignadas = Revision::buscarAsignadasPorRevisor($revisor['id']);
    $revisionesCompletadas = Revision::buscarCompletadasPorRevisor($revisor['id']);
  

    require_once BACKEND_ROOT . '/app/views/layout/header.php';
    require_once BACKEND_ROOT . '/app/views/revisor/dashboard.php';
    require_once BACKEND_ROOT . '/app/views/layout/footer.php';
}

    /**
     * Muestra la página para evaluar un resumen específico.
     * @param int $id El ID del resumen a evaluar.
     */
    public function evaluar($id) {
        
        if (!$this->autorizar()) return;
        CSRFHelper::generateToken();

        $revision = Revision::buscarPorResumenYRevisor($id, $_SESSION['usuario_id']);
        if (!$revision || $revision['veredicto'] !== 'Pendiente') {
            //echo "Acceso denegado o la revisión ya fue completada.";
            //return;
            redirect('revisor/dashboard');
        }

        $resumen = Resumen::buscarPorId($id);

        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/revisor/evaluar.php';
        require_once BACKEND_ROOT . '/app/views/layout/footer.php';
    }
    public function guardarBorrador() {
    header('Content-Type: application/json');
    if (!$this->autorizar()) return;

    $datos = json_decode(file_get_contents('php://input'), true);

    if (empty($datos['resumen_id']) || !isset($datos['comentarios'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan datos para guardar el borrador.']);
        return;
    }

    $revision = Revision::buscarPorResumenYRevisor($datos['resumen_id'], $_SESSION['usuario_id']);

    if (!$revision) {
        http_response_code(404);
        echo json_encode(['error' => 'No se encontró la revisión asignada.']);
        return;
    }
    
    $pdo = Database::conectar();
    $sql = "UPDATE revisiones SET comentarios = :comentarios WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute(['comentarios' => $datos['comentarios'], 'id' => $revision['id']])) {
        http_response_code(200);
        echo json_encode(['mensaje' => 'Borrador guardado correctamente.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo guardar el borrador.']);
    }
}
/**
 * (API) Un Coordinador de Area devuelve un resumen al Coordinador principal.
 */
public function devolverResumen() {
    header('Content-Type: application/json');
    if (!$this->autorizar()) return;

    $datos = json_decode(file_get_contents('php://input'), true);
    if (empty($datos['resumen_id'])) {
        http_response_code(400); echo json_encode(['error' => 'Se requiere el ID del resumen.']); return;
    }

    $resumen_id = $datos['resumen_id'];

    $pdo = Database::conectar();
    try {
        $pdo->beginTransaction();

        Resumen::devolverACoordinador($resumen_id);

        Revision::eliminarPorResumenId($resumen_id);

        $pdo->commit();
        echo json_encode(['mensaje' => 'Resumen devuelto al coordinador correctamente.']);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo devolver el resumen.']);
    }
}
/**
 * (API) Asigna un extenso a dos revisores.
 */
/**
 * (API) Asigna un extenso a dos revisores.
 */
public function asignarRevisoresExtenso() {
    header('Content-Type: application/json');
    if (!$this->autorizar()) return; 

    $datos = json_decode(file_get_contents('php://input'), true);

    if (empty($datos['extenso_id']) || empty($datos['revisores_ids']) || count($datos['revisores_ids']) != 2) {
        http_response_code(400); echo json_encode(['error' => 'Se requiere el ID del extenso y exactamente dos IDs de revisores.']); return;
    }

    $extenso_version_id = Extenso::obtenerIdUltimaVersion($datos['extenso_id']);
    if (!$extenso_version_id) {
        http_response_code(404); echo json_encode(['error' => 'No se encontró una versión del extenso para asignar.']); return;
    }
    if (EvaluacionExtenso::asignarRevisores($extenso_version_id, $datos['revisores_ids'])) {
        Extenso::actualizarEstatus($datos['extenso_id'], 'En Revisión');
        echo json_encode(['mensaje' => 'Revisores asignados con éxito.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo completar la asignación.']);
    }
}
/**
 * (API) Procesa la validación (aprobación/rechazo) de una evaluación firmada.
 */

public function validarEvaluacionFirmada() {
    header('Content-Type: application/json');
    if (!$this->autorizar()) return;

    $datos = json_decode(file_get_contents('php://input'), true);
    if (empty($datos['evaluacion_id']) || empty($datos['accion'])) {
        http_response_code(400); echo json_encode(['error' => 'Faltan datos.']); return;
    }

    $evaluacion_id = $datos['evaluacion_id'];
    $accion = $datos['accion'];
    $comentarios = $datos['comentarios'] ?? null;

    if (EvaluacionExtenso::validarEvaluacion($evaluacion_id, $accion, $comentarios)) {
        
        if ($accion === 'Validada') {
            $evaluacionActual = EvaluacionExtenso::buscarPorId($evaluacion_id);
            $evaluacionesValidadas = EvaluacionExtenso::obtenerEvaluacionesValidadas($evaluacionActual['extenso_version_id']);
            $numEvaluaciones = count($evaluacionesValidadas);
            $estatus_final_extenso = '';

            // Solo se toma una decisión si hay 2 o 3 evaluaciones validadas
            if ($numEvaluaciones >= 2) {
                $veredictos = array_column($evaluacionesValidadas, 'veredicto');
                $conteos = array_count_values($veredictos);
                $aceptados = $conteos['Favorable y Publicable'] ?? 0;
                $conCorrecciones = $conteos['Favorable con Correcciones'] ?? 0;
                $rechazados = $conteos['No Publicable'] ?? 0;

                // --- LÓGICA DE DECISIÓN FINAL (INCLUYE DESEMPATE) ---
                if ($numEvaluaciones >= 3) { // Si hay 3 o más, la mayoría gana
                    if ($rechazados >= 2) $estatus_final_extenso = 'Rechazado';
                    elseif ($aceptados >= 2) $estatus_final_extenso = 'Aceptado Final';
                    elseif ($conCorrecciones >= 2) $estatus_final_extenso = 'Aceptado con Correcciones';
                } elseif ($numEvaluaciones == 2) { // Lógica para 2 evaluaciones
                    if ($rechazados == 2) $estatus_final_extenso = 'Rechazado';
                    elseif ($aceptados == 2) $estatus_final_extenso = 'Aceptado Final';
                    elseif ($conCorrecciones > 0 && $rechazados == 0) $estatus_final_extenso = 'Aceptado con Correcciones';
                    elseif ($rechazados == 1 && ($aceptados == 1 || $conCorrecciones == 1)) $estatus_final_extenso = 'Conflicto';
                }
                
                if (!empty($estatus_final_extenso)) {
                    $extenso = Extenso::buscarPorVersionId($evaluacionActual['extenso_version_id']);
                    Extenso::actualizarEstatus($extenso['id'], $estatus_final_extenso);
                    
                    // Notificar al autor
                    $autor = Usuario::buscarPorId($extenso['autor_id']);
                    MailHelper::enviarCorreo(
                        $autor['correo'], 
                        $autor['nombre_completo'], 
                        'Decisión Final sobre tu Artículo Extenso', 
                        "<h1>Hola {$autor['nombre_completo']}</h1><p>Tu artículo ha sido procesado con un veredicto final de: <strong>{$estatus_final_extenso}</strong>. Por favor, inicia sesión en el sistema para ver los comentarios de los revisores y los siguientes pasos.</p>"
                    );
                }
            }
        }

        echo json_encode(['mensaje' => 'Evaluación procesada con éxito.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo procesar la evaluación.']);
    }
}
/**
 * (API) Asigna un tercer revisor a un extenso en conflicto.
 */
public function asignarTercerRevisor() {
    header('Content-Type: application/json');
    if (!$this->autorizar()) return;

    $datos = json_decode(file_get_contents('php://input'), true);
    if (empty($datos['extenso_version_id']) || empty($datos['revisor_id'])) {
        http_response_code(400); echo json_encode(['error' => 'Se requiere el ID del extenso y el ID del revisor.']); return;
    }

    if (EvaluacionExtenso::asignarTercerRevisor($datos['extenso_version_id'], $datos['revisor_id'])) {
        // Actualizamos el estado del extenso para que salga de la cola de conflictos
        Extenso::actualizarEstatus($datos['extenso_version_id'], 'En Revisión');
        echo json_encode(['mensaje' => 'Tercer revisor asignado con éxito.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo completar la asignación.']);
    }
}
/**
 * (API) Devuelve un extenso al autor por problemas de formato.
 */
public function devolverExtensoPorFormato() {
    header('Content-Type: application/json');
    if (!$this->autorizar()) return;

    $datos = json_decode(file_get_contents('php://input'), true);
    if (empty($datos['extenso_id']) || empty($datos['comentarios'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Se requiere el ID del extenso y los comentarios.']);
        return;
    }

    if (Extenso::actualizarEstatusYComentarios($datos['extenso_id'], 'Rechazado por Formato', $datos['comentarios'])) {
    $detalles = Extenso::obtenerDetallesParaNotificacion($datos['extenso_id']);
    if ($detalles) {
            $asunto = "Tu extenso requiere correcciones de formato";
            $cuerpo = "<h1>Hola, {$detalles['autor_nombre']}</h1>
                       <p>Tu artículo extenso titulado '<strong>{$detalles['titulo']}</strong>' ha sido revisado por el Coordinador de Área y requiere correcciones de formato.</p>
                       <p><strong>Comentarios del coordinador:</strong></p>
                       <blockquote style='border-left: 4px solid #ccc; padding-left: 15px;'>
                           <p><em>" . htmlspecialchars($datos['comentarios']) . "</em></p>
                       </blockquote>
                       <p>Por favor, inicia sesión en la plataforma Cortex para subir una nueva versión corregida.</p>";

            MailHelper::enviarCorreo($detalles['autor_correo'], $detalles['autor_nombre'], $asunto, $cuerpo);
        }

        echo json_encode(['mensaje' => 'Extenso devuelto al autor con observaciones.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo devolver el extenso.']);
    }
}
/**
 * Muestra el nuevo panel dedicado a la gestión de artículos extensos.
 */
public function gestionExtensos() {
    if (!$this->autorizar()) return;

    CSRFHelper::generateToken();

    $coordinadorArea = Usuario::buscarPorId($_SESSION['usuario_id']);
    if (empty($coordinadorArea['area_id'])) {
        echo "Error: No tienes un área de especialización asignada.";
        return;
    }
    $extensosEnRevision = Extenso::obtenerEnRevisionPorArea($coordinadorArea['area_id']);

    $extensosParaFiltro = Extenso::obtenerPendientesDeFiltroPorArea($coordinadorArea['area_id']);

    $revisoresDisponibles = Usuario::buscarRevisoresExtensosPorArea($coordinadorArea['area_id']);


    require_once BACKEND_ROOT . '/app/views/layout/header.php';
    require_once BACKEND_ROOT . '/app/views/revisor/gestion_extensos.php'; 
    require_once BACKEND_ROOT . '/app/views/layout/footer.php';
}
/**
 * (API) Obtiene los IDs de los revisores actualmente asignados a un extenso.
 */
public function obtenerRevisoresAsignados($extenso_id) {
    header('Content-Type: application/json');
    if (!$this->autorizar()) return;

    $extenso_version_id = Extenso::obtenerIdUltimaVersion((int)$extenso_id);
    if ($extenso_version_id) {
        $revisores_ids = EvaluacionExtenso::obtenerIdsRevisoresAsignados($extenso_version_id);
        echo json_encode($revisores_ids);
    } else {
        echo json_encode([]);
    }
}

/**
 * (API) Actualiza los revisores asignados a un extenso.
 */
public function actualizarRevisoresExtenso() {
    header('Content-Type: application/json');
    if (!$this->autorizar()) return;

    $datos = json_decode(file_get_contents('php://input'), true);
    if (empty($datos['extenso_id']) || !isset($datos['revisores_ids']) || count($datos['revisores_ids']) != 2) {
        http_response_code(400); echo json_encode(['error' => 'Se requiere el ID del extenso y exactamente dos IDs de revisores.']); return;
    }

    $extenso_version_id = Extenso::obtenerIdUltimaVersion($datos['extenso_id']);
    if (!$extenso_version_id) {
        http_response_code(404); echo json_encode(['error' => 'No se encontró una versión del extenso para actualizar.']); return;
    }

    if (EvaluacionExtenso::actualizarRevisores($extenso_version_id, $datos['revisores_ids'])) {
        echo json_encode(['mensaje' => 'Revisores actualizados con éxito.']);
    } else {
        http_response_code(500); echo json_encode(['error' => 'No se pudo actualizar la asignación.']);
    }
}
}