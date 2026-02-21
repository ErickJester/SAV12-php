# Reporte de corrida smoke QA

- Fecha/hora corrida: 2026-02-21T21:49:48+00:00
- Comando ejecutado: `php scripts/qa_run_all.php`
- Resumen: PASS=4, FAIL=0, BLOCKED=4

## Detalle
| Prueba | Estado | Evidencia breve |
|---|---|---|
| http_base_url | BLOCKED | No existe `QA_BASE_URL` ni `APP_URL` en `.env` del entorno actual |
| cron_check_sla_breaches | BLOCKED | Bloqueado por hardening `APP_SECRET` en producción |
| cron_auto_escalate | BLOCKED | Bloqueado por hardening `APP_SECRET` en producción |
| cron_send_reminders | BLOCKED | Bloqueado por hardening `APP_SECRET` en producción |
| asset_reports_js_exists | PASS | Existe `public_html/assets/js/reports.js` |
| view_reportes_ref_base_url_admin_reportes | PASS | Referencia `base_url('admin/reportes')` encontrada |
| view_reportes_ref_export | PASS | Referencia `base_url('admin/reportes/export/')` encontrada |
| view_reportes_ref_window_reportes_data | PASS | Referencia `window.__reportesData` encontrada |

## Evidencia completa
- `docs/qa/artifacts/smoke_http.json`
- `docs/qa/artifacts/smoke_cron.json`
- `docs/qa/artifacts/smoke_assets.json`
- `docs/qa/artifacts/smoke_summary.json`
- `docs/qa/artifacts/smoke_summary.md`
