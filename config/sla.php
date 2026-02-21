<?php
/**
 * Configuración de SLA por defecto
 */

return [
    'default_policies' => [
        'ALUMNO' => [
            'primera_respuesta_min' => 240,  // 4 horas
            'resolucion_min'        => 1440, // 24 horas
        ],
        'DOCENTE' => [
            'primera_respuesta_min' => 180,  // 3 horas
            'resolucion_min'        => 1200, // 20 horas
        ],
        'ADMINISTRATIVO' => [
            'primera_respuesta_min' => 180,
            'resolucion_min'        => 1200,
        ],
    ],
    'prioridades' => ['BAJA', 'MEDIA', 'ALTA', 'URGENTE'],
    'estados'     => ['ABIERTO', 'REABIERTO', 'EN_PROCESO', 'EN_ESPERA', 'RESUELTO', 'CERRADO', 'CANCELADO'],
    'roles'       => ['ALUMNO', 'DOCENTE', 'ADMINISTRATIVO', 'TECNICO', 'ADMIN'],
];
