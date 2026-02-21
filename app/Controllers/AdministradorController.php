<?php

require_once APP_PATH . '/Models/Ticket.php';
require_once APP_PATH . '/Models/Usuario.php';
require_once APP_PATH . '/Models/Categoria.php';
require_once APP_PATH . '/Models/Ubicacion.php';
require_once APP_PATH . '/Models/Comentario.php';
require_once APP_PATH . '/Models/HistorialAccion.php';
require_once APP_PATH . '/Services/TicketService.php';
require_once APP_PATH . '/Services/ComentarioService.php';
require_once APP_PATH . '/Services/ReporteService.php';

class AdministradorController {

    public function panel(): void {
        AuthMiddleware::requireAdmin();
        $usuario = AuthMiddleware::currentUser();
        $ahora = date('Y-m-d H:i:s');
        $desde = date('Y-m-d H:i:s', strtotime('-1 month'));
        $reporte = ReporteService::reporteGeneral($desde, $ahora);
        $kpis = ReporteService::kpisEjecutivos($desde, $ahora);
        include VIEW_PATH . '/admin/panel.php';
    }

    // === GESTIÓN USUARIOS ===
    public function usuarios(): void {
        AuthMiddleware::requireAdmin();
        $usuario = AuthMiddleware::currentUser();
        $usuarios = Usuario::getAll();
        include VIEW_PATH . '/admin/usuarios.php';
    }

    public function cambiarEstadoUsuario(string $id): void {
        AuthMiddleware::requireAdmin();
        AuthMiddleware::verifyCsrf();
        $activo = ($_POST['activo'] ?? '1') === '1';
        Usuario::cambiarEstado((int) $id, $activo);
        header('Location: /admin/usuarios?success=updated');
        exit;
    }

    public function cambiarRolUsuario(string $id): void {
        AuthMiddleware::requireAdmin();
        AuthMiddleware::verifyCsrf();
        $rol = $_POST['rol'] ?? '';
        $rolesValidos = ['ALUMNO', 'DOCENTE', 'ADMINISTRATIVO', 'TECNICO', 'ADMIN'];
        if (in_array($rol, $rolesValidos)) {
            Usuario::cambiarRol((int) $id, $rol);
        }
        header('Location: /admin/usuarios?success=rolchanged');
        exit;
    }

    // === GESTIÓN CATEGORÍAS ===
    public function categorias(): void {
        AuthMiddleware::requireAdmin();
        $usuario = AuthMiddleware::currentUser();
        $categorias = Categoria::getAll();
        include VIEW_PATH . '/admin/categorias.php';
    }

    public function crearCategoria(): void {
        AuthMiddleware::requireAdmin();
        AuthMiddleware::verifyCsrf();
        Categoria::crear(trim($_POST['nombre'] ?? ''), trim($_POST['descripcion'] ?? ''));
        header('Location: /admin/categorias?success=created');
        exit;
    }

    public function desactivarCategoria(string $id): void {
        AuthMiddleware::requireAdmin();
        AuthMiddleware::verifyCsrf();
        Categoria::desactivar((int) $id);
        header('Location: /admin/categorias?success=deactivated');
        exit;
    }

    // === GESTIÓN UBICACIONES ===
    public function ubicaciones(): void {
        AuthMiddleware::requireAdmin();
        $usuario = AuthMiddleware::currentUser();
        $ubicaciones = Ubicacion::getAll();
        include VIEW_PATH . '/admin/ubicaciones.php';
    }

    public function crearUbicacion(): void {
        AuthMiddleware::requireAdmin();
        AuthMiddleware::verifyCsrf();
        Ubicacion::crear(trim($_POST['edificio'] ?? ''), trim($_POST['piso'] ?? ''), trim($_POST['salon'] ?? ''));
        header('Location: /admin/ubicaciones?success=created');
        exit;
    }

    public function desactivarUbicacion(string $id): void {
        AuthMiddleware::requireAdmin();
        AuthMiddleware::verifyCsrf();
        Ubicacion::desactivar((int) $id);
        header('Location: /admin/ubicaciones?success=deactivated');
        exit;
    }

    // === TICKETS ===
    public function tickets(): void {
        AuthMiddleware::requireAdmin();
        $usuario = AuthMiddleware::currentUser();
        $filtro = $_GET['filtro'] ?? null;
        $tickets = Ticket::getAll($filtro);
        $asignables = Usuario::getAsignables();
        include VIEW_PATH . '/admin/tickets.php';
    }

    public function detalleTicket(string $id): void {
        AuthMiddleware::requireAdmin();
        $usuario = AuthMiddleware::currentUser();
        $ticket = Ticket::findById((int) $id);
        if (!$ticket) {
            header('Location: /admin/tickets?error=notfound');
            exit;
        }
        $comentarios = Comentario::getByTicket((int) $id);
        $historial = HistorialAccion::getByTicket((int) $id);
        $asignables = Usuario::getAsignables();
        $estados = ['ABIERTO', 'REABIERTO', 'EN_PROCESO', 'EN_ESPERA', 'RESUELTO', 'CERRADO', 'CANCELADO'];
        include VIEW_PATH . '/admin/detalle-ticket.php';
    }

    public function comentarTicket(string $id): void {
        AuthMiddleware::requireAdmin();
        AuthMiddleware::verifyCsrf();
        $usuario = AuthMiddleware::currentUser();
        $contenido = trim($_POST['contenido'] ?? '');
        if (!empty($contenido)) {
            ComentarioService::agregar((int) $id, $usuario, $contenido);
        }
        header("Location: /admin/ticket/$id");
        exit;
    }

    public function asignarTecnico(string $id): void {
        AuthMiddleware::requireAdmin();
        AuthMiddleware::verifyCsrf();
        $usuario = AuthMiddleware::currentUser();
        $tecnicoId = (int) ($_POST['tecnicoId'] ?? 0);
        if ($tecnicoId > 0) {
            TicketService::asignarTecnico((int) $id, $tecnicoId, $usuario);
        }
        header('Location: /admin/tickets?success=assigned');
        exit;
    }

    public function asignarmeTicket(string $id): void {
        AuthMiddleware::requireAdmin();
        AuthMiddleware::verifyCsrf();
        $usuario = AuthMiddleware::currentUser();
        TicketService::asignarTecnico((int) $id, $usuario['id'], $usuario);
        header("Location: /admin/ticket/$id");
        exit;
    }

    public function reabrirTicket(string $id): void {
        AuthMiddleware::requireAdmin();
        AuthMiddleware::verifyCsrf();
        $usuario = AuthMiddleware::currentUser();
        TicketService::reabrirTicket((int) $id, $usuario);
        header("Location: /admin/ticket/$id");
        exit;
    }

    // === REPORTES ===
    public function reportes(): void {
        AuthMiddleware::requireAdmin();
        $usuario = AuthMiddleware::currentUser();

        $periodo = $_GET['periodo'] ?? 'semanal';
        $ahora = date('Y-m-d H:i:s');

        switch ($periodo) {
            case 'mensual':    $desde = date('Y-m-d H:i:s', strtotime('-1 month')); break;
            case 'trimestral': $desde = date('Y-m-d H:i:s', strtotime('-3 months')); break;
            case 'anual':      $desde = date('Y-m-d H:i:s', strtotime('-1 year')); break;
            case 'custom':
                $desde = ($_GET['desde'] ?? '') ? $_GET['desde'] . ' 00:00:00' : date('Y-m-d H:i:s', strtotime('-1 week'));
                $ahora = ($_GET['hasta'] ?? '') ? $_GET['hasta'] . ' 23:59:59' : $ahora;
                break;
            default: $desde = date('Y-m-d H:i:s', strtotime('-1 week')); break;
        }

        $kpis = ReporteService::kpisEjecutivos($desde, $ahora);
        $reporteSLA = ReporteService::reporteSLA($desde, $ahora);
        $reportePorEstado = ReporteService::reportePorEstado($desde, $ahora);
        $reporteGeneral = ReporteService::reporteGeneral($desde, $ahora);
        $topCategorias = ReporteService::topCategorias($desde, $ahora);
        $analisisTiempos = ReporteService::analisisTiempos($desde, $ahora);
        $desempenoTecnicos = ReporteService::desempenoTecnicos($desde, $ahora);
        $analisisPorPrioridad = ReporteService::analisisPorPrioridad($desde, $ahora);
        $analisisPorUbicaciones = ReporteService::analisisPorUbicaciones($desde, $ahora);
        $alertas = ReporteService::alertas($desde, $ahora);

        $periodoSeleccionado = $periodo;
        include VIEW_PATH . '/admin/reportes.php';
    }
}
