<?php $pageTitle = 'Usuarios - SAV12'; ob_start(); ?>
<h2 class="mb-4"><i class="bi bi-people"></i> Gestión de Usuarios</h2>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Boleta/ID</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['nombre']) ?></td>
            <td><?= htmlspecialchars($u['correo']) ?></td>
            <td>
                <form action="/admin/usuarios/<?= $u['id'] ?>/cambiar-rol" method="POST" class="d-inline">
                    <?= Session::csrfField() ?>
                    <select name="rol" class="form-select form-select-sm d-inline" style="width:140px" onchange="this.form.submit()">
                        <?php foreach (['ALUMNO','DOCENTE','ADMINISTRATIVO','TECNICO','ADMIN'] as $r): ?>
                            <option value="<?= $r ?>" <?= $u['rol'] === $r ? 'selected' : '' ?>><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </td>
            <td><?= htmlspecialchars($u['boleta'] ?? $u['id_trabajador'] ?? '-') ?></td>
            <td><span class="badge bg-<?= $u['activo'] ? 'success' : 'danger' ?>"><?= $u['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
            <td>
                <form action="/admin/usuarios/<?= $u['id'] ?>/cambiar-estado" method="POST" class="d-inline">
                    <?= Session::csrfField() ?>
                    <input type="hidden" name="activo" value="<?= $u['activo'] ? '0' : '1' ?>">
                    <button class="btn btn-sm btn-<?= $u['activo'] ? 'warning' : 'success' ?>"><?= $u['activo'] ? 'Desactivar' : 'Activar' ?></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody></table></div></div>
<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
