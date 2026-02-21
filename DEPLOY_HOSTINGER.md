# Deploy y rollback en Hostinger (SAV12)

## 1) Prerrequisitos
- Hosting Hostinger con acceso a **hPanel**, SSH y Cron Jobs.
- PHP 8.1+ con extensiones `pdo_mysql`, `mbstring`, `json`, `openssl`.
- Base de datos MySQL creada y credenciales activas.
- Acceso a carpeta del proyecto (ejemplo: `/home/u123456789/domains/tu-dominio.com/sav12`).
- Acceso para editar `.env` y configurar tareas cron.

## 2) Estructura de despliegue
- Código de aplicación: carpeta raíz del proyecto (`sav12/`).
- Web root: `public_html/` del proyecto (o subcarpeta equivalente si se publica bajo ruta).
- `APP_URL` debe reflejar URL final (ej. `https://tu-dominio.com/sav12`).

## 3) Configuración inicial
```bash
cp .env.example .env
```
Configurar en `.env`:
- `APP_ENV=production`
- `APP_URL`, `APP_SECRET`
- `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`
- `MAIL_ENABLED`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM`, `MAIL_FROM_NAME`, `MAIL_SECURE`

Validación rápida:
```bash
php scripts/check_env.php
php scripts/check_paths.php
```

## 4) Base de datos (orden)
Ejecutar en orden:
```bash
mysql -u <user> -p -D <db_name> < database/schema.sql
mysql -u <user> -p -D <db_name> < database/seed.sql
mysql -u <user> -p -D <db_name> < database/verify_runtime_seed.sql
```
Si existe carpeta `database/patches/`, aplicar patches antes de `seed.sql`.

## 5) Permisos
- `logs/` debe ser escribible por PHP/cron.
- `public_html/uploads/` debe ser escribible y conservar su `.htaccess`.
- Referencia detallada: `docs/operations/permisos_seguridad_hostinger.md`.

## 6) Cron setup
Referencia detallada y comandos exactos:
- `docs/operations/cron_hostinger.md`

Prueba manual inicial:
```bash
php cron/check_sla_breaches.php
php cron/auto_escalate.php
php cron/send_reminders.php
```

## 7) Smoke checks post-deploy
1. Login exitoso (admin/técnico).
2. Crear ticket y verificar listado.
3. Revisar reportes.
4. Probar exportaciones CSV/PDF.
5. Subir adjunto y verificar acceso.
6. Ejecutar SMTP test:
   ```bash
   php scripts/test_smtp.php --to=destino@dominio.com
   ```

## 8) Rollback

### 8.1 Código
- Conservar release previo (`sav12_prev/`) o usar etiqueta Git.
- Restaurar carpeta previa y verificar que `public_html` apunte al release estable.

### 8.2 `.env`
- Restaurar respaldo del `.env` anterior.
- Revalidar:
  ```bash
  php scripts/check_env.php
  ```

### 8.3 Cron
- Revertir comandos cron a los del release estable.
- Ejecutar una corrida manual por job para verificar.

### 8.4 Base de datos
- Si se aplicó patch/migración, restaurar backup de DB previo al deploy.
- Si no hubo cambios de DB, no aplicar rollback de datos.

### 8.5 Checklist post-rollback
- Login funcional.
- Alta de ticket funcional.
- Logs sin errores críticos.
- Cron ejecuta con `exit 0`.
