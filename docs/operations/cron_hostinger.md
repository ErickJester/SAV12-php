# Cron en Hostinger para SAV12

## Pre-checks Hostinger
1. Identificar binario PHP:
   ```bash
   which php
   php -v
   ```
   En algunos planes puede ser `/usr/bin/php` o ruta versionada.
2. Identificar path absoluto del proyecto:
   ```bash
   pwd
   ```
3. Verificar timezone de PHP y sistema (recomendado `America/Mexico_City`).

## Recomendaciones generales
- Ejecutar cron siempre con rutas absolutas.
- Redirigir salida y errores a archivo en `logs/`.
- Confirmar permisos de escritura en `logs/`.

## Inventario de jobs

| Script | Propósito | Frecuencia sugerida | Comando Hostinger | Log sugerido | Exit esperado |
|---|---|---|---|---|---|
| `cron/check_sla_breaches.php` | Detectar tickets que exceden SLA de primera respuesta y registrar alertas. | Cada 5 min | `/usr/bin/php /home/USER/domains/DOMINIO/sav12/cron/check_sla_breaches.php >> /home/USER/domains/DOMINIO/sav12/logs/cron_sla.log 2>&1` | `logs/cron_sla.log` | `0` OK, `1` error |
| `cron/auto_escalate.php` | Escalar prioridad de tickets sin asignar > 60 min, evitando doble escalado en prioridades altas. | Cada 15 min | `/usr/bin/php /home/USER/domains/DOMINIO/sav12/cron/auto_escalate.php >> /home/USER/domains/DOMINIO/sav12/logs/cron_escalate.log 2>&1` | `logs/cron_escalate.log` | `0` OK, `1` error |
| `cron/send_reminders.php` | Enviar recordatorios por correo a responsables de tickets sin actividad > 4 horas. | Cada hora | `/usr/bin/php /home/USER/domains/DOMINIO/sav12/cron/send_reminders.php >> /home/USER/domains/DOMINIO/sav12/logs/cron_reminders.log 2>&1` | `logs/cron_reminders.log` | `0` OK, `1` error |

## Prueba manual previa a cron
```bash
php cron/check_sla_breaches.php
php cron/auto_escalate.php
php cron/send_reminders.php
```

## Prerrequisitos por job
- `.env` configurado con DB y SMTP.
- Conexión DB activa.
- `MAIL_ENABLED=true` para envío real en `send_reminders.php`.
- `logs/` escribible para redirecciones.

## Diagnóstico rápido
- Ver últimas líneas de logs:
  ```bash
  tail -n 50 logs/cron_sla.log
  tail -n 50 logs/cron_escalate.log
  tail -n 50 logs/cron_reminders.log
  ```
- Revalidar entorno y rutas:
  ```bash
  php scripts/check_env.php
  php scripts/check_paths.php
  ```
