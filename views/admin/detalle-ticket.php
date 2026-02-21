<?php $pageTitle = 'Ticket #' . $ticket['id'] . ' - Admin'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-ticket-detailed"></i> Ticket #<?= $ticket['id'] ?></h2>
    <a href="/admin/tickets" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="card p-4 mb-4" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
            <h4><?= htmlspecialchars($ticket['titulo']) ?></h4>
            <div class="d-flex gap-2 mb-3">
                <span class="badge-estado badge-<?= $ticket['estado'] ?>"><?= str_replace('_',' ',$ticket['estado']) ?></span>
                <span class="badge-estado badge-<?= $ticket['prioridad'] ?>"><?= $ticket['prioridad'] ?></span>
            </div>
            <p><?= nl2br(htmlspecialchars($ticket['descripcion'])) ?></p>
            <?php if (!empty($ticket['evidencia_problema'])): ?><img src="/uploads/<?= $ticket['evidencia_problema'] ?>" class="img-fluid" style="max-height:300px;border-radius:8px"><?php endif; ?>
        </div>

        <!-- Acciones -->
        <div class="card p-4 mb-4" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
            <h5>Acciones</h5>
            <div class="d-flex gap-2 flex-wrap">
                <?php if (empty($ticket['asignado_a_id'])): ?>
                    <form action="/admin/tickets/<?= $ticket['id'] ?>/asignarme" method="POST" class="d-inline"><?= Session::csrfField() ?><button class="btn btn-success"><i class="bi bi-person-plus"></i> Asignarme</button></form>
                <?php endif; ?>
                <?php if (empty($ticket['asignado_a_id'])): ?>
                    <form action="/admin/tickets/<?= $ticket['id'] ?>/asignar-tecnico" method="POST" class="d-inline-flex gap-1"><?= Session::csrfField() ?>
                        <select name="tecnicoId" class="form-select form-select-sm"><?php foreach ($asignables as $a): ?><option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nombre']) ?></option><?php endforeach; ?></select>
                        <button class="btn btn-sm btn-primary">Asignar</button>
                    </form>
                <?php endif; ?>
                <?php if (in_array($ticket['estado'], ['RESUELTO','CERRADO','CANCELADO'])): ?>
                    <form action="/admin/ticket/<?= $ticket['id'] ?>/reabrir" method="POST" class="d-inline"><?= Session::csrfField() ?><button class="btn btn-warning"><i class="bi bi-arrow-counterclockwise"></i> Reabrir</button></form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Comentarios -->
        <div class="card p-4 mb-4" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
            <h5><i class="bi bi-chat-dots"></i> Comentarios</h5>
            <?php foreach ($comentarios as $c): ?>
                <div class="border-start border-3 ps-3 my-3 <?= in_array($c['usuario_rol'], ['TECNICO','ADMIN']) ? 'border-primary' : 'border-secondary' ?>">
                    <strong><?= htmlspecialchars($c['usuario_nombre']) ?></strong> <small class="text-muted"><?= date('d/m/Y H:i', strtotime($c['fecha_creacion'])) ?></small>
                    <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($c['contenido'])) ?></p>
                </div>
            <?php endforeach; ?>
            <form action="/admin/ticket/<?= $ticket['id'] ?>/comentar" method="POST" class="mt-3">
                <?= Session::csrfField() ?>
                <textarea name="contenido" class="form-control mb-2" rows="3" placeholder="Comentario..." required></textarea>
                <button class="btn btn-primary btn-sm"><i class="bi bi-send"></i> Enviar</button>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
            <h6>Detalles</h6>
            <p class="mb-1"><small class="text-muted">Creado por:</small><br><?= htmlspecialchars($ticket['creador_nombre'] ?? '-') ?> (<?= $ticket['creador_rol'] ?? '' ?>)</p>
            <p class="mb-1"><small class="text-muted">Categoría:</small><br><?= htmlspecialchars($ticket['categoria_nombre'] ?? '-') ?></p>
            <p class="mb-1"><small class="text-muted">Ubicación:</small><br><?= htmlspecialchars($ticket['ubicacion_nombre'] ?? '-') ?></p>
            <p class="mb-1"><small class="text-muted">Asignado:</small><br><?= htmlspecialchars($ticket['asignado_nombre'] ?? 'Sin asignar') ?></p>
            <p class="mb-1"><small class="text-muted">Creado:</small><br><?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></p>
            <?php if ($ticket['fecha_primera_respuesta']): ?><p class="mb-1"><small class="text-muted">1ª Respuesta:</small><br><?= date('d/m/Y H:i', strtotime($ticket['fecha_primera_respuesta'])) ?></p><?php endif; ?>
            <?php if ($ticket['fecha_resolucion']): ?><p class="mb-1"><small class="text-muted">Resuelto:</small><br><?= date('d/m/Y H:i', strtotime($ticket['fecha_resolucion'])) ?></p><?php endif; ?>
            <?php if ($ticket['reabierto_count'] > 0): ?><p class="mb-1 text-danger"><small class="text-muted">Reaperturas:</small><br><?= $ticket['reabierto_count'] ?></p><?php endif; ?>
        </div>
        <div class="card p-4 mt-3" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
            <h6>Historial</h6>
            <?php foreach (array_slice($historial, 0, 15) as $h): ?>
                <div class="border-bottom py-2"><small class="text-muted"><?= date('d/m H:i', strtotime($h['fecha_accion'])) ?></small><br>
                <small><strong><?= htmlspecialchars($h['usuario_nombre']) ?>:</strong> <?= htmlspecialchars($h['accion']) ?></small></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
