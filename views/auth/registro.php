<?php $pageTitle = 'Registro - SAV12'; $registro = $registro ?? []; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; padding: 20px; }
        .registro-card { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 500px; width: 100%; overflow: hidden; }
        .registro-header { background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%); padding: 30px; text-align: center; color: white; }
        .registro-body { padding: 35px; }
        .form-control, .form-select { padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; }
        .btn-registro { width: 100%; padding: 12px; background: linear-gradient(135deg, #2563eb, #7c3aed); border: none; color: white; font-weight: 600; border-radius: 10px; }
    </style>
</head>
<body>
<div class="container">
    <div class="registro-card mx-auto">
        <div class="registro-header">
            <h2><i class="bi bi-person-plus"></i> Crear Cuenta</h2>
            <p class="mb-0" style="opacity:0.9">Regístrate en SAV12</p>
        </div>
        <div class="registro-body">
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <form action="<?= base_url('registro') ?>" method="POST">
                <?= Session::csrfField() ?>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre completo</label>
                    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($registro['nombre'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Correo electrónico</label>
                    <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($registro['correo'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Tipo de cuenta</label>
                    <select name="rol" id="rolSelect" class="form-select" required onchange="toggleCampos()">
                        <option value="">Selecciona...</option>
                        <option value="ALUMNO" <?= ($registro['rol'] ?? '') === 'ALUMNO' ? 'selected' : '' ?>>Alumno</option>
                        <option value="DOCENTE" <?= ($registro['rol'] ?? '') === 'DOCENTE' ? 'selected' : '' ?>>Docente</option>
                        <option value="ADMINISTRATIVO" <?= ($registro['rol'] ?? '') === 'ADMINISTRATIVO' ? 'selected' : '' ?>>Administrativo</option>
                    </select>
                </div>
                <div class="mb-3" id="campoBoleta" style="display:none">
                    <label class="form-label fw-bold">Boleta</label>
                    <input type="text" name="boleta" class="form-control" value="<?= htmlspecialchars($registro['boleta'] ?? '') ?>">
                </div>
                <div class="mb-3" id="campoTrabajador" style="display:none">
                    <label class="form-label fw-bold">ID de Trabajador</label>
                    <input type="text" name="id_trabajador" class="form-control" value="<?= htmlspecialchars($registro['id_trabajador'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Contraseña</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Confirmar Contraseña</label>
                    <input type="password" name="password2" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-registro"><i class="bi bi-person-plus"></i> Crear Cuenta</button>
            </form>
            <div class="text-center mt-3">
                <a href="<?= base_url('login') ?>" class="text-decoration-none" style="color:#2563eb">Ya tengo cuenta → Iniciar Sesión</a>
            </div>
        </div>
    </div>
</div>
<script>
function toggleCampos() {
    const rol = document.getElementById('rolSelect').value;
    document.getElementById('campoBoleta').style.display = rol === 'ALUMNO' ? 'block' : 'none';
    document.getElementById('campoTrabajador').style.display = (rol && rol !== 'ALUMNO') ? 'block' : 'none';
}
toggleCampos();
</script>
</body>
</html>
