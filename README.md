# SAV12 PHP

## Operación en Hostinger
Documentación operativa de esta rama:
- Deploy y rollback: `DEPLOY_HOSTINGER.md`
- Cron jobs: `docs/operations/cron_hostinger.md`
- SMTP en producción: `docs/operations/smtp_hostinger.md`
- Permisos y seguridad operativa: `docs/operations/permisos_seguridad_hostinger.md`

## Scripts operativos
- `php scripts/check_env.php`
- `php scripts/check_paths.php`
- `php scripts/test_smtp.php --to=destino@dominio.com`

## QA / Regresión
- Plan de ejecución QA: `docs/qa/plan_ejecucion_qa.md`
- Checklist manual: `docs/qa/checklist_regresion.md`
- Datos de prueba QA: `docs/qa/datos_prueba.md` y `tests/fixtures/qa_dataset.sql`
- Hallazgos QA: `docs/qa/hallazgos_qa.md`
- Ejecutar smoke automático:
  ```bash
  php scripts/qa_run_all.php
  ```
- Artefactos de salida: `docs/qa/artifacts/`
