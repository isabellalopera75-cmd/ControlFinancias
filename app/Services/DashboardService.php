<?php

namespace App\Services;

use App\Models\Negocio;
use App\Models\ConfigEstrategica;
use App\Models\Item;
use App\Models\VentaDetalle;
use App\Models\MetaMensual;
use App\Services\FinanzasService;
use App\Services\MetaMensualService;
use App\Services\RecomendacionesService;

class DashboardService
{
    /**
     * @var MetaMensualService
     */
    protected $metaMensualService;

    /**
     * @var RecomendacionesService
     */
    protected $recomendacionesService;

    /**
     * DashboardService constructor.
     *
     * @param MetaMensualService $metaMensualService
     * @param RecomendacionesService $recomendacionesService
     */
    public function __construct(MetaMensualService $metaMensualService, RecomendacionesService $recomendacionesService)
    {
        $this->metaMensualService = $metaMensualService;
        $this->recomendacionesService = $recomendacionesService;
    }

    /**
     * Obtener todos los datos calculados para el Dashboard.
     *
     * @param Negocio $negocio
     * @param ConfigEstrategica $config
     * @return array
     */
    public function obtenerDatosDashboard(Negocio $negocio, ConfigEstrategica $config): array
    {
        $margenPonderado = 0; 
        $utilidadMes = 0;

        $gastosFijos = $negocio->gastosFijos()->where('activo', 1)->sum('monto');

        $ventasMes = $negocio->movimientosCaja()
            ->where('es_venta', true)
            ->whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->sum('monto');

        $gastosMes = $negocio->movimientosCaja()
            ->where('es_venta', false)
            ->whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->sum('monto');

        // ─────────────────────────────────────────────
        // LÓGICA POR TIPO DE NEGOCIO
        // ─────────────────────────────────────────────
        if ($negocio->esServicios()) {
            $margenPonderado = null;
            $costoVentasMes  = 0;

            $puntoEquilibrio = $gastosFijos + $config->sueldo_dueno;

            $avanceReal  = $ventasMes - $gastosMes;
            $utilidadMes = FinanzasService::utilidadMensual(
                $ventasMes, $gastosFijos, $gastosMes, $config->sueldo_dueno
            );

            $gastoDiario = $config->dias_operacion > 0
                ? ($gastosFijos + $gastosMes) / $config->dias_operacion
                : 0;

        } else {
            // REVENTA
            $margenPonderado = $this->calcularMargenPonderado($negocio, now()->month, now()->year);

            if ($margenPonderado <= 0) {
                $margenPonderado = $this->calcularMargenPromedioInventario($negocio);
            }

            $puntoEquilibrio = $margenPonderado > 0
                ? ($gastosFijos + $config->sueldo_dueno) / $margenPonderado
                : 0;

            $costoVentasMes = VentaDetalle::whereHas('movimientoCaja', function ($q) use ($negocio) {
                $q->where('negocio_id', $negocio->id)
                  ->whereMonth('fecha', now()->month)
                  ->whereYear('fecha', now()->year);
            })->sum('costo_total');

            $avanceReal  = $ventasMes - $costoVentasMes - $gastosMes;
            $utilidadMes = FinanzasService::utilidadMensual(
                $ventasMes, $gastosFijos, $gastosMes, $config->sueldo_dueno, $costoVentasMes
            );

            $gastoDiario = $config->dias_operacion > 0
                ? ($gastosFijos + $costoVentasMes + $gastosMes) / $config->dias_operacion
                : 0;
        }

        // ─────────────────────────────────────────────
        // DÍAS SUPERVIVENCIA
        // ─────────────────────────────────────────────
        $dineroDisponibleReal = $config->dinero_disponible + $utilidadMes;
        $diasSupervivencia    = $gastoDiario > 0 ? floor(max($dineroDisponibleReal, 0) / $gastoDiario) : 999;

        // ─────────────────────────────────────────────
        // META MENSUAL (Delegado a MetaMensualService)
        // ─────────────────────────────────────────────
        $metaMes = $this->metaMensualService->gestionarMetaMensual($negocio, $puntoEquilibrio, $config);

        // ─────────────────────────────────────────────
        // PORCENTAJE Y PROYECCIÓN
        // ─────────────────────────────────────────────
        $porcentajeAvance = $metaMes->meta > 0
            ? min(max(($avanceReal / $metaMes->meta) * 100, 0), 100)
            : 0;

        $diasTranscurridos = now()->day;
        $diasDelMes        = now()->daysInMonth;
        $diasRestantes     = $diasDelMes - $diasTranscurridos;

        $totalVentas = $negocio->movimientosCaja()
            ->where('es_venta', true)
            ->whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->count();

        $proyeccionCierre = $totalVentas >= 4 && $diasTranscurridos > 0
            ? ($ventasMes / $diasTranscurridos) * $diasDelMes
            : null;

        // ─────────────────────────────────────────────
        // BANNER MES ANTERIOR
        // ─────────────────────────────────────────────
        ['mostrarBanner' => $mostrarBanner, 'bannerData' => $bannerData] = $this->recomendacionesService->obtenerBanner($negocio);

        // ─────────────────────────────────────────────
        // ALERTA
        // ─────────────────────────────────────────────
        $porcentajeMesTranscurrido = ($diasTranscurridos / $diasDelMes) * 100;
        $alerta = $this->recomendacionesService->generarAlerta(
            $avanceReal, $puntoEquilibrio, $metaMes->meta, $diasRestantes, $porcentajeMesTranscurrido, $negocio->moneda
        );

        // ─────────────────────────────────────────────
        // MOVIMIENTOS
        // ─────────────────────────────────────────────
        $movimientos = $negocio->movimientosCaja()
            ->withCount(['ventasDetalle', 'comprasDetalle'])
            ->whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->orderBy('fecha', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // ─────────────────────────────────────────────
        // RECOMENDACIONES
        // ─────────────────────────────────────────────
        ['recomendaciones' => $recomendaciones, 'tipoEstado' => $tipoEstado, 'hayRecomendacionesNuevas' => $hayRecomendacionesNuevas] = $this->recomendacionesService->generarRecomendaciones(
            $negocio, $avanceReal, $puntoEquilibrio, $metaMes->meta, $gastosFijos, $diasRestantes, $negocio->moneda
        );

        // ─────────────────────────────────────────────
        // INVENTARIO (solo reventa)
        // ─────────────────────────────────────────────
        $stockBajo = $negocio->tieneInventario()
            ? Item::where('negocio_id', $negocio->id)->where('activo', true)
                  ->where('tiene_stock', true)->whereColumn('stock', '<=', 'stock_minimo')->get()
            : collect();

        $productosVendibles = $negocio->tieneInventario()
            ? Item::where('negocio_id', $negocio->id)->where('tipo', 'producto')
                  ->where('activo', true)->get(['id', 'nombre', 'precio_venta', 'costo_compra', 'stock'])
            : collect();

        $itemsConStock = $negocio->tieneInventario()
            ? Item::where('negocio_id', $negocio->id)->where('tiene_stock', true)
                  ->where('activo', true)->get(['id', 'nombre', 'stock', 'unidad_base'])
            : collect();

        return compact(
            'negocio', 'config', 'gastosFijos',
            'puntoEquilibrio', 'margenPonderado',
            'ventasMes', 'gastosMes', 'utilidadMes',
            'porcentajeAvance', 'avanceReal',
            'diasSupervivencia', 'movimientos',
            'metaMes', 'alerta',
            'mostrarBanner', 'bannerData',
            'proyeccionCierre',
            'recomendaciones', 'tipoEstado',
            'hayRecomendacionesNuevas',
            'stockBajo', 'productosVendibles', 'itemsConStock'
        );
    }

    /**
     * Calcular margen real ponderado de ventas.
     *
     * @param Negocio $negocio
     * @param int $mes
     * @param int $anio
     * @return float
     */
    private function calcularMargenPonderado(Negocio $negocio, int $mes, int $anio): float
    {
        $ventas = VentaDetalle::whereHas('movimientoCaja', function ($q) use ($negocio, $mes, $anio) {
            $q->where('negocio_id', $negocio->id)
              ->whereMonth('fecha', $mes)->whereYear('fecha', $anio);
        })->get();

        $totalVentas = $ventas->sum('subtotal');
        if ($totalVentas <= 0) return 0;

        return $ventas->sum(fn($v) => (($v->margen_real ?? 0) / 100) * $v->subtotal) / $totalVentas;
    }

    /**
     * Calcular margen real promedio del inventario.
     *
     * @param Negocio $negocio
     * @return float
     */
    private function calcularMargenPromedioInventario(Negocio $negocio): float
    {
        $items = Item::where('negocio_id', $negocio->id)
            ->where('tipo', 'producto')->where('activo', true)->get();

        if ($items->isEmpty()) return 0;

        $totalMargen = 0;
        $count = 0;

        foreach ($items as $item) {
            if ($item->precio_venta <= 0) continue;
            $totalMargen += ($item->precio_venta - $item->costo_compra) / $item->precio_venta;
            $count++;
        }

        return $count > 0 ? $totalMargen / $count : 0;
    }
}
