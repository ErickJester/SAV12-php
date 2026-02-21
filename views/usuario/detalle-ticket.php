<?php $pageTitle = 'Ticket #' . $ticket['id'] . ' - SAV12'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-ticket-detailed"></i> Ticket #<?= $ticket['id'] ?></h2>
    <a href="<?= base_url('usuario/mis-tickets') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Info del ticket -->
        <div class="card p-4 mb-4" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
            <h4><?= htmlspecialchars($ticket['titulo']) ?></h4>
            <div class="d-flex gap-2 mb-3">
                <span class="badge-estado badge-<?= $ticket['estado'] ?>"><?= str_replace('_',' ',$ticket['estado']) ?></span>
                <span class="badge-estado badge-<?= $ticket['prioridad'] ?>"><?= $ticket['prioridad'] ?></span>
            </div>
            <p><?= nl2br(htmlspecialchars($ticket['descripcion'])) ?></p>
            <?php if (!empty($ticket['evidencia_problema'])): ?>
                <div class="mt-2"><strong>Evidencia:</strong><br><img src="<?= base_url('uploads/' . $ticket['evidencia_problema']) ?>" class="img-fluid mt-1" style="max-height:300px;border-radius:8px"></div>
            <?php endif; ?>
            <?php if (!empty($ticket['evidencia_resolucion'])): ?>
                <div class="mt-2"><strong>Evidencia resolución:</strong><br><img src="<?= base_url('uploads/' . $ticket['evidencia_resolucion']) ?>" class="img-fluid mt-1" style="max-height:300px;border-radius:8px"></div>
            <?php endif; ?>
        </div>

        <!-- Comentarios -->
        <div class="card p-4 mb-4" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
            <h5><i class="bi bi-chat-dots"></i> Comentarios (<?= count($comentarios) ?>)</h5>
            <?php foreach ($comentarios as $c): ?>
                <div class="border-start border-3 ps-3 my-3 <?= in_array($c['usuario_rol'], ['TECNICO','ADMIN']) ? 'border-primary' : 'border-secondary' ?>">
                    <strong><?= htmlspecialchars($c['usuario_nombre']) ?></strong>
                    <small class="text-muted ms-2"><?= date('d/m/Y H:i', strtotime($c['fecha_creacion'])) ?></small>
                    <span class="badge bg-<?= in_array($c['usuario_rol'], ['TECNICO','ADMIN']) ? 'primary' : 'secondary' ?> ms-2" style="font-size:10px"><?= $c['usuario_rol'] ?></span>
                    <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($c['contenido'])) ?></p>
                </div>
            <?php endforeach; ?>
            <?php if (empty($comentarios)): ?><p class="text-muted">Sin comentarios aún</p><?php endif; ?>

            <?php if (!in_array($ticket['estado'], ['CERRADO', 'CANCELADO'])): ?>
            <form action="<?= base_url('usuario/ticket/' . $ticket['id'] . '/comentar') ?>" method="POST" class="mt-3">
                <?= Session::csrfField() ?>
                <textarea name="contenido" class="form-control mb-2" rows="3" placeholder="Escribe un comentario..." required></textarea>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-send"></i> Enviar</button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Detalles -->
        <div class="card p-4 mb-3" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
            <h6 class="mb-3">Detalles</h6>
            <p class="mb-1"><small class="text-muted">Categoría:</small><br><?= htmlspecialchars($ticket['categoria_nombre'] ?? 'Sin categoría') ?></p>
            <p class="mb-1"><small class="text-muted">Ubicación:</small><br><?= htmlspecialchars($ticket['ubicacion_nombre'] ?? 'Sin ubicación') ?></p>
            <p class="mb-1"><small class="text-muted">Asignado a:</small><br><?= htmlspecialchars($ticket['asignado_nombre'] ?? 'Sin asignar') ?></p>
            <p class="mb-1"><small class="text-muted">Creado:</small><br><?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></p>
            <?php if ($ticket['fecha_resolucion']): ?>
                <p class="mb-1"><small class="text-muted">Resuelto:</small><br><?= date('d/m/Y H:i', strtotime($ticket['fecha_resolucion'])) ?></p>
            <?php endif; ?>
        </div>

        <!-- Reabrir -->
        <?php if (in_array($ticket['estado'], ['RESUELTO', 'CERRADO', 'CANCELADO'])): ?>
        <form action="<?= base_url('usuario/ticket/' . $ticket['id'] . '/reabrir') ?>" method="POST">
            <?= Session::csrfField() ?>
            <button type="submit" class="btn btn-warning w-100" onclick="return confirm('¿Reabrir este ticket?')"><i class="bi bi-arrow-counterclockwise"></i> Reabrir Ticket</button>
        </form>
        <?php endif; ?>

        <!-- Historial -->
        <div class="card p-4 mt-3" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
            <h6>Historial</h6>
            <?php foreach (array_slice($historial, 0, 10) as $h): ?>
                <div class="border-bottom py-2">
                    <small class="text-muted"><?= date('d/m H:i', strtotime($h['fecha_accion'])) ?></small><br>
                    <small><strong><?= htmlspecialchars($h['usuario_nombre']) ?>:</strong> <?= htmlspecialchars($h['accion']) ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
