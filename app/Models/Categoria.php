<?php

class Categoria {
    public static function getAll(): array {
        return Database::fetchAll("SELECT * FROM categorias ORDER BY nombre");
    }

    public static function getActivas(): array {
        return Database::fetchAll("SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre");
    }

    public static function crear(string $nombre, ?string $descripcion): int {
        return Database::insert(
            "INSERT INTO categorias (nombre, descripcion, activo) VALUES (?, ?, 1)",
            [$nombre, $descripcion]
        );
    }

    public static function desactivar(int $id): void {
        Database::execute("UPDATE categorias SET activo = 0 WHERE id = ?", [$id]);
    }
}
