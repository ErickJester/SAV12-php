<?php
/**
 * Configuración de correo SMTP
 */

return [
    'enabled'    => str_to_bool(env('MAIL_ENABLED', 'false')),
    'host'       => env('MAIL_HOST', 'smtp.hostinger.com'),
    'port'       => (int) env('MAIL_PORT', 465),
    'username'   => env('MAIL_USERNAME', ''),
    'password'   => env('MAIL_PASSWORD', ''),
    'from'       => env('MAIL_FROM', 'soporte@tudominio.com'),
    'from_name'  => env('MAIL_FROM_NAME', 'SAV12'),
    'encryption' => env('MAIL_SECURE', env('MAIL_ENCRYPTION', 'ssl')),
];
