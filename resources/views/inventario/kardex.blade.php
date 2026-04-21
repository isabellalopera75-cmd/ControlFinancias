@extends('layouts.app')
@section('title', 'Kardex - ' . $item->nombre)

@section('content')

<div class="flex items-center justify-between mb-8">
    <div>
        <p class="text-[#9a9390] text-xs tracking-widest uppercase mb-1">Inventario · Kardex</p>
        <h1 class="text-[#2a2522] text-3xl">{{ $item->nombre }}</h1>
        <p class="text-[#9a9390] text-sm mt-1">{{ ucfirst($item->tipo) }} · {{ $item->unidad_base ?? $item->unidad }}</p>
    </div>
    <div class="flex gap-3">
        <form action="{{ route('inventario.reconstruir', $item->id) }}" method="POST"
            onsubmit="return confirm('¿Reconstruir el stock desde los movimientos?')">
            @csrf
            <button type="submit"
                class="flex items-center gap-2 bg-[#fff3cd] text-[#856404] text-sm px-4 py-2.5 rounded-xl hover:bg-[#ffe69c] transition">
                <i class="bi bi-arrow-clockwise"></i> Reconstruir stock
            </button>
        </form>
        <a href="{{ route('inventario.index') }}"
            class="flex items-center gap-2 bg-[#f0ede8] text-[#5a5250] text-sm px-4 py-2.5 rounded-xl hover:bg-[#e8e4e0] transition">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

@if(session('success'))
<div class="mb-5 bg-[#d6e8d0] border border-[#a8c8a0] text-[#2d4a35] px-5 py-3 rounded-xl text-sm flex items-center gap-2">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif

{{-- Cards resumen --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white border border-[#ede8e2] rounded-2xl p-5">
        <p class="text-[#9a9390] text-xs tracking-widest uppercase mb-1">Stock actual</p>
        <p class="text-[#2a2522] text-2xl font-medium">
            {{ number_format($item->stock, 2) }}
            <span class="text-sm font-normal text-[#9a9390]">{{ $item->unidad_base ?? $item->unidad }}</span>
        </p>
        @if($item->tiene_stock && $item->stock <= $item->stock_minimo)
            <p class="text-red-400 text-xs mt-1">⚠ Bajo stock mínimo ({{ $item->stock_minimo }})</p>
        @endif
    </div>
    <div class="bg-white border border-[#ede8e2] rounded-2xl p-5">
        <p class="text-[#9a9390] text-xs tracking-widest uppercase mb-1">Costo promedio</p>
        <p class="text-[#2a2522] text-2xl font-medium">
            {{ number_format($item->costo_compra, 2, ',', '.') }}
        </p>
    </div>
    <div class="bg-white border border-[#ede8e2] rounded-2xl p-5">
        <p class="text-[#9a9390] text-xs tracking-widest uppercase mb-1">Total movimientos</p>
        <p class="text-[#2a2522] text-2xl font-medium">{{ $movimientos->count() }}</p>
    </div>
</div>

{{-- Historial --}}
<div class="bg-white rounded-2xl shadow-sm border border-[#ede8e2] overflow-hidden">
    <div class="px-6 py-4 border-b border-[#f0ede8] flex items-center justify-between">
        <h2 class="text-[#2a2522] text-base font-medium">Historial de movimientos</h2>
        <button onclick="abrirAjuste()"
            class="flex items-center gap-1.5 bg-[#fff3cd] text-[#856404] text-xs px-3 py-1.5 rounded-lg hover:bg-[#ffe69c] transition">
            <i class="bi bi-plus-lg"></i> Nuevo ajuste
        </button>
    </div>

    @if($movimientos->isEmpty())
    <div class="p-10 text-center">
        <p class="text-[#9a9390] text-sm">No hay movimientos registrados para este ítem.</p>
    </div>
    @else
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-[#d6e8d0]">
                <th class="px-5 py-3 text-left text-[#2d4a35] text-xs tracking-widest uppercase font-medium">Fecha</th>
                <th class="px-5 py-3 text-left text-[#2d4a35] text-xs tracking-widest uppercase font-medium">Tipo</th>
                <th class="px-5 py-3 text-left text-[#2d4a35] text-xs tracking-widest uppercase font-medium">Cantidad</th>
                <th class="px-5 py-3 text-left text-[#2d4a35] text-xs tracking-widest uppercase font-medium">Costo unitario</th>
                <th class="px-5 py-3 text-left text-[#2d4a35] text-xs tracking-widest uppercase font-medium">Referencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movimientos as $mov)
            <tr class="border-b border-[#f0ede8] hover:bg-[#faf8f5] transition">
                <td class="px-5 py-3 text-[#9a9390] text-xs">
                    {{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}
                </td>
                <td class="px-5 py-3">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium
                        {{ $mov->tipo === 'entrada' ? 'bg-[#d6e8d0] text-[#2d4a35]' : '' }}
                        {{ $mov->tipo === 'salida'  ? 'bg-[#f2d8d8] text-[#8a3a3a]' : '' }}
                        {{ $mov->tipo === 'ajuste'  ? 'bg-[#fff3cd] text-[#856404]' : '' }}">
                        {{ ucfirst($mov->tipo) }}
                    </span>
                </td>
                <td class="px-5 py-3 font-medium
                    {{ $mov->tipo === 'entrada' ? 'text-[#4a7c59]' : ($mov->tipo === 'salida' ? 'text-red-400' : 'text-[#856404]') }}">
                    {{ $mov->tipo === 'entrada' ? '+' : ($mov->tipo === 'salida' ? '-' : '=') }}
                    {{ number_format($mov->cantidad, 3) }}
                    {{ $item->unidad_base ?? $item->unidad }}
                </td>
                <td class="px-5 py-3 text-[#2a2522]">
                    {{ number_format($mov->costo_unitario, 2, ',', '.') }}
                </td>
                <td class="px-5 py-3 text-[#9a9390] text-xs">
                    @if($mov->referencia_id)
                        <span class="bg-[#dcedf5] text-[#3a6a9a] px-2 py-1 rounded">
                            Caja #{{ $mov->referencia_id }}
                        </span>
                    @else
                        Ajuste manual
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- Modal ajuste --}}
<div id="modalAjuste"
    class="fixed inset-0 bg-gray-900/20 backdrop-blur-sm hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex justify-between items-center mb-5">
            <div>
                <p class="text-[#9a9390] text-xs tracking-widest uppercase mb-1">Kardex</p>
                <h2 class="text-[#2a2522] text-xl">Nuevo ajuste</h2>
            </div>
            <button onclick="cerrarAjuste()"
                class="w-8 h-8 flex items-center justify-center rounded-full bg-[#f5f3ef] text-[#9a9390] hover:bg-[#ede8e2] transition text-sm">✕</button>
        </div>
        <form action="{{ route('inventario.ajuste', $item->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-[#9a9390] text-xs tracking-widest uppercase mb-2">Tipo</label>
                <select name="tipo" required
                    class="w-full border border-[#e8e4e0] rounded-xl px-4 py-3 text-sm text-[#2a2522] focus:outline-none focus:ring-2 focus:ring-[#b8d8b0]">
                    <option value="entrada">Entrada</option>
                    <option value="salida">Salida</option>
                    <option value="ajuste">Ajuste exacto</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-[#9a9390] text-xs tracking-widest uppercase mb-2">Cantidad</label>
                <input type="number" name="cantidad" step="0.001" min="0.001" required
                    class="w-full border border-[#e8e4e0] rounded-xl px-4 py-3 text-sm text-[#2a2522] focus:outline-none focus:ring-2 focus:ring-[#b8d8b0]">
            </div>
            <div class="mb-4">
                <label class="block text-[#9a9390] text-xs tracking-widest uppercase mb-2">Costo unitario (opcional)</label>
                <input type="number" name="costo_unitario" step="0.01" min="0"
                    class="w-full border border-[#e8e4e0] rounded-xl px-4 py-3 text-sm text-[#2a2522] focus:outline-none focus:ring-2 focus:ring-[#b8d8b0]">
            </div>
            <div class="mb-6">
                <label class="block text-[#9a9390] text-xs tracking-widest uppercase mb-2">Fecha</label>
                <input type="date" name="fecha" required value="{{ date('Y-m-d') }}"
                    class="w-full border border-[#e8e4e0] rounded-xl px-4 py-3 text-sm text-[#2a2522] focus:outline-none focus:ring-2 focus:ring-[#b8d8b0]">
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="cerrarAjuste()"
                    class="px-5 py-2.5 bg-[#f5f3ef] text-[#9a9390] text-sm rounded-xl hover:bg-[#ede8e2] transition">Cancelar</button>
                <button type="submit"
                    class="px-5 py-2.5 bg-[#2d4a35] text-white text-sm font-medium rounded-xl hover:bg-[#3d5e45] transition">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirAjuste() {
        const modal = document.getElementById('modalAjuste');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function cerrarAjuste() {
        const modal = document.getElementById('modalAjuste');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endsection