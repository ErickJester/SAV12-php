<?php
/**
 * Servicio de Email - SMTP (PHPMailer si está disponible) + fallback nativo.
 */

class EmailService {

    private static function send(string $to, string $subject, string $body): bool {
        $config = require BASE_PATH . '/config/mail.php';

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log('[EmailService] Destino inválido, envío omitido.');
            return false;
        }

        if (empty($config['from']) || !filter_var((string) $config['from'], FILTER_VALIDATE_EMAIL)) {
            error_log('[EmailService] MAIL_FROM inválido, envío omitido.');
            return false;
        }

        if (empty($config['enabled'])) {
            error_log("[EmailService] MAIL_ENABLED=false; envío omitido a {$to}");
            return false;
        }

        $phpmailerPath = BASE_PATH . '/vendor/autoload.php';
        if (file_exists($phpmailerPath)) {
            require_once $phpmailerPath;
            if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                $ok = self::sendWithPHPMailer($to, $subject, $body, $config);
                if ($ok) {
                    return true;
                }
                error_log("[EmailService] SMTP falló (host={$config['host']} port={$config['port']}), intentando fallback mail().");
            }
        }

        return self::sendWithNativeMail($to, $subject, $body, $config);
    }

    private static function sendWithNativeMail(string $to, string $subject, string $body, array $config): bool {
        $headers = "From: {$config['from_name']} <{$config['from']}>\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $ok = @mail($to, $subject, $body, $headers);
        if (!$ok) {
            error_log('[EmailService] Fallback mail() falló.');
        }
        return $ok;
    }

    private static function sendWithPHPMailer(string $to, string $subject, string $body, array $config): bool {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = (string) $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = (string) $config['username'];
            $mail->Password   = (string) $config['password'];
            $mail->SMTPSecure = (string) $config['encryption'];
            $mail->Port       = (int) $config['port'];
            $mail->CharSet    = 'UTF-8';
            $mail->Timeout    = 10;

            $mail->setFrom((string) $config['from'], (string) $config['from_name']);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Throwable $e) {
            error_log('[EmailService] SMTP error: ' . $e->getMessage());
            return false;
        }
    }

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
            'Actualización en tu ticket: ' . ($ticket['titulo'] ?? ''),
            "Hola " . ($ticket['creador_nombre'] ?? '') . ",\n\nTu ticket ha sido actualizado.\n\nDetalles: $detalles\n\nEstado actual: {$ticket['estado']}\n\nSaludos."
        );
    }

    public static function notificarTecnicoAsignado(array $ticket): void {
        $destino = null;
        $nombreTecnico = 'Técnico';

        // 1) Enviar directo si asignado_correo viene en payload
        if (!empty($ticket['asignado_correo']) && filter_var((string) $ticket['asignado_correo'], FILTER_VALIDATE_EMAIL)) {
            $destino = (string) $ticket['asignado_correo'];
            $nombreTecnico = (string) ($ticket['asignado_nombre'] ?? $nombreTecnico);
        }

        // 2) Si no viene correo, buscar por asignado_a_id
        if ($destino === null && !empty($ticket['asignado_a_id'])) {
            require_once APP_PATH . '/Models/Usuario.php';
            $tecnico = Usuario::findById((int) $ticket['asignado_a_id']);
            if ($tecnico && !empty($tecnico['correo']) && filter_var((string) $tecnico['correo'], FILTER_VALIDATE_EMAIL)) {
                $destino = (string) $tecnico['correo'];
                $nombreTecnico = (string) ($tecnico['nombre'] ?? $nombreTecnico);
            }
        }

        // 3) Si no hay destinatario resoluble, log y salir
        if ($destino === null) {
            error_log('[EmailService] notificarTecnicoAsignado sin destinatario válido.');
            return;
        }

        self::send(
            $destino,
            'Se te ha asignado un ticket: ' . ($ticket['titulo'] ?? ''),
            "Hola {$nombreTecnico},\n\nSe te ha asignado el ticket:\nTítulo: {$ticket['titulo']}\nDescripción: {$ticket['descripcion']}\n\nPor favor revisa el sistema."
        );
    }

    // Alias opcionales de paridad semántica
    public static function sendTestEmail(string $to, string $subject = 'SAV12 Test Email', string $body = 'Correo de prueba SAV12'): bool {
        return self::send($to, $subject, $body);
    }

    public static function sendLoginConfirmation(array $usuario): bool {
        return self::send(
            (string) ($usuario['correo'] ?? ''),
            'Confirmación de inicio de sesión',
            "Hola " . ($usuario['nombre'] ?? 'usuario') . ",\n\nSe detectó un inicio de sesión en tu cuenta SAV12."
        );
    }
}
