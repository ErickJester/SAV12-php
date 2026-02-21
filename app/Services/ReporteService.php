<?php
/**
 * Servicio de Reportes y análisis SLA
 * Equivalente al ReporteService.java
 */

require_once APP_PATH . '/Models/Ticket.php';

class ReporteService {

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
        $criticos = count(array_filter($tickets, fn($t) => $t['prioridad'] === 'ALTA' && !in_array($t['estado'], ['RESUELTO', 'CERRADO'])));

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
        $tickets = array_filter(Ticket::getByPeriodo($desde, $hasta), fn($t) => !empty($t['asignado_a_id']));

        $porTecnico = [];
        foreach ($tickets as $t) {
            $nombre = $t['asignado_nombre'] ?? 'Sin nombre';
            $porTecnico[$nombre][] = $t;
        }

        $result = [];
        foreach ($porTecnico as $tecnico => $ticketsTec) {
            $total = count($ticketsTec);
            $resueltos = count(array_filter($ticketsTec, fn($t) => in_array($t['estado'], ['RESUELTO', 'CERRADO'])));
            $enProceso = count(array_filter($ticketsTec, fn($t) => $t['estado'] === 'EN_PROCESO'));
            $reabiertos = count(array_filter($ticketsTec, fn($t) => ($t['reabierto_count'] ?? 0) > 0));

            $cumpleSLA = 0;
            $totalSLA = 0;
            foreach ($ticketsTec as $t) {
                if (!empty($t['fecha_resolucion']) && !empty($t['sla_primera_respuesta_min'])) {
                    $totalSLA++;
                    $r = self::evaluarSla($t);
                    if ($r['cumpleGlobal']) $cumpleSLA++;
                }
            }

            $result[] = [
                'tecnico' => $tecnico,
                'totalAsignados' => $total,
                'resueltos' => $resueltos,
                'enProceso' => $enProceso,
                'reabiertos' => $reabiertos,
                'tasaExito' => $total > 0 ? round($resueltos * 100.0 / $total, 2) : 0,
                'cumplimientoSLA' => $totalSLA > 0 ? round($cumpleSLA * 100.0 / $totalSLA, 2) : 0,
                'tiempoPromedioResolucionMin' => round(self::avg(array_map(function($t) {
                    if ($t['tiempo_resolucion_seg'] === null) return null;
                    return max(0, $t['tiempo_resolucion_seg'] - ($t['tiempo_espera_seg'] ?? 0));
                }, array_filter($ticketsTec, fn($t) => $t['tiempo_resolucion_seg'] !== null))) / 60, 2),
            ];
        }

        usort($result, fn($a, $b) => $b['resueltos'] <=> $a['resueltos']);
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
            $resueltos = count(array_filter($tks, fn($t) => in_array($t['estado'], ['RESUELTO', 'CERRADO'])));
            $result[] = [
                'prioridad' => $prioridad,
                'total' => $total,
                'resueltos' => $resueltos,
                'pendientes' => $total - $resueltos,
            ];
        }

        // Ordenar: URGENTE > ALTA > MEDIA > BAJA
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
            $resueltos = count(array_filter($tks, fn($t) => in_array($t['estado'], ['RESUELTO', 'CERRADO'])));
            $result[] = ['ubicacion' => $ubicacion, 'total' => $total, 'resueltos' => $resueltos, 'pendientes' => $total - $resueltos];
        }

        usort($result, fn($a, $b) => $b['total'] <=> $a['total']);
        return array_slice($result, 0, 10);
    }

    // ========================
    // ALERTAS
    // ========================
    public static function alertas(string $desde, string $hasta): array {
        $tickets = Ticket::getByPeriodo($desde, $hasta);
        $ahora = time();

        $sinAsignar = count(array_filter($tickets, fn($t) => empty($t['asignado_a_id']) && !in_array($t['estado'], ['CERRADO', 'CANCELADO'])));
        $vencidos = count(array_filter($tickets, function($t) use ($ahora) {
            if (in_array($t['estado'], ['RESUELTO', 'CERRADO'])) return false;
            if (empty($t['sla_primera_respuesta_min'])) return false;
            if (empty($t['fecha_primera_respuesta'])) {
                $min = ($ahora - strtotime($t['fecha_creacion'])) / 60;
                return $min > $t['sla_primera_respuesta_min'];
            }
            return false;
        }));
        $criticos = count(array_filter($tickets, fn($t) => $t['prioridad'] === 'ALTA' && !in_array($t['estado'], ['RESUELTO', 'CERRADO'])));

        return [
            'ticketsSinAsignar' => $sinAsignar,
            'ticketsVencidos' => $vencidos,
            'ticketsCriticosPendientes' => $criticos,
            'tieneAlertas' => $sinAsignar > 0 || $vencidos > 0 || $criticos > 0,
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
