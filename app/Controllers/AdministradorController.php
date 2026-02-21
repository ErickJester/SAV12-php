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
require_once APP_PATH . '/Services/ExportService.php';

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
        [$desde, $hasta] = $this->resolveRangoReporte($periodo, $_GET['desde'] ?? null, $_GET['hasta'] ?? null);

        try {
            $payload = ReporteService::generarPayloadReporteAdmin($desde, $hasta);
        } catch (Throwable $e) {
            error_log('[AdministradorController::reportes] ' . $e->getMessage());
            Session::flash('error', 'No fue posible cargar el reporte.');
            $payload = [];
        }

        $kpis = $payload['kpis'] ?? [];
        $reporteSLA = $payload['reporteSLA'] ?? [];
        $reportePorEstado = ReporteService::reportePorEstado($desde, $hasta);
        $reporteGeneral = ReporteService::reporteGeneral($desde, $hasta);
        $topCategorias = $payload['topCategorias'] ?? [];
        $analisisTiempos = $payload['analisisTiempos'] ?? [];
        $desempenoTecnicos = $payload['desempenoTecnicos'] ?? [];
        $analisisPorPrioridad = $payload['analisisPorPrioridad'] ?? [];
        $analisisPorUbicaciones = $payload['analisisPorUbicaciones'] ?? [];
        $alertas = $payload['alertas'] ?? [];
        $ticketsProblematicos = $payload['ticketsProblematicos'] ?? [];

        $periodoSeleccionado = $periodo;
        include VIEW_PATH . '/admin/reportes.php';
    }

    public function exportarReportesCsv(): void {
        AuthMiddleware::requireAdmin();
        [$desde, $hasta] = $this->resolveRangoReporte('custom', $_GET['desde'] ?? null, $_GET['hasta'] ?? null);

        try {
            $payload = ReporteService::generarPayloadReporteAdmin($desde, $hasta);
            $export = ExportService::exportarReporteCsv($payload, []);
            if (empty($export['ok'])) {
                throw new RuntimeException('CSV export no disponible');
            }

            header('Content-Type: ' . ($export['mime'] ?? 'text/csv; charset=UTF-8'));
            header('Content-Disposition: attachment; filename="' . ($export['filename'] ?? 'reporte.csv') . '"');
            echo $export['content'] ?? '';
            exit;
        } catch (Throwable $e) {
            error_log('[AdministradorController::exportarReportesCsv] ' . $e->getMessage());
            Session::flash('error', 'Error al exportar CSV');
            header('Location: ' . base_url('admin/reportes'));
            exit;
        }
    }

    public function exportarReportesPdf(): void {
        AuthMiddleware::requireAdmin();
        [$desde, $hasta] = $this->resolveRangoReporte('custom', $_GET['desde'] ?? null, $_GET['hasta'] ?? null);

        try {
            $payload = ReporteService::generarPayloadReporteAdmin($desde, $hasta);
            $export = ExportService::exportarReportePdf($payload, []);
            if (empty($export['ok'])) {
                throw new RuntimeException('PDF export no disponible');
            }

            header('Content-Type: ' . ($export['mime'] ?? 'application/pdf'));
            header('Content-Disposition: attachment; filename="' . ($export['filename'] ?? 'reporte.pdf') . '"');
            echo $export['content'] ?? '';
            exit;
        } catch (Throwable $e) {
            error_log('[AdministradorController::exportarReportesPdf] ' . $e->getMessage());
            Session::flash('error', 'Error al exportar PDF');
            header('Location: ' . base_url('admin/reportes'));
            exit;
        }
    }

    private function resolveRangoReporte(?string $periodo, ?string $desdeParam, ?string $hastaParam): array {
        $ahora = date('Y-m-d H:i:s');
        $periodo = $periodo ?: 'semanal';

        switch ($periodo) {
            case 'mensual':
                $desde = date('Y-m-d H:i:s', strtotime('-1 month'));
                $hasta = $ahora;
                break;
            case 'trimestral':
                $desde = date('Y-m-d H:i:s', strtotime('-3 months'));
                $hasta = $ahora;
                break;
            case 'anual':
                $desde = date('Y-m-d H:i:s', strtotime('-1 year'));
                $hasta = $ahora;
                break;
            case 'custom':
                $desde = $this->normalizeFechaInicio($desdeParam) ?? date('Y-m-d H:i:s', strtotime('-1 month'));
                $hasta = $this->normalizeFechaFin($hastaParam) ?? $ahora;
                break;
            default:
                $desde = date('Y-m-d H:i:s', strtotime('-1 week'));
                $hasta = $ahora;
                break;
        }

        if (strtotime($desde) > strtotime($hasta)) {
            $desde = date('Y-m-d H:i:s', strtotime('-1 week'));
            $hasta = $ahora;
        }
        return [$desde, $hasta];
    }

    private function normalizeFechaInicio(?string $value): ?string {
        if (empty($value)) return null;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) return null;
        return $value . ' 00:00:00';
    }

    private function normalizeFechaFin(?string $value): ?string {
        if (empty($value)) return null;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) return null;
        return $value . ' 23:59:59';
    }
}
