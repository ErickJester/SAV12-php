<?php

require_once APP_PATH . '/Models/Comentario.php';
require_once APP_PATH . '/Models/Ticket.php';
require_once APP_PATH . '/Models/HistorialAccion.php';

class ComentarioService {

    public static function agregar(int $ticketId, array $usuario, string $contenido): int {
        $ticket = Ticket::findById($ticketId);
        if (!$ticket) throw new Exception('Ticket no encontrado');

        // Si es staff y no hay primera respuesta, registrarla
        if (in_array($usuario['rol'], ['TECNICO', 'ADMIN']) && empty($ticket['fecha_primera_respuesta'])) {
            $ahora = date('Y-m-d H:i:s');
            $seg = strtotime($ahora) - strtotime($ticket['fecha_creacion']);
            Ticket::update($ticketId, [
                'fecha_primera_respuesta'      => $ahora,
                'tiempo_primera_respuesta_seg' => $seg,
            ]);
        }

        $comentarioId = Comentario::crear($ticketId, $usuario['id'], $contenido);

        HistorialAccion::registrar([
            'ticket_id'  => $ticketId,
            'usuario_id' => $usuario['id'],
            'tipo'       => 'COMENTARIO',
            'accion'     => 'Comentario agregado',
            'detalles'   => $contenido,
        ]);

        return $comentarioId;
    }
}
