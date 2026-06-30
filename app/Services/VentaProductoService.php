<?php

namespace App\Services;

use App\Models\Item;
use App\Models\VentaDetalle;
use App\Models\MovimientoCaja;
use App\Models\MovimientoInventario;
use App\Models\Negocio;
use App\Http\Requests\RegistrarVentaRequest;
use Illuminate\Support\Facades\DB;

class VentaProductoService
{
    public function registrar(RegistrarVentaRequest $request, Negocio $negocio): array
    {
        $advertencias = [];
        $movCaja = null;

        DB::transaction(function () use ($request, $negocio, &$advertencias, &$movCaja) {

            $montoTotal = 0;
            foreach ($request->items as $linea) {
                $item        = Item::where('id', $linea['item_id'])
                                   ->where('negocio_id', $negocio->id)
                                   ->lockForUpdate()
                                   ->firstOrFail();
                $precio      = $linea['precio_unitario'] ?? $item->precio_venta;
                $montoTotal += $precio * $linea['cantidad'];
            }

            $movCaja = MovimientoCaja::create([
                'negocio_id'  => $negocio->id,
                'monto'       => $montoTotal,
                'descripcion' => $request->descripcion ?? 'Venta',
                'es_venta'    => true,
                'fecha'       => $request->fecha,
                'metodo_pago' => $request->metodo_pago ?? 'efectivo',
            ]);

            foreach ($request->items as $linea) {
                $item     = Item::where('id', $linea['item_id'])
                                ->where('negocio_id', $negocio->id)
                                ->lockForUpdate()
                                ->firstOrFail();
                $cantidad = $linea['cantidad'];
                $precio   = $linea['precio_unitario'] ?? $item->precio_venta;
                $costo    = $item->costo_compra;

                if ($item->stock < $cantidad) {
                    $advertencias[] = "Stock insuficiente de {$item->nombre}. Disponible: {$item->stock}";
                }

                $item->decrement('stock', $cantidad);

                MovimientoInventario::create([
                    'negocio_id'     => $negocio->id,
                    'item_id'        => $item->id,
                    'tipo'           => 'salida',
                    'cantidad'       => $cantidad,
                    'costo_unitario' => $costo,
                    'referencia_id'  => $movCaja->id,
                    'fecha'          => $request->fecha,
                ]);

                $subtotal   = $precio * $cantidad;
                $costoTotal = $costo * $cantidad;
                $markup     = $costo > 0 ? round((($precio - $costo) / $costo) * 100, 2) : null;
                $margenReal = $precio > 0 ? round((($precio - $costo) / $precio) * 100, 2) : null;

                VentaDetalle::create([
                    'movimiento_caja_id' => $movCaja->id,
                    'item_id'            => $item->id,
                    'cantidad'           => $cantidad,
                    'precio_unitario'    => $precio,
                    'costo_unitario'     => $costo,
                    'costo_total'        => $costoTotal,
                    'subtotal'           => $subtotal,
                    'markup'             => $markup,
                    'margen_real'        => $margenReal,
                ]);
            }
        });

        return ['movimiento' => $movCaja, 'advertencias' => $advertencias];
    }
}
