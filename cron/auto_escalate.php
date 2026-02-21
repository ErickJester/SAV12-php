<?php
/**
 * CRON: Auto-escalar tickets sin asignar después de X minutos
 * Ejecutar cada 15 min: */15 * * * * php /home/u123/sav12-php/cron/auto_escalate.php
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once APP_PATH . '/Helpers/Database.php';

$ahora = date('Y-m-d H:i:s');

// Tickets sin asignar por más de 60 minutos
$tickets = Database::fetchAll(
    "SELECT t.id, t.titulo, t.fecha_creacion, t.prioridad
     FROM tickets t
     WHERE t.asignado_a_id IS NULL
       AND t.estado IN ('ABIERTO','REABIERTO')
       AND TIMESTAMPDIFF(MINUTE, t.fecha_creacion, NOW()) > 60"
);

$escalados = 0;
foreach ($tickets as $ticket) {
    // Subir prioridad si aún no es ALTA/URGENTE
    if (!in_array($ticket['prioridad'], ['ALTA', 'URGENTE'])) {
        $nuevaPrioridad = $ticket['prioridad'] === 'BAJA' ? 'MEDIA' : 'ALTA';
        Database::execute("UPDATE tickets SET prioridad = ? WHERE id = ?", [$nuevaPrioridad, $ticket['id']]);
        $escalados++;
        error_log("[ESCALATION] Ticket #{$ticket['id']} escalado a $nuevaPrioridad");
    }
}

echo date('Y-m-d H:i:s') . " - Escalation: $escalados tickets escalated\n";
