<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este script solo se ejecuta por CLI.\n");
    exit(1);
}

$checks = [
    'logs_dir' => LOG_PATH,
    'uploads_dir' => UPLOAD_PATH,
    'uploads_htaccess' => PUBLIC_PATH . '/uploads/.htaccess',
];

$failed = false;

foreach ($checks as $label => $path) {
    if (!file_exists($path)) {
        echo "[ERROR] {$label}: no existe ({$path})\n";
        $failed = true;
        continue;
    }

    echo "[OK] {$label}: existe ({$path})\n";
}

foreach (['logs_dir' => LOG_PATH, 'uploads_dir' => UPLOAD_PATH] as $label => $path) {
    if (!is_writable($path)) {
        echo "[ERROR] {$label}: sin permisos de escritura ({$path})\n";
        $failed = true;
        continue;
    }

    $testFile = rtrim($path, '/') . '/.write_test_' . getmypid();
    $bytes = @file_put_contents($testFile, "ok\n");
    if ($bytes === false) {
        echo "[ERROR] {$label}: escritura fallida en prueba ({$path})\n";
        $failed = true;
        continue;
    }
    @unlink($testFile);
    echo "[OK] {$label}: escritura validada\n";
}

exit($failed ? 1 : 0);
