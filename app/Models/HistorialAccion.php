<?php

class HistorialAccion {

    public static function registrar(array $data): int {
        return Database::insert(
            "INSERT INTO historial_acciones 
             (ticket_id, usuario_id, tipo, accion, estado_anterior, estado_nuevo, 
              asignado_anterior_id, asignado_nuevo_id, detalles, fecha_accion)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $data['ticket_id'], $data['usuario_id'], $data['tipo'], $data['accion'],
                $data['estado_anterior'] ?? null, $data['estado_nuevo'] ?? null,
                $data['asignado_anterior_id'] ?? null, $data['asignado_nuevo_id'] ?? null,
                $data['detalles'] ?? null
            ]
        );
    }

    public static function getByTicket(int $ticketId): array {
        return Database::fetchAll(
            "SELECT h.*, u.nombre AS usuario_nombre,
                    ua.nombre AS asignado_anterior_nombre,
                    un.nombre AS asignado_nuevo_nombre
             FROM historial_acciones h
             JOIN usuarios u ON h.usuario_id = u.id
             LEFT JOIN usuarios ua ON h.asignado_anterior_id = ua.id
             LEFT JOIN usuarios un ON h.asignado_nuevo_id = un.id
             WHERE h.ticket_id = ?
             ORDER BY h.fecha_accion DESC",
            [$ticketId]
        );
    }
}
