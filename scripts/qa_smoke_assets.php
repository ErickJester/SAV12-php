<?php

declare(strict_types=1);

$options = getopt('', ['out::']);
$results = [];
$startedAt = date('c');

$assetPath = dirname(__DIR__) . '/public_html/assets/js/reports.js';
if (file_exists($assetPath)) {
    $results[] = [
        'test' => 'asset_reports_js_exists',
        'status' => 'PASS',
        'evidence' => 'Existe public_html/assets/js/reports.js',
        'timestamp' => date('c'),
    ];
} else {
    $results[] = [
        'test' => 'asset_reports_js_exists',
        'status' => 'FAIL',
        'evidence' => 'No existe public_html/assets/js/reports.js',
        'timestamp' => date('c'),
    ];
}

$viewPath = dirname(__DIR__) . '/views/admin/reportes.php';
if (!file_exists($viewPath)) {
    $results[] = [
        'test' => 'view_reportes_exists',
        'status' => 'FAIL',
        'evidence' => 'No existe views/admin/reportes.php',
        'timestamp' => date('c'),
    ];
} else {
    $content = (string) file_get_contents($viewPath);
    $checks = [
        'base_url(\'admin/reportes\')',
        'base_url(\'admin/reportes/export/\'',
        'window.__reportesData',
    ];

    foreach ($checks as $needle) {
        $results[] = [
            'test' => 'view_reportes_ref_' . md5($needle),
            'status' => str_contains($content, $needle) ? 'PASS' : 'FAIL',
            'evidence' => str_contains($content, $needle)
                ? "Referencia encontrada: {$needle}"
                : "Referencia faltante: {$needle}",
            'timestamp' => date('c'),
        ];
    }
}

$output = [
    'suite' => 'qa_smoke_assets',
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
