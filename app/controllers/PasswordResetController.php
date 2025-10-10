<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//require_once BACKEND_ROOT . '/vendor/autoload.php';
class PasswordResetController {

    public function vistaSolicitud() {
        CSRFHelper::generateToken();

        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/password/solicitud.php';
        require_once BACKEND_ROOT . '/app/views/layout/footer.php';
    }

    public function enviarEnlace() {
        header('Content-Type: application/json');
        $datos = json_decode(file_get_contents('php://input'), true);
        $email = $datos['correo'];
        
        $usuario = Usuario::buscarPorCorreo($email);

        if ($usuario) {
            $pdo = Database::conectar();
            $token = bin2hex(random_bytes(32)); 
            $sql = "INSERT INTO password_resets (email, token) VALUES (:email, :token)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['email' => $email, 'token' => $token]);
            
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = $_ENV['MAIL_HOST'];     
                $mail->SMTPAuth   = true;
                $mail->Username   = $_ENV['MAIL_USER'];     
                $mail->Password   = $_ENV['MAIL_PASS'];     
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
                $mail->Port       = 465;                      
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('ccti2025@fasbit.edu.mx', 'Cortex-CCTI 2025');
                $mail->addAddress($email, $usuario['nombre_completo']);

                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
                $domain = $_SERVER['HTTP_HOST'];
                $link = $protocol . "://" . $domain . "/backend/public/passwordReset/vistaRestablecer/" . $token;

                $mail->isHTML(true);
                $mail->Subject = 'Restablecimiento de Contraseña - CCTI 2025';
                $mail->Body    = "Hola,<br><br>Has solicitado restablecer tu contraseña. Por favor, haz clic en el siguiente enlace:<br><a href='{$link}'>Restablecer mi contraseña</a><br><br>Si no solicitaste esto, puedes ignorar este correo.";
                $mail->AltBody = "Hola,\n\nHas solicitado restablecer tu contraseña. Copia y pega el siguiente enlace en tu navegador:\n{$link}\n\nSi no solicitaste esto, ignora este correo.";

                $mail->send();
            } catch (Exception $e) {
                // error_log("PHPMailer Error: {$mail->ErrorInfo}");
            }
        }
        echo json_encode(['mensaje' => 'Si tu correo está registrado, recibirás un enlace para restablecer tu contraseña.']);
    }

    public function vistaRestablecer($token) {
        
        $pdo = Database::conectar();
        $sql = "SELECT * FROM password_resets WHERE token = :token";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['token' => $token]);
        $reset_request = $stmt->fetch();

        if (!$reset_request) {
            echo "El enlace no es válido o ha expirado. Por favor, solicita uno nuevo.";
            return;
        }
        CSRFHelper::generateToken();

        require_once BACKEND_ROOT . '/app/views/layout/header.php';
        require_once BACKEND_ROOT . '/app/views/password/restablecer.php';
        require_once BACKEND_ROOT . '/app/views/layout/footer.php';
    }

    public function restablecerContrasena() {        
        header('Content-Type: application/json');
        $datos = json_decode(file_get_contents('php://input'), true);
        $token = $datos['token'];
        $nuevaContrasena = $datos['nueva_contrasena'];

        $pdo = Database::conectar();
        $sql = "SELECT * FROM password_resets WHERE token = :token";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['token' => $token]);
        $reset_request = $stmt->fetch();

        if (!$reset_request) {
            http_response_code(400);
            echo json_encode(['error' => 'El enlace de restablecimiento no es válido o ha expirado.']);
            return;
        }

        Usuario::actualizarContrasenaPorEmail($reset_request['email'], $nuevaContrasena);
        
        $sql_delete = "DELETE FROM password_resets WHERE email = :email";
        $stmt_delete = $pdo->prepare($sql_delete);
        $stmt_delete->execute(['email' => $reset_request['email']]);

        echo json_encode(['mensaje' => 'Tu contraseña ha sido restablecida con éxito.']);
    }

}