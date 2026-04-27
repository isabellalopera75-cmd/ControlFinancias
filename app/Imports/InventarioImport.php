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

            $nombre = trim($row['nombre'] ?? '');
            $rawCosto = $row['costo_compra'] ?? '';
            $rawPrecio = $row['precio_venta'] ?? '';
            $rawStock = $row['stock_inicial'] ?? '0';
            $rawMinimo = $row['stock_minimo'] ?? '0';

            if (empty($nombre)) {
                $this->errores++;
                $this->mensajesError[] = "Fila {$fila}: el nombre es obligatorio.";
                continue;
            }

            if (!is_numeric($rawCosto) || !is_numeric($rawPrecio)) {
                $this->errores++;
                $this->mensajesError[] = "Fila {$fila}: el costo de compra y precio de venta deben ser numéricos.";
                continue;
            }

            $costoCompra = floatval($rawCosto);
            $precioVenta = floatval($rawPrecio);

            if ($costoCompra <= 0 || $precioVenta <= 0) {
                $this->errores++;
                $this->mensajesError[] = "Fila {$fila}: el costo y el precio deben ser mayores a 0.";
                continue;
            }

            if ($costoCompra > $precioVenta) {
                $this->errores++;
                $this->mensajesError[] = "Fila {$fila}: el costo ({$costoCompra}) no puede ser mayor al precio de venta ({$precioVenta}).";
                continue;
            }

            if (!is_numeric($rawStock) || !is_numeric($rawMinimo)) {
                $this->errores++;
                $this->mensajesError[] = "Fila {$fila}: el stock inicial y stock mínimo deben ser numéricos.";
                continue;
            }

            $stockInicial = floatval($rawStock);
            $stockMinimo  = floatval($rawMinimo);
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