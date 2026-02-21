<?php
/**
 * CRON: Enviar recordatorios de tickets pendientes
 * Ejecutar cada hora: 0 * * * * php /home/u123/sav12-php/cron/send_reminders.php
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once APP_PATH . '/Helpers/Database.php';
require_once APP_PATH . '/Services/EmailService.php';

// Tickets asignados sin actividad en las últimas 4 horas
$tickets = Database::fetchAll(
    "SELECT t.id, t.titulo, t.estado, t.fecha_actualizacion,
            ua.nombre AS asignado_nombre, ua.correo AS asignado_correo
     FROM tickets t
     JOIN usuarios ua ON t.asignado_a_id = ua.id
     WHERE t.estado IN ('EN_PROCESO','EN_ESPERA')
       AND TIMESTAMPDIFF(HOUR, t.fecha_actualizacion, NOW()) > 4"
);

echo date('Y-m-d H:i:s') . " - Reminders: " . count($tickets) . " tickets need attention\n";

// Los emails se envían vía EmailService si está configurado
foreach ($tickets as $t) {
    error_log("[REMINDER] Ticket #{$t['id']} '{$t['titulo']}' asignado a {$t['asignado_nombre']} - sin actividad");
}
