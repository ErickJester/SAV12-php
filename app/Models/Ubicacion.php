<?php

class Ubicacion {
    public static function getAll(): array {
        return Database::fetchAll("SELECT * FROM ubicaciones ORDER BY edificio, piso, salon");
    }

    public static function getActivas(): array {
        return Database::fetchAll("SELECT * FROM ubicaciones WHERE activo = 1 ORDER BY edificio, piso, salon");
    }

    public static function crear(string $edificio, ?string $piso, ?string $salon): int {
        return Database::insert(
            "INSERT INTO ubicaciones (edificio, piso, salon, activo) VALUES (?, ?, ?, 1)",
            [$edificio, $piso, $salon]
        );
    }

    public static function desactivar(int $id): void {
        Database::execute("UPDATE ubicaciones SET activo = 0 WHERE id = ?", [$id]);
    }
}
