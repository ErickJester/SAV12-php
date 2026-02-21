<?php

class Usuario {

    public static function findById(int $id): ?array {
        return Database::fetchOne("SELECT * FROM usuarios WHERE id = ?", [$id]);
    }

    public static function findByCorreo(string $correo): ?array {
        return Database::fetchOne("SELECT * FROM usuarios WHERE correo = ?", [$correo]);
    }

    public static function existsByCorreo(string $correo): bool {
        $r = Database::fetchOne("SELECT COUNT(*) as c FROM usuarios WHERE correo = ?", [$correo]);
        return $r['c'] > 0;
    }

    public static function existsByBoleta(string $boleta): bool {
        $r = Database::fetchOne("SELECT COUNT(*) as c FROM usuarios WHERE boleta = ?", [$boleta]);
        return $r['c'] > 0;
    }

    public static function existsByIdTrabajador(string $id): bool {
        $r = Database::fetchOne("SELECT COUNT(*) as c FROM usuarios WHERE id_trabajador = ?", [$id]);
        return $r['c'] > 0;
    }

    public static function crear(array $data): int {
        return Database::insert(
            "INSERT INTO usuarios (nombre, correo, password_hash, rol, boleta, id_trabajador, activo) 
             VALUES (?, ?, ?, ?, ?, ?, 1)",
            [$data['nombre'], $data['correo'], $data['password_hash'], $data['rol'],
             $data['boleta'] ?? null, $data['id_trabajador'] ?? null]
        );
    }

    public static function getAll(): array {
        return Database::fetchAll("SELECT * FROM usuarios ORDER BY nombre");
    }

    public static function getAsignables(): array {
        return Database::fetchAll(
            "SELECT * FROM usuarios WHERE rol IN ('TECNICO', 'ADMIN') AND activo = 1 ORDER BY nombre"
        );
    }

    public static function getTecnicos(): array {
        return Database::fetchAll("SELECT * FROM usuarios WHERE rol = 'TECNICO' AND activo = 1");
    }

    public static function cambiarEstado(int $id, bool $activo): void {
        Database::execute("UPDATE usuarios SET activo = ? WHERE id = ?", [$activo ? 1 : 0, $id]);
    }

    public static function cambiarRol(int $id, string $rol): void {
        Database::execute("UPDATE usuarios SET rol = ? WHERE id = ?", [$rol, $id]);
    }

    public static function update(int $id, array $data): void {
        $sets = [];
        $params = [];
        foreach ($data as $key => $value) {
            $sets[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $id;
        Database::execute("UPDATE usuarios SET " . implode(', ', $sets) . " WHERE id = ?", $params);
    }
}
