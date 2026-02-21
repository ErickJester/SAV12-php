<?php
/**
 * CRON: Verificar tickets que han excedido SLA de primera respuesta.
 */

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

$jobName = 'check_sla_breaches';

try {
    cron_init($jobName);

    $ahora = date('Y-m-d H:i:s');
    $tickets = Database::fetchAll(
        "SELECT t.id, t.titulo, t.fecha_creacion, t.estado,
                sp.sla_primera_respuesta_min
         FROM tickets t
         JOIN sla_politicas sp ON t.sla_politica_id = sp.id
         WHERE t.estado IN ('ABIERTO','REABIERTO','EN_PROCESO','EN_ESPERA')
           AND t.fecha_primera_respuesta IS NULL"
    );

    $breaches = 0;
    foreach ($tickets as $ticket) {
        $minutosTranscurridos = (strtotime($ahora) - strtotime((string) $ticket['fecha_creacion'])) / 60;
        if ($minutosTranscurridos > (float) $ticket['sla_primera_respuesta_min']) {
            $breaches++;
            cron_log(
                $jobName,
                'WARN',
                'SLA excedido',
                [
                    'ticket_id' => (int) $ticket['id'],
                    'minutos' => (int) $minutosTranscurridos,
                    'sla_min' => (int) $ticket['sla_primera_respuesta_min'],
                ]
            );
        }
    }

    cron_log($jobName, 'INFO', 'Ejecución finalizada', [
        'status' => 'ok',
        'tickets_analizados' => count($tickets),
        'breaches' => $breaches,
    ]);
    exit(0);
} catch (Throwable $e) {
    cron_handle_exception($jobName, $e);
    exit(1);
}
