<?php $pageTitle = 'Iniciar Sesión - SAV12'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; }
        .login-card { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 450px; width: 100%; overflow: hidden; }
        .login-header { background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%); padding: 40px 30px; text-align: center; color: white; }
        .login-header h2 { font-size: 32px; font-weight: 700; margin: 0; }
        .login-body { padding: 40px; }
        .form-control { padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; }
        .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .btn-login { width: 100%; padding: 12px; background: linear-gradient(135deg, #2563eb, #7c3aed); border: none; color: white; font-weight: 600; border-radius: 10px; font-size: 16px; }
        .btn-login:hover { background: linear-gradient(135deg, #1e40af, #6d28d9); color: white; }
    </style>
</head>
<body>
<div class="container">
    <div class="login-card mx-auto">
        <div class="login-header">
            <h2><i class="bi bi-headset"></i> SAV12</h2>
            <p class="mt-2 mb-0" style="opacity:0.9">Quality ESCOM - Sistema de Tickets</p>
        </div>
        <div class="login-body">
            <?php if (!empty($error) || isset($_GET['error'])): ?>
                <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i>
                    <?= ($_GET['error'] ?? '') === 'disabled' ? 'Tu cuenta está desactivada.' : 'Credenciales inválidas.' ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['registro']) && $_GET['registro'] === 'success'): ?>
                <div class="alert alert-success"><i class="bi bi-check-circle"></i> Registro exitoso. Ya puedes iniciar sesión.</div>
            <?php endif; ?>
            <?php if (isset($_GET['logout'])): ?>
                <div class="alert alert-info"><i class="bi bi-info-circle"></i> Sesión cerrada correctamente.</div>
            <?php endif; ?>

            <form action="/login" method="POST">
                <?= Session::csrfField() ?>
                <div class="mb-3">
                    <label class="form-label fw-bold">Correo electrónico</label>
                    <input type="email" name="correo" class="form-control" placeholder="tu@correo.com" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Contraseña</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-login"><i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión</button>
            </form>
            <div class="text-center mt-4">
                <span class="text-muted">¿No tienes cuenta?</span>
                <a href="/registro" class="fw-bold text-decoration-none" style="color:#2563eb">Regístrate aquí</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
