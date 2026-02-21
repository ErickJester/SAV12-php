-- Verificación de baseline operativo de DB

SELECT 'admin_exists' AS check_name, COUNT(*) AS total
FROM usuarios
WHERE rol = 'ADMIN' AND activo = true;

SELECT 'tecnico_exists' AS check_name, COUNT(*) AS total
FROM usuarios
WHERE rol = 'TECNICO' AND activo = true;

SELECT 'categorias_activas' AS check_name, COUNT(*) AS total
FROM categorias
WHERE activo = true;

SELECT 'ubicaciones_activas' AS check_name, COUNT(*) AS total
FROM ubicaciones
WHERE activo = true;

SELECT 'sla_politicas_activas' AS check_name, COUNT(*) AS total
FROM sla_politicas
WHERE activo = true;

SELECT rol, COUNT(*) AS total
FROM usuarios
GROUP BY rol
ORDER BY rol;

SELECT estado, COUNT(*) AS total
FROM tickets
GROUP BY estado
ORDER BY estado;

SELECT table_name, index_name, GROUP_CONCAT(column_name ORDER BY seq_in_index) AS columnas
FROM information_schema.statistics
WHERE table_schema = DATABASE()
  AND table_name = 'tickets'
  AND index_name IN (
    'idx_estado',
    'idx_prioridad',
    'idx_creado_por',
    'idx_asignado_a',
    'idx_categoria_id',
    'idx_ubicacion_id',
    'idx_fecha_creacion',
    'idx_fecha_actualizacion',
    'idx_fecha_resolucion',
    'idx_fecha_cierre',
    'idx_sla_politica'
  )
GROUP BY table_name, index_name
ORDER BY index_name;
