<?php $pageTitle = 'Mi Panel - SAV12'; ob_start(); ?>

<h2 class="mb-4"><i class="bi bi-speedometer2"></i> Mi Panel</h2>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card card-stat text-center p-4">
            <h3 class="text-primary"><?= count($tickets) ?></h3>
            <small class="text-muted">Total Tickets</small>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card card-stat text-center p-4">
            <h3 class="text-success"><?= count(array_filter($tickets, fn($t) => in_array($t['estado'], ['RESUELTO','CERRADO']))) ?></h3>
            <small class="text-muted">Resueltos</small>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card card-stat text-center p-4">
            <h3 class="text-warning"><?= count(array_filter($tickets, fn($t) => in_array($t['estado'], ['ABIERTO','REABIERTO','EN_PROCESO','EN_ESPERA']))) ?></h3>
            <small class="text-muted">En Proceso</small>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Mis Tickets Recientes</h4>
    <a href="<?= base_url('usuario/crear-ticket') ?>" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nuevo Ticket</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Título</th><th>Estado</th><th>Prioridad</th><th>Fecha</th><th>Asignado</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($tickets, 0, 10) as $t): ?>
                <tr>
                    <td><?= $t['id'] ?></td>
                    <td><?= htmlspecialchars($t['titulo']) ?></td>
                    <td><span class="badge-estado badge-<?= $t['estado'] ?>"><?= str_replace('_', ' ', $t['estado']) ?></span></td>
                    <td><span class="badge-estado badge-<?= $t['prioridad'] ?>"><?= $t['prioridad'] ?></span></td>
                    <td><?= date('d/m/Y H:i', strtotime($t['fecha_creacion'])) ?></td>
                    <td><?= htmlspecialchars($t['asignado_nombre'] ?? 'Sin asignar') ?></td>
                    <td><a href="<?= base_url('usuario/ticket/' . $t['id']) ?>" class="btn btn-sm btn-outline-primary">Ver</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($tickets)): ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">No tienes tickets creados aún</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
