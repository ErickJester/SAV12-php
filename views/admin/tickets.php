<?php $pageTitle = 'Tickets - SAV12'; ob_start(); ?>
<h2 class="mb-4"><i class="bi bi-ticket-perforated"></i> Todos los Tickets</h2>
<div class="mb-3">
    <a href="/admin/tickets" class="btn btn-sm <?= !isset($_GET['filtro']) ? 'btn-primary' : 'btn-outline-primary' ?>">Todos</a>
    <a href="/admin/tickets?filtro=activo" class="btn btn-sm <?= ($_GET['filtro'] ?? '') === 'activo' ? 'btn-warning' : 'btn-outline-warning' ?>">Activos</a>
    <a href="/admin/tickets?filtro=resuelto" class="btn btn-sm <?= ($_GET['filtro'] ?? '') === 'resuelto' ? 'btn-success' : 'btn-outline-success' ?>">Resueltos</a>
</div>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>#</th><th>Título</th><th>Estado</th><th>Prioridad</th><th>Creado por</th><th>Asignado</th><th>Fecha</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach ($tickets as $t): ?>
        <tr>
            <td><?= $t['id'] ?></td>
            <td><a href="/admin/ticket/<?= $t['id'] ?>"><?= htmlspecialchars($t['titulo']) ?></a></td>
            <td><span class="badge-estado badge-<?= $t['estado'] ?>"><?= str_replace('_',' ',$t['estado']) ?></span></td>
            <td><span class="badge-estado badge-<?= $t['prioridad'] ?>"><?= $t['prioridad'] ?></span></td>
            <td><?= htmlspecialchars($t['creador_nombre'] ?? '-') ?></td>
            <td><?= htmlspecialchars($t['asignado_nombre'] ?? 'Sin asignar') ?></td>
            <td><?= date('d/m/Y H:i', strtotime($t['fecha_creacion'])) ?></td>
            <td>
                <?php if (empty($t['asignado_a_id'])): ?>
                <form action="/admin/tickets/<?= $t['id'] ?>/asignar-tecnico" method="POST" class="d-inline-flex gap-1">
                    <?= Session::csrfField() ?>
                    <select name="tecnicoId" class="form-select form-select-sm" style="width:140px">
                        <?php foreach ($asignables as $a): ?><option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nombre']) ?></option><?php endforeach; ?>
                    </select>
                    <button class="btn btn-sm btn-success">Asignar</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div></div>
<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
