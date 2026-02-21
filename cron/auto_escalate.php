<?php
/**
 * CRON: Auto-escalar tickets sin asignar después de 60 minutos.
 */

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

$jobName = 'auto_escalate';

try {
    cron_init($jobName);

    $tickets = Database::fetchAll(
        "SELECT t.id, t.prioridad
         FROM tickets t
         WHERE t.asignado_a_id IS NULL
           AND t.estado IN ('ABIERTO','REABIERTO')
           AND TIMESTAMPDIFF(MINUTE, t.fecha_creacion, NOW()) > 60"
    );

    $escalados = 0;
    $skipped = 0;

    foreach ($tickets as $ticket) {
        $prioridadActual = (string) $ticket['prioridad'];
        if (in_array($prioridadActual, ['ALTA', 'URGENTE'], true)) {
            $skipped++;
            continue;
        }

        $nuevaPrioridad = $prioridadActual === 'BAJA' ? 'MEDIA' : 'ALTA';
        Database::execute('UPDATE tickets SET prioridad = ? WHERE id = ?', [$nuevaPrioridad, (int) $ticket['id']]);
        $escalados++;

        cron_log($jobName, 'INFO', 'Ticket escalado', [
            'ticket_id' => (int) $ticket['id'],
            'from' => $prioridadActual,
            'to' => $nuevaPrioridad,
        ]);
    }

    cron_log($jobName, 'INFO', 'Ejecución finalizada', [
        'status' => 'ok',
        'candidatos' => count($tickets),
        'escalados' => $escalados,
        'skipped' => $skipped,
    ]);
    exit(0);
} catch (Throwable $e) {
    cron_handle_exception($jobName, $e);
    exit(1);
}
