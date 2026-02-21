# Checklist de regresión QA (manual)

> Completar columna **Resultado real** en cada ejecución.

## 1) Login por rol

| ID | Precondición | Acción | Resultado esperado | Resultado real |
|---|---|---|---|---|
| REG-LOGIN-01 | Usuario admin activo | Ir a `/login` e iniciar sesión con credenciales válidas admin | Redirección al panel admin, sesión activa | |
| REG-LOGIN-02 | Usuario técnico activo | Iniciar sesión con técnico | Redirección al panel técnico, sesión activa | |
| REG-LOGIN-03 | Usuario final activo (si aplica) | Iniciar sesión con usuario | Redirección al panel de usuario, sesión activa | |
| REG-LOGIN-04 | Credenciales inválidas | Iniciar sesión con password incorrecto | Error controlado, sin iniciar sesión | |
| REG-LOGIN-05 | Sesión activa | Ejecutar logout | Sesión destruida y redirección a `/login` | |

## 2) Tickets

| ID | Precondición | Acción | Resultado esperado | Resultado real |
|---|---|---|---|---|
| REG-TICKET-01 | Usuario autenticado | Crear ticket con datos válidos | Ticket creado y visible en listado | |
| REG-TICKET-02 | Ticket existente | Abrir detalle del ticket | Detalle carga sin errores | |
| REG-TICKET-03 | Ticket existente | Agregar comentario | Comentario persistido y visible | |
| REG-TICKET-04 | Ticket asignado | Cambiar estado (ej. EN_PROCESO/EN_ESPERA) | Estado actualizado y reflejado en UI | |
| REG-TICKET-05 | Admin autenticado + técnico existente | Asignar técnico a ticket | Asignación persistida | |
| REG-TICKET-06 | Ticket en atención | Resolver/cerrar según flujo | Estado final correcto (RESUELTO/CERRADO) | |

## 3) SLA / tiempos

| ID | Precondición | Acción | Resultado esperado | Resultado real |
|---|---|---|---|---|
| REG-SLA-01 | Datos de tickets con tiempos variados | Abrir reportes y validar métricas SLA | Métricas renderizan sin error | |
| REG-SLA-02 | Entorno sin datos (o filtros vacíos) | Abrir reportes | Página carga sin errores fatales | |

## 4) Reportes

| ID | Precondición | Acción | Resultado esperado | Resultado real |
|---|---|---|---|---|
| REG-REP-01 | Admin autenticado | Abrir `/admin/reportes` | Carga de vista completa sin error | |
| REG-REP-02 | Reportes con datos | Aplicar filtro de periodo/fechas | KPIs y secciones se actualizan | |
| REG-REP-03 | Reportes | Verificar secciones KPI/SLA/tiempos/técnicos/prioridad/ubicaciones/alertas/problemáticos | Todas las secciones renderizan | |

## 5) Exportes

| ID | Precondición | Acción | Resultado esperado | Resultado real |
|---|---|---|---|---|
| REG-EXP-01 | Admin autenticado | Descargar CSV desde reportes | Descarga exitosa, headers correctos, archivo no vacío | |
| REG-EXP-02 | Admin autenticado | Descargar PDF desde reportes | Descarga exitosa, headers correctos, archivo no vacío | |

## 6) Adjuntos

| ID | Precondición | Acción | Resultado esperado | Resultado real |
|---|---|---|---|---|
| REG-ADJ-01 | Ticket editable | Subir adjunto permitido (ej. PDF/JPG) | Carga exitosa, adjunto visible | |
| REG-ADJ-02 | Ticket editable | Intentar adjunto tipo/tamaño inválido | Rechazo controlado con mensaje | |
| REG-ADJ-03 | Adjunto cargado | Descargar/visualizar adjunto | Archivo accesible según permisos | |
| REG-ADJ-04 | Uploads con hardening activo | Intentar ejecutar archivo PHP en uploads | No ejecución (bloqueado por servidor) | |

## 7) Cron jobs

| ID | Precondición | Acción | Resultado esperado | Resultado real |
|---|---|---|---|---|
| REG-CRON-01 | CLI con rutas correctas | `php cron/check_sla_breaches.php` | Sin fatal error, exit code consistente, log útil | |
| REG-CRON-02 | CLI con rutas correctas | `php cron/auto_escalate.php` | Sin fatal error, exit code consistente, log útil | |
| REG-CRON-03 | CLI con rutas correctas + SMTP habilitado | `php cron/send_reminders.php` | Sin fatal error, exit code consistente, log útil | |

## 8) Subcarpeta / base_url

| ID | Precondición | Acción | Resultado esperado | Resultado real |
|---|---|---|---|---|
| REG-BASEURL-01 | `APP_URL` apuntando a subcarpeta (Hostinger style) | Navegar login → panel → reportes | Links/form actions mantienen subruta correcta | |
| REG-BASEURL-02 | Misma configuración | Ejecutar acciones críticas (login, ticket, exportes) | No redirecciones rotas ni 404 por ruta base | |
