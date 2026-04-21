<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventarioExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function collection()
    {
        // Plantilla vacía con una fila de ejemplo
        return collect([
            [
                'Coca-Cola 250ml',  // nombre
                'Bebidas',          // categoria
                '2500',             // costo_compra
                '3500',             // precio_venta
                '100',              // stock_inicial
                '10',               // stock_minimo
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'nombre *',
            'categoria',
            'costo_compra *',
            'precio_venta *',
            'stock_inicial',
            'stock_minimo',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header verde
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '2D4A35']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        // Fila de ejemplo en gris claro
        $sheet->getStyle('A2:F2')->applyFromArray([
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F5F3EF']],
            'font' => ['color' => ['rgb' => '9A9390'], 'italic' => true],
        ]);

        // Nota en fila 4
        $sheet->setCellValue('A4', '⚠ Los campos con * son obligatorios. La fila 2 es un ejemplo, puedes eliminarla.');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '856404']],
        ]);
        $sheet->mergeCells('A4:F4');
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 20,
            'C' => 18,
            'D' => 18,
            'E' => 18,
            'F' => 18,
        ];
    }
}