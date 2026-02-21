# Datos de prueba controlados QA

## Objetivo
Definir datos reproducibles para regresión/smoke sin modificar seeds productivos.

## Fuente oficial QA
- Fixture SQL: `tests/fixtures/qa_dataset.sql`

## Cobertura del fixture
- Usuarios por rol:
  - `qa.admin@sav12.local` (ADMIN)
  - `qa.tecnico@sav12.local` (TECNICO)
  - `qa.usuario@sav12.local` (USUARIO)
- Tickets en estados variados:
  - ABIERTO
  - EN_PROCESO
  - EN_ESPERA
  - CERRADO
- Prioridades variadas:
  - BAJA / MEDIA / ALTA / URGENTE
- Caso para reportes/alertas:
  - Ticket `QA Ticket SLA Riesgo`
- Caso de adjuntos:
  - definido como caso de prueba manual en checklist (según estructura real de tabla de adjuntos del entorno)

## Ejecución sugerida
```bash
mysql -u <user> -p -D <db_name> < tests/fixtures/qa_dataset.sql
```

## Notas
- Este fixture es **solo QA**.
- No reemplaza `database/seed.sql`.
- Si hay diferencias de IDs FK (catálogo/SLA) ajustar localmente antes de ejecutar.
