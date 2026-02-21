<?php
/**
 * CRON: Verificar tickets que han excedido SLA
 * Ejecutar cada 5 minutos: */5 * * * * php /home/u123/sav12-php/cron/check_sla_breaches.php
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once APP_PATH . '/Helpers/Database.php';

$ahora = date('Y-m-d H:i:s');

// Buscar tickets activos sin primera respuesta que exceden SLA
$tickets = Database::fetchAll(
    "SELECT t.id, t.titulo, t.fecha_creacion, t.estado,
            sp.sla_primera_respuesta_min, sp.sla_resolucion_min,
            ua.nombre AS asignado_nombre, ua.correo AS asignado_correo,
            uc.nombre AS creador_nombre
     FROM tickets t
     JOIN sla_politicas sp ON t.sla_politica_id = sp.id
     LEFT JOIN usuarios ua ON t.asignado_a_id = ua.id
     LEFT JOIN usuarios uc ON t.creado_por_id = uc.id
     WHERE t.estado IN ('ABIERTO','REABIERTO','EN_PROCESO','EN_ESPERA')
       AND t.fecha_primera_respuesta IS NULL"
);

$breaches = 0;
foreach ($tickets as $ticket) {
    $minutosTranscurridos = (strtotime($ahora) - strtotime($ticket['fecha_creacion'])) / 60;

    if ($minutosTranscurridos > $ticket['sla_primera_respuesta_min']) {
        $breaches++;
        error_log(sprintf(
            "[SLA BREACH] Ticket #%d '%s' - %.0f min sin respuesta (SLA: %d min)",
            $ticket['id'], $ticket['titulo'], $minutosTranscurridos, $ticket['sla_primera_respuesta_min']
        ));
    }
}

echo date('Y-m-d H:i:s') . " - SLA Check: $breaches breaches found out of " . count($tickets) . " active tickets\n";
