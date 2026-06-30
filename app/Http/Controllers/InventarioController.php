<?php
// Controlador que gestiona los productos, stock e historial de movimientos del inventario.

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\InventarioExport;
use App\Imports\InventarioImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\AjustarInventarioRequest;
use App\Http\Requests\ImportarInventarioRequest;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        $negocio = Auth::user()->negocio;
        abort_if(!$negocio->tieneInventario(), 403);

        // Para el filtro: traemos las categorías únicas
$categoriasExistentes = Item::where('negocio_id', $negocio->id)
                            ->whereNotNull('categoria')
                            ->select('categoria')
                            ->distinct()
                            ->pluck('categoria');

        // Consulta base
        $query = Item::where('negocio_id', $negocio->id)->where('activo', true);

        // Si el usuario eligió una categoría, filtramos
        if ($request->filled('filtro_categoria')) {
            $query->where('categoria', $request->filtro_categoria);
        }

        $items = $query->orderBy('nombre')->paginate(20)->withQueryString();

        $stockBajo = Item::where('negocio_id', $negocio->id)
                        ->where('activo', true)
                        ->where('tiene_stock', true)
                        ->whereColumn('stock', '<=', 'stock_minimo')
                        ->count();

        return view('inventario.index', compact('items', 'negocio', 'stockBajo', 'categoriasExistentes'));
    }

    public function entradas()
    {
        $negocio = Auth::user()->negocio;
        abort_if(!$negocio->tieneInventario(), 403);

        $items = Item::where('negocio_id', $negocio->id)
                    ->where('activo', true)
                    ->where('tipo', 'producto')
                    ->orderBy('nombre')
                    ->get();

        // Historial de compras agrupado por movimiento de caja
        $compras = \App\Models\MovimientoCaja::where('negocio_id', $negocio->id)
                    ->where('es_venta', false)
                    ->with(['comprasDetalle.item'])
                    ->orderBy('fecha', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);

        return view('inventario.entradas', compact('negocio', 'items', 'compras'));
    }

        public function create()
        {
            $negocio = Auth::user()->negocio;
            abort_if(!$negocio->tieneInventario(), 403);

            // Esta consulta es más estricta: ignora nulos, vacíos y espacios
            $categoriasExistentes = Item::where('negocio_id', $negocio->id)
                        ->whereNotNull('categoria')
                        ->where('categoria', '<>', '')
                        ->where('categoria', '<>', ' ')
                        ->distinct()
                        ->orderBy('categoria', 'asc')
                        ->pluck('categoria');

            return view('inventario.create', compact('negocio', 'categoriasExistentes'));
        }

    public function store(Request $request)
    {
        $negocio = Auth::user()->negocio;
        abort_if(!$negocio->tieneInventario(), 403);

        $request->validate([
            'nombre'               => 'required|string|max:255',
            'precio_venta'         => 'required|numeric|min:0.01',
            'costo_unidad'         => 'required|numeric|min:0.01|lte:precio_venta',
            'unidad_compra'        => 'required|in:unidad,caja',
            'unidades_por_paquete' => 'nullable|integer|min:1',
            'stock_inicial'        => 'nullable|numeric|min:0',
            'stock_minimo'         => 'nullable|numeric|min:0',
        ], [
            'costo_unidad.lte' => 'El costo no puede ser mayor al precio de venta.',
        ]);

        $existe = Item::where('negocio_id', $negocio->id)
                      ->where('nombre', $request->nombre)
                      ->where('activo', true)
                      ->exists();

        if ($existe) {
            return back()->withErrors(['nombre' => 'Ya existe un producto con este nombre en el inventario.'])->withInput();
        }

        $factorEmpaque = $request->unidad_compra === 'unidad'
            ? 1
            : (intval($request->unidades_por_paquete) ?: 1);

        $stockReal = floatval($request->stock_inicial ?? 0) * $factorEmpaque;

        $item = Item::create([
            'negocio_id'          => $negocio->id,
            'nombre'              => $request->nombre,
            'categoria'           => $request->categoria ?? null,  // nullable
            'tipo'                => 'producto',
            'costo_compra'        => $request->costo_unidad,
            'precio_venta'        => $request->precio_venta,
            'stock'               => $stockReal,
            'stock_minimo'        => $request->stock_minimo ?? 0,
            'unidad'              => 'ud',
            'unidad_base'         => 'ud',
            'factor_conversion'   => 1,   // ← faltaba esto
            'presentacion_compra' => $request->unidad_compra,
            'unidades_por_caja'   => $request->unidad_compra !== 'unidad' ? $factorEmpaque : null,
            'activo'              => true,
            'tiene_stock'         => true,
        ]);

        if ($stockReal > 0) {
            MovimientoInventario::create([
                'negocio_id'     => $negocio->id,
                'item_id'        => $item->id,
                'tipo'           => 'entrada',
                'cantidad'       => $stockReal,
                'costo_unitario' => $request->costo_unidad,
                'referencia_id'  => null,
                'fecha'          => now()->toDateString(),
            ]);
        }

        return redirect()->route('inventario.index')
                         ->with('success', '¡Producto creado correctamente!');
    }

    public function edit($id)
    {
        $item = Item::findOrFail($id);
        $this->autorizarItem($item);
        $negocio = Auth::user()->negocio;
        return view('inventario.edit', compact('item', 'negocio'));
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        $this->autorizarItem($item);

        $request->validate([
            'nombre'       => 'required|string|max:255',
            'precio_venta' => 'required|numeric|min:0.01',
            'costo_compra' => 'required|numeric|min:0.01|lte:precio_venta',
            'stock_minimo' => 'required|numeric|min:0',
        ], [
            'costo_compra.lte' => 'El costo no puede ser mayor al precio de venta.',
        ]);

        $existe = Item::where('negocio_id', Auth::user()->negocio->id)
                      ->where('nombre', $request->nombre)
                      ->where('activo', true)
                      ->where('id', '!=', $item->id)
                      ->exists();

        if ($existe) {
            return back()->withErrors(['nombre' => 'Ya existe otro producto con este nombre en el inventario.'])->withInput();
        }

        $item->update([
            'nombre'       => $request->nombre,
            'costo_compra' => $request->costo_compra,
            'precio_venta' => $request->precio_venta,
            'stock_minimo' => $request->stock_minimo,
            'categoria'    => $request->categoria,
        ]);

        return redirect()->route('inventario.index')
                         ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $this->autorizarItem($item);
        $item->update(['activo' => false]);
        return redirect()->route('inventario.index')
                         ->with('success', 'Producto eliminado correctamente.');
    }

    public function ajuste(AjustarInventarioRequest $request, $id)
    {
        $item = Item::findOrFail($id);
        $this->autorizarItem($item);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $item) {
            $itemBloqueado = Item::where('id', $item->id)->lockForUpdate()->firstOrFail();

            MovimientoInventario::create([
                'negocio_id'     => $itemBloqueado->negocio_id,
                'item_id'        => $itemBloqueado->id,
                'tipo'           => $request->tipo,
                'cantidad'       => $request->cantidad,
                'costo_unitario' => $itemBloqueado->costo_compra,
                'referencia_id'  => null,
                'fecha'          => now()->toDateString(),
            ]);

            $itemBloqueado->recalcularCostoYStock();
        });

        return back()->with('success', 'Ajuste realizado correctamente.');
    }

    public function kardex($id)
    {
        $item = Item::findOrFail($id);
        $this->autorizarItem($item);

        $movimientos = $item->movimientosInventario()
                            ->orderBy('fecha', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate(20);

        $totalMovimientos = $item->movimientosInventario()->count();

        return view('inventario.kardex', compact('item', 'movimientos', 'totalMovimientos'));
    }

    public function reconstruirStock($id)
    {
        $item = Item::findOrFail($id);
        $this->autorizarItem($item);

        \Illuminate\Support\Facades\DB::transaction(function () use ($item) {
            $itemBloqueado = Item::where('id', $item->id)->lockForUpdate()->firstOrFail();
            $itemBloqueado->recalcularCostoYStock();
        });

        return back()->with('success', 'Stock y costo recalculados desde el historial.');
    }

    private function autorizarItem(Item $item): void
    {
        abort_if($item->negocio_id !== Auth::user()->negocio->id, 403);
    }

        public function exportarPlantilla()
    {
        return Excel::download(new InventarioExport(), 'plantilla_inventario.xlsx');
    }

        public function importar(ImportarInventarioRequest $request)
    {
        $negocio = Auth::user()->negocio;
        abort_if(!$negocio->tieneInventario(), 403);

        try {
            $import = new InventarioImport();
            Excel::import($import, $request->file('archivo'));

            if ($import->importados === 0 && $import->errores > 0) {
                $mensaje = "⚠ El archivo Excel no cumple con el formato o los datos requeridos.";
                return redirect()->route('inventario.index')->with('error', $mensaje);
            }

            $mensaje = "✅ {$import->importados} productos importados correctamente.";

            if ($import->errores > 0) {
                $mensaje .= " ⚠ {$import->errores} filas tenían errores de formato y no se importaron.";
            }

            return redirect()->route('inventario.index')->with('success', $mensaje);
        } catch (\Exception $e) {
            return redirect()->route('inventario.index')->with('error', $e->getMessage());
        }
    }
}