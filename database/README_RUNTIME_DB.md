# Runtime DB Baseline (arranque mínimo operable)

Este paquete deja la base de datos lista para arrancar la aplicación en modo mínimo, sin crear usuarios manualmente desde consola SQL.

## Archivos y responsabilidades
- `database/schema.sql`: estructura principal (tablas, FK, índices base) y catálogos/SLA base idempotentes.
- `database/patches/20260221_runtime_baseline.sql`: ajustes runtime de indexación para entornos existentes (idempotente y sin depender del nombre del índice).
- `database/seed.sql`: usuarios mínimos operativos (ADMIN/TECNICO + demo opcional) y datos mínimos de operación.
- `database/verify_runtime_seed.sql`: validación post-seed (usuarios por rol, catálogos/SLA activos e índices esperados).

## Credenciales iniciales (solo desarrollo)
> Cambiar inmediatamente en producción.

- ADMIN
  - correo: `admin@sav12.local`
  - contraseña: `Admin123!`
- TECNICO
  - correo: `tecnico@sav12.local`
  - contraseña: `Tecnico123!`
- ALUMNO demo (opcional)
  - correo: `alumno.demo@sav12.local`
  - contraseña: `Alumno123!`

## Compatibilidad de contraseña
El sistema PHP usa `password_hash(..., PASSWORD_BCRYPT, ['cost' => 12])` y valida con `password_verify`, por lo tanto el seed usa hashes bcrypt (`$2y$...`).

## Orden de ejecución recomendado

### Opción recomendada (portátil): seleccionar BD con `-D`
```bash
mysql -u <user> -p -D <nombre_db> < database/schema.sql
mysql -u <user> -p -D <nombre_db> < database/patches/20260221_runtime_baseline.sql
mysql -u <user> -p -D <nombre_db> < database/seed.sql
mysql -u <user> -p -D <nombre_db> < database/verify_runtime_seed.sql
```

## Notas de idempotencia
- `schema.sql` usa `CREATE TABLE IF NOT EXISTS`.
- Catálogos/SLA usan `ON DUPLICATE KEY UPDATE` o `INSERT ... WHERE NOT EXISTS`.
- `seed.sql` usa `ON DUPLICATE KEY UPDATE` para usuarios y catálogos.
- El patch usa `information_schema.statistics` + SQL dinámico para no duplicar índices.

## Qué valida `verify_runtime_seed.sql`
- Existencia de al menos un ADMIN activo.
- Existencia de al menos un TECNICO activo.
- Conteo de categorías, ubicaciones y SLA activos.
- Conteos por rol de usuario y por estado de tickets.
- Presencia de índices objetivo en `tickets`.
