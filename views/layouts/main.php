<?php
/**
 * Layout principal - equivalente al template base de Thymeleaf
 * Variables esperadas: $pageTitle, $content (buffer de salida)
 */
$pageTitle = $pageTitle ?? APP_NAME;
$currentUser = Session::isLoggedIn() ? [
    'nombre' => Session::userName(),
    'rol'    => Session::userRol(),
] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="/assets/css/panel.css" rel="stylesheet">
    <?php if (isset($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link href="<?= $css ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1e40af;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --light-bg: #f8fafc;
            --border-light: #e2e8f0;
        }
        body { font-family: 'Segoe UI', sans-serif; background: var(--light-bg); }
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #7c3aed 100%);
            min-height: 100vh; padding: 20px 0; width: 260px; position: fixed; top: 0; left: 0;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
        }
        .sidebar a { color: rgba(255,255,255,0.8); display: block; padding: 12px 24px; text-decoration: none; transition: all 0.3s; }
        .sidebar a:hover, .sidebar a.active { color: white; background: rgba(255,255,255,0.15); border-radius: 0 8px 8px 0; }
        .sidebar .brand { color: white; font-size: 22px; font-weight: 700; padding: 20px 24px; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.2); }
        .main-content { margin-left: 260px; padding: 30px; }
        .card-stat { border-radius: 16px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.3s; }
        .card-stat:hover { transform: translateY(-4px); }
        .badge-estado { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-ABIERTO, .badge-REABIERTO { background: #dbeafe; color: #1e40af; }
        .badge-EN_PROCESO { background: #fef3c7; color: #92400e; }
        .badge-EN_ESPERA { background: #fed7aa; color: #9a3412; }
        .badge-RESUELTO { background: #d1fae5; color: #065f46; }
        .badge-CERRADO { background: #e5e7eb; color: #374151; }
        .badge-CANCELADO { background: #fecaca; color: #991b1b; }
        .badge-ALTA, .badge-URGENTE { background: #fecaca; color: #991b1b; }
        .badge-MEDIA { background: #fef3c7; color: #92400e; }
        .badge-BAJA { background: #d1fae5; color: #065f46; }
        .user-info { color: rgba(255,255,255,0.7); padding: 15px 24px; font-size: 13px; border-top: 1px solid rgba(255,255,255,0.2); position: absolute; bottom: 0; width: 100%; }
        @media (max-width: 768px) {
            .sidebar { position: relative; width: 100%; min-height: auto; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
<?php if ($currentUser): ?>
    <!-- Sidebar -->
    <nav class="sidebar d-none d-md-block">
        <div class="brand">
            <i class="bi bi-headset"></i> SAV12
        </div>
        
        <?php if (Session::isAdmin()): ?>
            <a href="/admin/panel" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/admin/panel') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="/admin/tickets" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/admin/ticket') ? 'active' : '' ?>">
                <i class="bi bi-ticket-perforated"></i> Tickets
            </a>
            <a href="/admin/usuarios" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/admin/usuarios') ? 'active' : '' ?>">
                <i class="bi bi-people"></i> Usuarios
            </a>
            <a href="/admin/categorias" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/admin/categorias') ? 'active' : '' ?>">
                <i class="bi bi-tags"></i> Categorías
            </a>
            <a href="/admin/ubicaciones" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/admin/ubicaciones') ? 'active' : '' ?>">
                <i class="bi bi-geo-alt"></i> Ubicaciones
            </a>
            <a href="/admin/reportes" class="<?= str_starts_with($_SERVER['REQUEST_URI'], '/admin/reportes') ? 'active' : '' ?>">
                <i class="bi bi-bar-chart"></i> Reportes
            </a>
        <?php elseif (Session::isTecnico()): ?>
            <a href="/tecnico/panel"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="/tecnico/mis-tickets"><i class="bi bi-ticket-perforated"></i> Mis Tickets</a>
        <?php else: ?>
            <a href="/usuario/panel"><i class="bi bi-speedometer2"></i> Mi Panel</a>
            <a href="/usuario/crear-ticket"><i class="bi bi-plus-circle"></i> Nuevo Ticket</a>
            <a href="/usuario/mis-tickets"><i class="bi bi-ticket-perforated"></i> Mis Tickets</a>
        <?php endif; ?>

        <div class="user-info">
            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($currentUser['nombre']) ?><br>
            <small><?= $currentUser['rol'] ?></small><br>
            <a href="/logout" class="mt-2 d-inline-block" style="padding:4px 0"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a>
        </div>
    </nav>

    <!-- Mobile header -->
    <nav class="navbar d-md-none bg-primary text-white p-3">
        <span class="navbar-brand text-white"><i class="bi bi-headset"></i> SAV12</span>
        <a href="/logout" class="text-white"><i class="bi bi-box-arrow-left"></i></a>
    </nav>

    <main class="main-content">
        <?php if ($flashSuccess = Session::getFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($flashSuccess) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($flashError = Session::getFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($flashError) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        
        <?= $content ?? '' ?>
    </main>
<?php else: ?>
    <?= $content ?? '' ?>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php if (isset($extraJs)): ?>
    <?php foreach ($extraJs as $js): ?>
        <script src="<?= $js ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
