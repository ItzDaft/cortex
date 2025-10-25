<?php

class AdministradorController {

    /**
     * Autoriza el acceso verificando sesión y roles.
     * Si se pasa un array de roles permitidos, cualquiera de esos roles permitirá el acceso.
     * Caso contrario se requiere el rol 'Administrador'.
     * @param array|null $rolesPermitidos
     * @return bool
     */
    private function autorizar(?array $rolesPermitidos = null): bool {
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Acceso no autorizado.']);
            return false;
        }
        $rolesUsuario = Usuario::obtenerRoles($_SESSION['usuario_id']);
        if (empty($rolesPermitidos)) {
            $rolesPermitidos = ['Administrador'];
        }
        foreach ($rolesPermitidos as $rol) {
            if (in_array($rol, $rolesUsuario)) {
                return true;
            }
        }
        http_response_code(403);
        echo json_encode(['error' => 'Permisos insuficientes.']);
        return false;
    }
        public function dashboard() {
        if (!$this->autorizar()) return;

        $stats = Resumen::contarPorEstatus();
        
        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/admin/dashboard.php';
        require_once BACKEND_ROOT . '/app/views/layout/footer.php';
    }

    /**
     * Muestra la página de gestión de usuarios.
     */
public function usuarios() {
    if (!$this->autorizar()) return;
    CSRFHelper::generateToken();

    $todosLosUsuarios = Usuario::obtenerTodos();
    $roles = $this->obtenerRolesCatalogo();
    $areas = AreaTematica::obtenerTodas();

    $usuariosPorRol = [];
    foreach ($todosLosUsuarios as $usuario) {
        $rolesDelUsuario = explode(', ', $usuario['roles'] ?? '');
        foreach ($rolesDelUsuario as $rol) {
            if (!empty($rol)) {
                $usuariosPorRol[$rol][] = $usuario;
            }
        }
    }
    $estadisticasRoles = [];
    foreach ($roles as $rol) {
        $nombreRol = $rol['nombre_rol'];
        $estadisticasRoles[$nombreRol] = count($usuariosPorRol[$nombreRol] ?? []);
    }
    $estadisticasRoles['Total'] = count($todosLosUsuarios);
    $roles = $this->obtenerRolesCatalogo();
    $areas = AreaTematica::obtenerTodas();
    require_once BACKEND_ROOT . '/app/views/layout/header.php';
    require_once BACKEND_ROOT . '/app/views/admin/usuarios.php';
    require_once BACKEND_ROOT . '/app/views/layout/footer.php';
}


    public function dashboardStats() {
        header('Content-Type: application/json');
        if (!$this->autorizar()) return;
        $stats = Resumen::contarPorEstatus();
        echo json_encode($stats);
    }

    public function listarUsuarios() {
        header('Content-Type: application/json');
        if (!$this->autorizar()) return;
        $usuarios = Usuario::obtenerTodos();
        echo json_encode($usuarios);
    }

    /**
     * Crea un nuevo usuario (Coordinador o Revisor).
     */
    public function crearUsuario() {
        header('Content-Type: application/json');
        if (!$this->autorizar()) return;

        $datos = json_decode(file_get_contents('php://input'), true);

        $contrasenaTemporal = bin2hex(random_bytes(8)); 

        $usuario = new Usuario();
        $usuario->nombre_completo = $datos['nombre'];
        $usuario->correo = $datos['correo'];
        $usuario->contrasena = $contrasenaTemporal; 
        $usuario->institucion_procedencia = $datos['institucion'] ?? null;
        $usuario->area_id = $datos['area_id'] ?? null;
        
        if (Usuario::buscarPorCorreo($usuario->correo)) {
            http_response_code(409);
            echo json_encode(['error' => 'El correo ya está en uso.']);
            return;
        }

        if ($usuario->guardar()) {
            $nuevoUsuario = Usuario::buscarPorCorreo($usuario->correo);
            $rol_id = (int)$datos['rol_id'];

            Usuario::asignarRol($nuevoUsuario['id'], (int)$datos['rol_id']);
        $asunto = "Bienvenido al Cortex el sistema de revision del CCTI 2025";
        $linkLogin = 'https://ccti2025.fasbit.edu.mx/backend/public/usuario/login';

        $cuerpo="";
        if ($rol_id === 8) {
            $cuerpo = "<h1>¡Hola, {$usuario->nombre_completo}!</h1>
                       <p>Has sido invitado a participar como <strong>Revisor de Extensos</strong> en el CCTI 2025.</p>
                       <p>Por favor, inicia sesión con las siguientes credenciales temporales para completar tu perfil y confirmar tu participación:</p>
                       <ul>
                           <li><strong>Usuario:</strong> {$usuario->correo}</li>
                           <li><strong>Contraseña Temporal:</strong> {$contrasenaTemporal}</li>
                       </ul>
                       <p>Accede al sistema aquí: <a href='{$linkLogin}'>Ingresar al sistema</a></p>";
        } else {
            // 
            $cuerpo = "<h1>¡Hola, {$usuario->nombre_completo}!</h1>
                       <p>Se ha creado una cuenta para ti en el sistema Cortex.</p>
                       <p>Credenciales de acceso:</p>
                       <ul>
                           <li><strong>Usuario:</strong> {$usuario->correo}</li>
                           <li><strong>Contraseña Temporal:</strong> {$contrasenaTemporal}</li>
                       </ul>
                       <p>Accede al sistema aquí: <a href='{$linkLogin}'>Ingresar al sistema</a></p>";
        }
        MailHelper::enviarCorreo($usuario->correo, $usuario->nombre_completo, $asunto, $cuerpo);

            http_response_code(201);
            echo json_encode([
                'mensaje' => 'Usuario creado con éxito. Se han enviado las credenciales por correo.',
                'contrasena_temporal' => $contrasenaTemporal
            ]);

        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo crear el usuario.']);
        }
    }
        private function obtenerRolesCatalogo() {
        $pdo = Database::conectar();
        return $pdo->query("SELECT * FROM roles")->fetchAll();
    }


public function obtenerUsuario($id) {
    header('Content-Type: application/json');
    if (!$this->autorizar()) return;
    
    $usuario = Usuario::buscarPorId($id);
    if($usuario){
        $usuario['roles_ids']=Usuario::obtenerIdsRoles($id);
    }
    echo json_encode($usuario);
}


public function actualizarUsuario($id) {
    header('Content-Type: application/json');
    if (!$this->autorizar()) return;

    $datos = json_decode(file_get_contents('php://input'), true);
    
    if (isset($datos['detalles'])) {
        Usuario::actualizar($id, $datos['detalles']);
    }
        if (isset($datos['roles_ids'])) {
        Usuario::actualizarRoles($id, $datos['roles_ids']);
    }

    echo json_encode(['mensaje' => 'Usuario actualizado correctamente.']);
}

public function reactivarUsuario($id) {
    header('Content-Type: application/json');
    if (!$this->autorizar()) return;

    if (Usuario::reactivar($id)) {
        echo json_encode(['mensaje' => 'Usuario reactivado correctamente.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo reactivar el usuario.']);
    }
}

public function eliminarUsuario($id) {
    header('Content-Type: application/json');
    if (!$this->autorizar()) return;

    if (Usuario::eliminarLogico($id)) {
        echo json_encode(['mensaje' => 'Usuario desactivado correctamente.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo desactivar el usuario.']);
    }
}
/**
 * Muestra la página de gestión de todos los resúmenes.
 */
public function resumenes() {
    if (!$this->autorizar()) return;
    CSRFHelper::generateToken();

    $todosLosResumenes = Resumen::obtenerTodosConDetalles();
    $areas = AreaTematica::obtenerTodas();
    $resumenesPorArea = [];
    foreach ($todosLosResumenes as $resumen) {
        $areaNombre = $resumen['nombre_area'];
        $resumenesPorArea[$areaNombre][] = $resumen;
    }
    $resumenesPorEstatus = [];
    foreach ($todosLosResumenes as $resumen) {
        $estatus = $resumen['estatus'];
        $resumenesPorEstatus[$estatus][] = $resumen;
    }
    $estadisticasResumenes = Resumen::contarPorArea();
    $estadisticasResumenes['Total'] = count($todosLosResumenes);
    require_once BACKEND_ROOT . '/app/views/layout/header.php';
    require_once BACKEND_ROOT . '/app/views/admin/resumenes.php';
    require_once BACKEND_ROOT . '/app/views/layout/footer.php';
}


public function obtenerRevisionesResumen($id) {
    header('Content-Type: application/json');
    if (!$this->autorizar()) return;
    
    $revisiones = Revision::buscarPorResumen($id);
    echo json_encode($revisiones);
}


public function actualizarAreaResumen($id) {
    header('Content-Type: application/json');
    if (!$this->autorizar()) return;
    
    $datos = json_decode(file_get_contents('php://input'), true);
    if (empty($datos['area_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Se requiere el ID del área.']);
        return;
    }

    $pdo = Database::conectar();
    $sql = "UPDATE resumenes SET area_id = :area_id WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute(['area_id' => $datos['area_id'], 'id' => $id])) {
        echo json_encode(['mensaje' => 'Área del resumen actualizada.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo actualizar el área.']);
    }
}
/**
 * Muestra la página de gestión de todos los pagos.
 */
public function pagos() {
    if (!$this->autorizar(['Administrador', 'Revisor de Pagos'])) return;
    // Generar token CSRF para el formulario de exportación
    CSRFHelper::generateToken();

    $todosLosPagos = Pago::obtenerTodosConDetalles();
    $estadisticas = Pago::obtenerEstadisticas();
$estadisticasPorTipo = Pago::obtenerEstadisticasPorTipo();
    $pagosPorEstatus = [
        'Pendiente' => [],
        'Aprobado' => [],
        'Rechazado' => []
    ];
    foreach ($todosLosPagos as $pago) {
        $estatus = $pago['estatus_pago'];
        if (isset($pagosPorEstatus[$estatus])) {
            $pagosPorEstatus[$estatus][] = $pago;
        }
    }
    $estadisticas = Pago::obtenerEstadisticas();
    $estadisticasPorTipo = Pago::obtenerEstadisticasPorTipo();   
    $estadisticasPorRol = Pago::obtenerEstadisticasPorRol();
    require_once BACKEND_ROOT . '/app/views/layout/header.php';
    require_once BACKEND_ROOT . '/app/views/admin/pagos.php';
    require_once BACKEND_ROOT . '/app/views/layout/footer.php';
}
/**
 * (API) Permite a un administrador generar una orden de pago manualmente para un usuario.
 */
public function generarPagoManual() {
    header('Content-Type: application/json');
    if (!$this->autorizar()) return;

    $datos = json_decode(file_get_contents('php://input'), true);

    if (empty($datos['usuario_id']) || empty($datos['tipo_pago']) || !isset($datos['monto'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Se requieren el ID del usuario, el tipo de pago y el monto.']);
        return;
    }

    if (Pago::crearPagoInscripcion($datos['usuario_id'], $datos['tipo_pago'], (float)$datos['monto'])) {
        echo json_encode(['mensaje' => 'Orden de pago generada con éxito para el usuario.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo generar la orden de pago.']);
    }
}
/**
 * (API) Envía un correo masivo a todos los Autores y Asistentes con Cartel.
 */
public function enviarCorreoMasivo() {
    header('Content-Type: application/json');
    if (!$this->autorizar()) return;
    if (empty($_POST['roles']) || !is_array($_POST['roles'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Debe seleccionar al menos un rol de destino.']);
        return;
    }
    $nombresRoles = $_POST['roles'];
    $asunto = $_POST['asunto'];
    $cuerpo = $_POST['cuerpo'];
    $adjunto = null;

    
    if (isset($_FILES['adjunto']) && $_FILES['adjunto']['error'] === UPLOAD_ERR_OK) {
        $adjunto = [
            'ruta' => $_FILES['adjunto']['tmp_name'],
            'nombre' => $_FILES['adjunto']['name']
        ];
    }

    $destinatarios = Usuario::buscarPorRoles($nombresRoles);
    $enviados = 0;
    $fallidos = 0;

    foreach ($destinatarios as $usuario) {
        if (MailHelper::enviarCorreo($usuario['correo'], $usuario['nombre_completo'], $asunto, $cuerpo, $adjunto ? [$adjunto] : [])) {
            $enviados++;
        } else {
            $fallidos++;
        }
    }

    echo json_encode([
        'mensaje' => "Proceso completado. Correos enviados: $enviados. Correos fallidos: $fallidos."
    ]);
}
/**
 * (API) Gets the full details of a summary to display in a modal.
 */
public function obtenerResumenDetalles($id) {
    header('Content-Type: application/json');
    if (!$this->autorizar()) return;

    $resumen = Resumen::obtenerDetallesPorId((int)$id);

    if ($resumen) {
        echo json_encode($resumen);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Resumen not found.']);
    }
}
/**
 * Muestra el panel de gestión de artículos extensos.
 */
public function extensos() {
    if (!$this->autorizar()) return;

    $extensos = Extenso::obtenerTodosConDetalles();

    require_once BACKEND_ROOT . '/app/views/layout/header.php';
    require_once BACKEND_ROOT . '/app/views/admin/extensos.php';
    require_once BACKEND_ROOT . '/app/views/layout/footer.php';
}
/**
 * (API) Obtiene todas las evaluaciones de un artículo extenso para mostrarlas en un modal.
 */
public function obtenerEvaluacionesExtenso($extenso_id) {
    header('Content-Type: application/json');
    if (!$this->autorizar()) return;

    $evaluaciones = EvaluacionExtenso::obtenerPorExtensoId((int)$extenso_id);

    if ($evaluaciones) {
        echo json_encode($evaluaciones);
    } else {
        // Devuelve un array vacío si no hay evaluaciones, lo cual no es un error.
        echo json_encode([]);
    }
}

/**
 * (API) Condonar un pago: pone monto a 0.00, marca como Aprobado,
 * asigna el id del usuario que realizó la condonación y fecha de revisión.
 */
public function condonarPago() {
    header('Content-Type: application/json');
    if (!$this->autorizar(['Administrador', 'Revisor de Pagos'])) return;

    $pagoId = isset($_POST['pago_id']) ? (int)$_POST['pago_id'] : 0;
    $token = $_POST['csrf_token'] ?? null;

    if (!CSRFHelper::verifyToken($token)) {
        http_response_code(403);
        echo json_encode(['error' => 'Token CSRF inválido.']);
        return;
    }

    if ($pagoId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de pago inválido.']);
        return;
    }

    // Obtener datos del pago y usuario antes de modificar (para notificación)
    $pagoAntes = Pago::buscarPorId($pagoId);
    $usuario = null;
    if ($pagoAntes && !empty($pagoAntes['usuario_id'])) {
        $usuario = Usuario::buscarPorId((int)$pagoAntes['usuario_id']);
    }

    $revisorId = $_SESSION['usuario_id'];
    if (Pago::condonarPago($pagoId, $revisorId)) {
        // obtener estadísticas actualizadas para enviar al cliente
        $estadisticasActuales = Pago::obtenerEstadisticas();

        // Enviar correo al usuario notificando la condonación (si se encontró correo)
        if ($usuario && !empty($usuario['correo'])) {
            $asunto = "Notificación de condonación de pago - CCTI 2025";
            $montoAntes = isset($pagoAntes['monto']) ? number_format($pagoAntes['monto'], 2) : '0.00';
            $cuerpo = "<p>Estimado/a " . htmlspecialchars($usuario['nombre_completo'] ?? '') . ",</p>" .
                     "<p>Le informamos que su pago (ID: <strong>" . htmlspecialchars($pagoId) . "</strong>) ha sido <strong>condonado</strong> por el comité administrativo.</p>" .
                     "<p>Monto anterior: <strong>$" . $montoAntes . " MXN</strong><br>Nuevo monto: <strong>$0.00 MXN</strong></p>" .
                     "<p>Fecha de la acción: " . date('Y-m-d H:i:s') . "</p>" .
                     "<p>Si tiene dudas, por favor contacte al comité a través de la plataforma.</p>";

            MailHelper::enviarCorreo($usuario['correo'], $usuario['nombre_completo'] ?? '', $asunto, $cuerpo);
        }

        echo json_encode(['mensaje' => 'Pago condonado correctamente.', 'estadisticas' => $estadisticasActuales]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo condonar el pago.']);
    }
}
public function exportarPagos() {
    if (!$this->autorizar()) return;
    $nombresRoles = $_POST['roles'] ?? [];
    $estatusPagos = $_POST['estatus'] ?? [];
    $query = trim($_POST['query'] ?? '');

    // Si no se especifican filtros, obtenemos todos los pagos
    if (empty($nombresRoles) && empty($estatusPagos)) {
        $pagos = Pago::obtenerTodosConDetalles();
    } else {
        $pagos = Pago::obtenerPagosFiltrados($nombresRoles, $estatusPagos);
    }

    // Si se envió un query desde la vista, aplicarlo sobre los resultados
    if ($query !== '') {
        $q = mb_strtolower($query);
        $pagos = array_filter($pagos, function($p) use ($q) {
            $id = (string)($p['id'] ?? '');
            $usuarioId = (string)($p['usuario_id'] ?? '');
            $nombre = mb_strtolower($p['nombre_completo'] ?? '');
            $tipoPago = mb_strtolower($p['tipo_pago'] ?? '');
            $roles = mb_strtolower($p['roles'] ?? '');

            return str_contains($id, $q)
                || str_contains($usuarioId, $q)
                || str_contains($nombre, $q)
                || str_contains($tipoPago, $q)
                || str_contains($roles, $q);
        });
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=reporte_pagos_' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, [
        '#', 'ID Pago', 'Resumen ID', 'ID Usuario', 'Nombre Usuario', 'Institución', 'Tipo de Pago', 'Tipo de Participante', 'Monto', 'Estatus', 'Comprobante'
    ]);

    $contador = 1;
    foreach ($pagos as $pago) {
        $roles = $pago['roles'] ?? '';
        $monto = $pago['monto'] ?? null;

        $tipoParticipante = '-';
        if (is_string($roles) && $roles !== '') {
            if (strpos($roles, 'Autor') !== false) $tipoParticipante = 'Autor';
            elseif (strpos($roles, 'Asistente con Cartel') !== false) $tipoParticipante = 'Asistente con Cartel';
            elseif (strpos($roles, 'Revisor de Pagos') !== false) $tipoParticipante = 'Revisor de Pagos';
            elseif (strpos($roles, 'Revisor') !== false) $tipoParticipante = 'Revisor';
        }

        if ($tipoParticipante === '-' && is_numeric($monto)) {
            if ($monto == 300) $tipoParticipante = 'Asistente Estudiante';
            elseif ($monto == 1000) $tipoParticipante = 'Asistente Profesionista';
        }

        if ($tipoParticipante === '-' && !empty($roles)) {
            $tipoParticipante = $roles;
        }

        $comprobante = $pago['comprobante_ruta'] ?? '';
        if (empty($comprobante)) $comprobante = 'N/A';

        // Resumen ID (solo el id, sin URL)
        $resumenId = $pago['resumen_id'] ?? '';

        fputcsv($output, [
            $contador++,
            $pago['id'] ?? '',
            $resumenId,
            $pago['usuario_id'] ?? '',
            $pago['nombre_completo'] ?? '',
            $pago['institucion_procedencia'] ?? '',
            $pago['tipo_pago'] ?? '',
            $tipoParticipante,
            $pago['monto'] ?? '',
            $pago['estatus_pago'] ?? '',
            $comprobante
        ]);
    }

    fclose($output);
    exit;
}
}