# 06 - Tablas y Campos PHP

Fuente: `database/schema.sql`.

## Resumen de tablas clave
- `usuarios`: identidad/roles/estado.
- `tickets`: flujo principal, estados, SLA y evidencias.
- `comentarios`: conversación por ticket.
- `historial_acciones`: trazabilidad de cambios.
- `sla_politicas`: tiempos objetivo por rol.
- `categorias`, `ubicaciones`: catálogos operativos.

## Inventario

### usuarios
| Campo | Tipo | Nullable | Default |
|---|---|---|---|
| id | BIGINT | true* | null |
| nombre | VARCHAR(255) | false | null |
| correo | VARCHAR(255) | false | null |
| password_hash | VARCHAR(255) | false | null |
| rol | VARCHAR(50) | false | null |
| boleta | VARCHAR(50) | true | null |
| id_trabajador | VARCHAR(50) | true | null |
| activo | BOOLEAN | true | TRUE |

### categorias
| Campo | Tipo | Nullable | Default |
|---|---|---|---|
| id | BIGINT | true* | null |
| nombre | VARCHAR(255) | false | null |
| descripcion | TEXT | true | null |
| activo | BOOLEAN | true | TRUE |

### ubicaciones
| Campo | Tipo | Nullable | Default |
|---|---|---|---|
| id | BIGINT | true* | null |
| edificio | VARCHAR(255) | false | null |
| piso | VARCHAR(100) | true | null |
| salon | VARCHAR(100) | true | null |
| activo | BOOLEAN | true | TRUE |

### sla_politicas
| Campo | Tipo | Nullable | Default |
|---|---|---|---|
| id | BIGINT | true* | null |
| rol_solicitante | VARCHAR(50) | false | null |
| sla_primera_respuesta_min | INT | false | null |
| sla_resolucion_min | INT | false | null |
| activo | BOOLEAN | true | TRUE |

### tickets
| Campo | Tipo | Nullable | Default |
|---|---|---|---|
| id | BIGINT | true* | null |
| titulo | VARCHAR(255) | false | null |
| descripcion | TEXT | true | null |
| estado | ENUM('ABIERTO',...) | false | 'ABIERTO' |
| prioridad | VARCHAR(50) | true | 'MEDIA' |
| creado_por_id | BIGINT | false | null |
| asignado_a_id | BIGINT | true | null |
| categoria_id | BIGINT | true | null |
| ubicacion_id | BIGINT | true | null |
| sla_politica_id | BIGINT | true | null |
| fecha_creacion | DATETIME | false | CURRENT_TIMESTAMP |
| fecha_actualizacion | DATETIME | true | null |
| fecha_primera_respuesta | DATETIME | true | null |
| fecha_resolucion | DATETIME | true | null |
| fecha_cierre | DATETIME | true | null |
| evidencia_problema | VARCHAR(500) | true | null |
| evidencia_resolucion | VARCHAR(500) | true | null |
| tiempo_primera_respuesta_seg | INT | true | null |
| tiempo_resolucion_seg | INT | true | null |
| tiempo_espera_seg | INT | false | 0 |
| espera_desde | DATETIME | true | null |
| reabierto_count | INT | false | 0 |

### comentarios
| Campo | Tipo | Nullable | Default |
|---|---|---|---|
| id | BIGINT | true* | null |
| ticket_id | BIGINT | false | null |
| usuario_id | BIGINT | false | null |
| contenido | TEXT | false | null |
| fecha_creacion | DATETIME | false | CURRENT_TIMESTAMP |

### historial_acciones
| Campo | Tipo | Nullable | Default |
|---|---|---|---|
| id | BIGINT | true* | null |
| ticket_id | BIGINT | false | null |
| usuario_id | BIGINT | false | null |
| tipo | VARCHAR(50) | false | null |
| accion | VARCHAR(255) | false | null |
| estado_anterior | VARCHAR(50) | true | null |
| estado_nuevo | VARCHAR(50) | true | null |
| asignado_anterior_id | BIGINT | true | null |
| asignado_nuevo_id | BIGINT | true | null |
| fecha_accion | DATETIME | false | CURRENT_TIMESTAMP |
| detalles | TEXT | true | null |

\* `id` aparece como nullable=true por limitación del parser estático (la PK no se proyecta a nullability en esta versión del script).
