<?php $pageTitle = 'Crear Ticket - SAV12'; ob_start(); ?>

<h2 class="mb-4"><i class="bi bi-plus-circle"></i> Crear Nuevo Ticket</h2>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card p-4" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
    <form action="<?= base_url('usuario/crear-ticket') ?>" method="POST" enctype="multipart/form-data">
        <?= Session::csrfField() ?>
        <div class="mb-3">
            <label class="form-label fw-bold">Título del problema</label>
            <input type="text" name="titulo" class="form-control" placeholder="Ej: No funciona el proyector" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="4" placeholder="Describe el problema en detalle..." required></textarea>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Categoría</label>
                <select name="categoriaId" class="form-select">
                    <option value="">Selecciona...</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Ubicación</label>
                <select name="ubicacionId" class="form-select">
                    <option value="">Selecciona...</option>
                    <?php foreach ($ubicaciones as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['edificio'] . ' - ' . ($u['piso'] ?? '') . ' - ' . ($u['salon'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Prioridad</label>
                <select name="prioridad" class="form-select">
                    <option value="BAJA">Baja</option>
                    <option value="MEDIA" selected>Media</option>
                    <option value="ALTA">Alta</option>
                    <option value="URGENTE">Urgente</option>
                </select>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label fw-bold">Evidencia (imagen)</label>
            <input type="file" name="archivoEvidencia" class="form-control" accept="image/*">
            <small class="text-muted">Máximo 5MB. Solo imágenes.</small>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Enviar Ticket</button>
            <a href="<?= base_url('usuario/panel') ?>" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
