<?php $pageTitle = 'Categorías - SAV12'; ob_start(); ?>
<h2 class="mb-4"><i class="bi bi-tags"></i> Gestión de Categorías</h2>
<div class="card p-4 mb-4" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
    <h5>Nueva Categoría</h5>
    <form action="<?= base_url('admin/categorias/crear') ?>" method="POST" class="row g-2">
        <?= Session::csrfField() ?>
        <div class="col-md-4"><input type="text" name="nombre" class="form-control" placeholder="Nombre" required></div>
        <div class="col-md-6"><input type="text" name="descripcion" class="form-control" placeholder="Descripción"></div>
        <div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-plus"></i> Crear</button></div>
    </form>
</div>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($categorias as $c): ?>
        <tr><td><?= $c['id'] ?></td><td><?= htmlspecialchars($c['nombre']) ?></td><td><?= htmlspecialchars($c['descripcion'] ?? '-') ?></td>
        <td><span class="badge bg-<?= $c['activo'] ? 'success' : 'danger' ?>"><?= $c['activo'] ? 'Activa' : 'Inactiva' ?></span></td>
        <td><?php if ($c['activo']): ?><form action="<?= base_url('admin/categorias/' . $c['id'] . '/desactivar') ?>" method="POST" class="d-inline"><?= Session::csrfField() ?><button class="btn btn-sm btn-warning">Desactivar</button></form><?php endif; ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></div></div>
<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
