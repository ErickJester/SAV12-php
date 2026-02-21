<?php

class FileService {

    public static function guardar(array $archivo): ?string {
        if (empty($archivo['tmp_name']) || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        if (!is_uploaded_file($archivo['tmp_name'])) {
            throw new Exception('Archivo inválido para carga');
        }

        $maxSize = (int) config('runtime.max_upload_size', env('UPLOAD_MAX_SIZE', 5242880));
        if (($archivo['size'] ?? 0) > $maxSize) {
            throw new Exception('El archivo supera el tamaño máximo permitido');
        }

        $allowedExt = self::parseCsvList((string) config('runtime.allowed_upload_extensions', env('ALLOWED_UPLOAD_EXTENSIONS', 'jpg,jpeg,png,gif,webp,pdf')));
        $allowedMime = self::parseCsvList((string) config('runtime.allowed_upload_mime', env('ALLOWED_UPLOAD_MIME', 'image/jpeg,image/png,image/gif,image/webp,application/pdf')));

        $originalName = (string) ($archivo['name'] ?? 'archivo');
        $safeOriginal = self::sanitizeBaseName($originalName);
        $ext = strtolower(pathinfo($safeOriginal, PATHINFO_EXTENSION));
        if ($ext === '' || !in_array($ext, $allowedExt, true)) {
            throw new Exception('Extensión de archivo no permitida');
        }

        $mime = self::detectMime((string) $archivo['tmp_name']);
        if (!in_array($mime, $allowedMime, true)) {
            throw new Exception('Tipo MIME no permitido');
        }

        if (!is_dir(UPLOAD_PATH) && !@mkdir(UPLOAD_PATH, 0755, true)) {
            throw new Exception('No fue posible preparar el directorio de carga');
        }

        $nombreUnico = date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $ruta = rtrim(UPLOAD_PATH, '/') . '/' . $nombreUnico;

        if (!move_uploaded_file((string) $archivo['tmp_name'], $ruta)) {
            throw new Exception('Error al guardar el archivo');
        }

        return $nombreUnico;
    }

    public static function eliminar(?string $nombre): void {
        if (empty($nombre)) return;
        $safe = basename((string) $nombre);
        $ruta = rtrim(UPLOAD_PATH, '/') . '/' . $safe;
        if (is_file($ruta)) {
            @unlink($ruta);
        }
    }

    private static function parseCsvList(string $value): array {
        $parts = array_map('trim', explode(',', strtolower($value)));
        return array_values(array_filter($parts, fn($x) => $x !== ''));
    }

    private static function sanitizeBaseName(string $name): string {
        $name = basename($name);
        $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name) ?? 'archivo';
        return ltrim($name, '.');
    }

    private static function detectMime(string $tmpPath): string {
        if (!is_file($tmpPath)) {
            return 'application/octet-stream';
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return 'application/octet-stream';
        }
        $mime = finfo_file($finfo, $tmpPath) ?: 'application/octet-stream';
        finfo_close($finfo);
        return strtolower((string) $mime);
    }
}
