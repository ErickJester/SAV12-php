<?php $pageTitle = 'Acceso Institucional - SAV12'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --ipn-guinda: #6C1D45;
            --ipn-guinda-dark: #4a132f;
            --ipn-gold: #B38E5D;
            --bg-light: #f3f4f6;
        }
        body {
            background-color: var(--bg-light);
            background-image: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            min-height: 100vh;
            font-family: 'Montserrat', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 16px;
        }
        .top-bar {
            position: absolute; top: 0; left: 0; width: 100%; height: 5px;
            background: linear-gradient(90deg, var(--ipn-guinda) 70%, var(--ipn-gold) 70%);
        }
        .login-card {
            background: #fff; border: none; border-radius: 15px;
            box-shadow: 0 10px 40px rgba(108, 29, 69, 0.15);
            width: 100%; max-width: 430px; overflow: hidden;
        }
        .login-header { background: white; padding: 34px 30px 16px; text-align: center; }
        .logo-img { height: 72px; width: auto; margin-bottom: 14px; filter: drop-shadow(0 2px 4px rgba(0,0,0,.1)); }
        .login-header h2 { color: var(--ipn-guinda); font-weight: 700; font-size: 28px; margin-bottom: 6px; letter-spacing: -.5px; }
        .login-header p { color: #6b7280; font-size: 14px; font-weight: 500; margin: 0; }
        .login-body { padding: 12px 36px 34px; }
        .form-floating > .form-control { border: 2px solid #e5e7eb; border-radius: 8px; min-height: 56px; }
        .form-floating > .form-control:focus {
            border-color: var(--ipn-guinda);
            box-shadow: 0 0 0 .25rem rgba(108, 29, 69, 0.15);
        }
        .form-floating > label { color: #9ca3af; }
        .btn-ipn {
            background-color: var(--ipn-guinda); color: #fff; border: none; width: 100%;
            padding: 14px; font-weight: 700; border-radius: 8px; margin-top: 12px;
            text-transform: uppercase; font-size: 13px; letter-spacing: 1px; transition: .25s ease;
        }
        .btn-ipn:hover {
            background-color: var(--ipn-guinda-dark); color: #fff; transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 29, 69, 0.28);
        }
        .alert-custom { border-left: 4px solid #ef4444; background: #fef2f2; color: #991b1b; border-radius: 8px; border-top: none; border-right: none; border-bottom: none; }
        .footer-text { text-align: center; font-size: 11px; color: #9ca3af; margin-top: 22px; border-top: 1px solid #f3f4f6; padding-top: 14px; }
        .register-link { text-align: center; font-size: 13px; margin-top: 14px; }
        .register-link a { color: var(--ipn-guinda); font-weight: 700; text-decoration: none; }
        .register-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="top-bar"></div>
<div class="login-card">
    <div class="login-header">
        <img src="/assets/img/SAV12.png" alt="SAV12" class="logo-img">
        <h2>SAV12</h2>
        <p>Sistema de Atención de Verificaciones<br>CECyT 12 "José María Morelos"</p>
    </div>

    <div class="login-body">
        <?php if (!empty($error) || isset($_GET['error'])): ?>
            <div class="alert alert-custom mb-4" role="alert">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                    <div>
                        <strong>Acceso denegado</strong><br>
                        <span><?= ($_GET['error'] ?? '') === 'disabled' ? 'Tu cuenta está desactivada.' : htmlspecialchars($error ?? 'Credenciales inválidas.') ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['registro']) && $_GET['registro'] === 'success'): ?>
            <div class="alert alert-success mb-3"><i class="bi bi-check-circle-fill me-1"></i> Registro exitoso. Ya puedes iniciar sesión.</div>
        <?php endif; ?>
        <?php if (isset($_GET['logout'])): ?>
            <div class="alert alert-info mb-3"><i class="bi bi-info-circle-fill me-1"></i> Sesión cerrada correctamente.</div>
        <?php endif; ?>

        <form action="/login" method="POST">
            <?= Session::csrfField() ?>

            <div class="form-floating mb-3">
                <input type="email" name="correo" class="form-control" id="floatingInput" placeholder="name@example.com" required autofocus>
                <label for="floatingInput"><i class="bi bi-envelope me-1"></i> Correo electrónico</label>
            </div>

            <div class="form-floating mb-4">
                <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required>
                <label for="floatingPassword"><i class="bi bi-lock me-1"></i> Contraseña</label>
            </div>

            <button type="submit" class="btn btn-ipn">
                <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
            </button>
        </form>

        <div class="register-link">
            ¿No tienes cuenta? <a href="/registro">Regístrate aquí</a>
        </div>

        <div class="footer-text">
            © <?= date('Y') ?> Instituto Politécnico Nacional<br>
            SAV12 - CECyT 12
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
