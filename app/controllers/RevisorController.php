<?php

class RevisorController {

    private $rolesPermitidos = ['Coordinador de Area', 'Administrador'];

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

    public function enviarEvaluacion() {
        header('Content-Type: application/json');
        if (!$this->autorizar()) return;
        
        $datos = json_decode(file_get_contents('php://input'), true);
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
        
        // Nueva lista para supervisión de extensos
        $asignacionesExtensos = EvaluacionExtenso::obtenerDetallesDeAsignacionesPorArea($revisor['area_id']);

        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/revisor/dashboard.php';
        require_once BACKEND_ROOT . '/app/views/layout/footer.php';
    }

    public function obtenerDetallesEvaluacion($evaluacion_id) {
        header('Content-Type: application/json');
        if (!$this->autorizar()) return;

        $evaluacion = EvaluacionExtenso::buscarPorId($evaluacion_id);
        if ($evaluacion) {
            $evaluacion['respuestas_formulario'] = json_decode($evaluacion['respuestas_formulario'], true);
            echo json_encode($evaluacion);
        } else {
            http_response_code(404); echo json_encode(['error' => 'Evaluación no encontrada.']);
        }
    }

    public function evaluar($id) {
        if (!$this->autorizar()) return;
        CSRFHelper::generateToken();

        $revision = Revision::buscarPorResumenYRevisor($id, $_SESSION['usuario_id']);
        if (!$revision || $revision['veredicto'] !== 'Pendiente') {
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
                EvaluacionExtenso::verificarConsenso($evaluacionActual['extenso_version_id']);
            }
            echo json_encode(['mensaje' => 'Evaluación procesada con éxito.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo procesar la evaluación.']);
        }
    }

    public function asignarTercerRevisor() {
        header('Content-Type: application/json');
        if (!$this->autorizar()) return;

        $datos = json_decode(file_get_contents('php://input'), true);
        if (empty($datos['extenso_version_id']) || empty($datos['revisor_id'])) {
            http_response_code(400); echo json_encode(['error' => 'Se requiere el ID del extenso y el ID del revisor.']); return;
        }

        if (EvaluacionExtenso::asignarTercerRevisor($datos['extenso_version_id'], $datos['revisor_id'])) {
            Extenso::actualizarEstatus($datos['extenso_version_id'], 'En Revisión');
            echo json_encode(['mensaje' => 'Tercer revisor asignado con éxito.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo completar la asignación.']);
        }
    }

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
     * Muestra el panel de Gestión de Extensos.
     */
    public function gestionExtensos() {
        if (!$this->autorizar()) return;

        CSRFHelper::generateToken();

        $coordinadorArea = Usuario::buscarPorId($_SESSION['usuario_id']);
        if (empty($coordinadorArea['area_id'])) {
            echo "Error: No tienes un área de especialización asignada.";
            return;
        }
        
        // Carga solo los datos necesarios para las etapas A, B, C y D
        $extensosPendientesFiltro = Extenso::obtenerPendientesDeFiltroPorArea($coordinadorArea['area_id']);
        $extensosPorAsignar = Extenso::obtenerPendientesDeAsignacionPorArea($coordinadorArea['area_id']);
        $extensosEnRevision = Extenso::obtenerEnRevisionPorArea($coordinadorArea['area_id']);
        $extensosEnConflicto = Extenso::obtenerEnConflictoPorArea($coordinadorArea['area_id']);

        $revisoresDisponibles = Usuario::buscarRevisoresExtensosPorArea($coordinadorArea['area_id']);

        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/revisor/gestion_extensos.php'; 
        require_once BACKEND_ROOT . '/app/views/layout/footer.php';
    }

    public function aprobarFormatoExtenso() {
        header('Content-Type: application/json');
        if (!$this->autorizar()) return;

        $datos = json_decode(file_get_contents('php://input'), true);
        if (empty($datos['extenso_id'])) {
            http_response_code(400); echo json_encode(['error' => 'Se requiere el ID del extenso.']); return;
        }
        
        if (Extenso::actualizarEstatus($datos['extenso_id'], 'Pendiente de Asignacion')) {
            echo json_encode(['mensaje' => 'Formato aprobado. El artículo está listo para asignación.']);
        } else {
            http_response_code(500); echo json_encode(['error' => 'No se pudo aprobar el formato.']);
        }
    }

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

    public function gestionRevisores() {
        if (!$this->autorizar()) return;

        $coordinadorArea = Usuario::buscarPorId($_SESSION['usuario_id']);
        if (empty($coordinadorArea['area_id'])) {
            echo "Error: No tienes un área asignada."; return;
        }

        $revisores = Usuario::buscarRevisoresExtensosPorArea($coordinadorArea['area_id']);
        $areaNombre = AreaTematica::buscarPorId($coordinadorArea['area_id'])['nombre_area'] ?? 'General';

        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/revisor/gestion_revisores.php';
        require_once BACKEND_ROOT . '/app/views/layout/footer.php';
    }

 public function supervisionEvaluaciones() {
        if (!$this->autorizar()) return;


        $pdo = Database::conectar();
        $usuario_id = $_SESSION['usuario_id'];
        $stmtArea = $pdo->prepare("SELECT area_id FROM usuarios WHERE id = ?");
        $stmtArea->execute([$usuario_id]);
        $area_id = $stmtArea->fetchColumn();

        $sql = "
            SELECT 
                ev.id AS evaluacion_id,
                ev.estatus_evaluacion, 
                ev.veredicto,
                ev.fecha_asignacion, -- Columna añadida recientemente
                ev.respuestas_formulario,
                ev.observaciones_generales,
                ev.argumento_rechazo,
                ev.pdf_firmado_ruta,
                res.titulo AS titulo_articulo, -- Obtenido desde resumenes
                ver.archivo_ruta AS archivo_extenso_ruta, -- Obtenido desde versiones
                rev.nombre_completo AS nombre_revisor
            FROM evaluaciones_extensos ev
            INNER JOIN extenso_versiones ver ON ev.extenso_version_id = ver.id
            INNER JOIN extensos ext ON ver.extenso_id = ext.id
            INNER JOIN resumenes res ON ext.resumen_id = res.id
            INNER JOIN usuarios rev ON ev.revisor_id = rev.id
            WHERE res.area_id = ?
            ORDER BY ev.fecha_asignacion DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$area_id]);
        $asignacionesExtensos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($asignacionesExtensos as &$asig) {
            if (!empty($asig['argumento_rechazo'])) {
                $asig['estatus_evaluacion'] = 'Rechazada por Coordinador';
            }
            elseif (empty($asig['estatus_evaluacion']) || $asig['estatus_evaluacion'] === 'Pendiente') {
                if (!empty($asig['veredicto']) && $asig['veredicto'] !== 'Pendiente') {
                    $asig['estatus_evaluacion'] = empty($asig['pdf_firmado_ruta']) ? 'Pendiente de Firma' : 'Pendiente de Validación';
                } else {
                    $asig['estatus_evaluacion'] = 'En Proceso';
                }
            }
        }
        unset($asig);

        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/revisor/supervision_evaluaciones.php';
        require_once BACKEND_ROOT . '/app/views/layout/footer.php';
    }
    /**
     * Envía un correo de recordatorio al revisor sobre una evaluación pendiente.
     * Módulo: Supervisión de Evaluaciones (Coordinador de Área).
     */
    public function enviarRecordatorio() {
        header('Content-Type: application/json');
        
        if (method_exists($this, 'autorizar')) {
            if (!$this->autorizar()) return;
        } else {
            if (!isset($_SESSION['usuario_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'No autorizado.']);
                return;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido.']);
            return;
        }

        $datos = json_decode(file_get_contents('php://input'), true);
        $evaluacion_id = $datos['evaluacion_id'] ?? null;

        if (!$evaluacion_id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de evaluación faltante.']);
            return;
        }

  
        $evaluacion = EvaluacionExtenso::buscarPorId($evaluacion_id);

        if (!$evaluacion) {
            http_response_code(404);
            echo json_encode(['error' => 'La evaluación no existe.']);
            return;
        }

    

        if (empty($evaluacion['fecha_asignacion'])) {
             http_response_code(400);
             echo json_encode(['error' => 'Esta asignación no tiene fecha registrada. No se puede calcular el plazo.']);
             return;
        }

        try {
            $fechaAsignacion = new DateTime($evaluacion['fecha_asignacion']);
            $fechaLimite = clone $fechaAsignacion;
            $fechaLimite->modify('+15 days'); 
            $hoy = new DateTime();
            
            $intervalo = $hoy->diff($fechaLimite);
            $esVencido = ($hoy > $fechaLimite);
            $dias = $intervalo->days;

            $situacionTexto = "";
            if ($esVencido) {
                $situacionTexto = "<span style='color:red; font-weight:bold;'>VENCIDO por {$dias} días.</span>";
            } elseif ($dias == 0) {
                $situacionTexto = "<span style='color:orange; font-weight:bold;'>Vence HOY.</span>";
            } else {
                $situacionTexto = "Días restantes: <strong>{$dias}</strong>";
            }

            $destinatario = $evaluacion['correo_revisor']; // Dato traído en el Paso 1
            $nombreRevisor = $evaluacion['nombre_revisor'];
            $tituloArticulo = $evaluacion['titulo'];
            
            $asunto = "Recordatorio de Revisión Pendiente - CCTI 2025";
            
            $urlLogin = defined('BASE_URL') ? BASE_URL . 'usuario/login' : 'https://ccti2025.fasbit.edu.mx/backend/public/usuario/login';

            $cuerpo = "
                <div style='font-family: sans-serif; padding: 20px; color: #333;'>
                    <h2 style='color: #d9534f;'>Recordatorio de Evaluación</h2>
                    <p>Estimado(a) <strong>{$nombreRevisor}</strong>,</p>
                    <p>Le recordamos cordialmente que tiene una evaluación pendiente en la plataforma Cortex.</p>
                    
                    <div style='background: #f4f4f4; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <p><strong>Artículo:</strong> {$tituloArticulo}</p>
                        <p><strong>Fecha Asignación:</strong> " . $fechaAsignacion->format('d/m/Y') . "</p>
                        <p><strong>Estado:</strong> {$situacionTexto}</p>
                    </div>

                    <p>Agradecemos su valioso apoyo para completar este proceso a tiempo.</p>
                    
                    <a href='{$urlLogin}' style='display:inline-block; padding:10px 20px; background-color:#0275d8; color:white; text-decoration:none; border-radius:5px;'>Acceder al Sistema</a>
                </div>
            ";

            if (MailHelper::enviarCorreo($destinatario, $nombreRevisor, $asunto, $cuerpo)) {
                echo json_encode(['mensaje' => 'Recordatorio enviado correctamente.']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Fallo al enviar el correo (Error de servidor de correo).']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
        }
    }
}