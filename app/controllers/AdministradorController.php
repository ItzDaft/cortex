<?php

class AdministradorController {

 private function autorizar(): bool {
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Acceso no autorizado.']);
            return false;
        }
        $rolesUsuario = Usuario::obtenerRoles($_SESSION['usuario_id']);
        if (!in_array('Administrador', $rolesUsuario)) {
            http_response_code(403);
            echo json_encode(['error' => 'Permisos insuficientes.']);
            return false;
        }
        return true;
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
    if (!$this->autorizar()) return;

    $todosLosPagos = Pago::obtenerTodosConDetalles();
    $estadisticas = Pago::obtenerEstadisticas();

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
public function exportarPagos() {
    if (!$this->autorizar()) return;

    $nombresRoles = $_POST['roles'] ?? [];
    $estatusPagos = $_POST['estatus'] ?? [];

    if (empty($nombresRoles) && empty($estatusPagos)) {
        redirect('administrador/pagos'); return;
    }

    $pagos = Pago::obtenerPagosFiltrados($nombresRoles, $estatusPagos);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=reporte_pagos_filtrado_' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, [
        '#', 'ID Pago', 'ID Usuario', 'Nombre Usuario', 'Tipo de Usuario', 'Monto', 'Tipo de Pago', 'Estatus'
    ]);

    $contador = 1;
    foreach ($pagos as $pago) {
        $tipoUsuario = $pago['roles'];
        if (str_contains($pago['roles'], 'Asistente')) {
            if ($pago['monto'] == 300) $tipoUsuario = 'Asistente Estudiante';
            elseif ($pago['monto'] == 1000) $tipoUsuario = 'Asistente Profesionista';
        }

        fputcsv($output, [
            $contador++,
            $pago['id'],
            $pago['usuario_id'],
            $pago['nombre_completo'],
            $tipoUsuario,
            $pago['monto'],
            $pago['tipo_pago'],
            $pago['estatus_pago']
        ]);
    }

    fclose($output);
    exit;
}
}