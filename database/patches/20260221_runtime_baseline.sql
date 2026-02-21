-- Patch de baseline operativo (idempotente) para entornos existentes
-- Objetivo: asegurar índices de soporte para filtros/reportes en tickets.
-- Portabilidad: ejecutar seleccionando DB objetivo con mysql -D <nombre_db>.

SET @db := DATABASE();

-- idx_prioridad en tickets(prioridad)
SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = @db
          AND table_name = 'tickets'
          AND column_name = 'prioridad'
          AND seq_in_index = 1
    ),
    'SELECT "coverage exists for tickets.prioridad"',
    'ALTER TABLE tickets ADD INDEX idx_prioridad (prioridad)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_categoria_id en tickets(categoria_id)
SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = @db
          AND table_name = 'tickets'
          AND column_name = 'categoria_id'
          AND seq_in_index = 1
    ),
    'SELECT "coverage exists for tickets.categoria_id"',
    'ALTER TABLE tickets ADD INDEX idx_categoria_id (categoria_id)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_ubicacion_id en tickets(ubicacion_id)
SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = @db
          AND table_name = 'tickets'
          AND column_name = 'ubicacion_id'
          AND seq_in_index = 1
    ),
    'SELECT "coverage exists for tickets.ubicacion_id"',
    'ALTER TABLE tickets ADD INDEX idx_ubicacion_id (ubicacion_id)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_fecha_actualizacion en tickets(fecha_actualizacion)
SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = @db
          AND table_name = 'tickets'
          AND column_name = 'fecha_actualizacion'
          AND seq_in_index = 1
    ),
    'SELECT "coverage exists for tickets.fecha_actualizacion"',
    'ALTER TABLE tickets ADD INDEX idx_fecha_actualizacion (fecha_actualizacion)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_fecha_resolucion en tickets(fecha_resolucion)
SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = @db
          AND table_name = 'tickets'
          AND column_name = 'fecha_resolucion'
          AND seq_in_index = 1
    ),
    'SELECT "coverage exists for tickets.fecha_resolucion"',
    'ALTER TABLE tickets ADD INDEX idx_fecha_resolucion (fecha_resolucion)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_fecha_cierre en tickets(fecha_cierre)
SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = @db
          AND table_name = 'tickets'
          AND column_name = 'fecha_cierre'
          AND seq_in_index = 1
    ),
    'SELECT "coverage exists for tickets.fecha_cierre"',
    'ALTER TABLE tickets ADD INDEX idx_fecha_cierre (fecha_cierre)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
