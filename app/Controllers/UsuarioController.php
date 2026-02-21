<?php

require_once APP_PATH . '/Models/Ticket.php';
require_once APP_PATH . '/Models/Comentario.php';
require_once APP_PATH . '/Models/HistorialAccion.php';
require_once APP_PATH . '/Models/Categoria.php';
require_once APP_PATH . '/Models/Ubicacion.php';
require_once APP_PATH . '/Services/TicketService.php';
require_once APP_PATH . '/Services/ComentarioService.php';
require_once APP_PATH . '/Services/FileService.php';

class UsuarioController {

    public function panel(): void {
        AuthMiddleware::requireLogin();
        $usuario = AuthMiddleware::currentUser();
        $tickets = Ticket::getByUsuario($usuario['id']);
        include VIEW_PATH . '/usuario/panel.php';
    }

    public function crearTicketForm(): void {
        AuthMiddleware::requireLogin();
        $usuario = AuthMiddleware::currentUser();
        $categorias = Categoria::getActivas();
        $ubicaciones = Ubicacion::getActivas();
        include VIEW_PATH . '/usuario/crear-ticket.php';
    }

    public function crearTicket(): void {
        AuthMiddleware::requireLogin();
        AuthMiddleware::verifyCsrf();
        $usuario = AuthMiddleware::currentUser();

        try {
            $evidencia = null;
            if (!empty($_FILES['archivoEvidencia']['tmp_name'])) {
                $evidencia = FileService::guardar($_FILES['archivoEvidencia']);
            }

            $ticketId = TicketService::crear([
                'titulo'             => trim($_POST['titulo'] ?? ''),
                'descripcion'        => trim($_POST['descripcion'] ?? ''),
                'prioridad'          => $_POST['prioridad'] ?? 'MEDIA',
                'categoria_id'       => !empty($_POST['categoriaId']) ? (int) $_POST['categoriaId'] : null,
                'ubicacion_id'       => !empty($_POST['ubicacionId']) ? (int) $_POST['ubicacionId'] : null,
                'evidencia_problema' => $evidencia,
            ], $usuario);

            header('Location: /usuario/mis-tickets?success=created');
            exit;
        } catch (Exception $e) {
            $error = 'Error al crear el ticket: ' . $e->getMessage();
            $categorias = Categoria::getActivas();
            $ubicaciones = Ubicacion::getActivas();
            include VIEW_PATH . '/usuario/crear-ticket.php';
        }
    }

    public function misTickets(): void {
        AuthMiddleware::requireLogin();
        $usuario = AuthMiddleware::currentUser();
        $tickets = Ticket::getByUsuario($usuario['id']);
        include VIEW_PATH . '/usuario/mis-tickets.php';
    }

    public function detalleTicket(string $id): void {
        AuthMiddleware::requireLogin();
        $usuario = AuthMiddleware::currentUser();
        $ticket = Ticket::findById((int) $id);

        if (!$ticket || $ticket['creado_por_id'] != $usuario['id']) {
            header('Location: /usuario/mis-tickets?error=notfound');
            exit;
        }

        $comentarios = Comentario::getByTicket((int) $id);
        $historial = HistorialAccion::getByTicket((int) $id);
        include VIEW_PATH . '/usuario/detalle-ticket.php';
    }

    public function comentar(string $id): void {
        AuthMiddleware::requireLogin();
        AuthMiddleware::verifyCsrf();
        $usuario = AuthMiddleware::currentUser();
        $contenido = trim($_POST['contenido'] ?? '');

        if (!empty($contenido)) {
            ComentarioService::agregar((int) $id, $usuario, $contenido);
        }
        header("Location: /usuario/ticket/$id");
        exit;
    }

    public function reabrir(string $id): void {
        AuthMiddleware::requireLogin();
        AuthMiddleware::verifyCsrf();
        $usuario = AuthMiddleware::currentUser();
        $ticket = Ticket::findById((int) $id);

        if ($ticket && $ticket['creado_por_id'] == $usuario['id']
            && in_array($ticket['estado'], ['RESUELTO', 'CERRADO', 'CANCELADO'])) {
            TicketService::reabrirTicket((int) $id, $usuario);
        }

        header("Location: /usuario/ticket/$id");
        exit;
    }
}
