<?php

require_once APP_PATH . '/Models/Usuario.php';
require_once APP_PATH . '/Services/EmailService.php';
require_once APP_PATH . '/Helpers/Validator.php';

class AuthController {

    public function loginForm(): void {
        if (Session::isLoggedIn()) {
            $this->redirectByRole();
            return;
        }
        $error = $_GET['error'] ?? null;
        $success = $_GET['registro'] ?? null;
        include VIEW_PATH . '/auth/login.php';
    }

    public function login(): void {
        AuthMiddleware::verifyCsrf();

        $correo = trim($_POST['correo'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($correo) || empty($password)) {
            header('Location: /login?error=1');
            exit;
        }

        $usuario = Usuario::findByCorreo($correo);

        if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
            header('Location: /login?error=1');
            exit;
        }

        if (!$usuario['activo']) {
            header('Location: /login?error=disabled');
            exit;
        }

        Session::login($usuario);
        $this->redirectByRole();
    }

    public function registroForm(): void {
        include VIEW_PATH . '/auth/registro.php';
    }

    public function registro(): void {
        AuthMiddleware::verifyCsrf();

        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $rol = trim($_POST['rol'] ?? '');
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        $boleta = trim($_POST['boleta'] ?? '') ?: null;
        $idTrabajador = trim($_POST['id_trabajador'] ?? '') ?: null;

        $slaConfig = require BASE_PATH . '/config/sla.php';
        $rolesPermitidos = ['ALUMNO', 'DOCENTE', 'ADMINISTRATIVO'];

        $v = new Validator();
        $v->required('nombre', $nombre, 'Nombre')
          ->required('correo', $correo, 'Correo')
          ->email('correo', $correo)
          ->required('rol', $rol, 'Tipo de cuenta')
          ->inArray('rol', $rol, $rolesPermitidos, 'Tipo de cuenta')
          ->required('password', $password, 'Contraseña')
          ->minLength('password', $password, 6, 'Contraseña')
          ->matches('password2', $password, $password2, 'Las contraseñas no coinciden');

        if ($v->fails()) {
            $mensaje = $v->firstError();
            $registro = $_POST;
            include VIEW_PATH . '/auth/registro.php';
            return;
        }

        if (Usuario::existsByCorreo($correo)) {
            $mensaje = 'El correo ya está registrado.';
            $registro = $_POST;
            include VIEW_PATH . '/auth/registro.php';
            return;
        }

        if ($rol === 'ALUMNO') {
            if (empty($boleta)) {
                $mensaje = 'Boleta requerida para cuenta ALUMNO.';
                $registro = $_POST;
                include VIEW_PATH . '/auth/registro.php';
                return;
            }
            if (Usuario::existsByBoleta($boleta)) {
                $mensaje = 'La boleta ya está registrada.';
                $registro = $_POST;
                include VIEW_PATH . '/auth/registro.php';
                return;
            }
            $idTrabajador = null;
        } else {
            if (empty($idTrabajador)) {
                $mensaje = 'ID de trabajador requerido.';
                $registro = $_POST;
                include VIEW_PATH . '/auth/registro.php';
                return;
            }
            if (Usuario::existsByIdTrabajador($idTrabajador)) {
                $mensaje = 'El ID de trabajador ya está registrado.';
                $registro = $_POST;
                include VIEW_PATH . '/auth/registro.php';
                return;
            }
            $boleta = null;
        }

        try {
            $userId = Usuario::crear([
                'nombre'        => $nombre,
                'correo'        => $correo,
                'password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
                'rol'           => $rol,
                'boleta'        => $boleta,
                'id_trabajador' => $idTrabajador,
            ]);

            $usuario = Usuario::findById($userId);
            if ($usuario) {
                EmailService::enviarBienvenida($usuario);
            }

            header('Location: /login?registro=success');
            exit;
        } catch (Exception $e) {
            $mensaje = 'Error al crear la cuenta. Intenta de nuevo.';
            error_log('Registro error: ' . $e->getMessage());
            $registro = $_POST;
            include VIEW_PATH . '/auth/registro.php';
        }
    }

    public function logout(): void {
        Session::logout();
        header('Location: /login?logout=1');
        exit;
    }

    public function forbidden(): void {
        http_response_code(403);
        include VIEW_PATH . '/auth/403.php';
    }

    private function redirectByRole(): void {
        if (Session::isAdmin()) {
            header('Location: /admin/panel');
        } elseif (Session::isTecnico()) {
            header('Location: /tecnico/panel');
        } else {
            header('Location: /usuario/panel');
        }
        exit;
    }
}
