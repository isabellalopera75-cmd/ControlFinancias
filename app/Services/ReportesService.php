<?php

namespace App\Services;

use App\Models\MovimientoCaja;
use App\Models\VentaDetalle;
use App\Models\Negocio;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportesService
{
    public function obtenerDatos(Request $request, Negocio $negocio): array
    {
        $tipo      = $request->get('tipo', 'ambos');
        $fechaDesde = $request->get('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->get('fecha_hasta', now()->toDateString());

        // ── Ventas por día ──────────────────────────────────────────
        $ventasPorDia = [];
        if (in_array($tipo, ['ventas', 'ambos'])) {
            $ventasPorDia = MovimientoCaja::where('negocio_id', $negocio->id)
                ->where('es_venta', true)
                ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
                ->selectRaw('DATE(fecha) as dia, SUM(monto) as total')
                ->groupBy('dia')
                ->orderBy('dia')
                ->get()
                ->keyBy('dia');
        }

        // ── Gastos (compras de inventario) por día ──────────────────
        $gastosPorDia = [];
        if (in_array($tipo, ['gastos', 'ambos'])) {
            $gastosPorDia = MovimientoCaja::where('negocio_id', $negocio->id)
                ->where('es_venta', false)
                ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
                ->selectRaw('DATE(fecha) as dia, SUM(monto) as total')
                ->groupBy('dia')
                ->orderBy('dia')
                ->get()
                ->keyBy('dia');
        }

        // ── Día con más ventas ──────────────────────────────────────
        $diaMasVentas = null;
        if (!empty($ventasPorDia) && count($ventasPorDia) > 0) {
            $diaMasVentas = $ventasPorDia->sortByDesc('total')->first();
        }

        // ── Producto más vendido ────────────────────────────────────
        $productoMasVendido = null;
        if ($negocio->esReventa() && in_array($tipo, ['ventas', 'ambos'])) {
            $productoMasVendido = VentaDetalle::whereHas('movimientoCaja', function ($q) use ($negocio, $fechaDesde, $fechaHasta) {
                $q->where('negocio_id', $negocio->id)
                  ->whereBetween('fecha', [$fechaDesde, $fechaHasta]);
            })
            ->with('item')
            ->selectRaw('item_id, SUM(cantidad) as total_cantidad, SUM(subtotal) as total_monto')
            ->groupBy('item_id')
            ->orderByDesc('total_monto')
            ->first();
        }

        // ── Totales ─────────────────────────────────────────────────
        $totalVentas = collect($ventasPorDia)->sum('total');
        $totalGastos = collect($gastosPorDia)->sum('total');

        // ── Todos los días en el rango ──────────────────────────────
        $diasRango = [];
        $current = Carbon::parse($fechaDesde);
        $hasta   = Carbon::parse($fechaHasta);
        while ($current->lte($hasta)) {
            $diasRango[] = $current->toDateString();
            $current->addDay();
        }

        return compact(
            'negocio', 'tipo', 'fechaDesde', 'fechaHasta',
            'ventasPorDia', 'gastosPorDia',
            'diaMasVentas', 'productoMasVendido',
            'totalVentas', 'totalGastos', 'diasRango'
        );
    }
}
