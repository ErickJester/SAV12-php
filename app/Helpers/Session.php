<?php
/**
 * Manejo de sesiones y autenticación
 */

class Session {
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set(string $key, $value): void {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void {
        session_destroy();
        $_SESSION = [];
    }

    // === Auth helpers ===

    public static function login(array $usuario): void {
        self::set('user_id', $usuario['id']);
        self::set('user_nombre', $usuario['nombre']);
        self::set('user_correo', $usuario['correo']);
        self::set('user_rol', $usuario['rol']);
        self::regenerate();
    }

    public static function logout(): void {
        self::destroy();
    }

    public static function isLoggedIn(): bool {
        return self::has('user_id');
    }

    public static function userId(): ?int {
        return self::get('user_id');
    }

    public static function userRol(): ?string {
        return self::get('user_rol');
    }

    public static function userName(): ?string {
        return self::get('user_nombre');
    }

    public static function userEmail(): ?string {
        return self::get('user_correo');
    }

    public static function isAdmin(): bool {
        return self::userRol() === 'ADMIN';
    }

    public static function isTecnico(): bool {
        return in_array(self::userRol(), ['TECNICO', 'ADMIN']);
    }

    public static function isUsuarioFinal(): bool {
        return in_array(self::userRol(), ['ALUMNO', 'DOCENTE', 'ADMINISTRATIVO']);
    }

    public static function isStaff(): bool {
        return in_array(self::userRol(), ['TECNICO', 'ADMIN']);
    }

    // CSRF protection
    public static function csrfToken(): string {
        if (!self::has('csrf_token')) {
            self::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return self::get('csrf_token');
    }

    public static function csrfField(): string {
        return '<input type="hidden" name="_token" value="' . self::csrfToken() . '">';
    }

    public static function verifyCsrf(): bool {
        $token = $_POST['_token'] ?? '';
        return hash_equals(self::csrfToken(), $token);
    }

    // Flash messages
    public static function flash(string $key, string $message): void {
        self::set('flash_' . $key, $message);
    }

    public static function getFlash(string $key): ?string {
        $value = self::get('flash_' . $key);
        self::remove('flash_' . $key);
        return $value;
    }

    // Regenerate session ID
    public static function regenerate(): void {
        session_regenerate_id(true);
    }
}
