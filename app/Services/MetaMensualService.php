<?php

namespace App\Services;

use App\Models\Negocio;
use App\Models\MetaMensual;
use App\Models\ConfigEstrategica;

class MetaMensualService
{
    /**
     * Gestione la creación, auto-corrección y auto-sanación de la meta mensual.
     *
     * @param Negocio $negocio
     * @param float $puntoEquilibrio
     * @param ConfigEstrategica $config
     * @return MetaMensual
     */
    public function gestionarMetaMensual(Negocio $negocio, float $puntoEquilibrio, ConfigEstrategica $config): MetaMensual
    {
        $ultimaMeta = $negocio->metasMensuales()
            ->orderBy('anio', 'desc')->orderBy('mes', 'desc')->first();

        if ($ultimaMeta && ($ultimaMeta->mes != now()->month || $ultimaMeta->anio != now()->year)) {

            $ventasRealesAnterior = $negocio->movimientosCaja()
                ->where('es_venta', true)
                ->whereMonth('fecha', $ultimaMeta->mes)
                ->whereYear('fecha', $ultimaMeta->anio)
                ->sum('monto');

            $ultimaMeta->update(['ventas_real' => $ventasRealesAnterior]);

            $nuevaMeta = $ventasRealesAnterior >= $ultimaMeta->meta
                ? $ultimaMeta->meta * 1.10
                : $ultimaMeta->meta * 0.95;

            if ($nuevaMeta < $puntoEquilibrio) $nuevaMeta = $puntoEquilibrio * 1.10;

            $metaMes = MetaMensual::create([
                'negocio_id'       => $negocio->id,
                'mes'              => now()->month,
                'anio'             => now()->year,
                'meta'             => $nuevaMeta,
                'punto_equilibrio' => $puntoEquilibrio,
                'ventas_real'      => 0,
                'alerta'           => null,
            ]);

        } else {
            $metaMes = $negocio->metasMensuales()
                ->where('mes', now()->month)
                ->where('anio', now()->year)
                ->first();

            if (!$metaMes) {
                $metaBase = $ultimaMeta ? (float)$ultimaMeta->meta : (float)$config->ingresos_proyectados;
                $metaBase = $metaBase > 0 ? $metaBase : 1000000;

                $metaMes = MetaMensual::create([
                    'negocio_id'       => $negocio->id,
                    'mes'              => now()->month,
                    'anio'             => now()->year,
                    'meta'             => $metaBase,
                    'punto_equilibrio' => $puntoEquilibrio,
                    'ventas_real'      => 0,
                    'alerta'           => null,
                ]);
            }
        }

        // Corregir meta si está por debajo del PE
        if ($metaMes && $metaMes->meta < $puntoEquilibrio && $puntoEquilibrio > 0) {
            $metaMes->update([
                'meta'             => round($puntoEquilibrio * 1.10),
                'punto_equilibrio' => $puntoEquilibrio,
            ]);
            $metaMes->meta = round($puntoEquilibrio * 1.10);
        }

        // Auto-Sanación: Si la meta quedó inflada por un PE erróneo del pasado
        if ($metaMes && $puntoEquilibrio > 0) {
            $promedioHistorico = ($config->ventas_mes1 + $config->ventas_mes2 + $config->ventas_mes3) / 3;
            if ($promedioHistorico <= 0) $promedioHistorico = $puntoEquilibrio;
            
            $techoLogico = max($puntoEquilibrio * 1.70, $promedioHistorico * 1.70);
            
            if ($metaMes->meta > $techoLogico && $metaMes->meta > 1000) {
                $metaOptima = max($puntoEquilibrio * 1.10, $promedioHistorico * 1.10);
                $metaMes->update(['meta' => round($metaOptima)]);
                $metaMes->meta = round($metaOptima);
            }
        }

        return $metaMes;
    }
}
