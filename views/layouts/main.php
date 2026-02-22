<?php
/**
 * Layout principal (tema IPN)
 * Variables esperadas: $pageTitle, $content (buffer de salida)
 */
$pageTitle = $pageTitle ?? APP_NAME;
$currentUser = Session::isLoggedIn() ? [
    'nombre' => Session::userName(),
    'rol'    => Session::userRol(),
] : null;

$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$normalizePath = static function (string $path): string {
    return rtrim($path, '/') ?: '/';
};
$isActive = static function (array $paths) use ($uriPath, $normalizePath): bool {
    $current = $normalizePath($uriPath);
    foreach ($paths as $p) {
        $base = $normalizePath($p);
        if ($current === $base || str_starts_with($current . '/', $base . '/')) {
            return true;
        }
    }
    return false;
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/estilos-ipn.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="/assets/css/panel.css" rel="stylesheet">
    <?php if (isset($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link href="<?= htmlspecialchars($css) ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
    <style>
        body { font-family: 'Montserrat', 'Segoe UI', system-ui, sans-serif; background: var(--bg-escolar); }
        .app-shell { min-height: 100vh; }
        .content-wrap { max-width: 1400px; }

        /* Encabezados tipo tarjeta (aplica a casi todas las vistas existentes sin tocarlas) */
        .content-wrap > h1:first-child,
        .content-wrap > h2:first-child,
        .content-wrap > h3:first-child {
            background: #fff;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1rem !important;
            border-left: 5px solid var(--ipn-dorado);
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
            color: var(--ipn-guinda);
            font-weight: 700;
        }

        /* Navbar interna */
        .navbar-ipn .navbar-brand { font-weight: 700; letter-spacing: .3px; }
        .navbar-ipn .nav-link { display: inline-flex; align-items: center; gap: .35rem; }
        .navbar-ipn .navbar-text { color: rgba(255,255,255,.95) !important; }
        .navbar-ipn .btn-outline-light { border-width: 2px; font-weight: 600; }

        /* Tarjetas, tablas y formularios del PHP actual -> look & feel IPN */
        .card {
            border: none;
            border-top: 4px solid var(--ipn-guinda);
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
            overflow: hidden;
        }
        .card.card-stat { border-top-color: var(--ipn-dorado); }
        .table { margin-bottom: 0; }
        .table thead th {
            background: #f8fafc !important;
            color: #334155;
            font-weight: 700;
            border-bottom-width: 1px;
            white-space: nowrap;
        }
        .table > :not(caption) > * > * { vertical-align: middle; }
        .table a { color: var(--ipn-guinda); font-weight: 600; text-decoration: none; }
        .table a:hover { text-decoration: underline; }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #dbe2ea;
            box-shadow: none !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: rgba(108,29,69,.4);
            box-shadow: 0 0 0 .2rem rgba(108, 29, 69, .08) !important;
        }

        .btn-primary { background: var(--ipn-guinda); border-color: var(--ipn-guinda); }
        .btn-primary:hover, .btn-primary:focus { background: var(--ipn-guinda-dark); border-color: var(--ipn-guinda-dark); }
        .btn-outline-primary { color: var(--ipn-guinda); border-color: var(--ipn-guinda); }
        .btn-outline-primary:hover { background: var(--ipn-guinda); border-color: var(--ipn-guinda); }
        .btn-outline-warning { color: #8a6b43; border-color: var(--ipn-dorado); }
        .btn-outline-warning:hover { background: var(--ipn-dorado); border-color: var(--ipn-dorado); color: #fff; }
        .btn-warning { background: var(--ipn-dorado); border-color: var(--ipn-dorado); color: #fff; }
        .btn-warning:hover { background: #9f7b49; border-color: #9f7b49; color: #fff; }

        .progress { background-color: #edeff3; height: 10px; border-radius: 999px; }
        .progress-bar { border-radius: 999px; }

        /* Badges existentes del sistema */
        .badge-estado {
            display: inline-block;
            padding: .35rem .7rem;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .2px;
            white-space: nowrap;
        }
        .badge-ABIERTO, .badge-REABIERTO { background: #dbeafe; color: #1e40af; }
        .badge-EN_PROCESO { background: #fef3c7; color: #92400e; }
        .badge-EN_ESPERA { background: #ffedd5; color: #9a3412; }
        .badge-RESUELTO { background: #d1fae5; color: #065f46; }
        .badge-CERRADO { background: #e5e7eb; color: #374151; }
        .badge-CANCELADO { background: #fecaca; color: #991b1b; }
        .badge-URGENTE, .badge-ALTA { background: #fee2e2; color: #991b1b; }
        .badge-MEDIA { background: #fef3c7; color: #92400e; }
        .badge-BAJA { background: #dcfce7; color: #166534; }

        /* Flash messages */
        .alert { border: none; border-left: 4px solid transparent; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,.03); }
        .alert-success { border-left-color: #16a34a; }
        .alert-danger { border-left-color: #dc2626; }
        .alert-warning { border-left-color: #d97706; }
        .alert-info { border-left-color: #0284c7; }

        @media (max-width: 991.98px) {
            .content-wrap { padding-left: .25rem; padding-right: .25rem; }
            .content-wrap > h1:first-child,
            .content-wrap > h2:first-child,
            .content-wrap > h3:first-child { padding: .9rem 1rem; }
        }
    </style>
</head>
<body>
<?php if ($currentUser): ?>
<div class="app-shell">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-ipn mb-4">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand" href="<?= Session::isAdmin() ? '/admin/panel' : (Session::isTecnico() ? '/tecnico/panel' : '/usuario/panel') ?>">
                <i class="bi bi-shield-lock-fill me-2"></i>SAV12
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSav12" aria-controls="navbarSav12" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSav12">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                    <?php if (Session::isAdmin()): ?>
                        <li class="nav-item"><a href="/admin/panel" class="nav-link <?= $isActive(['/admin/panel']) ? 'active' : '' ?>"><i class="bi bi-grid-1x2-fill"></i>Inicio</a></li>
                        <li class="nav-item"><a href="/admin/usuarios" class="nav-link <?= $isActive(['/admin/usuarios']) ? 'active' : '' ?>"><i class="bi bi-people-fill"></i>Usuarios</a></li>
                        <li class="nav-item"><a href="/admin/tickets" class="nav-link <?= $isActive(['/admin/tickets', '/admin/ticket']) ? 'active' : '' ?>"><i class="bi bi-ticket-detailed-fill"></i>Tickets</a></li>
                        <li class="nav-item"><a href="/admin/categorias" class="nav-link <?= $isActive(['/admin/categorias']) ? 'active' : '' ?>"><i class="bi bi-tags-fill"></i>Categorías</a></li>
                        <li class="nav-item"><a href="/admin/ubicaciones" class="nav-link <?= $isActive(['/admin/ubicaciones']) ? 'active' : '' ?>"><i class="bi bi-geo-alt-fill"></i>Ubicaciones</a></li>
                        <li class="nav-item"><a href="/admin/reportes" class="nav-link <?= $isActive(['/admin/reportes']) ? 'active' : '' ?>"><i class="bi bi-file-earmark-bar-graph-fill"></i>Reportes</a></li>
                    <?php elseif (Session::isTecnico()): ?>
                        <li class="nav-item"><a href="/tecnico/panel" class="nav-link <?= $isActive(['/tecnico/panel']) ? 'active' : '' ?>"><i class="bi bi-grid-1x2-fill"></i>Panel</a></li>
                        <li class="nav-item"><a href="/tecnico/mis-tickets" class="nav-link <?= $isActive(['/tecnico/mis-tickets', '/tecnico/ticket']) ? 'active' : '' ?>"><i class="bi bi-tools"></i>Mis Tickets</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a href="/usuario/panel" class="nav-link <?= $isActive(['/usuario/panel']) ? 'active' : '' ?>"><i class="bi bi-grid-1x2-fill"></i>Panel</a></li>
                        <li class="nav-item"><a href="/usuario/crear-ticket" class="nav-link <?= $isActive(['/usuario/crear-ticket']) ? 'active' : '' ?>"><i class="bi bi-plus-circle-fill"></i>Nuevo Ticket</a></li>
                        <li class="nav-item"><a href="/usuario/mis-tickets" class="nav-link <?= $isActive(['/usuario/mis-tickets', '/usuario/ticket']) ? 'active' : '' ?>"><i class="bi bi-ticket-detailed-fill"></i>Mis Tickets</a></li>
                    <?php endif; ?>
                </ul>

                <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-2 gap-lg-3 py-2 py-lg-0">
                    <span class="navbar-text">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($currentUser['nombre']) ?>
                        <small class="opacity-75">(<?= htmlspecialchars($currentUser['rol']) ?>)</small>
                    </span>
                    <a href="/logout" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> Salir</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="container pb-4 pb-lg-5">
        <div class="content-wrap mx-auto">
            <?php if ($flashSuccess = Session::getFlash('success')): ?>
                <div class="alert alert-success alert-dismissible fade show mb-3"><?= htmlspecialchars($flashSuccess) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if ($flashError = Session::getFlash('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-3"><?= htmlspecialchars($flashError) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <?= $content ?? '' ?>
        </div>
    </main>
</div>
<?php else: ?>
    <?= $content ?? '' ?>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php if (isset($extraJs)): ?>
    <?php foreach ($extraJs as $js): ?>
        <script src="<?= htmlspecialchars($js) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
