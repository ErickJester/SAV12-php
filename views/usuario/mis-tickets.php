<?php $pageTitle = 'Mis Tickets - SAV12'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-ticket-perforated"></i> Mis Tickets</h2>
    <a href="<?= base_url('usuario/crear-ticket') ?>" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nuevo</a>
</div>
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle"></i> Ticket creado exitosamente.</div>
<?php endif; ?>
<div class="card"><div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>#</th><th>Título</th><th>Categoría</th><th>Estado</th><th>Prioridad</th><th>Fecha</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($tickets as $t): ?>
            <tr>
                <td><?= $t['id'] ?></td>
                <td><?= htmlspecialchars($t['titulo']) ?></td>
                <td><?= htmlspecialchars($t['categoria_nombre'] ?? '-') ?></td>
                <td><span class="badge-estado badge-<?= $t['estado'] ?>"><?= str_replace('_',' ',$t['estado']) ?></span></td>
                <td><span class="badge-estado badge-<?= $t['prioridad'] ?>"><?= $t['prioridad'] ?></span></td>
                <td><?= date('d/m/Y H:i', strtotime($t['fecha_creacion'])) ?></td>
                <td><a href="<?= base_url('usuario/ticket/' . $t['id']) ?>" class="btn btn-sm btn-outline-primary">Ver</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($tickets)): ?><tr><td colspan="7" class="text-center py-4 text-muted">Sin tickets</td></tr><?php endif; ?>
        </tbody>
    </table>
</div></div>
<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
