# 01 - Checklist de Paridad Java vs PHP

| Módulo | Java (sí/no) | PHP (sí/no) | Estado | Evidencia | Notas |
|---|---|---|---|---|---|
| Auth / Roles / Seguridad | NO_CONFIRMADO_POR_ANALISIS_ESTATICO | sí | PARCIAL | `public_html/index.php`, `app/Middleware/AuthMiddleware.php` | Java no disponible en entorno; PHP sí exige login/rol por middleware. |
| Tickets (CRUD y flujo) | NO_CONFIRMADO_POR_ANALISIS_ESTATICO | sí | PARCIAL | `public_html/index.php`, `app/Controllers/*Controller.php` | Flujo visible en endpoints y controladores PHP. |
| Comentarios / Historial | NO_CONFIRMADO_POR_ANALISIS_ESTATICO | sí | PARCIAL | `database/schema.sql`, `app/Controllers/*Controller.php` | Tablas `comentarios`, `historial_acciones` presentes. |
| SLA / tiempos | NO_CONFIRMADO_POR_ANALISIS_ESTATICO | sí | PARCIAL | `database/schema.sql`, `config/sla.php` | Campos SLA presentes en `tickets` y tabla `sla_politicas`. |
| Catálogos (categorías, ubicaciones) | NO_CONFIRMADO_POR_ANALISIS_ESTATICO | sí | PARCIAL | `public_html/index.php`, `database/schema.sql` | CRUD básico detectado en admin. |
| Reportes | NO_CONFIRMADO_POR_ANALISIS_ESTATICO | sí | PARCIAL | `/admin/reportes`, `views/admin/reportes.php` | No posible validar paridad funcional con Java. |
| Exportes | NO_CONFIRMADO_POR_ANALISIS_ESTATICO | NO_CONFIRMADO_POR_ANALISIS_ESTATICO | NO_CONFIRMADO_POR_ANALISIS_ESTATICO | N/A | Requiere inspección Java y prueba manual. |
| Archivos / evidencias | NO_CONFIRMADO_POR_ANALISIS_ESTATICO | sí | PARCIAL | `app/Services/FileService.php`, campos `evidencia_*` | Evidencia en tickets detectada en PHP. |
| Correo | NO_CONFIRMADO_POR_ANALISIS_ESTATICO | sí | PARCIAL | `app/Services/EmailService.php` | Sin referencia Java para contraste. |
| Cron / jobs operativos | NO_CONFIRMADO_POR_ANALISIS_ESTATICO | sí | PARCIAL | `cron/` | Sin fuentes Java para comparativa. |
| Configuración / despliegue | NO_CONFIRMADO_POR_ANALISIS_ESTATICO | sí | PARCIAL | `config/`, `public_html/` | Falta contexto Java/infra para cierre de paridad. |

> Criterio aplicado: al no disponer del código Java en este entorno, el estado se mantiene conservador como `PARCIAL` o `NO_CONFIRMADO_POR_ANALISIS_ESTATICO`.
