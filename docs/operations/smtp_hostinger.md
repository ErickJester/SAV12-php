# Prueba SMTP en Hostinger (SAV12)

## 1) Checklist de configuración
Validar `.env`:
- `MAIL_ENABLED=true`
- `MAIL_HOST` (ej. `smtp.hostinger.com`)
- `MAIL_PORT` (`465` para SSL, `587` para TLS)
- `MAIL_SECURE` (`ssl` o `tls`)
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_FROM`
- `MAIL_FROM_NAME`

Checklist operativo:
- `MAIL_FROM` coincide con cuenta/autorización SMTP del dominio.
- Usuario SMTP tiene contraseña vigente.
- DNS del dominio y buzón activos.

## 2) Prueba operativa reproducible
Ejecutar:
```bash
php scripts/test_smtp.php --to=destino@dominio.com
```

Éxito esperado:
- salida: `[OK] Correo de prueba enviado...`
- código de salida `0`
- correo visible en bandeja de destino (o spam).

Logs para revisar si falla:
- salida del comando en consola/cron
- log de aplicación (`logs/app.log` en producción)
- archivo de log cron si se ejecutó vía cron

## 3) Diagnóstico de fallos típicos
- **Credenciales inválidas**: revisar `MAIL_USERNAME`/`MAIL_PASSWORD`.
- **Puerto incorrecto/bloqueado**: probar combinación estándar (`465+ssl`, `587+tls`).
- **TLS/SSL no compatible**: ajustar `MAIL_SECURE` al valor correcto del proveedor.
- **From no permitido**: usar `MAIL_FROM` autorizado por Hostinger.
- **Timeout SMTP**: validar conectividad saliente y latencia.
- **Fallback activado**: si falla SMTP, `EmailService` puede intentar `mail()`; verificar políticas del servidor para `mail()`.

## 4) Validaciones complementarias
```bash
php scripts/check_env.php
php scripts/check_paths.php
```
