# Parity Audit Scripts (Java vs PHP)

## Requisitos
- Python 3.9+ (solo stdlib).

## Comandos
Desde la raíz del repo PHP:

```bash
python3 tools/parity_audit/extract_php_inventory.py
python3 tools/parity_audit/extract_java_inventory.py --java-root "/ruta/al/proyecto/java_o_zip_extraido"
python3 tools/parity_audit/compare_inventories.py
```

Si no se pasa `--java-root`, `extract_java_inventory.py` intenta rutas candidatas (`/mnt/data/SAV12-main (1).zip`, etc.).

## Salidas
Se generan en `docs/parity-baseline/artifacts/`:
- `php_endpoints_inventory.json`
- `java_endpoints_inventory.json`
- `php_views_inventory.json`
- `java_views_inventory.json`
- `php_schema_inventory.json`
- `java_schema_inventory.json`
- `parity_diff_summary.md` (comparación)

## Supuestos y limitaciones
- Endpoints PHP: se extraen de `public_html/index.php` con patrón `$router->get/post/any(...)`.
- Endpoints Java: se infieren por anotaciones Spring (`@RequestMapping`, `@GetMapping`, `@PostMapping`, etc.) y firma `public` siguiente.
- Roles/seguridad: solo hint estático (`@PreAuthorize` o prefijo de ruta), no reemplaza pruebas dinámicas.
- SQL: parser simplificado de `CREATE TABLE`; constraints complejas pueden no mapearse al 100%.
- Si falta el código Java, los JSON Java se generan vacíos con nota `NO_CONFIRMADO_POR_ANALISIS_ESTATICO`.
