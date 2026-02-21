<?php $pageTitle = 'Panel Técnico - SAV12'; ob_start(); ?>
<h2 class="mb-4"><i class="bi bi-speedometer2"></i> Panel Técnico</h2>
<div class="row mb-4">
    <div class="col-md-4 mb-3"><div class="card card-stat text-center p-4"><h3 class="text-primary"><?= count($misTickets) ?></h3><small class="text-muted">Mis Tickets</small></div></div>
    <div class="col-md-4 mb-3"><div class="card card-stat text-center p-4"><h3 class="text-warning"><?= count($ticketsSinAsignar) ?></h3><small class="text-muted">Sin Asignar</small></div></div>
    <div class="col-md-4 mb-3"><div class="card card-stat text-center p-4"><h3 class="text-success"><?= count(array_filter($misTickets, fn($t) => in_array($t['estado'], ['RESUELTO','CERRADO']))) ?></h3><small class="text-muted">Resueltos</small></div></div>
</div>
<h4 class="mb-3">Tickets Sin Asignar</h4>
<div class="card mb-4"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>#</th><th>Título</th><th>Prioridad</th><th>Fecha</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($ticketsSinAsignar as $t): ?>
        <tr><td><?= $t['id'] ?></td><td><?= htmlspecialchars($t['titulo']) ?></td>
        <td><span class="badge-estado badge-<?= $t['prioridad'] ?>"><?= $t['prioridad'] ?></span></td>
        <td><?= date('d/m/Y H:i', strtotime($t['fecha_creacion'])) ?></td>
        <td><a href="/tecnico/ticket/<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary me-1">Ver</a>
        <form action="/tecnico/ticket/<?= $t['id'] ?>/asignar" method="POST" class="d-inline"><?= Session::csrfField() ?><button class="btn btn-sm btn-success">Tomar</button></form></td></tr>
    <?php endforeach; ?>
    <?php if (empty($ticketsSinAsignar)): ?><tr><td colspan="5" class="text-center py-3 text-muted">Sin tickets pendientes</td></tr><?php endif; ?>
    </tbody></table></div></div>

<h4 class="mb-3">Mis Tickets Asignados</h4>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>#</th><th>Título</th><th>Estado</th><th>Prioridad</th><th></th></tr></thead>
    <tbody>
    <?php foreach (array_slice($misTickets, 0, 15) as $t): ?>
        <tr><td><?= $t['id'] ?></td><td><?= htmlspecialchars($t['titulo']) ?></td>
        <td><span class="badge-estado badge-<?= $t['estado'] ?>"><?= str_replace('_',' ',$t['estado']) ?></span></td>
        <td><span class="badge-estado badge-<?= $t['prioridad'] ?>"><?= $t['prioridad'] ?></span></td>
        <td><a href="/tecnico/ticket/<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary">Ver</a></td></tr>
    <?php endforeach; ?>
    </tbody></table></div></div>
<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
