@extends('layouts.app')
@section('title', 'Inventario')

@push('styles')
    @vite(['resources/css/inventario.css'])
@endpush

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">


{{-- HEADER --}}
<div class="max-w-6xl mx-auto flex items-center justify-between mb-6 px-4">
        <div>
            <p class="text-[#9a9390] text-xs tracking-widest uppercase mb-1">Módulo</p>
            <h1 class="text-[#2a2522] text-3xl leading-tight">
                Gestión de <em class="text-[#4a7c59]">Inventario</em>
            </h1>
        </div>
    <div class="flex gap-3">
        <a href="{{ route('inventario.create') }}"
            class="premium-button-emerald">
            <i class="bi bi-plus-lg"></i> Nuevo ítem
        </a>
    </div>
</div>

{{-- PESTAÑAS --}}
<div class="max-w-6xl mx-auto mb-6 px-4">
    <div class="flex gap-1 bg-[#f0ede8] p-1 rounded-xl w-fit">
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
</div>

{{-- ALERTA STOCK BAJO --}}
@if($stockBajo > 0)
<div class="max-w-6xl mx-auto mb-6 px-4" id="alertStockBajo">
    <div class="bg-red-50 border border-red-100 rounded-2xl px-5 py-2.5 flex items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center gap-3">
            <i class="bi bi-exclamation-triangle-fill text-red-500 text-lg"></i>
            <div>
                <p class="text-red-800 text-sm font-semibold">
                    {{ $stockBajo }} {{ $stockBajo === 1 ? 'ítem tiene' : 'ítems tienen' }} stock bajo el mínimo
                </p>
                <p class="text-red-600/80 text-xs">Considera realizar una compra pronto.</p>
            </div>
        </div>
        <button onclick="document.getElementById('alertStockBajo').remove()" 
            class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-red-100 text-red-400 transition-colors">
            <i class="bi bi-x-lg text-xs"></i>
        </button>
    </div>
</div>
@endif


{{-- FILTROS POR CATEGORÍA --}}
<div class="max-w-6xl mx-auto flex gap-2 mb-5 flex-wrap px-4">
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
<div class="max-w-6xl mx-auto px-4 mb-10">
    <div class="glass-card overflow-hidden">
        <div class="max-h-[600px] overflow-y-auto custom-scrollbar">
            @if($items->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 bg-[#f5f3ef] rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-box-seam text-[#b0a8a0] text-2xl"></i>
                </div>
                <p class="text-[#9a9390] text-sm">Aún no tienes ítems registrados.</p>
                <a href="{{ route('inventario.create') }}"
                    class="inline-block mt-4 premium-button-emerald mx-auto">
                    + Agregar primer ítem
                </a>
            </div>
            @else
            <table class="w-full text-sm border-collapse">
                <thead class="sticky top-0 z-10">
                    <tr class="text-white">
                        <th class="bg-[#2d4a35] px-5 py-4 text-center text-xs tracking-widest uppercase font-semibold rounded-tl-xl">Nombre</th>
                        <th class="bg-[#2d4a35] px-5 py-4 text-left text-xs tracking-widest uppercase font-semibold">Stock</th>
                        <th class="bg-[#2d4a35] px-5 py-4 text-left text-xs tracking-widest uppercase font-semibold">Costo</th>
                        <th class="bg-[#2d4a35] px-5 py-4 text-left text-xs tracking-widest uppercase font-semibold">Precio venta</th>
                        <th class="bg-[#2d4a35] px-5 py-4 text-left text-xs tracking-widest uppercase font-semibold">Margen</th>
                        <th class="bg-[#2d4a35] px-5 py-4 text-left text-xs tracking-widest uppercase font-semibold rounded-tr-xl">Acciones</th>
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
        @endphp

        <tr class="border-b border-slate-100 hover:bg-emerald-50/30 transition fila-item"
            data-tipo="{{ $item->tipo }}">

            <td class="px-5 py-3 text-[#2a2522] font-medium text-center border-r border-slate-100">
                {{ $item->nombre }}
                @if($stockBajoItem)
                    <span class="ml-1 inline-block w-2 h-2 bg-red-400 rounded-full" title="Stock bajo"></span>
                @endif
            </td>

            <td class="px-5 py-3 {{ $stockBajoItem ? 'text-red-500 font-bold' : 'text-slate-600' }} border-r border-slate-100">
                @if($item->tiene_stock)
                    {{ number_format($item->stock, 0) }} <span class="text-[0.65rem] {{ $stockBajoItem ? 'text-red-400' : 'text-slate-400' }}">UD</span>
                    @if($stockBajoItem)
                        <span class="text-[0.6rem] text-red-400 block font-normal">Mín: {{ number_format($item->stock_minimo, 0) }}</span>
                    @endif
                @else
                    <span class="text-slate-300 text-xs">—</span>
                @endif
            </td>

            <td class="px-5 py-3 text-slate-600 border-r border-slate-100">
                {{ $negocio->moneda }} {{ number_format($item->costo_compra, 0, ',', '.') }}
            </td>

            <td class="px-5 py-3 text-slate-600 border-r border-slate-100">
                {{ $negocio->moneda }} {{ number_format($item->precio_venta, 0, ',', '.') }}
            </td>

            <td class="px-5 py-3 border-r border-slate-100">
                <span class="font-bold {{ $margen >= 30 ? 'text-emerald-600' : ($margen >= 10 ? 'text-amber-600' : 'text-red-500') }}">
                    {{ $margen }}%
                </span>
            </td>

            <td class="px-5 py-3">
                <div class="flex gap-1.5">
                    <a href="{{ route('inventario.kardex', $item->id) }}"
                        class="bg-slate-100 text-slate-600 px-2.5 py-1.5 rounded-lg text-xs hover:bg-slate-200 transition"
                        title="Kardex">
                        <i class="bi bi-list-ul"></i>
                    </a>
                    <a href="{{ route('inventario.edit', $item->id) }}"
                        class="bg-slate-100 text-slate-600 px-2.5 py-1.5 rounded-lg text-xs hover:bg-slate-200 transition"
                        title="Editar">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button 
                        onclick="abrirAjuste({{ $item->id }}, '{{ $item->nombre }}', {{ $item->stock ?? 0 }})"
                        class="bg-amber-50 text-amber-700 px-2.5 py-1.5 rounded-lg text-xs hover:bg-amber-100 transition border border-amber-100"
                        title="Ajuste">
                        <i class="bi bi-sliders"></i>
                    </button>
                    <form action="{{ route('inventario.destroy', $item->id) }}" method="POST"
                        onsubmit="return confirm('¿Desactivar este ítem?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="bg-rose-50 text-rose-600 px-2.5 py-1.5 rounded-lg text-xs hover:bg-rose-100 transition border border-rose-100"
                            title="Eliminar">
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
    </div>
</div>

{{-- MODAL AJUSTE DE STOCK --}}
<div id="modalAjuste"
    class="fixed inset-0 bg-slate-900/40 backdrop-blur-md hidden items-center justify-center z-50">
    <div class="glass-card w-full max-w-md mx-4 p-8">
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
                <label class="premium-label">Fecha</label>
                <input type="date" name="fecha" required value="{{ date('Y-m-d') }}"
                    class="premium-input">
            </div>
            <div class="flex gap-3 justify-end pt-4">
                <button type="button" onclick="cerrarAjuste()"
                    class="premium-button-slate flex-1">
                    Cancelar
                </button>
                <button type="submit"
                    class="premium-button-emerald flex-1">
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