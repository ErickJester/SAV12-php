# 09 - Gaps Iniciales Detectados (Baseline)

Priorización basada en análisis estático y disponibilidad de fuentes.

## 1) Gaps funcionales
1. **Comparativa Java endpoint-by-endpoint no ejecutable** — Estado: `FALTANTE` (fuente Java no disponible en entorno).
2. **Paridad de exportes CSV/PDF** — Estado: `NO_CONFIRMADO_POR_ANALISIS_ESTATICO`.
3. **Paridad de reglas SLA entre stacks** — Estado: `NO_CONFIRMADO_POR_ANALISIS_ESTATICO`.

## 2) Gaps de UI
1. **Mapeo de vistas Thymeleaf vs PHP no contrastado** — Estado: `FALTANTE`.
2. **Consistencia visual/flujo por rol entre Java/PHP** — Estado: `NO_CONFIRMADO_POR_ANALISIS_ESTATICO`.

## 3) Gaps operativos
1. **Jobs/cron Java no inventariados** — Estado: `FALTANTE`.
2. **Seeds/migraciones Java no inventariadas** — Estado: `FALTANTE`.
3. **Variables de entorno comparadas parcialmente (solo PHP disponible)** — Estado: `PARCIAL`.

## 4) Gaps seguridad/despliegue
1. **Reglas `SecurityConfig` Java no verificadas** — Estado: `FALTANTE`.
2. **Paridad de autorizaciones granulares (`@PreAuthorize` vs middleware PHP)** — Estado: `NO_CONFIRMADO_POR_ANALISIS_ESTATICO`.
3. **Endurecimiento de despliegue y permisos cross-stack** — Estado: `NO_CONFIRMADO_POR_ANALISIS_ESTATICO`.

## Recomendación inmediata
- Proveer acceso al ZIP o repo Java para regenerar artifacts Java y pasar de `NO_CONFIRMADO_POR_ANALISIS_ESTATICO` a estados concretos (`EQUIVALENTE`, `PARCIAL`, `FALTANTE`, `SOLO_PHP`, `SOLO_JAVA`).
