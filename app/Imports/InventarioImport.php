<?php

namespace App\Imports;

use App\Models\Item;
use App\Models\MovimientoInventario;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class InventarioImport implements ToCollection, WithHeadingRow
{
    public int $importados = 0;
    public int $errores    = 0;
    public array $mensajesError = [];

    public function collection(Collection $rows)
    {
        $negocio = Auth::user()->negocio;

        // Validar primero si hay duplicados en el archivo o si ya existen en la base de datos
        $nombresCSV = [];
        foreach ($rows as $row) {
            $nombre = trim($row['nombre'] ?? '');
            if (!empty($nombre)) {
                $nombresCSV[] = $nombre;
            }
        }

        if (!empty($nombresCSV)) {
            $duplicadosEnBD = Item::where('negocio_id', $negocio->id)
                ->whereIn('nombre', $nombresCSV)
                ->where('activo', true)
                ->exists();

            if ($duplicadosEnBD) {
                throw new \Exception('Revise nuevamente el archivo porque hay artículos que ya están registrados en el inventario.');
            }

            if (count($nombresCSV) !== count(array_unique($nombresCSV))) {
                throw new \Exception('Revise nuevamente el archivo porque contiene artículos con el mismo nombre repetidos entre sí.');
            }
        }

        foreach ($rows as $index => $row) {
            $fila = $index + 2; // +2 porque row 1 es header

            $nombre      = trim($row['nombre'] ?? '');
            $costoCompra = floatval($row['costo_compra'] ?? 0);
            $precioVenta = floatval($row['precio_venta'] ?? 0);

            // Validar campos obligatorios
            if (empty($nombre) || $costoCompra <= 0 || $precioVenta <= 0) {
                $this->errores++;
                $this->mensajesError[] = "Fila {$fila}: nombre, costo_compra y precio_venta son obligatorios.";
                continue;
            }

            $stockInicial = floatval($row['stock_inicial'] ?? 0);
            $stockMinimo  = floatval($row['stock_minimo'] ?? 0);
            $categoria    = trim($row['categoria'] ?? '');

            $item = Item::create([
                'negocio_id'          => $negocio->id,
                'nombre'              => $nombre,
                'categoria'           => $categoria ?: null,
                'tipo'                => 'producto',
                'costo_compra'        => $costoCompra,
                'precio_venta'        => $precioVenta,
                'stock'               => $stockInicial,
                'stock_minimo'        => $stockMinimo,
                'unidad'              => 'ud',
                'unidad_base'         => 'ud',
                'factor_conversion'   => 1,
                'presentacion_compra' => 'unidad',
                'unidades_por_caja'   => null,
                'activo'              => true,
                'tiene_stock'         => true,
            ]);

            if ($stockInicial > 0) {
                MovimientoInventario::create([
                    'negocio_id'     => $negocio->id,
                    'item_id'        => $item->id,
                    'tipo'           => 'entrada',
                    'cantidad'       => $stockInicial,
                    'costo_unitario' => $costoCompra,
                    'referencia_id'  => null,
                    'fecha'          => now()->toDateString(),
                ]);
            }

            $this->importados++;
        }
    }
}