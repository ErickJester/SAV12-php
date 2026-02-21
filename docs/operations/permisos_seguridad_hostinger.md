# Permisos y seguridad operativa (Hostinger)

## Rutas críticas
- `logs/` → escritura por PHP-FPM y cron.
- `public_html/uploads/` → escritura para adjuntos.
- `public_html/uploads/.htaccess` → conservar para hardening.

## Permisos recomendados
> Ajustar usuario/grupo según plan Hostinger.

```bash
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod -R 775 logs public_html/uploads
```

Si tienes shell con ownership administrable:
```bash
chown -R <usuario_hostinger>:<grupo_hostinger> logs public_html/uploads
```

## Validaciones rápidas
1. Revisar permisos:
   ```bash
   ls -ld logs public_html/uploads
   ls -l public_html/uploads/.htaccess
   ```
2. Verificar escritura con script operativo:
   ```bash
   php scripts/check_paths.php
   ```
3. Confirmar que uploads no ejecuta PHP:
   - revisar reglas de `public_html/uploads/.htaccess`
   - intentar cargar un archivo `.php` de prueba y confirmar que no se ejecuta.

## Qué NO hacer
- No usar `chmod -R 777` indiscriminado.
- No exponer `.env` en web root.
- No registrar contraseñas SMTP/DB en logs.
- No eliminar `.htaccess` de `uploads/`.
