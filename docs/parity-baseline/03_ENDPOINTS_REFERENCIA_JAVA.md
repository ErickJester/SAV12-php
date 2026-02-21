# 03 - Endpoints Referencia Java

## Estado de extracción
- Resultado: `NO_CONFIRMADO_POR_ANALISIS_ESTATICO`.
- Motivo: no se encontró el proyecto Java ni ZIP en rutas candidatas de este entorno.
- Rutas intentadas automáticamente por script:
  - `/mnt/data/SAV12-main (1).zip`
  - `/mnt/data/SAV12-main`
  - `/workspace/SAV12-main`
  - `/workspace/SAV12-main (1)`

## Tabla de endpoints Java extraídos
Sin resultados (inventario vacío).

| HTTP | class_level_mapping | method_mapping | resolved_path | controller::action | security_hint | Estado |
|---|---|---|---|---|---|---|
| UNKNOWN | N/A | N/A | N/A | N/A | N/A | NO_CONFIRMADO_POR_ANALISIS_ESTATICO |

## Anotaciones Spring esperadas (objetivo de parser)
- `@RequestMapping`
- `@GetMapping`
- `@PostMapping`
- `@PutMapping`
- `@DeleteMapping`
- `@PatchMapping`
- `@PreAuthorize` (hint de seguridad)

## Observaciones sobre roles
- Sin `SecurityConfig` ni controladores Java disponibles no es posible confirmar roles efectivos.
- Estado: `NO_CONFIRMADO_POR_ANALISIS_ESTATICO`.
