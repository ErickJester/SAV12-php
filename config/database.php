<?php
/**
 * Configuración y conexión a la base de datos
 */

return [
    'host'     => env('DB_HOST', 'localhost'),
    'port'     => env('DB_PORT', '3306'),
    'name'     => env('DB_NAME', 'sav12_app'),
    'user'     => env('DB_USER', 'root'),
    'password' => env('DB_PASS', ''),
    'charset'  => env('DB_CHARSET', 'utf8mb4'),
];
