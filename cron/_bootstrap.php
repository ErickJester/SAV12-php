<?php

declare(strict_types=1);

if (!function_exists('cron_init')) {
    function cron_init(string $jobName): void {
        $projectRoot = dirname(__DIR__);
        require_once $projectRoot . '/config/app.php';
        require_once APP_PATH . '/Helpers/Database.php';

        if (PHP_SAPI !== 'cli') {
            fwrite(STDERR, "[$jobName] Este script solo debe ejecutarse por CLI.\n");
            exit(1);
        }
    }
}

if (!function_exists('cron_log')) {
    function cron_log(string $jobName, string $level, string $message, array $context = []): void {
        $timestamp = date('Y-m-d H:i:s');
        $suffix = '';
        if (!empty($context)) {
            $parts = [];
            foreach ($context as $key => $value) {
                $parts[] = sprintf('%s=%s', (string) $key, (string) $value);
            }
            $suffix = ' | ' . implode(' ', $parts);
        }

        $line = sprintf('[%s] [%s] [%s] %s%s', $timestamp, $jobName, strtoupper($level), $message, $suffix);
        echo $line . PHP_EOL;

        if (strtoupper($level) !== 'INFO') {
            error_log($line);
        }
    }
}

if (!function_exists('cron_handle_exception')) {
    function cron_handle_exception(string $jobName, Throwable $exception): void {
        cron_log(
            $jobName,
            'ERROR',
            'Ejecución fallida',
            [
                'type' => get_class($exception),
                'message' => preg_replace('/\s+/', ' ', $exception->getMessage()) ?: 'sin_mensaje',
            ]
        );
    }
}
