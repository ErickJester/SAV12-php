# Plan de ejecución QA / regresión (Rama 7)

## Prerrequisitos
- `.env` configurado para el entorno bajo prueba.
- Base de datos disponible y con schema aplicado.
- (Opcional) fixture QA cargado desde `tests/fixtures/qa_dataset.sql`.
- Acceso CLI para ejecutar scripts en `scripts/`.
- Si se desea smoke HTTP real: `APP_URL` válido en `.env` o `QA_BASE_URL` exportado.

## Orden sugerido
1. **Smoke automático**
   ```bash
   php scripts/qa_run_all.php
   ```
2. **Checklist manual de regresión**
   - Ejecutar `docs/qa/checklist_regresion.md`.
3. **Verificación de exportes**
   - Confirmar descargas CSV/PDF con sesión admin activa.
4. **Cron**
   - Revisar resultado de `qa_smoke_cron.php` y logs de cron reales.

## Resultados y evidencias
- JSON por suite:
  - `docs/qa/artifacts/smoke_http.json`
  - `docs/qa/artifacts/smoke_cron.json`
  - `docs/qa/artifacts/smoke_assets.json`
- Resumen consolidado:
  - `docs/qa/artifacts/smoke_summary.json`
  - `docs/qa/artifacts/smoke_summary.md`
- Reporte manual:
  - `docs/qa/reporte_smoke.md`
  - `docs/qa/hallazgos_qa.md`

## Interpretación de estado
- **PASS**: validación completada y comportamiento esperado.
- **FAIL**: comportamiento incorrecto atribuible al sistema bajo prueba.
- **BLOCKED**: no concluyente por ambiente (sin DB, APP_URL no resolvible, SMTP no disponible, etc.).
