<?php $pageTitle = 'Dashboard Admin - SAV12'; ob_start(); ?>
<h2 class="mb-4"><i class="bi bi-speedometer2"></i> Dashboard</h2>
<div class="row mb-4">
    <div class="col-md-3 mb-3"><div class="card card-stat text-center p-4"><h3 class="text-primary"><?= $kpis['totalTickets'] ?></h3><small class="text-muted">Total Tickets</small></div></div>
    <div class="col-md-3 mb-3"><div class="card card-stat text-center p-4"><h3 class="text-success"><?= $kpis['ticketsResueltos'] ?></h3><small class="text-muted">Resueltos</small></div></div>
    <div class="col-md-3 mb-3"><div class="card card-stat text-center p-4"><h3 class="text-warning"><?= $kpis['ticketsPendientes'] ?></h3><small class="text-muted">Pendientes</small></div></div>
    <div class="col-md-3 mb-3"><div class="card card-stat text-center p-4"><h3 class="text-danger"><?= $kpis['ticketsCriticos'] ?></h3><small class="text-muted">Críticos</small></div></div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card p-4" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
            <h5>Tasa de Resolución</h5>
            <h2 class="text-success"><?= $kpis['tasaResolucion'] ?>%</h2>
            <div class="progress" style="height:10px;border-radius:5px"><div class="progress-bar bg-success" style="width:<?= $kpis['tasaResolucion'] ?>%"></div></div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card p-4" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
            <h5>Cumplimiento SLA</h5>
            <h2 class="text-primary"><?= $kpis['slaGlobalPorcentaje'] ?>%</h2>
            <div class="progress" style="height:10px;border-radius:5px"><div class="progress-bar bg-primary" style="width:<?= $kpis['slaGlobalPorcentaje'] ?>%"></div></div>
        </div>
    </div>
</div>
<div class="row mt-3">
    <div class="col-md-4"><a href="<?= base_url('admin/tickets') ?>" class="btn btn-outline-primary w-100 p-3"><i class="bi bi-ticket-perforated"></i> Ver Tickets</a></div>
    <div class="col-md-4"><a href="<?= base_url('admin/reportes') ?>" class="btn btn-outline-success w-100 p-3"><i class="bi bi-bar-chart"></i> Reportes</a></div>
    <div class="col-md-4"><a href="<?= base_url('admin/usuarios') ?>" class="btn btn-outline-warning w-100 p-3"><i class="bi bi-people"></i> Usuarios</a></div>
</div>
<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
