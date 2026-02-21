<?php

require_once APP_PATH . '/Models/Ticket.php';
require_once APP_PATH . '/Models/Comentario.php';
require_once APP_PATH . '/Models/HistorialAccion.php';
require_once APP_PATH . '/Services/TicketService.php';
require_once APP_PATH . '/Services/ComentarioService.php';
require_once APP_PATH . '/Services/FileService.php';

class TecnicoController {

    public function panel(): void {
        AuthMiddleware::requireTecnico();
        $usuario = AuthMiddleware::currentUser();
        $misTickets = Ticket::getByTecnico($usuario['id']);
        $ticketsSinAsignar = Ticket::getSinAsignar();
        include VIEW_PATH . '/tecnico/panel.php';
    }

    public function misTickets(): void {
        AuthMiddleware::requireTecnico();
        $usuario = AuthMiddleware::currentUser();
        $tickets = Ticket::getByTecnico($usuario['id']);
        include VIEW_PATH . '/tecnico/mis-tickets.php';
    }

    public function detalleTicket(string $id): void {
        AuthMiddleware::requireTecnico();
        $usuario = AuthMiddleware::currentUser();
        $ticket = Ticket::findById((int) $id);
        if (!$ticket) {
            header('Location: /tecnico/mis-tickets?error=notfound');
            exit;
        }
        $comentarios = Comentario::getByTicket((int) $id);
        $historial = HistorialAccion::getByTicket((int) $id);
        $estados = ['ABIERTO', 'REABIERTO', 'EN_PROCESO', 'EN_ESPERA', 'RESUELTO', 'CERRADO', 'CANCELADO'];
        include VIEW_PATH . '/tecnico/detalle-ticket.php';
    }

    public function cambiarEstado(string $id): void {
        AuthMiddleware::requireTecnico();
        AuthMiddleware::verifyCsrf();
        $usuario = AuthMiddleware::currentUser();

        try {
            $nuevoEstado = $_POST['nuevoEstado'] ?? '';
            $observaciones = $_POST['observaciones'] ?? null;
            $evidencia = null;

            if (!empty($_FILES['evidenciaResolucion']['tmp_name'])) {
                $evidencia = FileService::guardar($_FILES['evidenciaResolucion']);
            }

            TicketService::cambiarEstado((int) $id, $nuevoEstado, $usuario, $observaciones, $evidencia);
            header("Location: /tecnico/ticket/$id?success=estadocambiado");
        } catch (Exception $e) {
            header("Location: /tecnico/ticket/$id?error=cambioestado");
        }
        exit;
    }

    public function comentar(string $id): void {
        AuthMiddleware::requireTecnico();
        AuthMiddleware::verifyCsrf();
        $usuario = AuthMiddleware::currentUser();
        $contenido = trim($_POST['contenido'] ?? '');

        if (!empty($contenido)) {
            ComentarioService::agregar((int) $id, $usuario, $contenido);
        }
        header("Location: /tecnico/ticket/$id");
        exit;
    }

    public function reabrir(string $id): void {
        AuthMiddleware::requireTecnico();
        AuthMiddleware::verifyCsrf();
        $usuario = AuthMiddleware::currentUser();
        TicketService::reabrirTicket((int) $id, $usuario);
        header("Location: /tecnico/ticket/$id");
        exit;
    }

    public function asignarme(string $id): void {
        AuthMiddleware::requireTecnico();
        AuthMiddleware::verifyCsrf();
        $usuario = AuthMiddleware::currentUser();
        $ticket = Ticket::findById((int) $id);

        if ($ticket && empty($ticket['asignado_a_id'])) {
            TicketService::asignarTecnico((int) $id, $usuario['id'], $usuario);
            TicketService::cambiarEstado((int) $id, 'EN_PROCESO', $usuario, 'Ticket asignado y tomado en proceso');
        }
        header("Location: /tecnico/ticket/$id");
        exit;
    }
}
