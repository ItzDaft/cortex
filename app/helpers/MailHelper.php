<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class MailHelper {

    /**
     * Envía un correo electrónico usando PHPMailer con la configuración del hosting.
     *
     * @param string $destinatario_email El correo del destinatario.
     * @param string $destinatario_nombre El nombre del destinatario.
     * @param string $asunto El asunto del correo.
     * @param string $cuerpo_html El contenido del correo en formato HTML.
     * @return bool True si el correo se envió, false en caso de error.
     */
    public static function enviarCorreo(string $destinatario_email, string $destinatario_nombre, string $asunto, string $cuerpo_html): bool {
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

            $mail->setFrom('ccti2025@fasbit.edu.mx', 'CORTEX CCTI 2025');
            $mail->addAddress($destinatario_email, $destinatario_nombre);
        if (!empty($adjuntos)) {
            foreach ($adjuntos as $adjunto) {
                $mail->addAttachment($adjunto['ruta'], $adjunto['nombre']);
            }
        }
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $cuerpo_html;
            $mail->AltBody = strip_tags($cuerpo_html);

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}