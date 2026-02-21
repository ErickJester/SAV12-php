<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';
require_once APP_PATH . '/Services/EmailService.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este script solo se ejecuta por CLI.\n");
    exit(1);
}

$options = getopt('', ['to:']);
$to = (string) ($options['to'] ?? '');

if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Uso: php scripts/test_smtp.php --to=correo@dominio.com\n");
    exit(2);
}

$subject = 'SAV12 SMTP Test ' . date('Y-m-d H:i:s');
$body = "Prueba SMTP ejecutada por scripts/test_smtp.php\nHost: " . gethostname() . "\nFecha: " . date('c');

$result = EmailService::sendTestEmail($to, $subject, $body);

if ($result) {
    echo "[OK] Correo de prueba enviado a {$to}\n";
    exit(0);
}

echo "[ERROR] Falló el envío SMTP. Revisar logs de PHP/app para diagnóstico.\n";
exit(1);
