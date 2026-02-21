# 02 - Endpoints Actuales PHP

## Fuente principal de rutas
- `public_html/index.php`

## Observaciones de middleware/roles
- Rutas públicas/auth: `/`, `/login`, `/registro`, `/logout`, `/403`.
- Rutas de usuario: prefijo `/usuario` (requieren autenticación).
- Rutas de técnico: prefijo `/tecnico` (requireTecnico).
- Rutas de admin: prefijo `/admin` (requireAdmin).
- Nota: el inventario usa inferencia por prefijo de ruta y queda sujeto a confirmación dinámica.

## Inventario completo
| Método | Ruta | Controlador::acción | Rol/middleware (hint) | Fuente |
|---|---|---|---|---|
| GET | `/` | AuthController::loginForm | PUBLICO_O_AUTENTICADO | public_html/index.php:27 |
| GET | `/login` | AuthController::loginForm | PUBLICO_O_AUTENTICADO | public_html/index.php:28 |
| POST | `/login` | AuthController::login | PUBLICO_O_AUTENTICADO | public_html/index.php:29 |
| GET | `/registro` | AuthController::registroForm | PUBLICO_O_AUTENTICADO | public_html/index.php:30 |
| POST | `/registro` | AuthController::registro | PUBLICO_O_AUTENTICADO | public_html/index.php:31 |
| GET | `/logout` | AuthController::logout | PUBLICO_O_AUTENTICADO | public_html/index.php:32 |
| GET | `/403` | AuthController::forbidden | PUBLICO_O_AUTENTICADO | public_html/index.php:33 |
| GET | `/usuario/panel` | UsuarioController::panel | USUARIO_AUTENTICADO | public_html/index.php:38 |
| GET | `/usuario/crear-ticket` | UsuarioController::crearTicketForm | USUARIO_AUTENTICADO | public_html/index.php:39 |
| POST | `/usuario/crear-ticket` | UsuarioController::crearTicket | USUARIO_AUTENTICADO | public_html/index.php:40 |
| GET | `/usuario/mis-tickets` | UsuarioController::misTickets | USUARIO_AUTENTICADO | public_html/index.php:41 |
| GET | `/usuario/ticket/{id}` | UsuarioController::detalleTicket | USUARIO_AUTENTICADO | public_html/index.php:42 |
| POST | `/usuario/ticket/{id}/comentar` | UsuarioController::comentar | USUARIO_AUTENTICADO | public_html/index.php:43 |
| POST | `/usuario/ticket/{id}/reabrir` | UsuarioController::reabrir | USUARIO_AUTENTICADO | public_html/index.php:44 |
| GET | `/tecnico/panel` | TecnicoController::panel | TECNICO | public_html/index.php:49 |
| GET | `/tecnico/mis-tickets` | TecnicoController::misTickets | TECNICO | public_html/index.php:50 |
| GET | `/tecnico/ticket/{id}` | TecnicoController::detalleTicket | TECNICO | public_html/index.php:51 |
| POST | `/tecnico/ticket/{id}/cambiar-estado` | TecnicoController::cambiarEstado | TECNICO | public_html/index.php:52 |
| POST | `/tecnico/ticket/{id}/comentar` | TecnicoController::comentar | TECNICO | public_html/index.php:53 |
| POST | `/tecnico/ticket/{id}/reabrir` | TecnicoController::reabrir | TECNICO | public_html/index.php:54 |
| POST | `/tecnico/ticket/{id}/asignar` | TecnicoController::asignarme | TECNICO | public_html/index.php:55 |
| GET | `/admin/panel` | AdministradorController::panel | ADMIN | public_html/index.php:60 |
| GET | `/admin/usuarios` | AdministradorController::usuarios | ADMIN | public_html/index.php:61 |
| POST | `/admin/usuarios/{id}/cambiar-estado` | AdministradorController::cambiarEstadoUsuario | ADMIN | public_html/index.php:62 |
| POST | `/admin/usuarios/{id}/cambiar-rol` | AdministradorController::cambiarRolUsuario | ADMIN | public_html/index.php:63 |
| GET | `/admin/categorias` | AdministradorController::categorias | ADMIN | public_html/index.php:65 |
| POST | `/admin/categorias/crear` | AdministradorController::crearCategoria | ADMIN | public_html/index.php:66 |
| POST | `/admin/categorias/{id}/desactivar` | AdministradorController::desactivarCategoria | ADMIN | public_html/index.php:67 |
| GET | `/admin/ubicaciones` | AdministradorController::ubicaciones | ADMIN | public_html/index.php:69 |
| POST | `/admin/ubicaciones/crear` | AdministradorController::crearUbicacion | ADMIN | public_html/index.php:70 |
| POST | `/admin/ubicaciones/{id}/desactivar` | AdministradorController::desactivarUbicacion | ADMIN | public_html/index.php:71 |
| GET | `/admin/tickets` | AdministradorController::tickets | ADMIN | public_html/index.php:73 |
| GET | `/admin/ticket/{id}` | AdministradorController::detalleTicket | ADMIN | public_html/index.php:74 |
| POST | `/admin/ticket/{id}/comentar` | AdministradorController::comentarTicket | ADMIN | public_html/index.php:75 |
| POST | `/admin/tickets/{id}/asignar-tecnico` | AdministradorController::asignarTecnico | ADMIN | public_html/index.php:76 |
| POST | `/admin/tickets/{id}/asignarme` | AdministradorController::asignarmeTicket | ADMIN | public_html/index.php:77 |
| POST | `/admin/ticket/{id}/reabrir` | AdministradorController::reabrirTicket | ADMIN | public_html/index.php:78 |
| GET | `/admin/reportes` | AdministradorController::reportes | ADMIN | public_html/index.php:80 |
