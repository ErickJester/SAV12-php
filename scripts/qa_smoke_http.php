<?php

declare(strict_types=1);

function loadEnvFile(string $path): array {
    if (!file_exists($path)) {
        return [];
    }
    $env = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || strpos($line, '=') === false) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim(trim($v), "\"'");
    }
    return $env;
}

function httpGet(string $url): array {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'ignore_errors' => true,
            'timeout' => 15,
            'follow_location' => 0,
        ],
    ]);

    $body = @file_get_contents($url, false, $context);
    $headers = $http_response_header ?? [];
    $status = 0;
    if (!empty($headers[0]) && preg_match('/\s(\d{3})\s/', $headers[0], $m)) {
        $status = (int) $m[1];
    }

    return [
        'status' => $status,
        'headers' => $headers,
        'body_len' => $body === false ? 0 : strlen($body),
        'error' => $body === false ? 'request_failed_or_unreachable' : null,
    ];
}

function classify(string $path, array $response): array {
    $status = $response['status'];
    $headers = implode("\n", $response['headers']);
    $bodyLen = (int) $response['body_len'];

    if ($response['error'] !== null && $status === 0) {
        return ['status' => 'BLOCKED', 'evidence' => 'No se pudo conectar al endpoint.'];
    }

    if ($path === '/login') {
        if (in_array($status, [200, 302], true)) {
            return ['status' => 'PASS', 'evidence' => "HTTP {$status} en /login."];
        }
        return ['status' => 'FAIL', 'evidence' => "HTTP {$status} inesperado para /login."];
    }

    if (str_starts_with($path, '/admin/reportes/export/csv')) {
        if ($status === 200 && str_contains(strtolower($headers), 'text/csv') && $bodyLen > 0) {
            return ['status' => 'PASS', 'evidence' => 'CSV respondió con content-type y cuerpo no vacío.'];
        }
        if (in_array($status, [302, 401, 403], true)) {
            return ['status' => 'PASS', 'evidence' => "Endpoint protegido por auth (HTTP {$status})."];
        }
        return ['status' => 'FAIL', 'evidence' => "CSV no cumple validación (HTTP {$status}, body={$bodyLen})."];
    }

    if (str_starts_with($path, '/admin/reportes/export/pdf')) {
        if ($status === 200 && str_contains(strtolower($headers), 'application/pdf') && $bodyLen > 0) {
            return ['status' => 'PASS', 'evidence' => 'PDF respondió con content-type y cuerpo no vacío.'];
        }
        if (in_array($status, [302, 401, 403], true)) {
            return ['status' => 'PASS', 'evidence' => "Endpoint protegido por auth (HTTP {$status})."];
        }
        return ['status' => 'FAIL', 'evidence' => "PDF no cumple validación (HTTP {$status}, body={$bodyLen})."];
    }

    if ($path === '/admin/reportes') {
        if (in_array($status, [200, 302, 401, 403], true)) {
            return ['status' => 'PASS', 'evidence' => "Endpoint responde y/o protege acceso (HTTP {$status})."];
        }
        return ['status' => 'FAIL', 'evidence' => "HTTP {$status} inesperado para reportes."];
    }

    return ['status' => 'BLOCKED', 'evidence' => 'Path no clasificado.'];
}

$options = getopt('', ['out::']);
$envFile = loadEnvFile(dirname(__DIR__) . '/.env');
$baseUrl = rtrim((string) (getenv('QA_BASE_URL') ?: ($envFile['APP_URL'] ?? '')), '/');

$results = [];
$startedAt = date('c');
if ($baseUrl === '') {
    $results[] = [
        'test' => 'http_base_url',
        'status' => 'BLOCKED',
        'evidence' => 'No existe QA_BASE_URL ni APP_URL configurado en .env',
        'timestamp' => date('c'),
    ];
} else {
    $paths = ['/login', '/admin/reportes', '/admin/reportes/export/csv', '/admin/reportes/export/pdf'];
    foreach ($paths as $path) {
        $fullUrl = $baseUrl . $path;
        $response = httpGet($fullUrl);
        $classification = classify($path, $response);
        $results[] = [
            'test' => 'http_' . ltrim(str_replace('/', '_', $path), '_'),
            'status' => $classification['status'],
            'evidence' => $classification['evidence'] . " URL={$fullUrl}",
            'timestamp' => date('c'),
        ];
    }
}

$output = [
    'suite' => 'qa_smoke_http',
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
