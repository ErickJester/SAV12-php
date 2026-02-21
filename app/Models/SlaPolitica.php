<?php

class SlaPolitica {

    public static function findByRol(string $rol): ?array {
        return Database::fetchOne(
            "SELECT * FROM sla_politicas WHERE rol_solicitante = ? AND activo = 1 LIMIT 1",
            [$rol]
        );
    }

    public static function getIdParaRol(string $rol): ?int {
        $rolBuscar = in_array($rol, ['ALUMNO', 'DOCENTE', 'ADMINISTRATIVO']) ? $rol : 'ALUMNO';
        $sla = self::findByRol($rolBuscar);
        if (!$sla) {
            $sla = self::findByRol('ALUMNO');
        }
        return $sla ? (int) $sla['id'] : null;
    }

    public static function getAll(): array {
        return Database::fetchAll("SELECT * FROM sla_politicas ORDER BY rol_solicitante");
    }
}
