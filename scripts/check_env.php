<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este script solo se ejecuta por CLI.\n");
    exit(1);
}

$required = [
    'APP_ENV',
    'APP_URL',
    'APP_SECRET',
    'DB_HOST',
    'DB_PORT',
    'DB_NAME',
    'DB_USER',
    'DB_PASS',
    'MAIL_ENABLED',
    'MAIL_HOST',
    'MAIL_PORT',
    'MAIL_USERNAME',
    'MAIL_PASSWORD',
    'MAIL_FROM',
    'MAIL_FROM_NAME',
    'MAIL_SECURE',
];

$missing = [];
$warnings = [];

foreach ($required as $key) {
    $value = env($key, null);
    if ($value === null || trim((string) $value) === '') {
        $missing[] = $key;
        continue;
    }

    if (in_array($key, ['APP_SECRET', 'DB_PASS', 'MAIL_PASSWORD'], true)) {
        $display = '***';
    } else {
        $display = (string) $value;
    }

    echo sprintf("[OK] %s=%s\n", $key, $display);
}

if (!str_to_bool((string) env('MAIL_ENABLED', 'false'))) {
    $warnings[] = 'MAIL_ENABLED=false (correo deshabilitado).';
}

foreach ($warnings as $warning) {
    echo "[WARN] {$warning}\n";
}

if (!empty($missing)) {
    echo '[ERROR] Variables faltantes: ' . implode(', ', $missing) . PHP_EOL;
    exit(1);
}

echo "[OK] Entorno básico validado.\n";
exit(0);
