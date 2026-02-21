<?php

class Comentario {

    public static function crear(int $ticketId, int $usuarioId, string $contenido): int {
        return Database::insert(
            "INSERT INTO comentarios (ticket_id, usuario_id, contenido, fecha_creacion) VALUES (?, ?, ?, NOW())",
            [$ticketId, $usuarioId, $contenido]
        );
    }

    public static function getByTicket(int $ticketId): array {
        return Database::fetchAll(
            "SELECT c.*, u.nombre AS usuario_nombre, u.rol AS usuario_rol
             FROM comentarios c
             JOIN usuarios u ON c.usuario_id = u.id
             WHERE c.ticket_id = ?
             ORDER BY c.fecha_creacion ASC",
            [$ticketId]
        );
    }
}
