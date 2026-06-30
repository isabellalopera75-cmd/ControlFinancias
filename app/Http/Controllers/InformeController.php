<?php
// Controlador que maneja la descarga de reportes contables y reportes en Excel/PDF.

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ReportesService;

class InformeController extends Controller
{
    protected $reportesService;

    public function __construct(ReportesService $reportesService)
    {
        $this->reportesService = $reportesService;
    }

    public function index(Request $request)
    {
        $datos = $this->reportesService->obtenerDatos($request, Auth::user()->negocio);
        return view('informes.index', $datos);
    }

    public function pdf(Request $request)
    {
        $datos = $this->reportesService->obtenerDatos($request, Auth::user()->negocio);
        $pdf = Pdf::loadView('informes.pdf', $datos)->setPaper('a4', 'portrait');
        $nombre = 'informe-' . $datos['tipo'] . '-' . $datos['fechaDesde'] . '-al-' . $datos['fechaHasta'] . '.pdf';
        return $pdf->download($nombre);
    }

    public function excel(Request $request)
    {
        $datos   = $this->reportesService->obtenerDatos($request, Auth::user()->negocio);
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