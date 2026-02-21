<?php

declare(strict_types=1);

$artifactDir = dirname(__DIR__) . '/docs/qa/artifacts';
if (!is_dir($artifactDir)) {
    mkdir($artifactDir, 0775, true);
}

$suites = [
    'http' => 'php scripts/qa_smoke_http.php --out=' . escapeshellarg($artifactDir . '/smoke_http.json'),
    'cron' => 'php scripts/qa_smoke_cron.php --out=' . escapeshellarg($artifactDir . '/smoke_cron.json'),
    'assets' => 'php scripts/qa_smoke_assets.php --out=' . escapeshellarg($artifactDir . '/smoke_assets.json'),
];

$allResults = [];
foreach ($suites as $name => $cmd) {
    $output = [];
    $code = 0;
    exec($cmd . ' 2>&1', $output, $code);

    $jsonPath = $artifactDir . '/smoke_' . $name . '.json';
    if (!file_exists($jsonPath)) {
        $allResults[] = [
            'suite' => $name,
            'status' => 'FAIL',
            'results' => [[
                'test' => 'suite_execution_' . $name,
                'status' => 'FAIL',
                'evidence' => 'No se generó JSON de salida. code=' . $code . ' output=' . implode("\n", $output),
                'timestamp' => date('c'),
            ]],
        ];
        continue;
    }

    $decoded = json_decode((string) file_get_contents($jsonPath), true);
    if (!is_array($decoded)) {
        $allResults[] = [
            'suite' => $name,
            'status' => 'FAIL',
            'results' => [[
                'test' => 'suite_execution_' . $name,
                'status' => 'FAIL',
                'evidence' => 'JSON inválido en artefacto',
                'timestamp' => date('c'),
            ]],
        ];
        continue;
    }

    $allResults[] = [
        'suite' => $decoded['suite'] ?? $name,
        'status' => 'DONE',
        'results' => $decoded['results'] ?? [],
    ];
}

$flat = [];
foreach ($allResults as $suiteData) {
    foreach ($suiteData['results'] as $result) {
        $flat[] = $result;
    }
}

$summary = [
    'PASS' => count(array_filter($flat, fn($r) => ($r['status'] ?? '') === 'PASS')),
    'FAIL' => count(array_filter($flat, fn($r) => ($r['status'] ?? '') === 'FAIL')),
    'BLOCKED' => count(array_filter($flat, fn($r) => ($r['status'] ?? '') === 'BLOCKED')),
];

$summaryJson = [
    'generated_at' => date('c'),
    'summary' => $summary,
    'results' => $allResults,
];
file_put_contents($artifactDir . '/smoke_summary.json', json_encode($summaryJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);

$md = "# Reporte Smoke QA\n\n";
$md .= "Generado: " . date('c') . "\n\n";
$md .= "## Resumen\n";
$md .= "- PASS: {$summary['PASS']}\n";
$md .= "- FAIL: {$summary['FAIL']}\n";
$md .= "- BLOCKED: {$summary['BLOCKED']}\n\n";
$md .= "## Resultados\n\n";
$md .= "| Prueba | Estado | Evidencia | Timestamp |\n";
$md .= "|---|---|---|---|\n";
foreach ($flat as $r) {
    $md .= '| ' . ($r['test'] ?? '-') . ' | ' . ($r['status'] ?? '-') . ' | ' . str_replace('|', '/', (string) ($r['evidence'] ?? '')) . ' | ' . ($r['timestamp'] ?? '-') . " |\n";
}

file_put_contents($artifactDir . '/smoke_summary.md', $md);
echo $md;
exit(0);
