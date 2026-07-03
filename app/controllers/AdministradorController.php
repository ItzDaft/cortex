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
 * (API) Obtiene los detalles de un resumen específico.
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

    $pagoAntes = Pago::buscarPorId($pagoId);
    $usuario = null;
    if ($pagoAntes && !empty($pagoAntes['usuario_id'])) {
        $usuario = Usuario::buscarPorId((int)$pagoAntes['usuario_id']);
    }

    $revisorId = $_SESSION['usuario_id'];
    if (Pago::condonarPago($pagoId, $revisorId)) {
        $estadisticasActuales = Pago::obtenerEstadisticas();

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

    if (empty($nombresRoles) && empty($estatusPagos)) {
        $pagos = Pago::obtenerTodosConDetalles();
    } else {
        $pagos = Pago::obtenerPagosFiltrados($nombresRoles, $estatusPagos);
    }

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
public function exportarAutoresParaMemorias() {
    if (!$this->autorizar()) return;

    $resumenes = Resumen::obtenerDatosPorMemorias(); 

    $datosCSV = [];
    foreach ($resumenes as $resumen) {

        $datos_comunes = [
            'correo_autor_principal' => $resumen['autor_correo'],
            'titulo_de_trabajo'      => $resumen['titulo'],
            'adscripcion1'           => $resumen['adscripcion1'],
            'adscripcion2'           => $resumen['adscripcion2'],
            'tipo_de_usuario'        => $resumen['tipo_de_usuario'],
            'autor_principal'         => $resumen['autor_principal']
        ];

        $todos_los_nombres_str = $resumen['autor_principal'] . "\n" . $resumen['coautores'];

        $nombres_individuales = preg_split('/[\n,\/]/', $todos_los_nombres_str);

        $nombres_limpios = [];
        foreach ($nombres_individuales as $nombre) {
            $nombre_limpio = trim($nombre); 
            if (!empty($nombre_limpio)) {
                $nombres_limpios[] = $nombre_limpio;
            }
        }

        foreach ($nombres_limpios as $nombre_final) {
            $datosCSV[] = [
                'autor_individual' => $nombre_final
            ] + $datos_comunes; 
        }
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=reporte_autores_trabajos_' . date('Y-m-d') . '.csv');
    @setlocale(LC_ALL, 'es_MX.UTF-8', 'es_ES.UTF-8', 'es.UTF-8');

    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF");

    $header = ['Autor/Coautor', 'Correo (Autor Principal)', 'Título del Trabajo', 'Adscripción 1', 'Adscripción 2', 'Tipo de Trabajo', 'Autor Principal'];
    fputcsv($output, $header);

    $toUtf8 = function($v) {
        if ($v === null) return '';
        $s = (string)$v;
        if (!mb_check_encoding($s, 'UTF-8')) {
            $s = mb_convert_encoding($s, 'UTF-8', 'ISO-8859-1');
        }
        return $s;
    };

    foreach ($datosCSV as $fila) {
        $row = [
            $toUtf8($fila['autor_individual'] ?? ''),
            $toUtf8($fila['correo_autor_principal'] ?? ''),
            $toUtf8($fila['titulo_de_trabajo'] ?? ''),
            $toUtf8($fila['adscripcion1'] ?? ''),
            $toUtf8($fila['adscripcion2'] ?? ''),
            $toUtf8($fila['tipo_de_usuario'] ?? ''),
            $toUtf8($fila['autor_principal'] ?? '')
        ];
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}
/**
 * Muestra la nueva página de Reportes y Estadísticas.
 */
public function reportes() {
    if (!$this->autorizar()) return;

    CSRFHelper::generateToken();

    $areas = AreaTematica::obtenerTodas();

    require_once BACKEND_ROOT . '/app/views/layout/header.php';
    require_once BACKEND_ROOT . '/app/views/admin/reportes.php';
    require_once BACKEND_ROOT . '/app/views/layout/footer.php';
}
/**
 * Endpoint: devuelve estadísticas y series para los reportes.
 * Parámetros GET opcionales: from (YYYY-MM-DD), to (YYYY-MM-DD)
 */
public function estadisticasReportes() {
    header('Content-Type: application/json');
    if (!$this->autorizar(['Administrador', 'Revisor de Pagos'])) return;

    $from = $_GET['from'] ?? null;
    $to = $_GET['to'] ?? null;

    if (!$to) $to = date('Y-m-d');
    if (!$from) $from = date('Y-m-d', strtotime('-5 months', strtotime($to)));

    $pdo = Database::conectar();

    $stats = Pago::obtenerEstadisticas();

    $sql = "SELECT DATE_FORMAT(fecha_revision_pago, '%Y-%m') as mes, COALESCE(SUM(monto),0) as total
            FROM pagos
            WHERE estatus_pago = 'Aprobado' AND fecha_revision_pago BETWEEN :from AND :to
            GROUP BY mes
            ORDER BY mes";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['from' => $from . ' 00:00:00', 'to' => $to . ' 23:59:59']);
    $ingresosPorMes = $stmt->fetchAll();

    $sql2 = "SELECT tipo_pago, COUNT(*) as cantidad, COALESCE(SUM(monto),0) as total FROM pagos
             WHERE fecha_revision_pago BETWEEN :from AND :to
             GROUP BY tipo_pago";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute(['from' => $from . ' 00:00:00', 'to' => $to . ' 23:59:59']);
    $pagosPorTipo = $stmt2->fetchAll();

    echo json_encode([
        'stats' => $stats,
        'ingresos_por_mes' => $ingresosPorMes,
        'pagos_por_tipo' => $pagosPorTipo,
        'range' => ['from' => $from, 'to' => $to]
    ]);
}

/**
 * Endpoint para DataTables (server-side): lista de pagos para el módulo de reportes.
 * Acepta parámetros GET/POST de DataTables: draw, start, length, search[value]
 * También acepta filtros opcionales: from, to (YYYY-MM-DD)
 */
public function listarPagosReportes() {
    header('Content-Type: application/json');
    if (!$this->autorizar(['Administrador', 'Revisor de Pagos'])) {
        http_response_code(403);
        echo json_encode(['error' => 'No autorizado']);
        return;
    }

    try {
        $request = $_REQUEST; // combina GET/POST
        $draw = isset($request['draw']) ? (int)$request['draw'] : 1;
    $start = isset($request['start']) ? (int)$request['start'] : 0;
    $length = isset($request['length']) ? (int)$request['length'] : 25;
    $search = $request['search']['value'] ?? '';
    $from = $request['from'] ?? null;
    $to = $request['to'] ?? null;

        $pdo = Database::conectar();

    $where = "WHERE 1=1";
    $params = [];
    if ($from) {
        $where .= " AND ((p.fecha_revision_pago IS NOT NULL AND p.fecha_revision_pago >= :from) OR (p.fecha_revision_pago IS NULL AND p.fecha_carga >= :from))";
        $params['from'] = $from . ' 00:00:00';
    }
    if ($to) {
        $where .= " AND ((p.fecha_revision_pago IS NOT NULL AND p.fecha_revision_pago <= :to) OR (p.fecha_revision_pago IS NULL AND p.fecha_carga <= :to))";
        $params['to'] = $to . ' 23:59:59';
    }
    if (!empty($request['area_id'])) {
        $where .= " AND res.area_id = :area_id";
        $params['area_id'] = (int)$request['area_id'];
    }
    if (!empty($request['participant_type'])) {
        $participant = $request['participant_type'];
        if (in_array($participant, ['Autor','Asistente con Cartel','Revisor','Revisor de Pagos'])) {
            $where .= " AND EXISTS (SELECT 1 FROM usuario_roles ur2 JOIN roles rl2 ON ur2.rol_id = rl2.id WHERE ur2.usuario_id = u.id AND rl2.nombre_rol = :participant_role)";
            $params['participant_role'] = $participant;
        } elseif ($participant === 'Asistente Estudiante') {
            $where .= " AND p.monto = 300";
        } elseif ($participant === 'Asistente Profesionista') {
            $where .= " AND p.monto = 1000";
        }
    }
    if (!empty($search)) {
        $where .= " AND (CAST(p.id AS CHAR) LIKE :q OR CAST(p.resumen_id AS CHAR) LIKE :q OR CAST(p.usuario_id AS CHAR) LIKE :q OR LOWER(u.nombre_completo) LIKE :q OR LOWER(u.institucion_procedencia) LIKE :q)";
        $params['q'] = '%' . mb_strtolower($search) . '%';
    }

    $totalSql = "SELECT COUNT(*) FROM pagos p";
    $total = $pdo->query($totalSql)->fetchColumn();

    $countSql = "SELECT COUNT(DISTINCT p.id) FROM pagos p JOIN usuarios u ON p.usuario_id = u.id LEFT JOIN resumenes res ON p.resumen_id = res.id LEFT JOIN usuario_roles ur ON u.id = ur.usuario_id JOIN roles r ON ur.rol_id = r.id " . $where;
        $stmtCount = $pdo->prepare($countSql);
        $stmtCount->execute($params);
        $recordsFiltered = (int)$stmtCount->fetchColumn();

    $dataSql = "SELECT p.id, p.resumen_id, p.usuario_id, u.nombre_completo, u.institucion_procedencia, GROUP_CONCAT(r.nombre_rol SEPARATOR ', ') as roles, p.tipo_pago, p.monto, p.estatus_pago, p.comprobante_ruta, p.fecha_revision_pago
                FROM pagos p
                JOIN usuarios u ON p.usuario_id = u.id
                LEFT JOIN resumenes res ON p.resumen_id = res.id
                LEFT JOIN usuario_roles ur ON u.id = ur.usuario_id
                LEFT JOIN roles r ON ur.rol_id = r.id
                " . $where . "
                GROUP BY p.id
                ORDER BY p.id DESC
                LIMIT :start, :length";

        $stmt = $pdo->prepare($dataSql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

    $data = [];
    foreach ($rows as $r) {
        $rolesStr = $r['roles'] ?? '';
        $monto = $r['monto'];
        $tipoParticipante = '-';
        if (is_string($rolesStr) && $rolesStr !== '') {
            if (strpos($rolesStr, 'Autor') !== false) $tipoParticipante = 'Autor';
            elseif (strpos($rolesStr, 'Asistente con Cartel') !== false) $tipoParticipante = 'Asistente con Cartel';
            elseif (strpos($rolesStr, 'Revisor de Pagos') !== false) $tipoParticipante = 'Revisor de Pagos';
            elseif (strpos($rolesStr, 'Revisor') !== false) $tipoParticipante = 'Revisor';
        }
        if ($tipoParticipante === '-' && is_numeric($monto)) {
            if ($monto == 300) $tipoParticipante = 'Asistente Estudiante';
            elseif ($monto == 1000) $tipoParticipante = 'Asistente Profesionista';
        }
        if ($tipoParticipante === '-' && !empty($rolesStr)) $tipoParticipante = $rolesStr;

        $comprobanteHtml = empty($r['comprobante_ruta']) ? '<small class="text-muted">N/A</small>' : '<a href="' . (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/archivo/ver/pagos/' . htmlspecialchars($r['comprobante_ruta']) . '" target="_blank">Ver</a>';

        $data[] = [
            $r['id'],
            $r['resumen_id'] ?: '',
            $r['usuario_id'] ?: '',
            $r['nombre_completo'] ?: '',
            $r['institucion_procedencia'] ?: '',
            $r['tipo_pago'] ?: '',
            $tipoParticipante,
            number_format((float)$r['monto'], 2),
            $r['estatus_pago'] ?: '',
            $comprobanteHtml
        ];
    }

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => (int)$total,
            'recordsFiltered' => (int)$recordsFiltered,
            'data' => $data
        ]);
    } catch (\Throwable $ex) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error: ' . $ex->getMessage()]);
    }
}

/**
 * Exportar CSV para los pagos en reportes (respeta filtros from/to y search)
 */
public function exportarPagosReportes() {
    if (!$this->autorizar(['Administrador', 'Revisor de Pagos'])) return;

    $from = $_POST['from'] ?? $_GET['from'] ?? null;
    $to = $_POST['to'] ?? $_GET['to'] ?? null;
    $search = $_POST['search'] ?? $_GET['search'] ?? '';

    $pdo = Database::conectar();
    $where = "WHERE 1=1";
    $params = [];
    if ($from) { $where .= " AND p.fecha_revision_pago >= :from"; $params['from'] = $from . ' 00:00:00'; }
    if ($to) { $where .= " AND p.fecha_revision_pago <= :to"; $params['to'] = $to . ' 23:59:59'; }
    if (!empty($search)) { $where .= " AND (CAST(p.id AS CHAR) LIKE :q OR CAST(p.resumen_id AS CHAR) LIKE :q OR CAST(p.usuario_id AS CHAR) LIKE :q OR LOWER(u.nombre_completo) LIKE :q)"; $params['q'] = '%' . mb_strtolower($search) . '%'; }

    $sql = "SELECT p.id, p.resumen_id, p.usuario_id, u.nombre_completo, u.institucion_procedencia, GROUP_CONCAT(r.nombre_rol SEPARATOR ', ') as roles, p.tipo_pago, p.monto, p.estatus_pago, p.comprobante_ruta, p.fecha_revision_pago
            FROM pagos p
            JOIN usuarios u ON p.usuario_id = u.id
            LEFT JOIN usuario_roles ur ON u.id = ur.usuario_id
            LEFT JOIN roles r ON ur.rol_id = r.id
            " . $where . "
            GROUP BY p.id
            ORDER BY p.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=reporte_pagos_detalle_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, ['ID Pago','Resumen ID','ID Usuario','Nombre Usuario','Institución','Tipo Pago','Tipo Participante','Monto','Estatus','Comprobante','Fecha Revision']);

    foreach ($rows as $r) {
        $rolesStr = $r['roles'] ?? '';
        $monto = $r['monto'];
        $tipoParticipante = '-';
        if (is_string($rolesStr) && $rolesStr !== '') {
            if (strpos($rolesStr, 'Autor') !== false) $tipoParticipante = 'Autor';
            elseif (strpos($rolesStr, 'Asistente con Cartel') !== false) $tipoParticipante = 'Asistente con Cartel';
            elseif (strpos($rolesStr, 'Revisor de Pagos') !== false) $tipoParticipante = 'Revisor de Pagos';
            elseif (strpos($rolesStr, 'Revisor') !== false) $tipoParticipante = 'Revisor';
        }
        if ($tipoParticipante === '-' && is_numeric($monto)) {
            if ($monto == 300) $tipoParticipante = 'Asistente Estudiante';
            elseif ($monto == 1000) $tipoParticipante = 'Asistente Profesionista';
        }
        if ($tipoParticipante === '-' && !empty($rolesStr)) $tipoParticipante = $rolesStr;

        fputcsv($output, [
            $r['id'],
            $r['resumen_id'],
            $r['usuario_id'],
            $r['nombre_completo'],
            $r['institucion_procedencia'],
            $r['tipo_pago'],
            $tipoParticipante,
            $r['monto'],
            $r['estatus_pago'],
            $r['comprobante_ruta'],
            $r['fecha_revision_pago']
        ]);
    }

    fclose($output);
    exit;
}
/**
     * Envía credenciales masivas a todos los usuarios de un rol específico.
     * Genera una nueva contraseña aleatoria para cada usuario, la actualiza en la BD y envía el correo.
     */
    public function enviarCredencialesMasivas() {
        header('Content-Type: application/json');

        if (!$this->autorizar()) return;

        $datos = json_decode(file_get_contents('php://input'), true);
        $rol_id = isset($datos['rol_id']) ? (int)$datos['rol_id'] : null;

        if (!$rol_id) {
            http_response_code(400);
            echo json_encode(['error' => 'No se ha especificado un rol válido.']);
            return;
        }

       
        $usuarios = Usuario::obtenerPorRol($rol_id);

        if (empty($usuarios)) {
            http_response_code(404);
            echo json_encode(['error' => 'No se encontraron usuarios con ese rol.']);
            return;
        }

        $enviados = 0;
        $errores = 0;
        $linkLogin = 'https://ccti2025.fasbit.edu.mx/backend/public/usuario/login';

        foreach ($usuarios as $usuario) {
            $contrasenaTemporal = bin2hex(random_bytes(4)); 

            if ($usuario->actualizarContrasena($contrasenaTemporal)) {
                
                $asunto = "Actualización de Credenciales - CCTI 2025";
                
                $mensajeRol = "";
                
                switch ($rol_id) {
                    case 2: // Autores
                        $mensajeRol = "<p>Como <strong>Autor</strong> registrado en el CCTI 2025, te enviamos tus accesos actualizados.</p>";
                        break;
                    case 3: // Revisor de Extenso
                        $mensajeRol = "<p>Has sido confirmado como <strong>Revisor de Extensos</strong>. Agradecemos tu participación.</p>";
                        break;
                    case 5: // Coordinador de Área
                        $mensajeRol = "<p>Te enviamos tus credenciales para acceder al panel de <strong>Coordinación</strong>.</p>";
                        break;
                    default:
                        $mensajeRol = "<p>Se han generado nuevas credenciales para tu cuenta en el sistema Cortex.</p>";
                        break;
                }

                $cuerpo = "
                    <h1>¡Hola, {$usuario->nombre_completo}!</h1>
                    {$mensajeRol}
                    <p>Por favor, utiliza las siguientes credenciales para ingresar al sistema:</p>
                    <ul>
                        <li><strong>Usuario / Correo:</strong> {$usuario->correo}</li>
                        <li><strong>Contraseña Temporal:</strong> {$contrasenaTemporal}</li>
                    </ul>
                    <p>⚠️ <em>Si ya tenías una contraseña, esta ha sido reemplazada por la nueva mostrada arriba.</em></p>
                    <p>Accede al sistema aquí: <a href='{$linkLogin}' target='_blank'>Ingresar a Cortex</a></p>
                    <hr>
                    <small>Si no solicitaste este cambio o crees que es un error, contacta al administrador.</small>
                ";

                $enviado = MailHelper::enviarCorreo(
                    $usuario->correo, 
                    $usuario->nombre_completo, 
                    $asunto, 
                    $cuerpo
                );

                if ($enviado) {
                    $enviados++;
                } else {
                    $errores++; 
                }

            } else {
                $errores++; 
            }
        }

        http_response_code(200);
        echo json_encode([
            'mensaje' => 'Proceso finalizado.',
            'total_usuarios' => count($usuarios),
            'enviados_exitosamente' => $enviados,
            'fallidos' => $errores
        ]);
    }

    /**
     * (API) Exporta a CSV los reportes de memorias para extensos "Aceptado Final".
     * Soporta filtrado por área temática.
     */
    public function exportarMemoriasExtensos() {
        if (!$this->autorizar()) return;

        $area_id = isset($_GET['area_id']) && $_GET['area_id'] !== '' ? (int)$_GET['area_id'] : null;

        $extensos = Extenso::obtenerMemoriasAceptadosFinal($area_id);

        if (empty($extensos)) {
            echo "No hay datos para exportar.";
            return;
        }

        // Determine the overall maximum number of versions and the maximum evaluations per version
        $maxVersions = 0;
        $maxEvalsPerVersion = 0;

        foreach ($extensos as $ext) {
            if (isset($ext['versiones_evaluadas'])) {
                $numVersions = count($ext['versiones_evaluadas']);
                if ($numVersions > $maxVersions) {
                    $maxVersions = $numVersions;
                }

                foreach ($ext['versiones_evaluadas'] as $version => $evals) {
                    $numEvals = count($evals);
                    if ($numEvals > $maxEvalsPerVersion) {
                        $maxEvalsPerVersion = $numEvals;
                    }
                }
            }
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="memorias_extensos_' . date('Ymd_His') . '.csv"');

        $output = fopen('php://output', 'w');
        // Agregamos BOM para correcta visualización en Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Build dynamic headers based on max versions and max evaluations
        $headers = ['ID', 'Nombre del Extenso', 'Autor Principal', 'Área Temática'];
        for ($v = 1; $v <= $maxVersions; $v++) {
            for ($e = 1; $e <= $maxEvalsPerVersion; $e++) {
                $headers[] = "V{$v} - Revisor {$e} (Alias)";
                $headers[] = "V{$v} - Revisor {$e} (Veredicto)";
                $headers[] = "V{$v} - Revisor {$e} (Fecha)";
                $headers[] = "V{$v} - Revisor {$e} (Comentarios)";
            }
        }
        fputcsv($output, $headers);

        foreach ($extensos as $ext) {
            $row = [
                $ext['extenso_id'],
                $ext['titulo'],
                $ext['autor_principal'],
                $ext['nombre_area']
            ];

            $versionesList = array_values($ext['versiones_evaluadas'] ?? []);

            for ($v = 0; $v < $maxVersions; $v++) {
                $evalsForVersion = $versionesList[$v] ?? [];

                for ($e = 0; $e < $maxEvalsPerVersion; $e++) {
                    if (isset($evalsForVersion[$e])) {
                        $eval = $evalsForVersion[$e];
                        $row[] = $eval['alias'];
                        $row[] = $eval['veredicto'];
                        // Explicit string cast or space prepended can help Excel not parse weird dates,
                        // but DATE_FORMAT '%d/%m/%Y %H:%i' in the query is usually well supported.
                        $row[] = $eval['fecha_evaluacion'];

                        // Clean up newlines in comments for better CSV aesthetic
                        $comentarios = str_replace(["\r\n", "\r", "\n"], " ", $eval['observaciones']);
                        $row[] = $comentarios;
                    } else {
                        // Fill empty cells if this version/evaluator slot is missing
                        $row[] = '';
                        $row[] = '';
                        $row[] = '';
                        $row[] = '';
                    }
                }
            }
            fputcsv($output, $row);
        }

        fclose($output);
    }
}