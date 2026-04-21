<?php
// Controlador encargado de registrar la entrada de mercancía para negocios de reventa.

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\CompraDetalle;
use App\Models\MovimientoCaja;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    // Formulario registrar compra
    public function create()
    {
        $negocio = Auth::user()->negocio;

        $items = Item::where('negocio_id', $negocio->id)
                     ->where('activo', true)
                     ->whereIn('tipo', ['insumo', 'producto'])
                     ->orderBy('nombre')
                     ->get();

        return view('compras.create', compact('items', 'negocio'));
    }

    // Guardar compra
    public function store(Request $request)
    {
    $negocio = Auth::user()->negocio;

    // Adaptamos el request si viene del formulario simple del Dashboard
    if ($request->has('item_id')) {
        $request->merge([
            'items' => [
                [
                    'item_id' => $request->item_id,
                    'cantidad' => $request->cantidad,
                    'costo_unitario' => $request->costo_unitario,
                ]
            ],
            'fecha' => $request->fecha ?? now()->toDateString(),
            'descripcion' => $request->referencia ?? 'Compra rápida Dashboard'
        ]);
    }

    $request->validate([
        'fecha'                         => 'required|date',
        'descripcion'                   => 'nullable|string|max:255',
        'items'                         => 'required|array|min:1',
        'items.*.item_id'               => 'required|exists:items,id',
        'items.*.cantidad'              => 'required|numeric|min:0.001',
        'items.*.costo_unitario'        => 'required|numeric|min:0',
    ]);

        DB::transaction(function () use ($request, $negocio) {

            // Calcular monto total de la compra
            $montoTotal = collect($request->items)
                ->sum(fn($i) => $i['cantidad'] * $i['costo_unitario']);

            // Crear movimiento de caja (salida de dinero)
            $movCaja = MovimientoCaja::create([
                'negocio_id'  => $negocio->id,
                'monto'       => $montoTotal,
                'descripcion' => $request->descripcion ?? 'Compra de mercancía',
                'es_venta'    => false,
                'fecha'       => $request->fecha,
            ]);

            foreach ($request->items as $compra) {
                $item = Item::where('id', $compra['item_id'])
                            ->where('negocio_id', $negocio->id)
                            ->firstOrFail();

                $cantidad      = $compra['cantidad'];
                $costoNuevo    = $compra['costo_unitario'];
                $stockActual   = $item->stock;
                $costoActual   = $item->costo_compra;

                // Recalcular costo promedio ponderado
                if (($stockActual + $cantidad) > 0) {
                    $costoPromedio = (($stockActual * $costoActual) + ($cantidad * $costoNuevo))
                                   / ($stockActual + $cantidad);
                } else {
                    $costoPromedio = $costoNuevo;
                }

                // Actualizar stock y costo promedio del ítem
                $item->update([
                    'stock'        => $stockActual + $cantidad,
                    'costo_compra' => round($costoPromedio, 4),
                ]);

                // Crear movimiento de inventario (entrada)
                $movInv = MovimientoInventario::create([
                    'negocio_id'     => $negocio->id,
                    'item_id'        => $item->id,
                    'tipo'           => 'entrada',
                    'cantidad'       => $cantidad,
                    'costo_unitario' => $costoNuevo,
                    'referencia_id'  => $movCaja->id,
                    'fecha'          => $request->fecha,
                ]);

                // Crear detalle de compra
                CompraDetalle::create([
                    'movimiento_caja_id' => $movCaja->id,
                    'item_id'            => $item->id,
                    'cantidad'           => $cantidad,
                    'costo_unitario'     => $costoNuevo,
                ]);
            }
        });

        return redirect()->route('dashboard')
                         ->with('success', 'Compra registrada correctamente.');
    }
}