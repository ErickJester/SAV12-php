<?php

class FileService {

    public static function guardar(array $archivo): ?string {
        if (empty($archivo['tmp_name']) || $archivo['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Validar tipo
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($archivo['type'], $tiposPermitidos)) {
            throw new Exception('El archivo debe ser una imagen (JPG, PNG, GIF, WEBP)');
        }

        // Validar tamaño (5MB)
        $maxSize = (int) env('UPLOAD_MAX_SIZE', 5242880);
        if ($archivo['size'] > $maxSize) {
            throw new Exception('El archivo no debe superar los 5MB');
        }

        // Crear directorio si no existe
        if (!is_dir(UPLOAD_PATH)) {
            mkdir(UPLOAD_PATH, 0755, true);
        }

        // Generar nombre único
        $ext = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreUnico = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . strtolower($ext);

        $ruta = UPLOAD_PATH . '/' . $nombreUnico;
        if (!move_uploaded_file($archivo['tmp_name'], $ruta)) {
            throw new Exception('Error al guardar el archivo');
        }

        return $nombreUnico;
    }

    public static function eliminar(?string $nombre): void {
        if (empty($nombre)) return;
        $ruta = UPLOAD_PATH . '/' . $nombre;
        if (file_exists($ruta)) {
            unlink($ruta);
        }
    }
}
