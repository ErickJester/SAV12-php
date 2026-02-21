# Reporte de hallazgos QA

## Estado general de la corrida actual
- Sin hallazgos bloqueantes confirmados en código durante esta corrida.
- Se observaron limitaciones de ambiente para ejecución completa (ver artefactos de smoke).

## Hallazgos

| ID | Severidad | Módulo/Capa | Descripción | Pasos de reproducción | Resultado esperado | Resultado real | Evidencia | Estado |
|---|---|---|---|---|---|---|---|---|
| QA-OBS-001 | Medio | ops | Entorno de ejecución local puede bloquear cron por configuración de `APP_SECRET`/`.env` en modo producción sin secreto seguro. | 1) Ejecutar `php cron/check_sla_breaches.php` en entorno sin `.env` completo 2) Revisar salida CLI | Cron ejecuta con exit `0/1` por lógica de job | Job se bloquea por hardening de bootstrap al faltar secreto seguro | `docs/qa/artifacts/smoke_cron.json` | Bloqueado |

## Plantilla para nuevos hallazgos
Usar el mismo formato de tabla para registrar bugs reales sin corregirlos en esta rama.
