<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class RevisorPagosController {

    private array $rolesPermitidos = ['Revisor de Pagos', 'Administrador', 'Coordinador'];

    private function autorizar(): bool {
        if (!isset($_SESSION['usuario_id'])) {
            //http_response_code(401);
            redirect('usuario/login');
            //echo json_encode(['error' => 'Acceso no autorizado.']);
            return false;
        }
        $rolesUsuario = $_SESSION['usuario_roles'] ?? [];
        $rolesPermitidos = ['Revisor de Pagos', 'Administrador', 'Coordinador'];

        if (empty(array_intersect($rolesPermitidos, $rolesUsuario))) {
           // http_response_code(403);
            //echo json_encode(['error' => 'Permisos insuficientes.']);
            redirect('');
            return false;
        }
        return true;
    }

    /**
     * Muestra el panel principal con la lista de pagos pendientes.
     */
public function dashboard() {
    if (!isset($_SESSION['usuario_id'])) {
        redirect('usuario/login');
    }
    $rolesUsuario = Usuario::obtenerRoles($_SESSION['usuario_id']);
    if (empty(array_intersect($this->rolesPermitidos, $rolesUsuario))) {
        redirect('');
    }

    CSRFHelper::generateToken();

    $pagosPendientes = Pago::obtenerPendientes(); 
    $pagosHistorial = Pago::obtenerHistorial();  

    require_once BACKEND_ROOT . '/app/views/layout/header.php';
    require_once BACKEND_ROOT . '/app/views/revisor_pagos/dashboard.php';
    require_once BACKEND_ROOT . '/app/views/layout/footer.php';
}

    /**
     *  Procesa la aprobación o rechazo de un pago y notifica al usuario.
     */
    public function procesarPago() {
        header('Content-Type: application/json');
        if (!$this->autorizar()) return;

        $datos = json_decode(file_get_contents('php://input'), true);
        
        if (empty($datos['pago_id']) || empty($datos['nuevo_estatus'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan datos para procesar el pago.']);
            return;
        }

        $pago_id = $datos['pago_id'];
        $estatus = $datos['nuevo_estatus'];
        $revisor_id = $_SESSION['usuario_id'];
        $comentarios = $datos['comentarios'] ?? null;

        if (Pago::actualizarEstatus($pago_id, $estatus, $revisor_id, $comentarios)) {
            
            $pago = Pago::buscarPorId($pago_id);
            $usuario = Usuario::buscarPorId($pago['usuario_id']);
            $asunto = '';
            $cuerpo = '';

            if ($estatus === 'Aprobado') {
                $asunto = "✅ ¡Tu pago ha sido Aprobado!";
                $cuerpo = "<h1>¡Hola, {$usuario['nombre_completo']}!</h1>
                           <p>Te confirmamos que hemos recibido y aprobado tu pago de <strong>\${$pago['monto']}</strong> por el concepto de '<strong>{$pago['tipo_pago']}</strong>'.</p>
                           <p>¡Gracias por tu participación en el CCTI 2025!</p>";
            } elseif ($estatus === 'Rechazado') {
                $asunto = "❌ Actualización sobre tu pago";
                $cuerpo = "<h1>Hola, {$usuario['nombre_completo']},</h1>
                           <p>Te informamos que tu pago por el concepto de '<strong>{$pago['tipo_pago']}</strong>' ha sido <strong>Rechazado</strong>.</p>
                           <p><strong>Motivo del rechazo:</strong></p>
                           <blockquote style='border-left: 4px solid #ccc; padding-left: 15px; margin-left: 20px;'>
                               <p><em>" . htmlspecialchars($comentarios) . "</em></p>
                           </blockquote>
                           <p>Por favor, inicia sesión en la plataforma para subir un nuevo comprobante corregido.</p>";
            }
            
            if (!empty($asunto)) {
                MailHelper::enviarCorreo($usuario['correo'], $usuario['nombre_completo'], $asunto, $cuerpo);
            }
            
            echo json_encode(['mensaje' => 'Pago actualizado correctamente y usuario notificado.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al procesar el pago.']);
        }
    }
}