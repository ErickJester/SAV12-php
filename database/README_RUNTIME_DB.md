# Runtime DB Baseline (arranque mínimo operable)

Este paquete deja la base de datos lista para arrancar la aplicación en modo mínimo, sin crear usuarios manualmente desde consola SQL.

## Archivos
- `database/schema.sql`: esquema + catálogos/SLA base idempotentes.
- `database/patches/20260221_runtime_baseline.sql`: agrega índices de soporte en `tickets` en entornos existentes (idempotente).
- `database/seed.sql`: usuarios operativos mínimos + datos base idempotentes.
- `database/verify_runtime_seed.sql`: checks de verificación post-seed.

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
```bash
mysql -u <user> -p < database/schema.sql
mysql -u <user> -p < database/patches/20260221_runtime_baseline.sql
mysql -u <user> -p < database/seed.sql
mysql -u <user> -p < database/verify_runtime_seed.sql
```

## Notas de idempotencia
- `schema.sql` usa `CREATE TABLE IF NOT EXISTS`.
- Catálogos/SLA usan `ON DUPLICATE KEY UPDATE` o `INSERT ... WHERE NOT EXISTS`.
- `seed.sql` usa `ON DUPLICATE KEY UPDATE` para usuarios y catálogos.
- El patch usa `information_schema.statistics` + SQL dinámico para no duplicar índices.
