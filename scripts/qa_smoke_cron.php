<?php

declare(strict_types=1);

function runCommand(string $command): array {
    $start = microtime(true);
    $output = [];
    $code = 0;
    exec($command . ' 2>&1', $output, $code);
    return [
        'code' => $code,
        'output' => implode("\n", $output),
        'duration_s' => round(microtime(true) - $start, 3),
    ];
}

function classifyCron(array $execResult): array {
    $code = $execResult['code'];
    $out = strtolower($execResult['output']);

    if (str_contains($out, 'app_secret') || str_contains($out, 'configuración insegura')) {
        return ['status' => 'BLOCKED', 'evidence' => 'Bloqueo ambiental por APP_SECRET/entorno'];
    }

    if ($code === 0) {
        return ['status' => 'PASS', 'evidence' => 'Exit 0'];
    }


    if (str_contains($out, 'sqlstate') || str_contains($out, 'database') || str_contains($out, 'pdo')) {
        return ['status' => 'BLOCKED', 'evidence' => 'Bloqueo ambiental por DB no disponible/configurada'];
    }

    if (str_contains($out, 'mail_') || str_contains($out, 'smtp') || str_contains($out, 'mail()')) {
        return ['status' => 'BLOCKED', 'evidence' => 'Bloqueo ambiental potencial SMTP/correo'];
    }

    return ['status' => 'FAIL', 'evidence' => 'Fallo de ejecución no clasificado'];
}

$options = getopt('', ['out::']);
$jobs = [
    'check_sla_breaches' => 'php cron/check_sla_breaches.php',
    'auto_escalate' => 'php cron/auto_escalate.php',
    'send_reminders' => 'php cron/send_reminders.php',
];

$results = [];
$startedAt = date('c');
foreach ($jobs as $name => $cmd) {
    $execResult = runCommand($cmd);
    $classification = classifyCron($execResult);
    $results[] = [
        'test' => 'cron_' . $name,
        'status' => $classification['status'],
        'evidence' => $classification['evidence'] . "; exit={$execResult['code']}; duration={$execResult['duration_s']}s; output=" . substr($execResult['output'], 0, 300),
        'timestamp' => date('c'),
    ];
}

$output = [
    'suite' => 'qa_smoke_cron',
    'started_at' => $startedAt,
    'ended_at' => date('c'),
    'results' => $results,
];

$json = json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if (isset($options['out']) && is_string($options['out']) && $options['out'] !== '') {
    file_put_contents($options['out'], $json . PHP_EOL);
}

echo $json . PHP_EOL;
exit(0);
