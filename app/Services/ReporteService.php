<?php
/**
 * Servicio de Reportes y análisis SLA
 * Equivalente al ReporteService.java
 */

require_once APP_PATH . '/Models/Ticket.php';

class ReporteService {

    private const ALERTA_REAPERTURAS_UMBRAL = 2;
    private const ALERTA_SOBRECARGA_TECNICO_UMBRAL = 10;
    private const ALERTA_SIN_ASIGNAR_HORAS = 24;
    private const ALERTA_ESPERA_HORAS = 48;

    // ========================
    // REPORTE SLA
    // ========================
    public static function reporteSLA(string $desde, string $hasta): array {
        $tickets = Ticket::getByPeriodo($desde, $hasta);
        return self::construirReporteSla($tickets);
    }

    private static function construirReporteSla(array $tickets): array {
        $total = 0;
        $cumpleGlobal = $incumpleGlobal = 0;
        $cumplePrimera = $incumplePrimera = 0;
        $cumpleResol = $incumpleResol = 0;

        foreach ($tickets as $t) {
            if (in_array($t['estado'], ['RESUELTO', 'CERRADO']) && !empty($t['fecha_resolucion'])) {
                $total++;
                $r = self::evaluarSla($t);
                if ($r['cumplePrimera']) $cumplePrimera++; else $incumplePrimera++;
                if ($r['cumpleResolucion']) $cumpleResol++; else $incumpleResol++;
                if ($r['cumpleGlobal']) $cumpleGlobal++; else $incumpleGlobal++;
            }
        }

        return [
            'totalTickets' => $total,
            'ticketsCumplenSLA' => $cumpleGlobal, 'ticketsIncumplenSLA' => $incumpleGlobal,
            'ticketsCumplenPrimeraRespuesta' => $cumplePrimera, 'ticketsIncumplenPrimeraRespuesta' => $incumplePrimera,
            'ticketsCumplenResolucion' => $cumpleResol, 'ticketsIncumplenResolucion' => $incumpleResol,
            'slaPrimeraRespuestaPorcentaje' => self::pct($cumplePrimera, $total),
            'slaResolucionPorcentaje' => self::pct($cumpleResol, $total),
            'slaPorcentaje' => self::pct($cumpleGlobal, $total),
        ];
    }

    // ========================
    // KPIs EJECUTIVOS
    // ========================
    public static function kpisEjecutivos(string $desde, string $hasta): array {
        $tickets = Ticket::getByPeriodo($desde, $hasta);
        $total = count($tickets);

        $resueltos = count(array_filter($tickets, fn($t) => in_array($t['estado'], ['RESUELTO', 'CERRADO'])));
        $pendientes = count(array_filter($tickets, fn($t) => in_array($t['estado'], ['ABIERTO', 'REABIERTO', 'EN_PROCESO', 'EN_ESPERA'])));
        $sinAsignar = count(array_filter($tickets, fn($t) => empty($t['asignado_a_id']) && !in_array($t['estado'], ['CERRADO', 'CANCELADO'])));
        $criticos = count(array_filter($tickets, fn($t) => in_array(($t['prioridad'] ?? ''), ['ALTA', 'URGENTE'], true) && !in_array($t['estado'], ['RESUELTO', 'CERRADO'])));

        $tasaResolucion = $total > 0 ? round($resueltos * 100.0 / $total, 2) : 0;
        $slaData = self::construirReporteSla($tickets);

        return [
            'totalTickets' => $total, 'ticketsResueltos' => $resueltos,
            'ticketsPendientes' => $pendientes, 'ticketsSinAsignar' => $sinAsignar,
            'ticketsCriticos' => $criticos, 'tasaResolucion' => $tasaResolucion,
            'slaGlobalPorcentaje' => $slaData['slaPorcentaje'],
        ];
    }

    // ========================
    // REPORTE POR ESTADO
    // ========================
    public static function reportePorEstado(string $desde, string $hasta): array {
        $tickets = Ticket::getByPeriodo($desde, $hasta);
        $estados = ['ABIERTO' => 0, 'REABIERTO' => 0, 'EN_PROCESO' => 0, 'EN_ESPERA' => 0, 'RESUELTO' => 0, 'CERRADO' => 0, 'CANCELADO' => 0];
        foreach ($tickets as $t) {
            $estados[$t['estado']] = ($estados[$t['estado']] ?? 0) + 1;
        }
        return $estados;
    }

    // ========================
    // REPORTE GENERAL
    // ========================
    public static function reporteGeneral(string $desde, string $hasta): array {
        $tickets = Ticket::getByPeriodo($desde, $hasta);
        $conteo = fn($estado) => count(array_filter($tickets, fn($t) => $t['estado'] === $estado));

        $resueltos = $conteo('RESUELTO');
        $cerrados = $conteo('CERRADO');
        $abiertos = $conteo('ABIERTO');
        $reabiertos = $conteo('REABIERTO');
        $enProceso = $conteo('EN_PROCESO');
        $enEspera = $conteo('EN_ESPERA');

        return [
            'totalTickets' => count($tickets),
            'ticketsAbiertos' => $abiertos, 'ticketsReabiertos' => $reabiertos,
            'ticketsEnProceso' => $enProceso, 'ticketsEnEspera' => $enEspera,
            'ticketsResueltos' => $resueltos, 'ticketsCerrados' => $cerrados,
            'ticketsCancelados' => $conteo('CANCELADO'),
            'ticketsResueltosTotal' => $resueltos + $cerrados,
            'ticketsNoResueltos' => $abiertos + $reabiertos + $enProceso + $enEspera,
        ];
    }

    // ========================
    // ANÁLISIS DE TIEMPOS
    // ========================
    public static function analisisTiempos(string $desde, string $hasta): array {
        $tickets = array_filter(
            Ticket::getByPeriodo($desde, $hasta),
            fn($t) => !empty($t['fecha_resolucion'])
        );

        if (empty($tickets)) {
            return ['tiempoPromedioRespuestaMin' => 0, 'tiempoPromedioResolucionMin' => 0, 'tiempoPromedioEsperaMin' => 0, 'ticketsAnalizados' => 0];
        }

        $respuestas = array_filter(array_column($tickets, 'tiempo_primera_respuesta_seg'), fn($v) => $v !== null);
        $resoluciones = [];
        $esperas = [];

        foreach ($tickets as $t) {
            if ($t['tiempo_resolucion_seg'] !== null) {
                $espera = $t['tiempo_espera_seg'] ?? 0;
                $resoluciones[] = max(0, $t['tiempo_resolucion_seg'] - $espera);
            }
            if (($t['tiempo_espera_seg'] ?? 0) > 0) {
                $esperas[] = $t['tiempo_espera_seg'];
            }
        }

        return [
            'tiempoPromedioRespuestaMin' => round(self::avg($respuestas) / 60, 2),
            'tiempoPromedioResolucionMin' => round(self::avg($resoluciones) / 60, 2),
            'tiempoPromedioEsperaMin' => round(self::avg($esperas) / 60, 2),
            'ticketsAnalizados' => count($tickets),
        ];
    }

    // ========================
    // DESEMPEÑO TÉCNICOS
    // ========================
    public static function desempenoTecnicos(string $desde, string $hasta): array {
        $tickets = Ticket::getByPeriodo($desde, $hasta);
        $porTecnico = [];

        foreach ($tickets as $t) {
            $tecnicoId = $t['asignado_a_id'] ?? null;
            $tecnico = $t['asignado_nombre'] ?? 'Sin asignar';
            $clave = $tecnicoId ? "id:$tecnicoId" : "sin_asignar";
            if (!isset($porTecnico[$clave])) {
                $porTecnico[$clave] = [
                    'tecnicoId' => $tecnicoId,
                    'tecnico' => $tecnico,
                    'asignados' => 0,
                    'resueltos' => 0,
                    'pendientes' => 0,
                    'slaCumplido' => 0,
                ];
            }

            $porTecnico[$clave]['asignados']++;
            $resuelto = in_array($t['estado'], ['RESUELTO', 'CERRADO'], true);
            if ($resuelto) {
                $porTecnico[$clave]['resueltos']++;
                $sla = self::evaluarSla($t);
                if ($sla['cumpleGlobal']) {
                    $porTecnico[$clave]['slaCumplido']++;
                }
            } else {
                $porTecnico[$clave]['pendientes']++;
            }
        }

        $result = [];
        foreach ($porTecnico as $r) {
            $result[] = $r + [
                'tasaResolucion' => $r['asignados'] > 0 ? round(($r['resueltos'] / $r['asignados']) * 100, 2) : 0,
                'tasaSla' => $r['resueltos'] > 0 ? round(($r['slaCumplido'] / $r['resueltos']) * 100, 2) : 0,
            ];
        }

        usort($result, fn($a, $b) => $b['asignados'] <=> $a['asignados']);
        return $result;
    }

    // ========================
    // ANÁLISIS POR PRIORIDAD
    // ========================
    public static function analisisPorPrioridad(string $desde, string $hasta): array {
        $tickets = Ticket::getByPeriodo($desde, $hasta);
        $porPrioridad = [];
        foreach ($tickets as $t) {
            $p = $t['prioridad'] ?? 'MEDIA';
            $porPrioridad[$p][] = $t;
        }

        $result = [];
        foreach ($porPrioridad as $prioridad => $tks) {
            $total = count($tks);
            $resueltos = count(array_filter($tks, fn($t) => in_array($t['estado'], ['RESUELTO', 'CERRADO'], true)));
            $result[] = [
                'prioridad' => $prioridad,
                'total' => $total,
                'resueltos' => $resueltos,
                'pendientes' => $total - $resueltos,
            ];
        }

        $orden = ['URGENTE' => 0, 'ALTA' => 1, 'MEDIA' => 2, 'BAJA' => 3];
        usort($result, fn($a, $b) => ($orden[$a['prioridad']] ?? 9) <=> ($orden[$b['prioridad']] ?? 9));
        return $result;
    }

    // ========================
    // ANÁLISIS POR UBICACIONES
    // ========================
    public static function analisisPorUbicaciones(string $desde, string $hasta): array {
        $tickets = Ticket::getByPeriodo($desde, $hasta);
        $porUbicacion = [];
        foreach ($tickets as $t) {
            $ubi = $t['ubicacion_nombre'] ?? 'Sin ubicación';
            $ubi = trim(str_replace(['- -', '- null'], '', $ubi)) ?: 'Sin ubicación';
            $porUbicacion[$ubi][] = $t;
        }

        $result = [];
        foreach ($porUbicacion as $ubicacion => $tks) {
            $total = count($tks);
            $resueltos = count(array_filter($tks, fn($t) => in_array($t['estado'], ['RESUELTO', 'CERRADO'], true)));
            $result[] = ['ubicacion' => $ubicacion, 'total' => $total, 'resueltos' => $resueltos, 'pendientes' => $total - $resueltos];
        }

        usort($result, fn($a, $b) => $b['total'] <=> $a['total']);
        return array_slice($result, 0, 10);
    }

    // ========================
    // TOP TICKETS PROBLEMÁTICOS
    // ========================
    public static function generarTopTicketsProblematicos(array $filtros = [], int $limite = 10): array {
        $desde = $filtros['desde'] ?? date('Y-m-d H:i:s', strtotime('-1 month'));
        $hasta = $filtros['hasta'] ?? date('Y-m-d H:i:s');
        $incluirResueltos = (bool) ($filtros['incluirResueltos'] ?? false);

        $tickets = Ticket::getByPeriodo($desde, $hasta);
        $activos = ['ABIERTO', 'REABIERTO', 'EN_PROCESO', 'EN_ESPERA'];
        if (!$incluirResueltos) {
            $tickets = array_values(array_filter($tickets, fn($t) => in_array($t['estado'], $activos, true)));
        }

        $items = [];
        foreach ($tickets as $t) {
            $analisis = self::calcularScoreProblema($t);
            $items[] = [
                'ticketId' => (int) ($t['id'] ?? 0),
                'folio' => $t['folio'] ?? null,
                'titulo' => $t['titulo'] ?? '',
                'estado' => $t['estado'] ?? 'NO_CONFIRMADO',
                'prioridad' => $t['prioridad'] ?? 'MEDIA',
                'tecnicoId' => !empty($t['asignado_a_id']) ? (int) $t['asignado_a_id'] : null,
                'tecnicoNombre' => $t['asignado_nombre'] ?? null,
                'categoriaId' => !empty($t['categoria_id']) ? (int) $t['categoria_id'] : null,
                'categoriaNombre' => $t['categoria_nombre'] ?? null,
                'ubicacionId' => !empty($t['ubicacion_id']) ? (int) $t['ubicacion_id'] : null,
                'ubicacionNombre' => $t['ubicacion_nombre'] ?? null,
                'fechaCreacion' => $t['fecha_creacion'] ?? null,
                'fechaActualizacion' => $t['fecha_actualizacion'] ?? null,
                'fechaResolucion' => $t['fecha_resolucion'] ?? null,
                'diasAbierto' => $analisis['diasAbierto'],
                'horasSinResolver' => $analisis['horasSinResolver'],
                'tiempoEnEsperaHoras' => $analisis['tiempoEnEsperaHoras'],
                'reaperturas' => (int) ($t['reabierto_count'] ?? 0),
                'comentariosCount' => $t['comentarios_count'] ?? null,
                'scoreProblema' => $analisis['scoreProblema'],
                'factores' => $analisis['factores'],
                'razones' => $analisis['razones'],
            ];
        }

        usort($items, fn($a, $b) => $b['scoreProblema'] <=> $a['scoreProblema']);
        $items = array_slice($items, 0, max(1, $limite));

        return [
            'criterios' => [
                'limite' => $limite,
                'scoreFormula' => 'prioridad + antiguedad + reaperturas + espera + sinAsignar + slaRiesgo + estadoCritico',
                'umbrales' => [
                    'reaperturas' => self::ALERTA_REAPERTURAS_UMBRAL,
                    'diasSinResolver' => 3,
                    'ticketsActivosSobrecargaTecnico' => self::ALERTA_SOBRECARGA_TECNICO_UMBRAL,
                ],
            ],
            'items' => $items,
            'resumen' => [
                'totalEvaluados' => count($tickets),
                'totalDevueltos' => count($items),
            ],
        ];
    }

    // ========================
    // ALERTAS
    // ========================
    public static function alertas(string $desde, string $hasta): array {
        $tickets = Ticket::getByPeriodo($desde, $hasta);
        $ahora = time();
        $activos = ['ABIERTO', 'REABIERTO', 'EN_PROCESO', 'EN_ESPERA'];

        $sinAsignarItems = [];
        $vencidosItems = [];
        $enEsperaProlongada = [];
        $reabiertosMucho = [];

        foreach ($tickets as $t) {
            $estado = $t['estado'] ?? '';
            $activo = in_array($estado, $activos, true);
            $horasAbierto = self::hoursSince($t['fecha_creacion'] ?? null, $ahora);

            if ($activo && empty($t['asignado_a_id']) && $horasAbierto >= self::ALERTA_SIN_ASIGNAR_HORAS) {
                $sinAsignarItems[] = [
                    'ticketId' => (int) ($t['id'] ?? 0),
                    'titulo' => $t['titulo'] ?? '',
                    'horasAbierto' => $horasAbierto,
                    'estado' => $estado,
                ];
            }

            if ($activo && !empty($t['sla_primera_respuesta_min']) && empty($t['fecha_primera_respuesta'])) {
                $min = ($ahora - strtotime((string) ($t['fecha_creacion'] ?? 'now'))) / 60;
                if ($min > (int) $t['sla_primera_respuesta_min']) {
                    $vencidosItems[] = [
                        'ticketId' => (int) ($t['id'] ?? 0),
                        'titulo' => $t['titulo'] ?? '',
                        'minutosTranscurridos' => round($min, 2),
                        'slaPrimeraRespuestaMin' => (int) $t['sla_primera_respuesta_min'],
                    ];
                }
            }

            $esperaHoras = round(((int) ($t['tiempo_espera_seg'] ?? 0)) / 3600, 2);
            if ($activo && $esperaHoras >= self::ALERTA_ESPERA_HORAS) {
                $enEsperaProlongada[] = [
                    'ticketId' => (int) ($t['id'] ?? 0),
                    'titulo' => $t['titulo'] ?? '',
                    'estado' => $estado,
                    'tiempoEnEsperaHoras' => $esperaHoras,
                ];
            }

            $reaperturas = (int) ($t['reabierto_count'] ?? 0);
            if ($reaperturas >= self::ALERTA_REAPERTURAS_UMBRAL) {
                $reabiertosMucho[] = [
                    'ticketId' => (int) ($t['id'] ?? 0),
                    'titulo' => $t['titulo'] ?? '',
                    'reaperturas' => $reaperturas,
                    'estado' => $estado,
                ];
            }
        }

        $criticos = count(array_filter($tickets, fn($t) => in_array(($t['prioridad'] ?? ''), ['ALTA', 'URGENTE'], true) && in_array(($t['estado'] ?? ''), $activos, true)));

        $tecnicos = [];
        foreach ($tickets as $t) {
            if (!in_array(($t['estado'] ?? ''), $activos, true) || empty($t['asignado_a_id'])) {
                continue;
            }
            $id = (int) $t['asignado_a_id'];
            if (!isset($tecnicos[$id])) {
                $tecnicos[$id] = [
                    'tecnicoId' => $id,
                    'tecnicoNombre' => $t['asignado_nombre'] ?? 'NO_CONFIRMADO',
                    'ticketsActivos' => 0,
                    'ticketsAltaPrioridad' => 0,
                ];
            }
            $tecnicos[$id]['ticketsActivos']++;
            if (in_array(($t['prioridad'] ?? ''), ['ALTA', 'URGENTE'], true)) {
                $tecnicos[$id]['ticketsAltaPrioridad']++;
            }
        }

        $tecnicosSobrecargados = array_values(array_filter(
            $tecnicos,
            fn($x) => $x['ticketsActivos'] >= self::ALERTA_SOBRECARGA_TECNICO_UMBRAL
        ));
        usort($tecnicosSobrecargados, fn($a, $b) => $b['ticketsActivos'] <=> $a['ticketsActivos']);

        return [
            // Compatibilidad previa
            'ticketsSinAsignar' => count($sinAsignarItems),
            'ticketsVencidos' => count($vencidosItems),
            'ticketsCriticosPendientes' => $criticos,

            // Nuevas alertas estructuradas
            'ticketsReabiertosMuchasVeces' => [
                'umbral' => self::ALERTA_REAPERTURAS_UMBRAL,
                'total' => count($reabiertosMucho),
                'items' => $reabiertosMucho,
            ],
            'tecnicosSobrecargados' => [
                'umbral' => self::ALERTA_SOBRECARGA_TECNICO_UMBRAL,
                'total' => count($tecnicosSobrecargados),
                'items' => $tecnicosSobrecargados,
            ],
            'ticketsSinAsignarAntiguos' => [
                'umbralHoras' => self::ALERTA_SIN_ASIGNAR_HORAS,
                'total' => count($sinAsignarItems),
                'items' => $sinAsignarItems,
            ],
            'ticketsVencidosSLA' => [
                'total' => count($vencidosItems),
                'items' => $vencidosItems,
            ],
            'ticketsEnEsperaProlongada' => [
                'umbralHoras' => self::ALERTA_ESPERA_HORAS,
                'total' => count($enEsperaProlongada),
                'items' => $enEsperaProlongada,
            ],

            'tieneAlertas' => count($sinAsignarItems) > 0
                || count($vencidosItems) > 0
                || $criticos > 0
                || count($reabiertosMucho) > 0
                || count($tecnicosSobrecargados) > 0
                || count($enEsperaProlongada) > 0,
        ];
    }

    // ========================
    // TOP CATEGORÍAS
    // ========================
    public static function topCategorias(string $desde, string $hasta): array {
        $tickets = Ticket::getByPeriodo($desde, $hasta);
        $porCat = [];
        foreach ($tickets as $t) {
            $cat = $t['categoria_nombre'] ?? 'Sin categoría';
            $porCat[$cat] = ($porCat[$cat] ?? 0) + 1;
        }
        arsort($porCat);
        $result = [];
        foreach (array_slice($porCat, 0, 5, true) as $nombre => $total) {
            $result[] = ['nombre' => $nombre, 'total' => $total];
        }
        return $result;
    }

    // ========================
    // HELPERS INTERNOS
    // ========================
    private static function calcularScoreProblema(array $t): array {
        $ahora = time();
        $estado = $t['estado'] ?? '';
        $activo = in_array($estado, ['ABIERTO', 'REABIERTO', 'EN_PROCESO', 'EN_ESPERA'], true);
        $horasSinResolver = self::hoursSince($t['fecha_creacion'] ?? null, $ahora);
        $diasAbierto = round($horasSinResolver / 24, 2);
        $tiempoEsperaHoras = round(((int) ($t['tiempo_espera_seg'] ?? 0)) / 3600, 2);
        $reaperturas = (int) ($t['reabierto_count'] ?? 0);

        $prioridadMap = [
            'URGENTE' => 35,
            'ALTA' => 30,
            'MEDIA' => 15,
            'BAJA' => 5,
        ];
        $scorePrioridad = $prioridadMap[strtoupper((string) ($t['prioridad'] ?? 'MEDIA'))] ?? 10;
        $scoreAntiguedad = min(25, round($diasAbierto * 4, 2));
        $scoreReaperturas = min(30, $reaperturas * 10);
        $scoreEspera = min(15, round($tiempoEsperaHoras * 0.5, 2));
        $scoreSinAsignar = ($activo && empty($t['asignado_a_id'])) ? 10 : 0;

        $scoreSlaRiesgo = 0;
        if ($activo && !empty($t['sla_primera_respuesta_min'])) {
            $minsTrans = $horasSinResolver * 60;
            $slaPrim = (int) $t['sla_primera_respuesta_min'];
            if (empty($t['fecha_primera_respuesta']) && $minsTrans > $slaPrim) {
                $scoreSlaRiesgo = 15;
            } elseif (empty($t['fecha_primera_respuesta']) && $minsTrans > ($slaPrim * 0.8)) {
                $scoreSlaRiesgo = 8;
            }
        }

        $scoreEstadoCritico = ($estado === 'EN_ESPERA' && $tiempoEsperaHoras >= 24) ? 5 : 0;

        $factores = [
            'prioridad' => $scorePrioridad,
            'antiguedad' => $scoreAntiguedad,
            'reaperturas' => $scoreReaperturas,
            'espera' => $scoreEspera,
            'sinAsignar' => $scoreSinAsignar,
            'slaRiesgo' => $scoreSlaRiesgo,
            'estadoCritico' => $scoreEstadoCritico,
        ];

        $razones = [];
        if ($reaperturas >= self::ALERTA_REAPERTURAS_UMBRAL) $razones[] = "Reabierto {$reaperturas} veces";
        if ($diasAbierto >= 3) $razones[] = "Más de {$diasAbierto} días sin resolver";
        if (in_array(strtoupper((string) ($t['prioridad'] ?? '')), ['ALTA', 'URGENTE'], true)) $razones[] = 'Alta prioridad';
        if ($scoreSinAsignar > 0) $razones[] = 'Ticket activo sin técnico asignado';
        if ($tiempoEsperaHoras >= 24) $razones[] = "Acumula {$tiempoEsperaHoras}h en espera";
        if ($scoreSlaRiesgo >= 15) $razones[] = 'Riesgo alto de incumplimiento SLA';

        return [
            'diasAbierto' => $diasAbierto,
            'horasSinResolver' => round($horasSinResolver, 2),
            'tiempoEnEsperaHoras' => $tiempoEsperaHoras,
            'factores' => $factores,
            'scoreProblema' => round(array_sum($factores), 2),
            'razones' => $razones,
        ];
    }

    private static function hoursSince(?string $date, ?int $nowTs = null): float {
        if (empty($date)) {
            return 0.0;
        }
        $ts = strtotime($date);
        if ($ts === false) {
            return 0.0;
        }
        $nowTs = $nowTs ?? time();
        return max(0.0, ($nowTs - $ts) / 3600);
    }

    private static function evaluarSla(array $ticket): array {
        $slaPrimeraMin = $ticket['sla_primera_respuesta_min'] ?? null;
        $slaResolMin = $ticket['sla_resolucion_min'] ?? null;

        if (!$slaPrimeraMin) return ['cumplePrimera' => false, 'cumpleResolucion' => false, 'cumpleGlobal' => false];

        $tPrimera = $ticket['tiempo_primera_respuesta_seg'] ?? null;
        $tResol = $ticket['tiempo_resolucion_seg'] ?? null;
        $espera = $ticket['tiempo_espera_seg'] ?? 0;

        $cumplePrimera = !empty($ticket['fecha_primera_respuesta']) && $tPrimera !== null && ($tPrimera / 60.0) <= $slaPrimeraMin;
        $cumpleResol = !empty($ticket['fecha_resolucion']) && $tResol !== null && (max(0, $tResol - $espera) / 60.0) <= $slaResolMin;

        return ['cumplePrimera' => $cumplePrimera, 'cumpleResolucion' => $cumpleResol, 'cumpleGlobal' => $cumplePrimera && $cumpleResol];
    }

    private static function pct(int $ok, int $total): string {
        return $total > 0 ? number_format($ok * 100.0 / $total, 2, '.', '') : '0.00';
    }

    private static function avg(array $values): float {
        $filtered = array_filter($values, fn($v) => $v !== null);
        return count($filtered) > 0 ? array_sum($filtered) / count($filtered) : 0;
    }
}
