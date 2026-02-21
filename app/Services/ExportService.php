<?php

class ExportService {

    // Aliases de compatibilidad
    public static function exportarReporteCsv(array $payload, array $opciones = []): array {
        return self::buildCsvResponse($payload, $opciones);
    }

    public static function exportarReportePdf(array $payload, array $opciones = []): array {
        return self::buildPdfResponse($payload, $opciones);
    }

    // Nombres de paridad semántica con Java
    public static function generarReporteCSV(array $datos): string {
        return self::generarCsvContenido($datos);
    }

    public static function generarReportePDF(array $datos): string {
        return self::generarPdfContenido($datos);
    }

    private static function buildCsvResponse(array $payload, array $opciones = []): array {
        $filename = self::safeFilename($opciones['filename'] ?? ('reporte_tickets_' . date('Y-m-d_H-i-s') . '.csv'));
        $content = self::generarReporteCSV($payload);

        return [
            'ok' => true,
            'mime' => 'text/csv; charset=UTF-8',
            'filename' => $filename,
            'content' => $content,
            'size' => strlen($content),
        ];
    }

    private static function buildPdfResponse(array $payload, array $opciones = []): array {
        $filename = self::safeFilename($opciones['filename'] ?? ('reporte_tickets_' . date('Y-m-d_H-i-s') . '.pdf'));
        $content = self::generarReportePDF($payload);

        return [
            'ok' => true,
            'mime' => 'application/pdf',
            'filename' => $filename,
            'content' => $content,
            'size' => strlen($content),
        ];
    }

    private static function generarCsvContenido(array $payload): string {
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            return '';
        }

        $periodo = $payload['periodo'] ?? [];
        fputcsv($stream, ['SAV12 - Reporte de tickets']);
        fputcsv($stream, ['Generado', (string) ($periodo['generadoEn'] ?? date('Y-m-d H:i:s'))]);
        fputcsv($stream, ['Desde', (string) ($periodo['desde'] ?? '')]);
        fputcsv($stream, ['Hasta', (string) ($periodo['hasta'] ?? '')]);
        fputcsv($stream, []);

        self::csvSectionScalarMap($stream, 'KPIs', (array) ($payload['kpis'] ?? []));
        self::csvSectionScalarMap($stream, 'Reporte SLA', (array) ($payload['reporteSLA'] ?? []));
        self::csvSectionScalarMap($stream, 'Análisis de tiempos', (array) ($payload['analisisTiempos'] ?? []));

        self::csvSectionTable($stream, 'Desempeño técnicos', (array) ($payload['desempenoTecnicos'] ?? []));
        self::csvSectionTable($stream, 'Análisis por prioridad', (array) ($payload['analisisPorPrioridad'] ?? []));
        self::csvSectionTable($stream, 'Análisis por ubicaciones', (array) ($payload['analisisPorUbicaciones'] ?? []));
        self::csvSectionTable($stream, 'Top categorías', (array) ($payload['topCategorias'] ?? []));

        $problematicos = (array) ($payload['ticketsProblematicos'] ?? []);
        self::csvSectionTable($stream, 'Tickets problemáticos - masReabiertos', (array) ($problematicos['masReabiertos'] ?? []));
        self::csvSectionTable($stream, 'Tickets problemáticos - mayorTiempoSinResolver', (array) ($problematicos['mayorTiempoSinResolver'] ?? []));
        self::csvSectionTable($stream, 'Tickets problemáticos - sinPrimeraRespuesta', (array) ($problematicos['sinPrimeraRespuesta'] ?? []));
        self::csvSectionTable($stream, 'Tickets problemáticos - criticosSinResolver', (array) ($problematicos['criticosSinResolver'] ?? []));

        $alertas = (array) ($payload['alertas'] ?? []);
        $alertasEscalares = [];
        foreach ($alertas as $k => $v) {
            if (is_scalar($v) || $v === null) {
                $alertasEscalares[$k] = $v;
            }
        }
        self::csvSectionScalarMap($stream, 'Alertas (escalares)', $alertasEscalares);

        rewind($stream);
        $content = (string) stream_get_contents($stream);
        fclose($stream);
        return $content;
    }

    private static function generarPdfContenido(array $payload): string {
        // Intentar librería real si está disponible
        $autoload = BASE_PATH . '/vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
            if (class_exists('Dompdf\\Dompdf')) {
                try {
                    $dompdf = new Dompdf\Dompdf();
                    $dompdf->loadHtml(self::buildSimpleHtml($payload));
                    $dompdf->setPaper('A4', 'portrait');
                    $dompdf->render();
                    return $dompdf->output();
                } catch (Throwable $e) {
                    error_log('[ExportService] Dompdf fallback a PDF simple: ' . $e->getMessage());
                }
            }
        }

        error_log('[ExportService] Fallback a generador PDF simple (sin librería externa).');
        $lines = self::buildSimpleTextLines($payload);
        return self::renderSimplePdf($lines);
    }

    private static function csvSectionScalarMap($stream, string $title, array $data): void {
        if (empty($data)) {
            return;
        }
        fputcsv($stream, [$title]);
        foreach ($data as $k => $v) {
            fputcsv($stream, [self::normalizeKey((string) $k), is_scalar($v) || $v === null ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE)]);
        }
        fputcsv($stream, []);
    }

    private static function csvSectionTable($stream, string $title, array $rows): void {
        if (empty($rows)) {
            return;
        }

        $first = reset($rows);
        if (!is_array($first)) {
            return;
        }

        $headers = array_keys($first);
        fputcsv($stream, [$title]);
        fputcsv($stream, $headers);

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $line = [];
            foreach ($headers as $h) {
                $value = $row[$h] ?? '';
                if (is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }
                $line[] = (string) $value;
            }
            fputcsv($stream, $line);
        }
        fputcsv($stream, []);
    }

    private static function buildSimpleHtml(array $payload): string {
        $periodo = $payload['periodo'] ?? [];
        $html = '<h1>SAV12 - Reporte</h1>';
        $html .= '<p>Generado: ' . htmlspecialchars((string) ($periodo['generadoEn'] ?? date('Y-m-d H:i:s'))) . '</p>';

        $html .= '<h2>KPIs</h2><ul>';
        foreach ((array) ($payload['kpis'] ?? []) as $k => $v) {
            $html .= '<li><strong>' . htmlspecialchars(self::normalizeKey((string) $k)) . ':</strong> ' . htmlspecialchars((string) $v) . '</li>';
        }
        $html .= '</ul>';

        $html .= '<h2>Alertas</h2><ul>';
        foreach ((array) ($payload['alertas'] ?? []) as $k => $v) {
            if (is_scalar($v) || $v === null) {
                $html .= '<li><strong>' . htmlspecialchars(self::normalizeKey((string) $k)) . ':</strong> ' . htmlspecialchars((string) $v) . '</li>';
            }
        }
        $html .= '</ul>';

        $html .= '<h2>Tickets problemáticos (Top)</h2><table border="1" cellspacing="0" cellpadding="4"><tr><th>ID</th><th>Título</th><th>Estado</th><th>Score</th></tr>';
        foreach ((array) (($payload['ticketsProblematicos']['rankingPorScore'] ?? [])) as $item) {
            $html .= '<tr><td>' . htmlspecialchars((string) ($item['ticketId'] ?? '')) . '</td><td>' . htmlspecialchars((string) ($item['titulo'] ?? '')) . '</td><td>' . htmlspecialchars((string) ($item['estado'] ?? '')) . '</td><td>' . htmlspecialchars((string) ($item['scoreProblema'] ?? '')) . '</td></tr>';
        }
        $html .= '</table>';

        return $html;
    }

    private static function buildSimpleTextLines(array $payload): array {
        $periodo = $payload['periodo'] ?? [];
        $lines = [
            'SAV12 - Reporte de tickets',
            'Generado: ' . ($periodo['generadoEn'] ?? date('Y-m-d H:i:s')),
            'Desde: ' . ($periodo['desde'] ?? ''),
            'Hasta: ' . ($periodo['hasta'] ?? ''),
            '',
            'KPIs:',
        ];

        foreach ((array) ($payload['kpis'] ?? []) as $k => $v) {
            $lines[] = '- ' . self::normalizeKey((string) $k) . ': ' . (string) $v;
        }

        $lines[] = '';
        $lines[] = 'Alertas:';
        foreach ((array) ($payload['alertas'] ?? []) as $k => $v) {
            if (is_scalar($v) || $v === null) {
                $lines[] = '- ' . self::normalizeKey((string) $k) . ': ' . (string) $v;
            }
        }

        $lines[] = '';
        $lines[] = 'Top tickets problemáticos:';
        foreach (array_slice((array) ($payload['ticketsProblematicos']['rankingPorScore'] ?? []), 0, 20) as $item) {
            $lines[] = sprintf(
                '#%s | %s | %s | score=%s',
                (string) ($item['ticketId'] ?? ''),
                self::shorten((string) ($item['titulo'] ?? ''), 70),
                (string) ($item['estado'] ?? ''),
                (string) ($item['scoreProblema'] ?? 0)
            );
        }

        return $lines;
    }

    private static function renderSimplePdf(array $lines): string {
        $escapedLines = array_map(fn($l) => self::pdfEscape($l), $lines);
        $text = "BT\n/F1 11 Tf\n50 790 Td\n";
        $first = true;
        foreach ($escapedLines as $line) {
            if ($first) {
                $text .= "({$line}) Tj\n";
                $first = false;
            } else {
                $text .= "T* ({$line}) Tj\n";
            }
        }
        $text .= "ET";

        $objects = [];
        $objects[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj";
        $objects[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj";
        $objects[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj";
        $objects[] = "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj";
        $objects[] = "5 0 obj << /Length " . strlen($text) . " >> stream\n{$text}\nendstream endobj";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= $obj . "\n";
        }

        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 6\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= 5; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";

        return $pdf;
    }

    private static function normalizeKey(string $key): string {
        return ucwords(str_replace(['_', '-'], ' ', $key));
    }

    private static function safeFilename(string $filename): string {
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename) ?? 'reporte';
        return ltrim($filename, '.');
    }

    private static function pdfEscape(string $text): string {
        $text = preg_replace('/[^\x20-\x7E]/', '?', $text) ?? '';
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private static function shorten(string $text, int $max): string {
        if (mb_strlen($text) <= $max) {
            return $text;
        }
        return mb_substr($text, 0, $max - 3) . '...';
    }
}
