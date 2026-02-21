<?php
/**
 * Servicio de Email - usa mail() nativo o PHPMailer si está disponible
 * Compatible con Hostinger SMTP
 */

class EmailService {

    private static function send(string $to, string $subject, string $body): bool {
        $config = require BASE_PATH . '/config/mail.php';

        if (!$config['enabled']) {
            error_log("[EmailService] Mail disabled - would send to: $to subj: $subject");
            return false;
        }

        // Si PHPMailer está disponible (composer require phpmailer/phpmailer)
        $phpmailerPath = BASE_PATH . '/vendor/autoload.php';
        if (file_exists($phpmailerPath)) {
            require_once $phpmailerPath;
            return self::sendWithPHPMailer($to, $subject, $body, $config);
        }

        // Fallback: mail() nativo
        $headers = "From: {$config['from_name']} <{$config['from']}>\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        return @mail($to, $subject, $body, $headers);
    }

    private static function sendWithPHPMailer(string $to, string $subject, string $body, array $config): bool {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            $mail->SMTPSecure = $config['encryption'];
            $mail->Port       = $config['port'];
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($config['from'], $config['from_name']);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("[EmailService] Error: " . $e->getMessage());
            return false;
        }
    }

    // === Métodos públicos (equivalentes al Java) ===

    public static function enviarBienvenida(array $usuario): void {
        self::send(
            $usuario['correo'],
            '¡Bienvenido a Quality ESCOM!',
            "Hola {$usuario['nombre']},\n\nTu cuenta ha sido creada exitosamente. Ya puedes iniciar sesión en el sistema.\n\nSaludos."
        );
    }

    public static function notificarTecnicosNuevoTicket(array $ticket): void {
        require_once APP_PATH . '/Models/Usuario.php';
        $tecnicos = Usuario::getTecnicos();
        $titulo = $ticket['titulo'] ?? '';
        $desc = $ticket['descripcion'] ?? '';
        $prioridad = $ticket['prioridad'] ?? 'N/A';

        foreach ($tecnicos as $t) {
            if (!empty($t['correo'])) {
                self::send(
                    $t['correo'],
                    "Nuevo reporte creado: $titulo",
                    "Se ha creado un nuevo reporte.\n\nTítulo: $titulo\nDescripción: $desc\nPrioridad: $prioridad\n\nPor favor revisa el sistema para más detalles."
                );
            }
        }
    }

    public static function notificarUsuarioCambio(array $ticket, string $detalles): void {
        if (empty($ticket['creador_correo'])) return;
        self::send(
            $ticket['creador_correo'],
            "Actualización en tu ticket: " . ($ticket['titulo'] ?? ''),
            "Hola " . ($ticket['creador_nombre'] ?? '') . ",\n\nTu ticket ha sido actualizado.\n\nDetalles: $detalles\n\nEstado actual: {$ticket['estado']}\n\nSaludos."
        );
    }

    public static function notificarTecnicoAsignado(array $ticket): void {
        if (empty($ticket['asignado_correo'] ?? ($ticket['asignado_a_id'] ? null : null))) {
            // Buscar correo del asignado
            if (!empty($ticket['asignado_a_id'])) {
                require_once APP_PATH . '/Models/Usuario.php';
                $tecnico = Usuario::findById($ticket['asignado_a_id']);
                if ($tecnico && !empty($tecnico['correo'])) {
                    self::send(
                        $tecnico['correo'],
                        "Se te ha asignado un ticket: " . ($ticket['titulo'] ?? ''),
                        "Hola {$tecnico['nombre']},\n\nSe te ha asignado el ticket:\nTítulo: {$ticket['titulo']}\nDescripción: {$ticket['descripcion']}\n\nPor favor revisa el sistema."
                    );
                }
            }
        }
    }
}
