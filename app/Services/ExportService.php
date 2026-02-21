<?php

class ExportService {

    public static function exportarReporteCsv(array $payload, array $opciones = []): array {
        $filename = self::safeFilename($opciones['filename'] ?? ('reporte_tickets_' . date('Y-m-d_H-i-s') . '.csv'));
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            return ['ok' => false, 'error' => 'No fue posible crear buffer CSV'];
        }

        fputcsv($stream, ['SAV12 - Reporte de tickets']);
        fputcsv($stream, ['Generado', date('Y-m-d H:i:s')]);
        fputcsv($stream, []);

        if (!empty($payload['kpis']) && is_array($payload['kpis'])) {
            fputcsv($stream, ['KPIs']);
            foreach ($payload['kpis'] as $k => $v) {
                fputcsv($stream, [self::normalizeKey((string) $k), is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE)]);
            }
            fputcsv($stream, []);
        }

        $problematicos = $payload['ticketsProblematicos']['items'] ?? [];
        if (is_array($problematicos) && !empty($problematicos)) {
            fputcsv($stream, ['Top tickets problemáticos']);
            fputcsv($stream, ['Ticket ID', 'Título', 'Estado', 'Prioridad', 'Técnico', 'Horas sin resolver', 'Reaperturas', 'Score', 'Razones']);
            foreach ($problematicos as $item) {
                fputcsv($stream, [
                    $item['ticketId'] ?? '',
                    $item['titulo'] ?? '',
                    $item['estado'] ?? '',
                    $item['prioridad'] ?? '',
                    $item['tecnicoNombre'] ?? 'Sin asignar',
                    $item['horasSinResolver'] ?? 0,
                    $item['reaperturas'] ?? 0,
                    $item['scoreProblema'] ?? 0,
                    implode(' | ', (array) ($item['razones'] ?? [])),
                ]);
            }
            fputcsv($stream, []);
        }

        if (!empty($payload['alertas']) && is_array($payload['alertas'])) {
            fputcsv($stream, ['Alertas']);
            foreach ($payload['alertas'] as $k => $v) {
                if (is_array($v) && array_key_exists('total', $v)) {
                    fputcsv($stream, [self::normalizeKey((string) $k), (string) $v['total']]);
                } elseif (is_scalar($v)) {
                    fputcsv($stream, [self::normalizeKey((string) $k), (string) $v]);
                }
            }
        }

        rewind($stream);
        $content = (string) stream_get_contents($stream);
        fclose($stream);

        return [
            'ok' => true,
            'mime' => 'text/csv; charset=UTF-8',
            'filename' => $filename,
            'content' => $content,
            'size' => strlen($content),
        ];
    }

    public static function exportarReportePdf(array $payload, array $opciones = []): array {
        $filename = self::safeFilename($opciones['filename'] ?? ('reporte_tickets_' . date('Y-m-d_H-i-s') . '.pdf'));

        $lines = [
            'SAV12 - Reporte de tickets',
            'Generado: ' . date('Y-m-d H:i:s'),
            '',
        ];

        if (!empty($payload['kpis']) && is_array($payload['kpis'])) {
            $lines[] = 'KPIs:';
            foreach ($payload['kpis'] as $k => $v) {
                $lines[] = '- ' . self::normalizeKey((string) $k) . ': ' . (is_scalar($v) ? (string) $v : '[objeto]');
            }
            $lines[] = '';
        }

        $problematicos = $payload['ticketsProblematicos']['items'] ?? [];
        if (is_array($problematicos) && !empty($problematicos)) {
            $lines[] = 'Top tickets problemáticos:';
            foreach (array_slice($problematicos, 0, 20) as $item) {
                $lines[] = sprintf(
                    '#%s | %s | %s | score=%s',
                    (string) ($item['ticketId'] ?? ''),
                    self::shorten((string) ($item['titulo'] ?? ''), 70),
                    (string) ($item['estado'] ?? ''),
                    (string) ($item['scoreProblema'] ?? 0)
                );
            }
            $lines[] = '';
        }

        if (!empty($payload['alertas']) && is_array($payload['alertas'])) {
            $lines[] = 'Alertas:';
            foreach ($payload['alertas'] as $k => $v) {
                if (is_array($v) && isset($v['total'])) {
                    $lines[] = '- ' . self::normalizeKey((string) $k) . ': ' . $v['total'];
                } elseif (is_scalar($v)) {
                    $lines[] = '- ' . self::normalizeKey((string) $k) . ': ' . $v;
                }
            }
        }

        $content = self::renderSimplePdf($lines);
        return [
            'ok' => true,
            'mime' => 'application/pdf',
            'filename' => $filename,
            'content' => $content,
            'size' => strlen($content),
        ];
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
