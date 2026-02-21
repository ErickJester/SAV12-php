<?php
/**
 * CRON: Enviar recordatorios de tickets pendientes sin actividad.
 */

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

$jobName = 'send_reminders';

try {
    cron_init($jobName);
    require_once APP_PATH . '/Services/EmailService.php';

    $tickets = Database::fetchAll(
        "SELECT t.id, t.titulo, t.estado, t.fecha_actualizacion,
                ua.nombre AS asignado_nombre, ua.correo AS asignado_correo
         FROM tickets t
         JOIN usuarios ua ON t.asignado_a_id = ua.id
         WHERE t.estado IN ('EN_PROCESO','EN_ESPERA')
           AND TIMESTAMPDIFF(HOUR, t.fecha_actualizacion, NOW()) > 4"
    );

    $enviados = 0;
    $omitidos = 0;

    foreach ($tickets as $ticket) {
        $correo = (string) ($ticket['asignado_correo'] ?? '');
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $omitidos++;
            cron_log($jobName, 'WARN', 'Recordatorio omitido por correo inválido', [
                'ticket_id' => (int) $ticket['id'],
            ]);
            continue;
        }

        $ok = EmailService::sendTestEmail(
            $correo,
            'SAV12 Recordatorio de ticket pendiente #' . (int) $ticket['id'],
            "Hola {$ticket['asignado_nombre']},\n\n" .
            "El ticket #{$ticket['id']} ({$ticket['titulo']}) permanece en estado {$ticket['estado']} sin actividad reciente.\n" .
            "Última actualización registrada: {$ticket['fecha_actualizacion']}.\n\n" .
            "Por favor revisa SAV12 para continuar la atención."
        );

        if ($ok) {
            $enviados++;
            cron_log($jobName, 'INFO', 'Recordatorio enviado', [
                'ticket_id' => (int) $ticket['id'],
                'to' => $correo,
            ]);
            continue;
        }

        $omitidos++;
        cron_log($jobName, 'WARN', 'Recordatorio no enviado', [
            'ticket_id' => (int) $ticket['id'],
            'to' => $correo,
        ]);
    }

    cron_log($jobName, 'INFO', 'Ejecución finalizada', [
        'status' => 'ok',
        'tickets_candidatos' => count($tickets),
        'enviados' => $enviados,
        'omitidos' => $omitidos,
    ]);
    exit(0);
} catch (Throwable $e) {
    cron_handle_exception($jobName, $e);
    exit(1);
}
