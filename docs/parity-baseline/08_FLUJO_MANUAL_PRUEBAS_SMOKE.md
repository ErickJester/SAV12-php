# 08 - Flujo Manual de Pruebas Smoke

> Estado de ejecución en esta baseline: **pendiente de ejecución manual** (solo análisis estático).

| ID | Rol | Precondiciones | Pasos | Resultado esperado | Evidencia sugerida | Estado |
|---|---|---|---|---|---|---|
| SMOKE-01 | Usuario | Usuario activo existente | 1) Ir a `/login` 2) Autenticar 3) validar redirección | Login exitoso y acceso a panel por rol | Captura panel + cookie/sesión | pendiente |
| SMOKE-02 | Usuario | Login activo + categorías/ubicaciones existentes | 1) Ir a `/usuario/crear-ticket` 2) llenar formulario 3) enviar | Ticket creado y visible en `/usuario/mis-tickets` | Captura ticket creado + ID | pendiente |
| SMOKE-03 | Admin | Admin autenticado + ticket abierto | 1) Ir a `/admin/tickets` 2) asignar técnico | Ticket asignado y registro en historial | Captura detalle ticket + historial | pendiente |
| SMOKE-04 | Técnico | Técnico autenticado + ticket asignado | 1) Abrir `/tecnico/ticket/{id}` 2) cambiar estado (`EN_PROCESO`,`EN_ESPERA`,`RESUELTO`,`CERRADO`) | Estado actualizado y trazabilidad | Capturas por transición de estado | pendiente |
| SMOKE-05 | Usuario/Técnico/Admin | Ticket existente | 1) comentar desde detalle según rol | Comentario persistido y visible | Captura comentario + timestamp | pendiente |
| SMOKE-06 | Usuario/Técnico/Admin | Ticket en `RESUELTO/CERRADO/CANCELADO` | 1) ejecutar acción reabrir | Ticket pasa a `REABIERTO` y aumenta contador | Captura antes/después estado | pendiente |
| SMOKE-07 | Admin | Admin autenticado + datos de tickets | 1) abrir `/admin/reportes` 2) revisar métricas | Reportes cargan sin error | Captura dashboard/reportes | pendiente |
| SMOKE-08 | Admin | Funcionalidad de exportes disponible (si aplica) | 1) ejecutar exportación CSV/PDF | Archivo generado/descargado | Archivo exportado + captura | pendiente |

## Notas
- Si algún caso falla, registrar error exacto, rol, endpoint y payload.
- Para paridad Java vs PHP, repetir los mismos casos en referencia Java y comparar resultado por resultado.
