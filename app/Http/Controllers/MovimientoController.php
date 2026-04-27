<?php
// Controlador que registra y edita las ventas diarias y los gastos operativos del negocio.

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\VentaDetalle;
use App\Models\MovimientoCaja;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MovimientoController extends Controller
{
    // =====================================================
    // REGISTRAR VENTA
    // =====================================================
    public function registrarVenta(Request $request)
    {
        $negocio = Auth::user()->negocio;

        if ($negocio->esReventa()) {
            return $this->registrarVentaReventa($request, $negocio);
        }

        // SERVICIOS: monto libre
        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
        ]);

        MovimientoCaja::create([
            'negocio_id'  => $negocio->id,
            'monto'       => $request->monto,
            'descripcion' => $request->descripcion ?? 'Venta',
            'es_venta'    => true,
            'fecha'       => $request->fecha,
        ]);

        return redirect()->route('dashboard')
                         ->with('success', 'Venta registrada correctamente.');
    }

    // =====================================================
    // REGISTRAR VENTA REVENTA (por producto con stock)
    // =====================================================
    private function registrarVentaReventa(Request $request, $negocio)
    {
        $request->validate([
            'items'                   => 'required|array|min:1',
            'items.*.item_id'         => 'required|exists:items,id',
            'items.*.cantidad'        => 'required|numeric|min:0.001',
            'items.*.precio_unitario' => 'nullable|numeric|min:0',
            'fecha'                   => 'required|date',
            'descripcion'             => 'nullable|string|max:255',
        ]);

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

        $mensaje = 'Venta registrada correctamente.';
        if (!empty($advertencias)) {
            $mensaje .= ' | ' . implode(' | ', $advertencias);
        }

        return redirect()->route('dashboard')->with('success', $mensaje)->with('nueva_venta_id', $movCaja->id);
    }

    // =====================================================
    // REGISTRAR GASTO
    // =====================================================
    public function registrarGasto(Request $request)
    {
        $negocio = Auth::user()->negocio;

        $request->validate([
            'monto'       => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string|max:255',
            'fecha'       => 'required|date',
        ]);

        MovimientoCaja::create([
            'negocio_id'  => $negocio->id,
            'monto'       => $request->monto,
            'descripcion' => $request->descripcion,
            'es_venta'    => false,
            'fecha'       => $request->fecha,
        ]);

        return redirect()->route('dashboard')
                         ->with('success', 'Gasto registrado correctamente.');
    }

    // =====================================================
    // EDITAR MOVIMIENTO
    // =====================================================
    public function editar($id)
    {
        $negocio    = Auth::user()->negocio;
        $movimiento = MovimientoCaja::where('negocio_id', $negocio->id)
                                    ->findOrFail($id);

        return view('movimientos.editar', compact('movimiento'));
    }

    public function actualizar(Request $request, $id)
    {
        $negocio    = Auth::user()->negocio;
        $movimiento = MovimientoCaja::where('negocio_id', $negocio->id)
                                    ->findOrFail($id);

        if ($movimiento->es_venta && $movimiento->ventasDetalle()->exists()) {
            return back()->with('error', 'No se puede editar una venta con productos asociados. Debe eliminarla y volver a registrarla.');
        }
        
        if (!$movimiento->es_venta && $movimiento->comprasDetalle()->exists()) {
            return back()->with('error', 'No se puede editar una compra con productos asociados. Debe eliminarla y volver a registrarla.');
        }

        $request->validate([
            'monto'       => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string|max:255',
            'fecha'       => 'required|date',
        ]);

        $movimiento->update([
            'monto'       => $request->monto,
            'descripcion' => $request->descripcion,
            'fecha'       => $request->fecha,
        ]);

        return redirect()->route('dashboard')
                         ->with('success', 'Movimiento actualizado correctamente.');
    }

    public function eliminar($id)
    {
        $negocio    = Auth::user()->negocio;
        $movimiento = MovimientoCaja::where('negocio_id', $negocio->id)
                                    ->with(['ventasDetalle.item', 'comprasDetalle.item'])
                                    ->findOrFail($id);

        DB::transaction(function () use ($movimiento) {
            $itemIds = [];
            if ($movimiento->es_venta) {
                $itemIds = $movimiento->ventasDetalle()->pluck('item_id')->filter()->toArray();
            } else {
                $itemIds = $movimiento->comprasDetalle()->pluck('item_id')->filter()->toArray();
            }

            // Bloquear ítems afectados antes de alterar nada
            $itemsBloqueados = Item::whereIn('id', $itemIds)->lockForUpdate()->get();

            // Eliminar movimientos de inventario asociados (si no hay cascade)
            MovimientoInventario::where('referencia_id', $movimiento->id)->delete();

            $movimiento->delete();

            // Recalcular stock y costo para los items afectados
            foreach ($itemsBloqueados as $item) {
                $item->recalcularCostoYStock();
            }
        });

        return redirect()->route('dashboard')
                         ->with('success', 'Movimiento eliminado y stock restaurado correctamente.');
    }
}