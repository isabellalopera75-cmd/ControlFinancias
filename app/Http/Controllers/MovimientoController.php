<?php
// Controlador que registra y edita las ventas diarias y los gastos operativos del negocio.

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\MovimientoCaja;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RegistrarVentaRequest;
use App\Services\VentaProductoService;
use App\Services\VentaServicioService;

class MovimientoController extends Controller
{
    protected $ventaProductoService;
    protected $ventaServicioService;

    public function __construct(VentaProductoService $ventaProductoService, VentaServicioService $ventaServicioService)
    {
        $this->ventaProductoService = $ventaProductoService;
        $this->ventaServicioService = $ventaServicioService;
    }

    // =====================================================
    // REGISTRAR VENTA
    // =====================================================
    public function registrarVenta(RegistrarVentaRequest $request)
    {
        $negocio = Auth::user()->negocio;

        if ($negocio->esReventa()) {
            $resultado = $this->ventaProductoService->registrar($request, $negocio);
            $movCaja = $resultado['movimiento'];
            $advertencias = $resultado['advertencias'];
            
            $mensaje = 'Venta registrada correctamente.';
            if (!empty($advertencias)) {
                $mensaje .= ' | ' . implode(' | ', $advertencias);
            }
            
            return redirect()->route('dashboard')->with('success', $mensaje)->with('nueva_venta_id', $movCaja->id);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Venta registrada correctamente.')
            ->with('nueva_venta_id', $this->ventaServicioService->registrar($request, $negocio)->id);
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