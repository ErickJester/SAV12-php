<?php

class Ticket {

    public static function findById(int $id): ?array {
        return Database::fetchOne(
            "SELECT t.*, 
                    uc.nombre AS creador_nombre, uc.correo AS creador_correo, uc.rol AS creador_rol,
                    ua.nombre AS asignado_nombre, ua.correo AS asignado_correo,
                    c.nombre AS categoria_nombre,
                    CONCAT(u2.edificio, ' - ', COALESCE(u2.piso,''), ' - ', COALESCE(u2.salon,'')) AS ubicacion_nombre,
                    sp.sla_primera_respuesta_min, sp.sla_resolucion_min
             FROM tickets t
             LEFT JOIN usuarios uc ON t.creado_por_id = uc.id
             LEFT JOIN usuarios ua ON t.asignado_a_id = ua.id
             LEFT JOIN categorias c ON t.categoria_id = c.id
             LEFT JOIN ubicaciones u2 ON t.ubicacion_id = u2.id
             LEFT JOIN sla_politicas sp ON t.sla_politica_id = sp.id
             WHERE t.id = ?",
            [$id]
        );
    }

    public static function crear(array $data): int {
        return Database::insert(
            "INSERT INTO tickets (titulo, descripcion, estado, prioridad, creado_por_id, categoria_id, 
                                  ubicacion_id, sla_politica_id, evidencia_problema, fecha_creacion, fecha_actualizacion)
             VALUES (?, ?, 'ABIERTO', ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $data['titulo'], $data['descripcion'], $data['prioridad'] ?? 'MEDIA',
                $data['creado_por_id'], $data['categoria_id'] ?? null,
                $data['ubicacion_id'] ?? null, $data['sla_politica_id'] ?? null,
                $data['evidencia_problema'] ?? null
            ]
        );
    }

    public static function getByUsuario(int $userId): array {
        return Database::fetchAll(
            "SELECT t.*, c.nombre AS categoria_nombre, ua.nombre AS asignado_nombre
             FROM tickets t
             LEFT JOIN categorias c ON t.categoria_id = c.id
             LEFT JOIN usuarios ua ON t.asignado_a_id = ua.id
             WHERE t.creado_por_id = ?
             ORDER BY t.fecha_creacion DESC",
            [$userId]
        );
    }

    public static function getByTecnico(int $tecnicoId): array {
        return Database::fetchAll(
            "SELECT t.*, c.nombre AS categoria_nombre, uc.nombre AS creador_nombre
             FROM tickets t
             LEFT JOIN categorias c ON t.categoria_id = c.id
             LEFT JOIN usuarios uc ON t.creado_por_id = uc.id
             WHERE t.asignado_a_id = ?
             ORDER BY t.fecha_creacion DESC",
            [$tecnicoId]
        );
    }

    public static function getAll(?string $filtro = null): array {
        $sql = "SELECT t.*, c.nombre AS categoria_nombre, 
                       uc.nombre AS creador_nombre, ua.nombre AS asignado_nombre,
                       sp.sla_primera_respuesta_min, sp.sla_resolucion_min
                FROM tickets t
                LEFT JOIN categorias c ON t.categoria_id = c.id
                LEFT JOIN usuarios uc ON t.creado_por_id = uc.id
                LEFT JOIN usuarios ua ON t.asignado_a_id = ua.id
                LEFT JOIN sla_politicas sp ON t.sla_politica_id = sp.id";

        if ($filtro === 'activo') {
            $sql .= " WHERE t.estado IN ('ABIERTO','REABIERTO','EN_PROCESO','EN_ESPERA')";
        } elseif ($filtro === 'resuelto') {
            $sql .= " WHERE t.estado IN ('RESUELTO','CERRADO')";
        }

        $sql .= " ORDER BY t.fecha_creacion DESC";
        return Database::fetchAll($sql);
    }

    public static function getSinAsignar(): array {
        return Database::fetchAll(
            "SELECT t.*, c.nombre AS categoria_nombre, uc.nombre AS creador_nombre
             FROM tickets t
             LEFT JOIN categorias c ON t.categoria_id = c.id
             LEFT JOIN usuarios uc ON t.creado_por_id = uc.id
             WHERE t.asignado_a_id IS NULL 
               AND t.estado IN ('ABIERTO','REABIERTO')
             ORDER BY t.fecha_creacion DESC"
        );
    }

    public static function update(int $id, array $data): void {
        $sets = [];
        $params = [];
        foreach ($data as $key => $value) {
            $sets[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $id;
        Database::execute("UPDATE tickets SET " . implode(', ', $sets) . " WHERE id = ?", $params);
    }

    // Para reportes - obtener tickets por periodo
    public static function getByPeriodo(string $desde, string $hasta): array {
        return Database::fetchAll(
            "SELECT t.*, 
                    uc.nombre AS creador_nombre, uc.rol AS creador_rol,
                    ua.nombre AS asignado_nombre,
                    c.nombre AS categoria_nombre,
                    CONCAT(u2.edificio, ' - ', COALESCE(u2.piso,''), ' - ', COALESCE(u2.salon,'')) AS ubicacion_nombre,
                    sp.sla_primera_respuesta_min, sp.sla_resolucion_min
             FROM tickets t
             LEFT JOIN usuarios uc ON t.creado_por_id = uc.id
             LEFT JOIN usuarios ua ON t.asignado_a_id = ua.id
             LEFT JOIN categorias c ON t.categoria_id = c.id
             LEFT JOIN ubicaciones u2 ON t.ubicacion_id = u2.id
             LEFT JOIN sla_politicas sp ON t.sla_politica_id = sp.id
             WHERE t.fecha_creacion BETWEEN ? AND ?
             ORDER BY t.fecha_creacion DESC",
            [$desde, $hasta]
        );
    }

    public static function countByEstado(): array {
        return Database::fetchAll(
            "SELECT estado, COUNT(*) as total FROM tickets GROUP BY estado"
        );
    }
}
