-- Seed operativo mínimo e idempotente para SAV12
-- Requiere haber ejecutado database/schema.sql previamente.
-- Credenciales iniciales (solo para desarrollo/pruebas):
--   ADMIN   -> admin@sav12.local    / Admin123!
--   TECNICO -> tecnico@sav12.local  / Tecnico123!
--   USUARIO -> alumno.demo@sav12.local / Alumno123! (opcional para flujos)
-- IMPORTANTE: Cambiar contraseñas inmediatamente en producción.

USE sav12_app;

-- Catálogos mínimos (idempotentes)
INSERT INTO categorias (nombre, descripcion, activo) VALUES
('Hardware', 'Problemas relacionados con equipo físico', true),
('Software', 'Problemas con aplicaciones y sistemas operativos', true),
('Red', 'Problemas de conectividad y red', true)
ON DUPLICATE KEY UPDATE
    descripcion = VALUES(descripcion),
    activo = VALUES(activo);

INSERT INTO ubicaciones (edificio, piso, salon, activo)
SELECT 'Edificio Central', 'Planta Baja', 'Mesa de Ayuda', true
WHERE NOT EXISTS (
    SELECT 1 FROM ubicaciones WHERE edificio = 'Edificio Central' AND piso = 'Planta Baja' AND salon = 'Mesa de Ayuda'
);
INSERT INTO ubicaciones (edificio, piso, salon, activo)
SELECT 'Edificio Norte', 'Primer Piso', 'Laboratorio 1', true
WHERE NOT EXISTS (
    SELECT 1 FROM ubicaciones WHERE edificio = 'Edificio Norte' AND piso = 'Primer Piso' AND salon = 'Laboratorio 1'
);

-- Políticas SLA mínimas (idempotentes)
INSERT INTO sla_politicas (rol_solicitante, sla_primera_respuesta_min, sla_resolucion_min, activo) VALUES
('ALUMNO', 240, 1440, true),
('DOCENTE', 180, 1200, true),
('ADMINISTRATIVO', 180, 1200, true)
ON DUPLICATE KEY UPDATE
    sla_primera_respuesta_min = VALUES(sla_primera_respuesta_min),
    sla_resolucion_min = VALUES(sla_resolucion_min),
    activo = VALUES(activo);

-- Usuarios mínimos operables
-- Hashes bcrypt compatibles con password_verify()/password_hash(PASSWORD_BCRYPT, cost=12)
INSERT INTO usuarios (nombre, correo, password_hash, rol, boleta, id_trabajador, activo)
VALUES (
    'Administrador Inicial',
    'admin@sav12.local',
    '$2y$12$MuXjRayKHKDI.z9E/Pvmd.coF8ytgMtChtXoKrmxn/HaTtHE6AYYe',
    'ADMIN',
    NULL,
    'ADM-0001',
    true
)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    password_hash = VALUES(password_hash),
    rol = VALUES(rol),
    activo = VALUES(activo);

INSERT INTO usuarios (nombre, correo, password_hash, rol, boleta, id_trabajador, activo)
VALUES (
    'Tecnico Inicial',
    'tecnico@sav12.local',
    '$2y$12$oZpItWXldvi.5xJfHTOFme19.yv83/wQz0IbyJBl8gGJM7NCkOhpK',
    'TECNICO',
    NULL,
    'TEC-0001',
    true
)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    password_hash = VALUES(password_hash),
    rol = VALUES(rol),
    activo = VALUES(activo);

-- Usuario final de prueba opcional para flujo de creación de tickets
INSERT INTO usuarios (nombre, correo, password_hash, rol, boleta, id_trabajador, activo)
VALUES (
    'Alumno Demo',
    'alumno.demo@sav12.local',
    '$2y$12$3TKoEhThKFNfHAk3b6bVtemQji1gAmjGcavlGQvOR.SPOEkRF8Awu',
    'ALUMNO',
    'A00000001',
    NULL,
    true
)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    password_hash = VALUES(password_hash),
    rol = VALUES(rol),
    activo = VALUES(activo);
