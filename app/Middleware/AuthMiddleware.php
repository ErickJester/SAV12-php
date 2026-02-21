<?php

class AuthMiddleware {

    /**
     * Requiere que el usuario esté autenticado
     */
    public static function requireLogin(): void {
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Requiere rol ADMIN
     */
    public static function requireAdmin(): void {
        self::requireLogin();
        if (!Session::isAdmin()) {
            header('Location: /403');
            exit;
        }
    }

    /**
     * Requiere rol TECNICO o ADMIN
     */
    public static function requireTecnico(): void {
        self::requireLogin();
        if (!Session::isTecnico()) {
            header('Location: /403');
            exit;
        }
    }

    /**
     * Requiere rol de usuario final (ALUMNO, DOCENTE, ADMINISTRATIVO)
     */
    public static function requireUsuario(): void {
        self::requireLogin();
        // Los técnicos y admins también pueden ver la vista de usuario si quieren
    }

    /**
     * Verificar CSRF en POST
     */
    public static function verifyCsrf(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                http_response_code(403);
                die('Token CSRF inválido. <a href="javascript:history.back()">Volver</a>');
            }
        }
    }

    /**
     * Obtener usuario actual completo desde BD
     */
    public static function currentUser(): ?array {
        if (!Session::isLoggedIn()) return null;
        require_once APP_PATH . '/Models/Usuario.php';
        return Usuario::findById(Session::userId());
    }
}
