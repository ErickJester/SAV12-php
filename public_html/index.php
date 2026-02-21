<?php
/**
 * SAV12 - Sistema de Atención y Verificación
 * Entry Point Principal
 * 
 * Convertido de Spring Boot (Java) a PHP para Hostinger
 */

// Cargar configuración
require_once dirname(__DIR__) . '/config/app.php';

// Cargar helpers
require_once APP_PATH . '/Helpers/Database.php';
require_once APP_PATH . '/Helpers/Session.php';
require_once APP_PATH . '/Helpers/Router.php';
require_once APP_PATH . '/Middleware/AuthMiddleware.php';

// Iniciar sesión
Session::start();

// Crear router
$router = new Router();

// =============================================
// RUTAS PÚBLICAS (sin autenticación)
// =============================================
$router->get('/',          'AuthController', 'loginForm');
$router->get('/login',     'AuthController', 'loginForm');
$router->post('/login',    'AuthController', 'login');
$router->get('/registro',  'AuthController', 'registroForm');
$router->post('/registro', 'AuthController', 'registro');
$router->get('/logout',    'AuthController', 'logout');
$router->get('/403',       'AuthController', 'forbidden');

// =============================================
// RUTAS DE USUARIO (ALUMNO, DOCENTE, ADMINISTRATIVO)
// =============================================
$router->get('/usuario/panel',           'UsuarioController', 'panel');
$router->get('/usuario/crear-ticket',    'UsuarioController', 'crearTicketForm');
$router->post('/usuario/crear-ticket',   'UsuarioController', 'crearTicket');
$router->get('/usuario/mis-tickets',     'UsuarioController', 'misTickets');
$router->get('/usuario/ticket/{id}',     'UsuarioController', 'detalleTicket');
$router->post('/usuario/ticket/{id}/comentar', 'UsuarioController', 'comentar');
$router->post('/usuario/ticket/{id}/reabrir',  'UsuarioController', 'reabrir');

// =============================================
// RUTAS DE TÉCNICO
// =============================================
$router->get('/tecnico/panel',           'TecnicoController', 'panel');
$router->get('/tecnico/mis-tickets',     'TecnicoController', 'misTickets');
$router->get('/tecnico/ticket/{id}',     'TecnicoController', 'detalleTicket');
$router->post('/tecnico/ticket/{id}/cambiar-estado', 'TecnicoController', 'cambiarEstado');
$router->post('/tecnico/ticket/{id}/comentar',       'TecnicoController', 'comentar');
$router->post('/tecnico/ticket/{id}/reabrir',        'TecnicoController', 'reabrir');
$router->post('/tecnico/ticket/{id}/asignar',        'TecnicoController', 'asignarme');

// =============================================
// RUTAS DE ADMINISTRADOR
// =============================================
$router->get('/admin/panel',     'AdministradorController', 'panel');
$router->get('/admin/usuarios',  'AdministradorController', 'usuarios');
$router->post('/admin/usuarios/{id}/cambiar-estado', 'AdministradorController', 'cambiarEstadoUsuario');
$router->post('/admin/usuarios/{id}/cambiar-rol',    'AdministradorController', 'cambiarRolUsuario');

$router->get('/admin/categorias',              'AdministradorController', 'categorias');
$router->post('/admin/categorias/crear',       'AdministradorController', 'crearCategoria');
$router->post('/admin/categorias/{id}/desactivar', 'AdministradorController', 'desactivarCategoria');

$router->get('/admin/ubicaciones',              'AdministradorController', 'ubicaciones');
$router->post('/admin/ubicaciones/crear',       'AdministradorController', 'crearUbicacion');
$router->post('/admin/ubicaciones/{id}/desactivar', 'AdministradorController', 'desactivarUbicacion');

$router->get('/admin/tickets',           'AdministradorController', 'tickets');
$router->get('/admin/ticket/{id}',       'AdministradorController', 'detalleTicket');
$router->post('/admin/ticket/{id}/comentar',        'AdministradorController', 'comentarTicket');
$router->post('/admin/tickets/{id}/asignar-tecnico', 'AdministradorController', 'asignarTecnico');
$router->post('/admin/tickets/{id}/asignarme',       'AdministradorController', 'asignarmeTicket');
$router->post('/admin/ticket/{id}/reabrir',          'AdministradorController', 'reabrirTicket');

$router->get('/admin/reportes', 'AdministradorController', 'reportes');
$router->get('/admin/reportes/export/csv', 'AdministradorController', 'exportarReportesCsv');
$router->get('/admin/reportes/export/pdf', 'AdministradorController', 'exportarReportesPdf');

// Ejecutar
$router->dispatch();
