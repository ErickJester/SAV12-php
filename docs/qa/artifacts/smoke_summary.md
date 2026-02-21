# Reporte Smoke QA

Generado: 2026-02-21T21:49:48+00:00

## Resumen
- PASS: 4
- FAIL: 0
- BLOCKED: 4

## Resultados

| Prueba | Estado | Evidencia | Timestamp |
|---|---|---|---|
| http_base_url | BLOCKED | No existe QA_BASE_URL ni APP_URL configurado en .env | 2026-02-21T21:49:47+00:00 |
| cron_check_sla_breaches | BLOCKED | Bloqueo ambiental por APP_SECRET/entorno; exit=0; duration=0.082s; output=[BOOTSTRAP] APP_SECRET inseguro o vacío en producción.
Configuración insegura: APP_SECRET requerido en producción. | 2026-02-21T21:49:47+00:00 |
| cron_auto_escalate | BLOCKED | Bloqueo ambiental por APP_SECRET/entorno; exit=0; duration=0.078s; output=[BOOTSTRAP] APP_SECRET inseguro o vacío en producción.
Configuración insegura: APP_SECRET requerido en producción. | 2026-02-21T21:49:47+00:00 |
| cron_send_reminders | BLOCKED | Bloqueo ambiental por APP_SECRET/entorno; exit=0; duration=0.065s; output=[BOOTSTRAP] APP_SECRET inseguro o vacío en producción.
Configuración insegura: APP_SECRET requerido en producción. | 2026-02-21T21:49:48+00:00 |
| asset_reports_js_exists | PASS | Existe public_html/assets/js/reports.js | 2026-02-21T21:49:48+00:00 |
| view_reportes_ref_2ff5fc96484d0b5890248252fe00d933 | PASS | Referencia encontrada: base_url('admin/reportes') | 2026-02-21T21:49:48+00:00 |
| view_reportes_ref_a7f8b16a6f087c67f2de22f5717645b4 | PASS | Referencia encontrada: base_url('admin/reportes/export/' | 2026-02-21T21:49:48+00:00 |
| view_reportes_ref_4e14533f809f04f11a5183b31746cd43 | PASS | Referencia encontrada: window.__reportesData | 2026-02-21T21:49:48+00:00 |
