<?php $pageTitle = 'Registro - SAV12'; $registro = $registro ?? []; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1e40af;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --light-bg: #f8fafc;
            --border-light: #e2e8f0;
        }
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, #7c3aed 100%);
            min-height: 100vh; font-family: 'Segoe UI', sans-serif; padding: 30px 20px;
        }
        .register-container { max-width: 640px; margin: 0 auto; }
        .register-card { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,.25); overflow: hidden; }
        .register-header { background: linear-gradient(135deg, var(--primary-color) 0%, #7c3aed 100%); padding: 35px 30px; text-align: center; color: white; }
        .register-header h2 { margin: 0; font-size: 28px; font-weight: 700; }
        .register-header p { margin: 8px 0 0; font-size: 14px; opacity: .95; }
        .register-body { padding: 35px; }
        .role-selector { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 26px; }
        .role-box {
            border: 2px solid var(--border-light); padding: 18px 12px; text-align: center; border-radius: 12px;
            cursor: pointer; transition: all .25s ease; font-weight: 600; background: #fff; font-size: 14px;
            user-select: none;
        }
        .role-box:hover { transform: translateY(-3px); border-color: var(--primary-color); background: var(--light-bg); }
        .role-box-icon { font-size: 28px; margin-bottom: 8px; display: block; }
        .role-selected { border-color: var(--primary-color); background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%); color: var(--primary-dark); box-shadow: 0 4px 12px rgba(37,99,235,.2); }
        .form-group { margin-bottom: 18px; }
        .form-group label { font-weight: 600; font-size: 14px; color: #1f2937; margin-bottom: 8px; display: block; }
        .form-control {
            padding: 12px 16px; border: 2px solid var(--border-light); border-radius: 10px; font-size: 15px;
            transition: all .25s ease; background: var(--light-bg);
        }
        .form-control:focus { border-color: var(--primary-color); background: white; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
        .button-register {
            background: linear-gradient(135deg, var(--primary-color) 0%, #7c3aed 100%); color: white; border: none;
            padding: 12px 24px; width: 100%; font-size: 16px; border-radius: 10px; cursor: pointer; font-weight: 600;
            transition: all .25s ease; margin-top: 8px;
        }
        .button-register:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(37,99,235,.3); }
        .login-link { text-align: center; margin-top: 18px; font-size: 14px; }
        .login-link a { color: var(--primary-color); text-decoration: none; font-weight: 600; }
        .login-link a:hover { text-decoration: underline; }
        .hidden-group { display: none; }
        .hidden-group.show { display: block; animation: slideDown .25s ease; }
        @keyframes slideDown { from { opacity:0; transform: translateY(-8px);} to { opacity:1; transform: translateY(0);} }
        .text-muted-help { font-size: 13px; color: #6b7280; margin-top: 4px; display: block; }
    </style>
</head>
<body>
<div class="register-container">
    <div class="register-card">
        <div class="register-header">
            <h2><i class="bi bi-person-check"></i> Crear Cuenta</h2>
            <p>Selecciona tu tipo de cuenta y regístrate</p>
        </div>

        <div class="register-body">
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <div class="role-selector" id="roleSelector">
                <div class="role-box" data-role="ALUMNO">
                    <i class="bi bi-mortarboard role-box-icon"></i>
                    Alumno
                </div>
                <div class="role-box" data-role="DOCENTE">
                    <i class="bi bi-journal-bookmark role-box-icon"></i>
                    Docente
                </div>
                <div class="role-box" data-role="ADMINISTRATIVO">
                    <i class="bi bi-briefcase role-box-icon"></i>
                    Administrativo
                </div>
            </div>

            <form action="/registro" method="POST" id="registroForm">
                <?= Session::csrfField() ?>
                <input type="hidden" name="rol" id="rolInput" value="<?= htmlspecialchars($registro['rol'] ?? '') ?>" required>

                <div class="form-group">
                    <label><i class="bi bi-person-fill"></i> Nombre completo</label>
                    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($registro['nombre'] ?? '') ?>" placeholder="Juan Pérez" required>
                </div>

                <div class="form-group">
                    <label><i class="bi bi-envelope"></i> Correo electrónico</label>
                    <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($registro['correo'] ?? '') ?>" placeholder="correo@ejemplo.com" required>
                </div>

                <div class="form-group hidden-group" id="boleta-group">
                    <label><i class="bi bi-card-text"></i> Boleta</label>
                    <input type="text" name="boleta" id="boleta" class="form-control" value="<?= htmlspecialchars($registro['boleta'] ?? '') ?>" placeholder="Ejemplo: 2021123456">
                    <small class="text-muted-help">Solo para alumnos</small>
                </div>

                <div class="form-group hidden-group" id="trabajador-group">
                    <label><i class="bi bi-hash"></i> ID de Trabajador</label>
                    <input type="text" name="id_trabajador" id="idTrabajador" class="form-control" value="<?= htmlspecialchars($registro['id_trabajador'] ?? '') ?>" placeholder="ID del trabajador">
                    <small class="text-muted-help">Para docente o administrativo</small>
                </div>

                <div class="form-group">
                    <label><i class="bi bi-lock"></i> Contraseña</label>
                    <input type="password" name="password" class="form-control" required minlength="6" placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label><i class="bi bi-lock"></i> Confirmar contraseña</label>
                    <input type="password" name="password2" class="form-control" required placeholder="••••••••">
                </div>

                <button type="submit" class="button-register">
                    <i class="bi bi-check-circle"></i> Crear Cuenta
                </button>
            </form>

            <div class="login-link">
                ¿Ya tienes cuenta? <a href="/login"><i class="bi bi-box-arrow-in-right"></i> Inicia sesión</a>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const boxes = document.querySelectorAll('.role-box');
    const rolInput = document.getElementById('rolInput');
    const boletaGroup = document.getElementById('boleta-group');
    const trabajadorGroup = document.getElementById('trabajador-group');
    const boletaInput = document.getElementById('boleta');
    const trabajadorInput = document.getElementById('idTrabajador');

    function applyRole(rol) {
        boxes.forEach(b => b.classList.toggle('role-selected', b.dataset.role === rol));
        rolInput.value = rol || '';

        boletaGroup.classList.remove('show');
        trabajadorGroup.classList.remove('show');
        boletaInput.required = false;
        trabajadorInput.required = false;

        if (rol === 'ALUMNO') {
            boletaGroup.classList.add('show');
            boletaInput.required = true;
        } else if (rol === 'DOCENTE' || rol === 'ADMINISTRATIVO') {
            trabajadorGroup.classList.add('show');
            trabajadorInput.required = true;
        }
    }

    boxes.forEach(box => box.addEventListener('click', () => applyRole(box.dataset.role)));
    applyRole(rolInput.value || '');
})();
</script>
</body>
</html>
