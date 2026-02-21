<?php $pageTitle = 'Reportes - SAV12'; ob_start(); ?>
<h2 class="mb-4"><i class="bi bi-bar-chart"></i> Reportes y Análisis</h2>

<!-- Filtro periodo -->
<div class="card p-3 mb-4" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
    <form method="GET" action="/admin/reportes" class="d-flex gap-2 align-items-center flex-wrap">
        <?php foreach (['semanal','mensual','trimestral','anual'] as $p): ?>
            <a href="/admin/reportes?periodo=<?= $p ?>" class="btn btn-sm <?= $periodoSeleccionado === $p ? 'btn-primary' : 'btn-outline-primary' ?>"><?= ucfirst($p) ?></a>
        <?php endforeach; ?>
    </form>
</div>

<!-- KPIs -->
<div class="row mb-4">
    <div class="col-md-3 mb-3"><div class="card card-stat text-center p-3"><h3 class="text-primary"><?= $kpis['totalTickets'] ?></h3><small>Total</small></div></div>
    <div class="col-md-3 mb-3"><div class="card card-stat text-center p-3"><h3 class="text-success"><?= $kpis['ticketsResueltos'] ?></h3><small>Resueltos</small></div></div>
    <div class="col-md-3 mb-3"><div class="card card-stat text-center p-3"><h3 class="text-warning"><?= $kpis['ticketsPendientes'] ?></h3><small>Pendientes</small></div></div>
    <div class="col-md-3 mb-3"><div class="card card-stat text-center p-3"><h3 class="text-danger"><?= $kpis['ticketsCriticos'] ?></h3><small>Críticos</small></div></div>
</div>

<!-- SLA -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card p-4" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
            <h5>SLA Primera Respuesta</h5>
            <h2 class="text-primary"><?= $reporteSLA['slaPrimeraRespuestaPorcentaje'] ?>%</h2>
            <div class="progress mb-2"><div class="progress-bar bg-primary" style="width:<?= $reporteSLA['slaPrimeraRespuestaPorcentaje'] ?>%"></div></div>
            <small>Cumplen: <?= $reporteSLA['ticketsCumplenPrimeraRespuesta'] ?> | Incumplen: <?= $reporteSLA['ticketsIncumplenPrimeraRespuesta'] ?></small>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card p-4" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
            <h5>SLA Resolución</h5>
            <h2 class="text-success"><?= $reporteSLA['slaResolucionPorcentaje'] ?>%</h2>
            <div class="progress mb-2"><div class="progress-bar bg-success" style="width:<?= $reporteSLA['slaResolucionPorcentaje'] ?>%"></div></div>
            <small>Cumplen: <?= $reporteSLA['ticketsCumplenResolucion'] ?> | Incumplen: <?= $reporteSLA['ticketsIncumplenResolucion'] ?></small>
        </div>
    </div>
</div>

<!-- Tiempos -->
<div class="card p-4 mb-4" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
    <h5>Tiempos Promedio (minutos)</h5>
    <div class="row text-center">
        <div class="col-md-4"><h3 class="text-info"><?= $analisisTiempos['tiempoPromedioRespuestaMin'] ?></h3><small>Primera Respuesta</small></div>
        <div class="col-md-4"><h3 class="text-primary"><?= $analisisTiempos['tiempoPromedioResolucionMin'] ?></h3><small>Resolución</small></div>
        <div class="col-md-4"><h3 class="text-warning"><?= $analisisTiempos['tiempoPromedioEsperaMin'] ?></h3><small>En Espera</small></div>
    </div>
</div>

<!-- Técnicos -->
<?php if (!empty($desempenoTecnicos)): ?>
<div class="card p-4 mb-4" style="border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
    <h5>Desempeño de Técnicos</h5>
    <div class="table-responsive"><table class="table table-sm">
        <thead><tr><th>Técnico</th><th>Total</th><th>Resueltos</th><th>Tasa Éxito</th><th>SLA %</th><th>Tiempo Prom.</th></tr></thead>
        <tbody>
        <?php foreach ($desempenoTecnicos as $tec): ?>
            <tr><td><?= htmlspecialchars($tec['tecnico']) ?></td><td><?= $tec['totalAsignados'] ?></td><td><?= $tec['resueltos'] ?></td>
            <td><?= $tec['tasaExito'] ?>%</td><td><?= $tec['cumplimientoSLA'] ?>%</td><td><?= $tec['tiempoPromedioResolucionMin'] ?> min</td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
</div>
<?php endif; ?>

<!-- Alertas -->
<?php if ($alertas['tieneAlertas']): ?>
<div class="card p-4 mb-4 border-danger" style="border-radius:16px;box-shadow:0 4px 15px rgba(0,0,0,0.05)">
    <h5 class="text-danger"><i class="bi bi-exclamation-triangle"></i> Alertas</h5>
    <?php if ($alertas['ticketsSinAsignar'] > 0): ?><div class="alert alert-warning py-2"><i class="bi bi-person-x"></i> <?= $alertas['ticketsSinAsignar'] ?> ticket(s) sin asignar</div><?php endif; ?>
    <?php if ($alertas['ticketsVencidos'] > 0): ?><div class="alert alert-danger py-2"><i class="bi bi-clock-history"></i> <?= $alertas['ticketsVencidos'] ?> ticket(s) vencidos (SLA)</div><?php endif; ?>
    <?php if ($alertas['ticketsCriticosPendientes'] > 0): ?><div class="alert alert-danger py-2"><i class="bi bi-exclamation-octagon"></i> <?= $alertas['ticketsCriticosPendientes'] ?> ticket(s) críticos pendientes</div><?php endif; ?>
</div>
<?php endif; ?>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/main.php'; ?>
