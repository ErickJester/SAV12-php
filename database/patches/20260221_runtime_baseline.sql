-- Patch de baseline operativo (idempotente) para entornos existentes
-- Objetivo: asegurar índices de soporte para filtros/reportes en tickets.

USE sav12_app;

SET @db := DATABASE();

-- idx_prioridad en tickets(prioridad)
SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = @db AND table_name = 'tickets' AND index_name = 'idx_prioridad'
    ),
    'SELECT "idx_prioridad already exists"',
    'ALTER TABLE tickets ADD INDEX idx_prioridad (prioridad)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_categoria_id en tickets(categoria_id)
SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = @db AND table_name = 'tickets' AND index_name = 'idx_categoria_id'
    ),
    'SELECT "idx_categoria_id already exists"',
    'ALTER TABLE tickets ADD INDEX idx_categoria_id (categoria_id)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_ubicacion_id en tickets(ubicacion_id)
SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = @db AND table_name = 'tickets' AND index_name = 'idx_ubicacion_id'
    ),
    'SELECT "idx_ubicacion_id already exists"',
    'ALTER TABLE tickets ADD INDEX idx_ubicacion_id (ubicacion_id)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_fecha_actualizacion en tickets(fecha_actualizacion)
SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = @db AND table_name = 'tickets' AND index_name = 'idx_fecha_actualizacion'
    ),
    'SELECT "idx_fecha_actualizacion already exists"',
    'ALTER TABLE tickets ADD INDEX idx_fecha_actualizacion (fecha_actualizacion)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_fecha_resolucion en tickets(fecha_resolucion)
SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = @db AND table_name = 'tickets' AND index_name = 'idx_fecha_resolucion'
    ),
    'SELECT "idx_fecha_resolucion already exists"',
    'ALTER TABLE tickets ADD INDEX idx_fecha_resolucion (fecha_resolucion)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_fecha_cierre en tickets(fecha_cierre)
SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = @db AND table_name = 'tickets' AND index_name = 'idx_fecha_cierre'
    ),
    'SELECT "idx_fecha_cierre already exists"',
    'ALTER TABLE tickets ADD INDEX idx_fecha_cierre (fecha_cierre)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
