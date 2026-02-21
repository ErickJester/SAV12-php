<?php $pageTitle = 'Ticket #' . $ticket['id'] . ' - SAV12'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-ticket-detailed"></i> Ticket #<?= $ticket['id'] ?></h2>
    <a href="<?= base_url('tecnico/panel') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
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
            <?php if (!empty($ticket['evidencia_problema'])): ?><img src="<?= base_url('uploads/' . $ticket['evidencia_problema']) ?>" class="img-fluid" style="max-height:300px;border-radius:8px"><?php endif; ?>
            <?php if (!empty($ticket['evidencia_resolucion'])): ?><div class="mt-2"><strong>Evidencia resolución:</strong><br><img src="<?= base_url('uploads/' . $ticket['evidencia_resolucion']) ?>" class="img-fluid" style="max-height:300px;border-radius:8px"></div><?php endif; ?>
        </div>

        <!-- Cambiar Estado -->
        <?php if (!in_array($ticket['estado'], ['CERRADO', 'CANCELADO'])): ?>
        <div class="card p-4 mb-4" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
            <h5><i class="bi bi-arrow-repeat"></i> Cambiar Estado</h5>
            <form action="<?= base_url('tecnico/ticket/' . $ticket['id'] . '/cambiar-estado') ?>" method="POST" enctype="multipart/form-data">
                <?= Session::csrfField() ?>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <select name="nuevoEstado" class="form-select" required>
                            <?php foreach ($estados as $e): if ($e !== $ticket['estado']): ?>
                                <option value="<?= $e ?>"><?= str_replace('_',' ',$e) ?></option>
                            <?php endif; endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-2">
                        <input type="text" name="observaciones" class="form-control" placeholder="Observaciones (opcional)">
                    </div>
                </div>
                <div class="mb-2">
                    <input type="file" name="evidenciaResolucion" class="form-control" accept="image/*">
                    <small class="text-muted">Evidencia de resolución (opcional)</small>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Cambiar Estado</button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Asignarme -->
        <?php if (empty($ticket['asignado_a_id'])): ?>
        <form action="<?= base_url('tecnico/ticket/' . $ticket['id'] . '/asignar') ?>" method="POST" class="mb-4">
            <?= Session::csrfField() ?>
            <button type="submit" class="btn btn-success w-100"><i class="bi bi-person-plus"></i> Asignarme este Ticket</button>
        </form>
        <?php endif; ?>

        <!-- Comentarios -->
        <div class="card p-4 mb-4" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
            <h5><i class="bi bi-chat-dots"></i> Comentarios</h5>
            <?php foreach ($comentarios as $c): ?>
                <div class="border-start border-3 ps-3 my-3 <?= in_array($c['usuario_rol'], ['TECNICO','ADMIN']) ? 'border-primary' : 'border-secondary' ?>">
                    <strong><?= htmlspecialchars($c['usuario_nombre']) ?></strong> <small class="text-muted"><?= date('d/m/Y H:i', strtotime($c['fecha_creacion'])) ?></small>
                    <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($c['contenido'])) ?></p>
                </div>
            <?php endforeach; ?>
            <form action="<?= base_url('tecnico/ticket/' . $ticket['id'] . '/comentar') ?>" method="POST" class="mt-3">
                <?= Session::csrfField() ?>
                <textarea name="contenido" class="form-control mb-2" rows="3" placeholder="Escribir comentario..." required></textarea>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-send"></i> Enviar</button>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
            <h6>Detalles</h6>
            <p class="mb-1"><small class="text-muted">Creado por:</small><br><?= htmlspecialchars($ticket['creador_nombre'] ?? '-') ?></p>
            <p class="mb-1"><small class="text-muted">Categoría:</small><br><?= htmlspecialchars($ticket['categoria_nombre'] ?? '-') ?></p>
            <p class="mb-1"><small class="text-muted">Ubicación:</small><br><?= htmlspecialchars($ticket['ubicacion_nombre'] ?? '-') ?></p>
            <p class="mb-1"><small class="text-muted">Asignado a:</small><br><?= htmlspecialchars($ticket['asignado_nombre'] ?? 'Sin asignar') ?></p>
            <p class="mb-1"><small class="text-muted">Creado:</small><br><?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></p>
            <?php if ($ticket['sla_primera_respuesta_min']): ?>
                <p class="mb-1"><small class="text-muted">SLA Respuesta:</small><br><?= $ticket['sla_primera_respuesta_min'] ?> min</p>
                <p class="mb-1"><small class="text-muted">SLA Resolución:</small><br><?= $ticket['sla_resolucion_min'] ?> min</p>
            <?php endif; ?>
        </div>
        <div class="card p-4 mt-3" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
            <h6>Historial</h6>
            <?php foreach (array_slice($historial, 0, 10) as $h): ?>
                <div class="border-bottom py-2"><small class="text-muted"><?= date('d/m H:i', strtotime($h['fecha_accion'])) ?></small><br>
                <small><strong><?= htmlspecialchars($h['usuario_nombre']) ?>:</strong> <?= htmlspecialchars($h['accion']) ?></small></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
