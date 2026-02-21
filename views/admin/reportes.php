<?php
$pageTitle = 'Reportes - SAV12';
$extraCss = [base_url('assets/css/reports.css')];
$extraJs = ['https://cdn.jsdelivr.net/npm/chart.js', base_url('assets/js/reports.js')];

$kpis = is_array($kpis ?? null) ? $kpis : [];
$reporteSLA = is_array($reporteSLA ?? null) ? $reporteSLA : [];
$analisisTiempos = is_array($analisisTiempos ?? null) ? $analisisTiempos : [];
$desempenoTecnicos = is_array($desempenoTecnicos ?? null) ? $desempenoTecnicos : [];
$analisisPorPrioridad = is_array($analisisPorPrioridad ?? null) ? $analisisPorPrioridad : [];
$analisisPorUbicaciones = is_array($analisisPorUbicaciones ?? null) ? $analisisPorUbicaciones : [];
$topCategorias = is_array($topCategorias ?? null) ? $topCategorias : [];
$alertas = is_array($alertas ?? null) ? $alertas : [];
$ticketsProblematicos = is_array($ticketsProblematicos ?? null) ? $ticketsProblematicos : [];
$periodoSeleccionado = $periodoSeleccionado ?? ($_GET['periodo'] ?? 'semanal');

$qp = $_GET;
$buildReportUrl = function (array $params = []) use ($qp): string {
    $q = array_merge($qp, $params);
    return base_url('admin/reportes') . '?' . http_build_query($q);
};
$buildExportUrl = function (string $type) use ($qp): string {
    return base_url('admin/reportes/export/' . $type) . (!empty($qp) ? ('?' . http_build_query($qp)) : '');
};
$val = fn(array $arr, string $key, $default = 'N/D') => $arr[$key] ?? $default;
$safeNum = fn($v, $d = 0) => is_numeric($v) ? $v : $d;

ob_start();
?>
<h2 class="mb-4"><i class="bi bi-bar-chart"></i> Reportes y Análisis</h2>

<div class="card p-3 mb-4 report-card">
    <form method="GET" action="<?= base_url('admin/reportes') ?>" class="d-flex gap-2 align-items-center flex-wrap">
        <?php foreach (['semanal', 'mensual', 'trimestral', 'anual'] as $p): ?>
            <a href="<?= htmlspecialchars($buildReportUrl(['periodo' => $p])) ?>" class="btn btn-sm <?= $periodoSeleccionado === $p ? 'btn-primary' : 'btn-outline-primary' ?>"><?= ucfirst($p) ?></a>
        <?php endforeach; ?>
        <input type="hidden" name="periodo" value="custom">
        <input type="date" name="desde" value="<?= htmlspecialchars($_GET['desde'] ?? '') ?>" class="form-control form-control-sm" style="max-width:160px">
        <input type="date" name="hasta" value="<?= htmlspecialchars($_GET['hasta'] ?? '') ?>" class="form-control form-control-sm" style="max-width:160px">
        <button class="btn btn-sm btn-outline-dark" type="submit"><i class="bi bi-funnel"></i> Aplicar</button>

        <a href="<?= htmlspecialchars($buildExportUrl('csv')) ?>" class="btn btn-sm btn-success ms-auto"><i class="bi bi-filetype-csv"></i> Exportar CSV</a>
        <a href="<?= htmlspecialchars($buildExportUrl('pdf')) ?>" class="btn btn-sm btn-danger"><i class="bi bi-filetype-pdf"></i> Exportar PDF</a>
    </form>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3"><div class="card card-stat text-center p-3"><h3 class="text-primary"><?= $safeNum($val($kpis, 'totalTickets', 0)) ?></h3><small>Total</small></div></div>
    <div class="col-md-3 mb-3"><div class="card card-stat text-center p-3"><h3 class="text-success"><?= $safeNum($val($kpis, 'ticketsResueltos', 0)) ?></h3><small>Resueltos</small></div></div>
    <div class="col-md-3 mb-3"><div class="card card-stat text-center p-3"><h3 class="text-warning"><?= $safeNum($val($kpis, 'ticketsPendientes', 0)) ?></h3><small>Pendientes</small></div></div>
    <div class="col-md-3 mb-3"><div class="card card-stat text-center p-3"><h3 class="text-danger"><?= $safeNum($val($kpis, 'ticketsCriticos', 0)) ?></h3><small>Críticos</small></div></div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card p-4 report-card">
            <h5>SLA Primera Respuesta</h5>
            <?php $slaPrim = (float) $safeNum($val($reporteSLA, 'slaPrimeraRespuestaPorcentaje', 0)); ?>
            <h2 class="text-primary"><?= number_format($slaPrim, 2) ?>%</h2>
            <div class="progress mb-2"><div class="progress-bar bg-primary" style="width:<?= max(0, min(100, $slaPrim)) ?>%"></div></div>
            <small>Cumplen: <?= $safeNum($val($reporteSLA, 'ticketsCumplenPrimeraRespuesta', 0)) ?> | Incumplen: <?= $safeNum($val($reporteSLA, 'ticketsIncumplenPrimeraRespuesta', 0)) ?></small>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card p-4 report-card">
            <h5>SLA Resolución</h5>
            <?php $slaRes = (float) $safeNum($val($reporteSLA, 'slaResolucionPorcentaje', 0)); ?>
            <h2 class="text-success"><?= number_format($slaRes, 2) ?>%</h2>
            <div class="progress mb-2"><div class="progress-bar bg-success" style="width:<?= max(0, min(100, $slaRes)) ?>%"></div></div>
            <small>Cumplen: <?= $safeNum($val($reporteSLA, 'ticketsCumplenResolucion', 0)) ?> | Incumplen: <?= $safeNum($val($reporteSLA, 'ticketsIncumplenResolucion', 0)) ?></small>
        </div>
    </div>
</div>

<div class="card p-4 mb-4 report-card">
    <h5>Análisis de Tiempos</h5>
    <div class="row text-center">
        <div class="col-md-4"><h3 class="text-info"><?= $safeNum($val($analisisTiempos, 'tiempoPromedioRespuestaMin', 0)) ?></h3><small>Primera Respuesta (min)</small></div>
        <div class="col-md-4"><h3 class="text-primary"><?= $safeNum($val($analisisTiempos, 'tiempoPromedioResolucionMin', 0)) ?></h3><small>Resolución (min)</small></div>
        <div class="col-md-4"><h3 class="text-warning"><?= $safeNum($val($analisisTiempos, 'tiempoPromedioEsperaMin', 0)) ?></h3><small>En Espera (min)</small></div>
    </div>
</div>

<div class="card p-4 mb-4 report-card">
    <h5>Desempeño de Técnicos</h5>
    <?php if (!empty($desempenoTecnicos)): ?>
        <div class="table-responsive"><table class="table table-sm align-middle">
            <thead><tr><th>Técnico</th><th>Asignados/Activos</th><th>Resueltos</th><th>Pendientes</th><th>Tasa Resolución</th><th>Tasa SLA</th></tr></thead>
            <tbody>
            <?php foreach ($desempenoTecnicos as $tec): ?>
                <tr>
                    <td><?= htmlspecialchars((string) ($tec['tecnico'] ?? $tec['tecnicoNombre'] ?? 'N/D')) ?></td>
                    <td><?= $safeNum($tec['asignados'] ?? $tec['ticketsActivos'] ?? 0) ?></td>
                    <td><?= $safeNum($tec['resueltos'] ?? 0) ?></td>
                    <td><?= $safeNum($tec['pendientes'] ?? 0) ?></td>
                    <td><?= $safeNum($tec['tasaResolucion'] ?? 0) ?>%</td>
                    <td><?= $safeNum($tec['tasaSla'] ?? 0) ?>%</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    <?php else: ?><div class="text-muted">Sin datos de desempeño.</div><?php endif; ?>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card p-3 report-card">
            <h5>Análisis por Prioridad</h5>
            <canvas id="chartPrioridad" height="180"></canvas>
            <?php if (empty($analisisPorPrioridad)): ?><div class="text-muted">Sin datos para prioridad.</div><?php endif; ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-3 report-card">
            <h5>Análisis por Ubicaciones</h5>
            <canvas id="chartUbicaciones" height="180"></canvas>
            <?php if (empty($analisisPorUbicaciones)): ?><div class="text-muted">Sin datos para ubicaciones.</div><?php endif; ?>
        </div>
    </div>
</div>

<div class="card p-4 mb-4 report-card">
    <h5>Top Categorías</h5>
    <?php if (!empty($topCategorias)): ?>
        <ul class="mb-0">
            <?php foreach ($topCategorias as $cat): ?>
                <li><?= htmlspecialchars((string) ($cat['nombre'] ?? 'N/D')) ?>: <strong><?= $safeNum($cat['total'] ?? 0) ?></strong></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?><div class="text-muted">Sin datos de categorías.</div><?php endif; ?>
</div>

<?php $hasAlerts = (bool) ($alertas['tieneAlertas'] ?? false); ?>
<div class="card p-4 mb-4 report-card <?= $hasAlerts ? 'border border-danger' : '' ?>">
    <h5 class="<?= $hasAlerts ? 'text-danger' : '' ?>"><i class="bi bi-exclamation-triangle"></i> Alertas</h5>
    <div class="row g-2">
        <div class="col-md-4"><div class="alert alert-light py-2 mb-0">Sin asignar: <strong><?= $safeNum($alertas['ticketsSinAsignar'] ?? 0) ?></strong></div></div>
        <div class="col-md-4"><div class="alert alert-light py-2 mb-0">Vencidos SLA: <strong><?= $safeNum($alertas['ticketsVencidos'] ?? 0) ?></strong></div></div>
        <div class="col-md-4"><div class="alert alert-light py-2 mb-0">Críticos pendientes: <strong><?= $safeNum($alertas['ticketsCriticosPendientes'] ?? 0) ?></strong></div></div>
        <div class="col-md-6"><div class="alert alert-light py-2 mb-0">Reabiertos muchas veces: <strong><?= $safeNum($alertas['ticketsReabiertosMuchasVeces'] ?? 0) ?></strong></div></div>
        <div class="col-md-6"><div class="alert alert-light py-2 mb-0">Técnicos sobrecargados: <strong><?= $safeNum($alertas['tecnicosSobrecargados'] ?? 0) ?></strong></div></div>
    </div>
</div>

<?php
$sections = [
    'masReabiertos' => 'Más Reabiertos',
    'mayorTiempoSinResolver' => 'Mayor Tiempo sin Resolver',
    'sinPrimeraRespuesta' => 'Sin Primera Respuesta',
    'criticosSinResolver' => 'Críticos sin Resolver',
    'rankingPorScore' => 'Ranking por Score',
];
?>
<div class="card p-4 mb-4 report-card">
    <h5>Tickets Problemáticos</h5>
    <?php foreach ($sections as $key => $title): ?>
        <h6 class="mt-3"><?= htmlspecialchars($title) ?></h6>
        <?php $list = is_array($ticketsProblematicos[$key] ?? null) ? $ticketsProblematicos[$key] : []; ?>
        <?php if (!empty($list)): ?>
            <div class="table-responsive"><table class="table table-sm align-middle">
                <thead><tr><th>ID</th><th>Título</th><th>Estado</th><th>Prioridad</th><th>Técnico</th><th>Reaperturas</th><th>Días</th><th>Score</th></tr></thead>
                <tbody>
                <?php foreach ($list as $item): ?>
                    <tr>
                        <td><?= $safeNum($item['id'] ?? $item['ticketId'] ?? 0) ?></td>
                        <td><?= htmlspecialchars((string) ($item['titulo'] ?? 'N/D')) ?></td>
                        <td><?= htmlspecialchars((string) ($item['estado'] ?? 'N/D')) ?></td>
                        <td><?= htmlspecialchars((string) ($item['prioridad'] ?? 'N/D')) ?></td>
                        <td><?= htmlspecialchars((string) ($item['tecnico'] ?? $item['tecnicoAsignado'] ?? 'N/D')) ?></td>
                        <td><?= $safeNum($item['reabiertos'] ?? $item['contadorReaperturas'] ?? 0) ?></td>
                        <td><?= $safeNum($item['diasAbierto'] ?? 0) ?></td>
                        <td><?= $safeNum($item['scoreProblema'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        <?php else: ?><div class="text-muted">Sin datos.</div><?php endif; ?>
    <?php endforeach; ?>
</div>

<script>
window.__reportesData = {
    prioridad: <?= json_encode($analisisPorPrioridad, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
    ubicaciones: <?= json_encode($analisisPorUbicaciones, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
    sla: <?= json_encode($reporteSLA, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
};
</script>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
