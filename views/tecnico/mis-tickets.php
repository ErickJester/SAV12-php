<?php $pageTitle = 'Mis Tickets - SAV12'; ob_start(); ?>
<h2 class="mb-4"><i class="bi bi-ticket-perforated"></i> Mis Tickets</h2>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>#</th><th>Título</th><th>Estado</th><th>Prioridad</th><th>Creado por</th><th>Fecha</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($tickets as $t): ?>
        <tr><td><?= $t['id'] ?></td><td><?= htmlspecialchars($t['titulo']) ?></td>
        <td><span class="badge-estado badge-<?= $t['estado'] ?>"><?= str_replace('_',' ',$t['estado']) ?></span></td>
        <td><span class="badge-estado badge-<?= $t['prioridad'] ?>"><?= $t['prioridad'] ?></span></td>
        <td><?= htmlspecialchars($t['creador_nombre'] ?? '-') ?></td>
        <td><?= date('d/m/Y H:i', strtotime($t['fecha_creacion'])) ?></td>
        <td><a href="/tecnico/ticket/<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary">Ver</a></td></tr>
    <?php endforeach; ?>
    </tbody></table></div></div>
<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
