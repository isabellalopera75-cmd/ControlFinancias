<?php
// Controlador que maneja la descarga de reportes contables y reportes en Excel/PDF.

namespace App\Http\Controllers;

use App\Models\MovimientoCaja;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class InformeController extends Controller
{
    private function obtenerDatos(Request $request)
    {
        $negocio   = Auth::user()->negocio;
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
        $current = \Carbon\Carbon::parse($fechaDesde);
        $hasta   = \Carbon\Carbon::parse($fechaHasta);
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

    public function index(Request $request)
    {
        $datos = $this->obtenerDatos($request);
        return view('informes.index', $datos);
    }

    public function pdf(Request $request)
    {
        $datos = $this->obtenerDatos($request);
        $pdf = Pdf::loadView('informes.pdf', $datos)->setPaper('a4', 'portrait');
        $nombre = 'informe-' . $datos['tipo'] . '-' . $datos['fechaDesde'] . '-al-' . $datos['fechaHasta'] . '.pdf';
        return $pdf->download($nombre);
    }

    public function excel(Request $request)
    {
        $datos   = $this->obtenerDatos($request);
        $negocio = $datos['negocio'];
        $tipo    = $datos['tipo'];

        $filename = 'informe-' . $tipo . '-' . $datos['fechaDesde'] . '-al-' . $datos['fechaHasta'] . '.xlsx';

        $rows = [];
        $rows[] = ['Informe ' . ucfirst($tipo) . ' — ' . $negocio->nombre_comercial];
        $rows[] = ['Desde: ' . $datos['fechaDesde'] . '  Hasta: ' . $datos['fechaHasta']];
        $rows[] = [];

        if (in_array($tipo, ['ventas', 'ambos'])) {
            $rows[] = ['VENTAS', ''];
            $rows[] = ['Fecha', 'Total Ventas (' . $negocio->moneda . ')'];
            foreach ($datos['diasRango'] as $dia) {
                $total = $datos['ventasPorDia'][$dia]->total ?? 0;
                $rows[] = [$dia, $total];
            }
            $rows[] = ['TOTAL VENTAS', $datos['totalVentas']];
            $rows[] = [];
        }

        if (in_array($tipo, ['gastos', 'ambos'])) {
            $rows[] = ['GASTOS', ''];
            $rows[] = ['Fecha', 'Total Gastos (' . $negocio->moneda . ')'];
            foreach ($datos['diasRango'] as $dia) {
                $total = $datos['gastosPorDia'][$dia]->total ?? 0;
                $rows[] = [$dia, $total];
            }
            $rows[] = ['TOTAL GASTOS', $datos['totalGastos']];
        }

        $export = new class($rows) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithStyles, \Maatwebsite\Excel\Concerns\ShouldAutoSize {
            protected $rows;
            public function __construct(array $rows) {
                $this->rows = $rows;
            }
            public function array(): array {
                return $this->rows;
            }
            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet) {
                $sheet->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14]]);
                $sheet->getStyle('A2')->applyFromArray(['font' => ['italic' => true, 'color' => ['rgb' => '666666']]]);
            }
        };

        return \Maatwebsite\Excel\Facades\Excel::download($export, $filename);
    }
}