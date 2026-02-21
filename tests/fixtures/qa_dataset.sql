-- QA FIXTURE DATASET (NO PRODUCTIVO)
-- Uso exclusivo para pruebas manuales/smoke/regresión.
-- No reemplaza database/seed.sql.

-- Requiere tablas existentes con schema productivo.

-- Usuarios QA por rol
INSERT INTO usuarios (nombre, correo, password_hash, rol, activo)
VALUES
  ('QA Admin', 'qa.admin@sav12.local', '$2y$12$w11f0R0zWvWg9YgJQv9BLemwD6HNdvVwR5XxRfcY6RrYH53lQxvBu', 'ADMIN', 1),
  ('QA Tecnico', 'qa.tecnico@sav12.local', '$2y$12$w11f0R0zWvWg9YgJQv9BLemwD6HNdvVwR5XxRfcY6RrYH53lQxvBu', 'TECNICO', 1),
  ('QA Usuario', 'qa.usuario@sav12.local', '$2y$12$w11f0R0zWvWg9YgJQv9BLemwD6HNdvVwR5XxRfcY6RrYH53lQxvBu', 'USUARIO', 1)
ON DUPLICATE KEY UPDATE
  nombre = VALUES(nombre),
  rol = VALUES(rol),
  activo = VALUES(activo);

-- Tickets QA en estados/prioridades variadas
-- Nota: IDs de catálogos/SLA pueden variar por entorno; ajustar FK según catálogos existentes.
INSERT INTO tickets (
  titulo, descripcion, estado, prioridad, creado_por_id, asignado_a_id,
  categoria_id, ubicacion_id, sla_politica_id, fecha_creacion, fecha_actualizacion
)
SELECT 'QA Ticket Abierto Alta', 'Caso QA para cola abierta', 'ABIERTO', 'ALTA', u1.id, u2.id, 1, 1, 1, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 1 DAY
FROM usuarios u1
JOIN usuarios u2 ON u2.correo='qa.tecnico@sav12.local'
WHERE u1.correo='qa.usuario@sav12.local'
  AND NOT EXISTS (SELECT 1 FROM tickets t WHERE t.titulo='QA Ticket Abierto Alta');

INSERT INTO tickets (
  titulo, descripcion, estado, prioridad, creado_por_id, asignado_a_id,
  categoria_id, ubicacion_id, sla_politica_id, fecha_creacion, fecha_actualizacion
)
SELECT 'QA Ticket En Proceso Media', 'Caso QA para reportes técnicos', 'EN_PROCESO', 'MEDIA', u1.id, u2.id, 1, 1, 1, NOW() - INTERVAL 3 DAY, NOW() - INTERVAL 6 HOUR
FROM usuarios u1
JOIN usuarios u2 ON u2.correo='qa.tecnico@sav12.local'
WHERE u1.correo='qa.usuario@sav12.local'
  AND NOT EXISTS (SELECT 1 FROM tickets t WHERE t.titulo='QA Ticket En Proceso Media');

INSERT INTO tickets (
  titulo, descripcion, estado, prioridad, creado_por_id, asignado_a_id,
  categoria_id, ubicacion_id, sla_politica_id, fecha_creacion, fecha_actualizacion, fecha_cierre
)
SELECT 'QA Ticket Cerrado Baja', 'Caso QA para histórico/cierre', 'CERRADO', 'BAJA', u1.id, u2.id, 1, 1, 1, NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 8 DAY, NOW() - INTERVAL 8 DAY
FROM usuarios u1
JOIN usuarios u2 ON u2.correo='qa.tecnico@sav12.local'
WHERE u1.correo='qa.usuario@sav12.local'
  AND NOT EXISTS (SELECT 1 FROM tickets t WHERE t.titulo='QA Ticket Cerrado Baja');

-- Caso de ticket problemático/SLA para alertas/reportes
INSERT INTO tickets (
  titulo, descripcion, estado, prioridad, creado_por_id, asignado_a_id,
  categoria_id, ubicacion_id, sla_politica_id, fecha_creacion, fecha_actualizacion
)
SELECT 'QA Ticket SLA Riesgo', 'Caso QA para alertas SLA', 'EN_ESPERA', 'URGENTE', u1.id, u2.id, 1, 1, 1, NOW() - INTERVAL 5 DAY, NOW() - INTERVAL 2 DAY
FROM usuarios u1
JOIN usuarios u2 ON u2.correo='qa.tecnico@sav12.local'
WHERE u1.correo='qa.usuario@sav12.local'
  AND NOT EXISTS (SELECT 1 FROM tickets t WHERE t.titulo='QA Ticket SLA Riesgo');

-- Adjuntos de QA: insertar solo metadato si existe tabla de adjuntos en el schema actual.
-- Si tu esquema usa otro nombre/estructura, registrar adjunto manualmente desde UI durante checklist.
