# 00 - Resumen Baseline de Paridad (Java vs PHP)

## Resumen ejecutivo
Se realizó una auditoría estática **sin cambios funcionales** al sistema PHP para congelar línea base de paridad contra Java antes de cambios de comportamiento. Se generaron inventarios reproducibles de endpoints, vistas y esquema, junto con checklist y gaps iniciales.

## Fecha/hora de generación
- 2026-02-21 19:02:01 UTC

## Rutas analizadas
- Repositorio PHP: `/workspace/SAV12-php`
- Java referencia esperado: `/mnt/data/SAV12-main (1).zip` (no encontrado en este entorno)
- ZIP PHP referencia esperado: `/mnt/data/sav12-php-hostinger.zip` (no encontrado en este entorno)
- Artifacts de auditoría: `docs/parity-baseline/artifacts/`

## Commit base
- `6bb9439`

## Resultado general
- Confirmado por análisis estático (PHP):
  - 38 endpoints registrados en `public_html/index.php`.
  - 18 vistas/layouts en `views/`.
  - 7 tablas de `database/schema.sql`.
- Java: inventarios en estado vacío con marca `NO_CONFIRMADO_POR_ANALISIS_ESTATICO` por ausencia de código fuente/ZIP Java en rutas candidatas.
- No se modificó lógica de negocio ni comportamiento de endpoints.

## NO_CONFIRMADO_POR_ANALISIS_ESTATICO
- Equivalencia endpoint a endpoint Java.
- Mapeo de vistas Thymeleaf Java.
- Comparativa de tablas/campos Java.
- Reglas exactas de seguridad Java (`SecurityConfig`, `@PreAuthorize`) al no contar con fuentes Java.
