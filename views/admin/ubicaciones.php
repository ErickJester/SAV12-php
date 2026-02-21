<?php $pageTitle = 'Ubicaciones - SAV12'; ob_start(); ?>
<h2 class="mb-4"><i class="bi bi-geo-alt"></i> Gestión de Ubicaciones</h2>
<div class="card p-4 mb-4" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
    <h5>Nueva Ubicación</h5>
    <form action="<?= base_url('admin/ubicaciones/crear') ?>" method="POST" class="row g-2">
        <?= Session::csrfField() ?>
        <div class="col-md-3"><input type="text" name="edificio" class="form-control" placeholder="Edificio" required></div>
        <div class="col-md-3"><input type="text" name="piso" class="form-control" placeholder="Piso"></div>
        <div class="col-md-3"><input type="text" name="salon" class="form-control" placeholder="Salón"></div>
        <div class="col-md-3"><button class="btn btn-primary w-100"><i class="bi bi-plus"></i> Crear</button></div>
    </form>
</div>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>ID</th><th>Edificio</th><th>Piso</th><th>Salón</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($ubicaciones as $u): ?>
        <tr><td><?= $u['id'] ?></td><td><?= htmlspecialchars($u['edificio']) ?></td><td><?= htmlspecialchars($u['piso'] ?? '-') ?></td><td><?= htmlspecialchars($u['salon'] ?? '-') ?></td>
        <td><span class="badge bg-<?= $u['activo'] ? 'success' : 'danger' ?>"><?= $u['activo'] ? 'Activa' : 'Inactiva' ?></span></td>
        <td><?php if ($u['activo']): ?><form action="<?= base_url('admin/ubicaciones/' . $u['id'] . '/desactivar') ?>" method="POST" class="d-inline"><?= Session::csrfField() ?><button class="btn btn-sm btn-warning">Desactivar</button></form><?php endif; ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></div></div>
<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
