<?php

namespace App\Services;

use App\Models\Negocio;

class RecomendacionesService
{
    /**
     * Obtiene el banner del mes anterior si no ha sido cerrado.
     */
    public function obtenerBanner(Negocio $negocio): array
    {
        $mostrarBanner = false;
        $bannerData    = null;

        if (now()->day <= 3) {
            $mesAnterior  = now()->month == 1 ? 12 : now()->month - 1;
            $anioAnterior = now()->month == 1 ? now()->year - 1 : now()->year;
            $metaAnterior = $negocio->metasMensuales()
                ->where('mes', $mesAnterior)->where('anio', $anioAnterior)->first();

            if ($metaAnterior && !session('banner_cerrado_' . $mesAnterior . '_' . $anioAnterior)) {
                $mostrarBanner = true;
                $bannerData    = $metaAnterior;
            }
        }

        return ['mostrarBanner' => $mostrarBanner, 'bannerData' => $bannerData];
    }

    /**
     * Genera alertas críticas o de retraso basado en el progreso del mes.
     */
    public function generarAlerta(float $avanceReal, float $puntoEquilibrio, float $metaMeta, float $diasRestantes, float $porcentajeMesTranscurrido, string $moneda): ?array
    {
        $faltante                  = $metaMeta - $avanceReal;
        $ventaDiariaRequerida      = $diasRestantes > 0 ? $faltante / $diasRestantes : 0;
        $porcentajeAvance          = $metaMeta > 0 ? min(max(($avanceReal / $metaMeta) * 100, 0), 100) : 0;

        $alerta = null;
        if ($porcentajeMesTranscurrido >= 80 && $avanceReal < $puntoEquilibrio) {
            $alerta = ['tipo' => 'rojo', 'mensaje' => 'Riesgo crítico: necesitas generar ' . $moneda . ' ' . number_format($ventaDiariaRequerida, 0, ',', '.') . ' por día.'];
        } elseif ($porcentajeMesTranscurrido >= 50 && $porcentajeAvance < 50) {
            $alerta = ['tipo' => 'amarillo', 'mensaje' => 'Vas retrasado. Necesitas generar ' . $moneda . ' ' . number_format($ventaDiariaRequerida, 0, ',', '.') . ' por día.'];
        }

        return $alerta;
    }

    /**
     * Genera la lista de recomendaciones dinámicas para el negocio.
     */
    public function generarRecomendaciones(Negocio $negocio, float $avanceReal, float $puntoEquilibrio, float $metaMeta, float $gastosFijos, float $diasRestantes, string $moneda): array
    {
        $diaActual       = now()->day;
        $recomendaciones = [];

        $tipoEstado = $avanceReal < $puntoEquilibrio ? 'riesgo'
            : ($avanceReal < $metaMeta ? 'estable' : 'prospero');

        $tieneMovimientos = $negocio->movimientosCaja()
            ->whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->exists();

        if (!$tieneMovimientos) {
            $recomendaciones[] = ['tipo' => 'info', 'mensaje' => 'Registra tus ventas y gastos para recibir recomendaciones.', 'accion' => null];
        } else {
            $ventaDiariaReq = $diasRestantes > 0 ? ($metaMeta - $avanceReal) / $diasRestantes : 0;

            if ($diaActual <= 7) {
                $mesAnteriorNum  = now()->month == 1 ? 12 : now()->month - 1;
                $anioAnteriorNum = now()->month == 1 ? now()->year - 1 : now()->year;
                $resumenAnterior = $negocio->metasMensuales()
                    ->where('mes', $mesAnteriorNum)->where('anio', $anioAnteriorNum)->first();

                if ($resumenAnterior) {
                    $supero = $resumenAnterior->ventas_real >= $resumenAnterior->meta;
                    $recomendaciones[] = ['tipo' => $supero ? 'verde' : 'rojo',
                        'mensaje' => 'Mes anterior: ' . ($supero ? '¡Superaste tu proyección!' : 'No alcanzaste la proyección.'), 'accion' => null];
                }
                $recomendaciones[] = ['tipo' => 'info', 'mensaje' => 'Inicio de mes: mantén un buen ritmo desde el primer día.', 'accion' => null];

            } elseif ($diaActual <= 20) {
                if ($tipoEstado === 'prospero') {
                    $recomendaciones[] = ['tipo' => 'verde', 'mensaje' => '¡Vas muy bien! Mantén el ritmo.', 'accion' => null];
                } elseif ($tipoEstado === 'estable') {
                    $recomendaciones[] = ['tipo' => 'amarillo', 'mensaje' => 'Necesitas generar ' . $moneda . ' ' . number_format($ventaDiariaReq, 0, ',', '.') . ' por día.', 'accion' => null];
                } else {
                    $recomendaciones[] = ['tipo' => 'rojo', 'mensaje' => 'Estás en riesgo. No cubres el punto de equilibrio.', 'accion' => null];
                }
            } else {
                if ($tipoEstado === 'prospero') {
                    $recomendaciones[] = ['tipo' => 'verde', 'mensaje' => '¡Excelente! Cerrarás el mes superando tu proyección.', 'accion' => null];
                } else {
                    $recomendaciones[] = ['tipo' => 'rojo', 'mensaje' => 'Quedan pocos días. Necesitas ' . $moneda . ' ' . number_format($ventaDiariaReq, 0, ',', '.') . ' por día.', 'accion' => null];
                }
            }

            if ($tipoEstado === 'riesgo') {
                $recomendaciones[] = ['tipo' => 'accion', 'mensaje' => 'Revisa tus gastos fijos: ' . $moneda . ' ' . number_format($gastosFijos, 0, ',', '.') . ' mensuales.',
                    'accion' => ['texto' => 'Reducir gastos', 'url' => '/configuracion/editar#gastos']];
            } elseif ($tipoEstado === 'prospero') {
                $recomendaciones[] = ['tipo' => 'accion', 'mensaje' => 'Las cosas van bien. Considera aumentar tu sueldo.',
                    'accion' => ['texto' => 'Ajustar sueldo', 'url' => '/configuracion/editar#sueldo']];
            }
        }

        $hayRecomendacionesNuevas = !session('recomendaciones_vistas_' . now()->month . '_' . now()->year);

        return ['recomendaciones' => $recomendaciones, 'tipoEstado' => $tipoEstado, 'hayRecomendacionesNuevas' => $hayRecomendacionesNuevas];
    }
}
