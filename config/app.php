<?php
/**
 * Configuración principal de SAV12
 * Carga variables de entorno y define utilidades globales.
 */

// Cargar .env
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // remover comillas envolventes
        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

function env(string $key, $default = null) {
    if (array_key_exists($key, $_ENV)) {
        return $_ENV[$key];
    }
    $value = getenv($key);
    return $value === false ? $default : $value;
}

function str_to_bool($value): bool {
    return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
}

function normalize_base_url(string $url): string {
    $url = trim($url);
    if ($url === '') {
        return 'http://localhost';
    }
    return rtrim($url, '/');
}

function base_url(string $path = ''): string {
    $base = defined('APP_URL') ? APP_URL : normalize_base_url((string) env('APP_URL', 'http://localhost'));
    $path = trim($path);
    if ($path === '') {
        return $base;
    }
    return $base . '/' . ltrim($path, '/');
}

// Config consolidada
$__appConfig = [
    'name' => env('APP_NAME', 'SAV12'),
    'env' => env('APP_ENV', 'production'),
    'debug' => str_to_bool(env('APP_DEBUG', 'false')),
    'url' => normalize_base_url((string) env('APP_URL', 'http://localhost')),
    'secret' => (string) env('APP_SECRET', ''),
    'runtime' => [
        'uploads_dir' => (string) env('UPLOADS_DIR', env('UPLOAD_DIR', 'uploads')),
        'log_dir' => (string) env('LOG_DIR', 'logs'),
        'max_upload_size' => (int) env('MAX_UPLOAD_SIZE', env('UPLOAD_MAX_SIZE', 5242880)),
        'allowed_upload_extensions' => (string) env('ALLOWED_UPLOAD_EXTENSIONS', 'jpg,jpeg,png,gif,webp,pdf'),
        'allowed_upload_mime' => (string) env('ALLOWED_UPLOAD_MIME', 'image/jpeg,image/png,image/gif,image/webp,application/pdf'),
    ],
];

function config(string $key, $default = null) {
    global $__appConfig;
    $segments = explode('.', $key);
    $current = $__appConfig;
    foreach ($segments as $segment) {
        if (!is_array($current) || !array_key_exists($segment, $current)) {
            return $default;
        }
        $current = $current[$segment];
    }
    return $current;
}

// Constantes de la app
define('APP_NAME', config('name', 'SAV12'));
define('APP_URL', config('url', 'http://localhost'));
define('APP_ENV', config('env', 'production'));
define('APP_DEBUG', (bool) config('debug', false));
define('APP_SECRET', (string) config('secret', ''));

// Directorio base
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('VIEW_PATH', BASE_PATH . '/views');
define('PUBLIC_PATH', BASE_PATH . '/public_html');
define('UPLOAD_PATH', PUBLIC_PATH . '/' . trim((string) config('runtime.uploads_dir', 'uploads'), '/'));
define('LOG_PATH', BASE_PATH . '/' . trim((string) config('runtime.log_dir', 'logs'), '/'));

// Hardening de secreto en producción
$unsafeSecrets = ['', 'default_secret_change_me', 'changeme', 'secret', 'default'];
if (APP_ENV === 'production' && in_array(strtolower(trim(APP_SECRET)), $unsafeSecrets, true)) {
    error_log('[BOOTSTRAP] APP_SECRET inseguro o vacío en producción.');
    http_response_code(500);
    die('Configuración insegura: APP_SECRET requerido en producción.');
}

// Crear directorios runtime esenciales
if (!is_dir(LOG_PATH)) {
    @mkdir(LOG_PATH, 0755, true);
}
if (!is_dir(UPLOAD_PATH)) {
    @mkdir(UPLOAD_PATH, 0755, true);
}

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// Sesión segura
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', APP_ENV === 'production' ? '1' : '0');
ini_set('session.use_strict_mode', '1');

// Errores
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', LOG_PATH . '/app.log');
}
