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
use App\Http\Requests\RegistrarCompraRequest;

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
    public function store(RegistrarCompraRequest $request)
    {
        $negocio = Auth::user()->negocio;

        DB::transaction(function () use ($request, $negocio) {

            // Pre-procesar items para asignar costo si es null (Usar el costo actual del item)
            $itemsProcesados = collect($request->items)->map(function($i) use ($negocio) {
                if (empty($i['costo_unitario']) && $i['costo_unitario'] !== "0" && $i['costo_unitario'] !== 0) {
                    $itemActual = Item::where('id', $i['item_id'])->where('negocio_id', $negocio->id)->first();
                    $i['costo_unitario'] = $itemActual ? $itemActual->costo_compra : 0;
                }
                return $i;
            })->all();

            // Calcular monto total de la compra
            $montoTotal = collect($itemsProcesados)->sum(fn($i) => $i['cantidad'] * $i['costo_unitario']);

            // Crear movimiento de caja (salida de dinero)
            $movCaja = MovimientoCaja::create([
                'negocio_id'  => $negocio->id,
                'monto'       => $montoTotal,
                'descripcion' => $request->descripcion ?? 'Compra de mercancía',
                'es_venta'    => false,
                'fecha'       => $request->fecha,
            ]);

            foreach ($itemsProcesados as $compra) {
                $item = Item::where('id', $compra['item_id'])
                            ->where('negocio_id', $negocio->id)
                            ->lockForUpdate()
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
                MovimientoInventario::create([
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

        // Redirigir según de donde venga
        if ($request->header('referer') && str_contains($request->header('referer'), 'inventario/entradas')) {
            return redirect()->route('inventario.entradas')
                             ->with('success', 'Entrada registrada correctamente.');
        }

        return redirect()->route('dashboard')
                         ->with('success', 'Compra registrada correctamente.');
    }
}