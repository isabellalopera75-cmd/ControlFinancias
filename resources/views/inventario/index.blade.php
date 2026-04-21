@extends('layouts.app')
@section('title', 'Inventario')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
    .serif { font-family: 'Playfair Display', serif; }
    body, * { font-family: 'DM Sans', sans-serif; }
    .card-pastel { transition: transform .2s, box-shadow .2s; }
    .card-pastel:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.07); }
</style>

    {{-- HEADER --}}
<div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-[#9a9390] text-xs tracking-widests uppercase mb-1">Módulo</p>
            <h1 class="text-[#2a2522] text-3xl leading-tight">
                Gestión de <em class="text-[#4a7c59]">Inventario</em>
            </h1>
        </div>
    <div class="flex gap-3">
        <a href="{{ route('inventario.create') }}"
            class="flex items-center gap-2 bg-[#2d4a35] text-white text-sm font-medium px-5 py-2.5 rounded-xl hover:bg-[#3d5e45] transition">
            <i class="bi bi-plus-lg"></i> Nuevo ítem
        </a>
    </div>
</div>

{{-- PESTAÑAS --}}
<div class="flex gap-1 mb-6 bg-[#f0ede8] p-1 rounded-xl w-fit">
    <a href="{{ route('inventario.index') }}"
        class="px-5 py-2 rounded-lg text-sm font-medium transition-all duration-200
               bg-white text-[#2d4a35] shadow-sm">
        <i class="bi bi-box-seam mr-1.5"></i> Productos
    </a>
    <a href="{{ route('inventario.entradas') }}"
        class="px-5 py-2 rounded-lg text-sm font-medium transition-all duration-200
               text-[#9a9390] hover:text-[#2d4a35] hover:bg-white/60">
        <i class="bi bi-truck mr-1.5"></i> Entradas
    </a>
</div>

{{-- ALERTA STOCK BAJO --}}
@if($stockBajo > 0)
<div class="mb-6 bg-[#f2d8d8] border border-[#e0b0b0] rounded-2xl px-5 py-4 flex items-center gap-3">
    <i class="bi bi-exclamation-triangle-fill text-[#8a3a3a] text-xl"></i>
    <div>
        <p class="text-[#8a3a3a] text-sm font-semibold">
            {{ $stockBajo }} {{ $stockBajo === 1 ? 'ítem tiene' : 'ítems tienen' }} stock bajo el mínimo
        </p>
        <p class="text-[#8a3a3a] text-xs mt-0.5">Revisa los ítems marcados en rojo y considera hacer una compra.</p>
    </div>
</div>
@endif


{{-- FILTROS POR CATEGORÍA --}}
<div class="flex gap-2 mb-5 flex-wrap">
    <a href="{{ route('inventario.index') }}"
        class="px-4 py-1.5 rounded-xl text-xs font-medium transition-all
        {{ !request('filtro_categoria') ? 'bg-[#2d4a35] text-white' : 'bg-[#f5f3ef] text-[#9a9390] hover:bg-[#ede8e2]' }}">
        Todos
    </a>
    @foreach($categoriasExistentes as $cat)
    <a href="{{ route('inventario.index', ['filtro_categoria' => $cat]) }}"
        class="px-4 py-1.5 rounded-xl text-xs font-medium transition-all
        {{ request('filtro_categoria') === $cat ? 'bg-[#2d4a35] text-white' : 'bg-[#f5f3ef] text-[#9a9390] hover:bg-[#ede8e2]' }}">
        {{ $cat }}
    </a>
    @endforeach
</div>

{{-- TABLA DE ÍTEMS --}}
<div class="bg-white rounded-2xl shadow-sm border border-[#ede8e2] overflow-hidden">
    @if($items->isEmpty())
    <div class="p-12 text-center">
        <div class="w-14 h-14 bg-[#f5f3ef] rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="bi bi-box-seam text-[#b0a8a0] text-2xl"></i>
        </div>
        <p class="text-[#9a9390] text-sm">Aún no tienes ítems registrados.</p>
        <a href="{{ route('inventario.create') }}"
            class="inline-block mt-4 bg-[#2d4a35] text-white text-sm px-5 py-2.5 rounded-xl hover:bg-[#3d5e45] transition">
            + Agregar primer ítem
        </a>
    </div>
    @else
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-[#d6e8d0]">
                <th class="px-5 py-3 text-left text-[#2d4a35] text-xs tracking-widest uppercase font-medium">Nombre</th>
                <th class="px-5 py-3 text-left text-[#2d4a35] text-xs tracking-widest uppercase font-medium">Tipo</th>
                <th class="px-5 py-3 text-left text-[#2d4a35] text-xs tracking-widest uppercase font-medium">Stock</th>
                <th class="px-5 py-3 text-left text-[#2d4a35] text-xs tracking-widest uppercase font-medium">Costo</th>
                <th class="px-5 py-3 text-left text-[#2d4a35] text-xs tracking-widest uppercase font-medium">Precio venta</th>
                <th class="px-5 py-3 text-left text-[#2d4a35] text-xs tracking-widest uppercase font-medium">Margen</th>
                <th class="px-5 py-3 text-left text-[#2d4a35] text-xs tracking-widest uppercase font-medium rounded-tr-xl">Acciones</th>
            </tr>
        </thead>
        <tbody>
@foreach($items as $item)
@php
    $stockBajoItem = $item->tiene_stock && $item->stock <= $item->stock_minimo;
    $costo = $item->costo_compra;
    $precio = $item->precio_venta;

    $margen = $precio > 0
        ? round((($precio - $costo) / $precio) * 100, 1)
        : 0;

    $unidadesEnteras = ['ud', 'caja', 'paquete'];
    $decimales = in_array($item->unidad, $unidadesEnteras) ? 0 : 2;
@endphp

<tr class="border-b border-[#f0ede8] hover:bg-[#faf8f5] transition fila-item"
    data-tipo="{{ $item->tipo }}">

    <td class="px-5 py-3 text-[#2a2522] font-medium">
        {{ $item->nombre }}
        @if($stockBajoItem)
            <span class="ml-1 inline-block w-2 h-2 bg-red-400 rounded-full" title="Stock bajo"></span>
        @endif
    </td>

    <td class="px-5 py-3">
        <span class="px-2.5 py-1 rounded-lg text-xs font-medium
            {{ $item->tipo === 'insumo' ? 'bg-[#fff3cd] text-[#856404]' : '' }}
            {{ $item->tipo === 'producto' ? 'bg-[#d6e8d0] text-[#2d4a35]' : '' }}
            {{ $item->tipo === 'servicio' ? 'bg-[#dce8f5] text-[#2d5a8a]' : '' }}">
            {{ ucfirst($item->tipo) }}
        </span>
    </td>

    <td class="px-5 py-3 {{ $stockBajoItem ? 'text-red-400 font-semibold' : 'text-[#2a2522]' }}">
        @if($item->tiene_stock)
            {{-- Forzamos a que diga 'ud' en lugar de $item->unidad --}}
            {{ number_format($item->stock, 0) }} <span class="text-xs {{ $stockBajoItem ? 'text-red-400' : 'text-[#9a9390]' }}">ud</span>
            
            @if($stockBajoItem)
                <span class="text-xs text-red-400 block font-normal">Mín: {{ number_format($item->stock_minimo, 0) }}</span>
            @endif
        @else
            <span class="text-[#b0a8a0] text-xs">Sin stock</span>
        @endif
    </td>

    <td class="px-5 py-3 text-[#2a2522]">
        {{ $negocio->moneda }} {{ number_format($item->costo_compra, 2, ',', '.') }}
    </td>

    <td class="px-5 py-3 text-[#2a2522]">
        {{ $negocio->moneda }} {{ number_format($item->precio_venta, 2, ',', '.') }}
    </td>

    <td class="px-5 py-3">
        <span class="font-medium {{ $margen >= 30 ? 'text-[#4a7c59]' : ($margen >= 10 ? 'text-[#856404]' : 'text-red-400') }}">
            {{ $margen }}%
        </span>
    </td>

    <td class="px-5 py-3">
        <div class="flex gap-1.5">

            {{-- ✅ SOLO inventario normal tiene kardex --}}
                <a href="{{ route('inventario.kardex', $item->id) }}"
                    class="bg-[#f0ede8] text-[#5a5250] px-2.5 py-1.5 rounded-lg text-xs hover:bg-[#e8e4e0] transition"
                    title="Kardex">
                    <i class="bi bi-list-ul"></i>
                </a>
                <a href="{{ route('inventario.edit', $item->id) }}"
                    class="btn btn-warning">
                    <i class="bi bi-pencil-square bg-[#f0ede8] text-[#5a5250] px-2.5 py-1.5 rounded-lg text-xs hover:bg-[#e8e4e0]"></i>
                </a>

            {{-- ✅ SOLO inventario normal tiene ajuste de stock --}}
                <button 
                    onclick="abrirAjuste({{ $item->id }}, '{{ $item->nombre }}', {{ $item->stock ?? 0 }})"
                    class="bg-[#fff3cd] text-[#856404] px-2.5 py-1.5 rounded-lg text-xs hover:bg-[#ffe69c] transition"
                    title="Ajuste de stock">
                    <i class="bi bi-sliders"></i>
                </button>

            {{-- 🗑 ELIMINAR (todos) --}}
            <form action="{{ route('inventario.destroy', $item->id) }}" method="POST"
                onsubmit="return confirm('¿Desactivar este ítem?')">
                @csrf @method('DELETE')
                <button type="submit"
                    class="bg-[#f2d8d8] text-[#8a3a3a] px-2.5 py-1.5 rounded-lg text-xs hover:bg-[#e0b0b0] transition"
                    title="Desactivar">
                    <i class="bi bi-trash"></i>
                </button>
            </form>

        </div>
    </td>
</tr>
@endforeach
</tbody>
    </table>
    @endif
</div>

{{-- MODAL AJUSTE DE STOCK --}}
<div id="modalAjuste"
    class="fixed inset-0 bg-gray-900/20 backdrop-blur-sm hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex justify-between items-center mb-5">
            <div>
                <p class="text-[#9a9390] text-xs tracking-widest uppercase mb-1">Inventario</p>
                <h2 class="text-[#2a2522] text-xl">Ajuste de stock</h2>
                <p id="ajusteNombreItem" class="text-[#4a7c59] text-sm font-medium mt-0.5"></p>
            </div>
            <button onclick="cerrarAjuste()"
                class="w-8 h-8 flex items-center justify-center rounded-full bg-[#f5f3ef] text-[#9a9390] hover:bg-[#ede8e2] transition text-sm">✕</button>
        </div>

        <form id="formAjuste" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-[#9a9390] text-xs tracking-widest uppercase mb-2">Tipo de ajuste</label>
                <select name="tipo" required
                    class="w-full border border-[#e8e4e0] rounded-xl px-4 py-3 text-sm text-[#2a2522] focus:outline-none focus:ring-2 focus:ring-[#b8d8b0]">
                    <option value="entrada">Entrada (suma stock)</option>
                    <option value="salida">Salida (resta stock)</option>
                    <option value="ajuste">Ajuste (establece stock exacto)</option>
                </select>
            </div>
            <div class="mb-4">
                <p class="text-sm text-gray-500 mb-2">
                Stock actual: <strong id="stockActual">0</strong>
                </p>
                <label id="labelCantidad" class="block text-[#9a9390] text-xs tracking-widest uppercase mb-2">Cantidad</label>
                <input type="number" name="cantidad" step="0.001" min="0.001" required
                    class="w-full border border-[#e8e4e0] rounded-xl px-4 py-3 text-sm text-[#2a2522] focus:outline-none focus:ring-2 focus:ring-[#b8d8b0]"
                    placeholder="0">
            </div>
            <div class="mb-4">
                <label class="block text-[#9a9390] text-xs tracking-widest uppercase mb-2">Costo unitario (opcional)</label>
                <input type="number" name="costo_unitario" step="0.01" min="0"
                    class="w-full border border-[#e8e4e0] rounded-xl px-4 py-3 text-sm text-[#2a2522] focus:outline-none focus:ring-2 focus:ring-[#b8d8b0]"
                    placeholder="0">
            </div>
            <div class="mb-6">
                <label class="block text-[#9a9390] text-xs tracking-widest uppercase mb-2">Fecha</label>
                <input type="date" name="fecha" required value="{{ date('Y-m-d') }}"
                    class="w-full border border-[#e8e4e0] rounded-xl px-4 py-3 text-sm text-[#2a2522] focus:outline-none focus:ring-2 focus:ring-[#b8d8b0]">
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="cerrarAjuste()"
                    class="px-5 py-2.5 bg-[#f5f3ef] text-[#9a9390] text-sm rounded-xl hover:bg-[#ede8e2] transition">
                    Cancelar
                </button>
                <button type="submit"
                    class="px-5 py-2.5 bg-[#2d4a35] text-white text-sm font-medium rounded-xl hover:bg-[#3d5e45] transition">
                    Guardar ajuste
                </button>
            </div>
        </form>
    </div>
</div>
{{-- MODAL IMPORTAR --}}
<div id="modalImportar"
    class="fixed inset-0 bg-gray-900/20 backdrop-blur-sm hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex justify-between items-center mb-5">
            <div>
                <p class="text-[#9a9390] text-xs tracking-widest uppercase mb-1">Inventario</p>
                <h2 class="text-[#2a2522] text-xl">Importar productos</h2>
            </div>
            <button onclick="cerrarImportar()"
                class="w-8 h-8 flex items-center justify-center rounded-full bg-[#f5f3ef] text-[#9a9390] hover:bg-[#ede8e2] transition text-sm">✕</button>
        </div>

        <div class="bg-[#f0f7f2] border border-[#c8e0cc] rounded-xl px-4 py-3 mb-5">
            <p class="text-[#2d4a35] text-xs leading-relaxed">
                <i class="bi bi-info-circle mr-1"></i>
                Descarga primero la <strong>Plantilla Excel</strong>, completa los datos y luego súbela aquí.
                Los campos con <strong>*</strong> son obligatorios.
            </p>
        </div>

        <form action="{{ route('inventario.importar') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-5">
                <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-2">
                    Archivo Excel (.xlsx, .xls, .csv)
                </label>
                <input type="file" name="archivo" accept=".xlsx,.xls,.csv" required
                    class="w-full px-4 py-2.5 bg-[#faf9f7] border border-[#e8e4e0] rounded-xl text-[#2a2522] text-sm focus:outline-none focus:border-[#a8c8a0]">
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="cerrarImportar()"
                    class="flex-1 py-2.5 bg-[#f5f3ef] text-[#9a9390] text-sm rounded-xl hover:bg-[#ede8e2] transition">
                    Cancelar
                </button>
                <button type="submit"
                    class="flex-1 py-2.5 bg-[#2d4a35] text-white text-sm font-medium rounded-xl hover:bg-[#3d5e45] transition">
                    <i class="bi bi-upload mr-1"></i> Importar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let stockActualGlobal = 0;

    function abrirAjuste(itemId, nombre, stock) {
        stockActualGlobal = stock;

        document.getElementById('ajusteNombreItem').textContent = nombre;
        document.getElementById('stockActual').textContent = stock;

        document.getElementById('formAjuste').action = `/inventario/${itemId}/ajuste`;

        const modal = document.getElementById('modalAjuste');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        actualizarTextoCantidad();
        document.querySelector('input[name="cantidad"]').value = '';
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelector('select[name="tipo"]').addEventListener('change', actualizarTextoCantidad);
    });

    function actualizarTextoCantidad() {
        const tipo = document.querySelector('select[name="tipo"]').value;
        const label = document.getElementById('labelCantidad');
        const input = document.querySelector('input[name="cantidad"]');

        if (tipo === 'entrada') {
            label.textContent = 'Cantidad a agregar';
            input.placeholder = `Ej: 5`;
        }

        if (tipo === 'salida') {
            label.textContent = 'Cantidad a retirar';
            input.placeholder = `Máx: ${stockActualGlobal}`;
        }

        if (tipo === 'ajuste') {
            label.textContent = 'Nuevo stock total';
            input.placeholder = `Actual: ${stockActualGlobal}`;
        }
    }

    document.getElementById('formAjuste').addEventListener('submit', function(e) {
    const tipo = document.querySelector('select[name="tipo"]').value;
    const cantidad = parseFloat(document.querySelector('input[name="cantidad"]').value);

    if (tipo === 'salida' && cantidad > stockActualGlobal) {
        alert('No puedes retirar más de lo que hay en stock');
        e.preventDefault();
    }
    });

    function cerrarAjuste() {
        const modal = document.getElementById('modalAjuste');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function abrirImportar() {
        const m = document.getElementById('modalImportar');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
    function cerrarImportar() {
        const m = document.getElementById('modalImportar');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }

</script>
@endsection