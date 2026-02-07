<?php

class UsuarioController {

public function registrar() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        $datos = json_decode(file_get_contents('php://input'), true);

        if (empty($datos['nombre']) || empty($datos['correo']) || empty($datos['contrasena']) || empty($datos['tipo_registro'])) {
            http_response_code(400); echo json_encode(['error' => 'Todos los campos son obligatorios.']); return;
        }
        if (Usuario::buscarPorCorreo($datos['correo'])) {
            http_response_code(409); echo json_encode(['error' => 'El correo ya está registrado.']); return;
        }

        $rol_id = null;
        $monto_pago = null;
        $tipo_pago = null;
        switch ($datos['tipo_registro']) {
            case 'autor':
                $rol_id = 4;
                break;
            case 'asistente_cartel':
                $rol_id = 7;
                break;
            case 'asistente_estudiante': 
                $rol_id = 5;
                $monto_pago = 300.00;
                $tipo_pago = 'Inscripción Asistente Estudiante';
                break;
            case 'asistente_profesionista': 
                $rol_id = 5;
                $monto_pago = 1000.00;
                $tipo_pago = 'Inscripción Asistente Profesionista';
                break;
            default:
                http_response_code(400); echo json_encode(['error' => 'Tipo de registro no válido.']); return;
        }
        
        $usuario = new Usuario();
        $usuario->nombre_completo = trim($datos['nombre']);
        $usuario->correo = trim($datos['correo']);
        $usuario->contrasena = $datos['contrasena'];
        $usuario->institucion_procedencia = $datos['institucion'] ?? null;

        if ($usuario->guardar()) {
            $nuevoUsuario = Usuario::buscarPorCorreo($usuario->correo);
            Usuario::asignarRol($nuevoUsuario['id'], $rol_id);

            if ($monto_pago !== null) {
                Pago::crearPagoInscripcion($nuevoUsuario['id'], $tipo_pago, $monto_pago);
            }
                        $asunto = "Bienvenido al Cortex - CCTI 2025";
            $cuerpo = "<h1>¡Hola, {$usuario->nombre_completo}!</h1>
                       <p>Bienvenido al sistema Cortex para el CCTI 2025.</p>
                       <p>Ahora puedes iniciar sesión y explorar todas las funcionalidades disponibles.</p>
                       <p>Accede al sistema a través de este enlace: 
                       <a href='https://ccti2025.fasbit.edu.mx/backend/public'>Ingresar al sistema</a></p>
                       <p>¡Estamos encantados de tenerte con nosotros!</p>";

            MailHelper::enviarCorreo($usuario->correo, $usuario->nombre_completo, $asunto, $cuerpo);     
            http_response_code(201);
            echo json_encode(['mensaje' => 'Usuario registrado con éxito.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Ocurrió un error al registrar el usuario.']);
        }
    } else {
        CSRFHelper::generateToken();

        $roles = []; 
        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/usuario/registro.php';
        require_once BACKEND_ROOT . '/app/views/layout/footer.php';
    }
}

    /**
     * Maneja el inicio de sesión de un usuario.
     */
public function login() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        $datos = json_decode(file_get_contents('php://input'), true);

        $usuario = Usuario::buscarPorCorreo($datos['correo']);

        if ($usuario && $usuario['activo'] == 1 && password_verify($datos['contrasena'], $usuario['contrasena'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre_completo'];
            $_SESSION['usuario_roles'] = Usuario::obtenerRoles($usuario['id']);

            http_response_code(200);
            echo json_encode(['mensaje' => 'Inicio de sesión exitoso.']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Correo o contraseña incorrectos, o la cuenta está inactiva.']);
        }
    } else {
        CSRFHelper::generateToken();
        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/usuario/login.php';
        require_once BACKEND_ROOT . '/app/views/layout/footer.php';
    }
}
/** Maneja el cierre de sesion */
    public function logout() {
        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();

        redirect('');
        exit;
    }
    /**
 * Muestra la página de perfil del usuario logueado.
 */
public function perfil() {
    if (!isset($_SESSION['usuario_id'])) {
        redirect('usuario/login');
        exit;
    }
    CSRFHelper::generateToken();
    $usuario = Usuario::buscarPorId($_SESSION['usuario_id']);
    
    $perfil_revisor = null;
    $nombre_area = null;
    $roles_usuario = Usuario::obtenerRoles($_SESSION['usuario_id']);

    if (in_array('Revisor de Extensos', $roles_usuario)) {
        $perfil_revisor = Usuario::obtenerPerfilRevisorExtenso($_SESSION['usuario_id']);
        if (!empty($usuario['area_id'])) {
            $area = AreaTematica::buscarPorId($usuario['area_id']);
            if ($area) {
                $nombre_area = $area['nombre_area'];
            }
        }
    }

    require_once BACKEND_ROOT . '/app/views/layout/header.php';
    require_once BACKEND_ROOT . '/app/views/usuario/perfil.php';
    require_once BACKEND_ROOT . '/app/views/layout/footer.php';
}

/**
 * (API) Actualiza los datos del perfil (nombre, institución).
 */
public function actualizarPerfil() {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401); echo json_encode(['error' => 'Acceso no autorizado.']); return;
    }

    $datos = json_decode(file_get_contents('php://input'), true);
    $usuario_id = $_SESSION['usuario_id'];
    
    // Preparamos los datos para el método 'actualizar' que ya teníamos
    $datos_actualizar = [
        'nombre_completo' => $datos['nombre_completo'],
        'correo' => $datos['correo'], // Aunque no se cambie, el método lo espera
        'institucion_procedencia' => $datos['institucion_procedencia'],
        'area_id' => $datos['area_id'] // Lo mismo para area_id
    ];

    if (Usuario::actualizar($usuario_id, $datos_actualizar)) {
        // Actualizamos el nombre en la sesión para que se refleje inmediatamente en el header
        $_SESSION['usuario_nombre'] = $datos['nombre_completo'];
        echo json_encode(['mensaje' => 'Perfil actualizado correctamente.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo actualizar el perfil.']);
    }
}

/**
 * (API) Cambia la contraseña del usuario.
 */
public function cambiarContrasena() {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401); echo json_encode(['error' => 'Acceso no autorizado.']); return;
    }

    $datos = json_decode(file_get_contents('php://input'), true);
    $usuario_id = $_SESSION['usuario_id'];

    $usuario = Usuario::buscarPorId($usuario_id);

    if (!$usuario || !password_verify($datos['contrasena_actual'], $usuario['contrasena'])) {
        http_response_code(401);
        echo json_encode(['error' => 'La contraseña actual es incorrecta.']);
        return;
    }
    
    if (Usuario::cambiarContrasena($usuario_id, $datos['nueva_contrasena'])) {
        echo json_encode(['mensaje' => 'Contraseña cambiada con éxito.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo cambiar la contraseña.']);
    }
}

}