<?php

require_once APP_PATH . '/Models/Ticket.php';
require_once APP_PATH . '/Models/HistorialAccion.php';
require_once APP_PATH . '/Models/SlaPolitica.php';
require_once APP_PATH . '/Services/EmailService.php';

class TicketService {

    public static function crear(array $data, array $usuario): int {
        $slaId = SlaPolitica::getIdParaRol($usuario['rol']);

        $ticketId = Ticket::crear([
            'titulo'            => $data['titulo'],
            'descripcion'       => $data['descripcion'],
            'prioridad'         => $data['prioridad'] ?? 'MEDIA',
            'creado_por_id'     => $usuario['id'],
            'categoria_id'      => $data['categoria_id'] ?? null,
            'ubicacion_id'      => $data['ubicacion_id'] ?? null,
            'sla_politica_id'   => $slaId,
            'evidencia_problema'=> $data['evidencia_problema'] ?? null,
        ]);

        HistorialAccion::registrar([
            'ticket_id'  => $ticketId,
            'usuario_id' => $usuario['id'],
            'tipo'       => 'CREACION',
            'accion'     => 'Ticket creado',
            'estado_nuevo'=> 'ABIERTO',
        ]);

        // Notificar técnicos
        $ticket = Ticket::findById($ticketId);
        if ($ticket) {
            EmailService::notificarTecnicosNuevoTicket($ticket);
        }

        return $ticketId;
    }

    public static function cambiarEstado(int $ticketId, string $nuevoEstado, array $usuario, ?string $observaciones = null, ?string $evidenciaResolucion = null): void {
        $ticket = Ticket::findById($ticketId);
        if (!$ticket) throw new Exception('Ticket no encontrado');

        $estadoAnterior = $ticket['estado'];
        $ahora = date('Y-m-d H:i:s');
        $updates = [
            'estado'              => $nuevoEstado,
            'fecha_actualizacion' => $ahora,
        ];

        // REABIERTO
        if ($nuevoEstado === 'REABIERTO') {
            $reabiertos = ($ticket['reabierto_count'] ?? 0) + 1;
            $updates['reabierto_count'] = $reabiertos;
            $updates['fecha_resolucion'] = null;
            $updates['tiempo_resolucion_seg'] = null;
            $updates['fecha_cierre'] = null;
            $updates['evidencia_resolucion'] = null;
        }

        // EN_ESPERA: arranca timer
        if ($nuevoEstado === 'EN_ESPERA' && empty($ticket['espera_desde'])) {
            $updates['espera_desde'] = $ahora;
        }

        // Salida de EN_ESPERA: acumula
        if ($estadoAnterior === 'EN_ESPERA' && $nuevoEstado !== 'EN_ESPERA' && !empty($ticket['espera_desde'])) {
            $esperaSeg = strtotime($ahora) - strtotime($ticket['espera_desde']);
            $acumulado = $ticket['tiempo_espera_seg'] ?? 0;
            $updates['tiempo_espera_seg'] = $acumulado + $esperaSeg;
            $updates['espera_desde'] = null;
        }

        // Primera respuesta (staff)
        if (empty($ticket['fecha_primera_respuesta']) && in_array($usuario['rol'], ['TECNICO', 'ADMIN'])) {
            $updates['fecha_primera_respuesta'] = $ahora;
            $seg = strtotime($ahora) - strtotime($ticket['fecha_creacion']);
            $updates['tiempo_primera_respuesta_seg'] = $seg;
        }

        // RESUELTO
        if ($nuevoEstado === 'RESUELTO') {
            $updates['fecha_resolucion'] = $ahora;
            $seg = strtotime($ahora) - strtotime($ticket['fecha_creacion']);
            $updates['tiempo_resolucion_seg'] = $seg;
            if ($evidenciaResolucion) {
                $updates['evidencia_resolucion'] = $evidenciaResolucion;
            }
        }

        // CERRADO
        if ($nuevoEstado === 'CERRADO') {
            $updates['fecha_cierre'] = $ahora;
            if (empty($ticket['fecha_resolucion'])) {
                $updates['fecha_resolucion'] = $ahora;
            }
            $fechaResol = $updates['fecha_resolucion'] ?? $ticket['fecha_resolucion'];
            $seg = strtotime($fechaResol) - strtotime($ticket['fecha_creacion']);
            $updates['tiempo_resolucion_seg'] = $seg;
            if ($evidenciaResolucion) {
                $updates['evidencia_resolucion'] = $evidenciaResolucion;
            }
        }

        Ticket::update($ticketId, $updates);

        // Historial
        $tipo = ($nuevoEstado === 'REABIERTO') ? 'REAPERTURA' : 'ESTADO';
        $accion = ($nuevoEstado === 'REABIERTO')
            ? 'Ticket reabierto'
            : "Estado cambiado de $estadoAnterior a $nuevoEstado";

        HistorialAccion::registrar([
            'ticket_id'       => $ticketId,
            'usuario_id'      => $usuario['id'],
            'tipo'            => $tipo,
            'accion'          => $accion,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo'    => $nuevoEstado,
            'detalles'        => $observaciones,
        ]);

        // Emails
        $ticketActualizado = Ticket::findById($ticketId);
        EmailService::notificarUsuarioCambio($ticketActualizado, $accion . ($observaciones ? " - $observaciones" : ''));

        if ($nuevoEstado === 'REABIERTO') {
            if (!empty($ticketActualizado['asignado_a_id'])) {
                EmailService::notificarTecnicoAsignado($ticketActualizado);
            } else {
                EmailService::notificarTecnicosNuevoTicket($ticketActualizado);
            }
        }
    }

    public static function asignarTecnico(int $ticketId, int $tecnicoId, array $actor): void {
        $ticket = Ticket::findById($ticketId);
        if (!$ticket) throw new Exception('Ticket no encontrado');

        $asignadoAnteriorId = $ticket['asignado_a_id'];
        $ahora = date('Y-m-d H:i:s');
        $updates = [
            'asignado_a_id'       => $tecnicoId,
            'fecha_actualizacion' => $ahora,
        ];

        // Primera respuesta
        if (empty($ticket['fecha_primera_respuesta']) && in_array($actor['rol'], ['TECNICO', 'ADMIN'])) {
            $updates['fecha_primera_respuesta'] = $ahora;
            $seg = strtotime($ahora) - strtotime($ticket['fecha_creacion']);
            $updates['tiempo_primera_respuesta_seg'] = $seg;
        }

        Ticket::update($ticketId, $updates);

        HistorialAccion::registrar([
            'ticket_id'            => $ticketId,
            'usuario_id'           => $actor['id'],
            'tipo'                 => 'ASIGNACION',
            'accion'               => 'Asignación de ticket',
            'asignado_anterior_id' => $asignadoAnteriorId,
            'asignado_nuevo_id'    => $tecnicoId,
        ]);

        $ticketActualizado = Ticket::findById($ticketId);
        EmailService::notificarTecnicoAsignado($ticketActualizado);
    }

    public static function reabrirTicket(int $ticketId, array $usuario): void {
        self::cambiarEstado($ticketId, 'REABIERTO', $usuario, 'Ticket reabierto');
    }
}
