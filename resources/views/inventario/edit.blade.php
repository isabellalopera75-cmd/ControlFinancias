@extends('layouts.app')
@section('title', 'Editar Ítem')

@push('styles')
    @vite(['resources/css/inventario.css'])
@endpush

@section('content')

<div class="flex items-center justify-between mb-8 max-w-2xl mx-auto">
    <div>
        <p class="text-[#9a9390] text-xs tracking-widest uppercase mb-1">Inventario</p>
        <h1 class="text-[#2a2522] text-3xl">Editar <em class="text-[#4a7c59]">{{ $item->nombre }}</em></h1>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('inventario.kardex', $item->id) }}"
            class="premium-button-slate py-2.5">
            <i class="bi bi-list-ul"></i> Kardex
        </a>
        <a href="{{ route('inventario.index') }}"
            class="premium-button-slate py-2.5">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

@if($errors->any())
<div class="mb-5 bg-[#fdecea] border border-[#f0c0bc] text-[#7a2d2d] px-5 py-3 rounded-xl text-sm max-w-2xl mx-auto">
    <ul class="space-y-0.5">
        @foreach($errors->all() as $error)
            <li><i class="bi bi-exclamation-circle mr-1"></i>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="glass-card p-8 max-w-2xl mx-auto">

    {{-- Badge informativo --}}
    <div class="mb-6 flex items-center gap-2">
        <span class="bg-[#dce8f5] text-[#2d5a8a] text-xs font-medium px-3 py-1.5 rounded-lg">
            <i class="bi bi-shop mr-1"></i> Producto de reventa
        </span>
        <span class="text-[#b0a8a0] text-xs">Información general del producto</span>
    </div>

    <form action="{{ route('inventario.update', $item->id) }}" method="POST">
        @csrf 
        @method('PUT')

        {{-- Nombre --}}
        <div class="mb-5">
            <label class="premium-label">Nombre del Producto *</label>
            <input type="text" name="nombre" value="{{ old('nombre', $item->nombre) }}" required
                class="premium-input">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            {{-- Costo de compra --}}
            <div>
                <label class="premium-label">Costo de compra *</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">$</span>
                    <input type="number" name="costo_compra" id="costoInput"
                        value="{{ old('costo_compra', $item->costo_compra) }}" step="0.01" min="0" required
                        class="premium-input pl-8">
                </div>
            </div>

            {{-- Precio de venta --}}
            <div>
                <label class="premium-label">Precio de venta *</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">$</span>
                    <input type="number" name="precio_venta" id="precioInput"
                        value="{{ old('precio_venta', $item->precio_venta) }}" step="0.01" min="0" required
                        class="premium-input pl-8">
                </div>
            </div>
        </div>

        {{-- Preview margen --}}
        <div id="previewMargen" class="mb-5 bg-[#f5f3ef] border border-[#e8e4e0] rounded-xl px-4 py-3">
            <p class="text-[#9a9390] text-xs tracking-widest uppercase">Rentabilidad estimada</p>
            <p id="valorMargen" class="text-[#2a2522] text-lg font-medium mt-0.5">—</p>
        </div>

        {{-- Stock mínimo --}}
        <div class="mb-5">
            <label class="premium-label">Alerta de Stock mínimo</label>
            <input type="number" name="stock_minimo" value="{{ old('stock_minimo', $item->stock_minimo) }}" step="0.01" min="0"
                class="premium-input">
            <p class="text-[#b0a8a0] text-[0.65rem] mt-1.5">Te avisaremos cuando el inventario baje de esta cantidad.</p>
        </div>

        {{-- Stock actual (Informativo) --}}
        <div class="mb-8 bg-[#fdfbf7] border border-[#f0ede8] rounded-xl px-5 py-4 flex items-center justify-between">
            <div>
                <p class="text-[#9a9390] text-xs tracking-widest uppercase mb-1">Inventario Actual</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-[#2d4a35]">{{ number_format($item->stock, 0) }}</span>
                    <span class="text-[#9a9390] text-sm uppercase">Unidades</span>
                </div>
            </div>
            <div class="text-right">
                <p class="text-[#9a9390] text-[0.65rem] uppercase mb-1">Valorizado al costo</p>
                <p class="text-[#5a5250] font-medium">${{ number_format($item->stock * $item->costo_compra, 2) }}</p>
            </div>
        </div>

        {{-- Botones de acción --}}
        <div class="flex gap-3 pt-6 border-t border-slate-200/60">
            <a href="{{ route('inventario.index') }}"
                class="flex-1 premium-button-slate">
                Cancelar
            </a>
            <button type="submit"
                class="flex-1 premium-button-emerald">
                <i class="bi bi-check-lg mr-1"></i> Actualizar Producto
            </button>
        </div>
    </form>
</div>

<script>
    const costoEl  = document.getElementById('costoInput');
    const precioEl = document.getElementById('precioInput');
    const valorMargenEl = document.getElementById('valorMargen');

    function actualizarMargen() {
        const costo  = parseFloat(costoEl?.value) || 0;
        const precio = parseFloat(precioEl?.value) || 0;
        
        if (!valorMargenEl) return;

        if (costo > 0 && precio > 0) {
            const margen = ((precio - costo) / precio * 100).toFixed(1);
            const ganancia = (precio - costo).toFixed(2);
            
            valorMargenEl.textContent = `${margen}% de margen ($${ganancia} por unidad)`;
            
            // Colores según rentabilidad
            if (parseFloat(margen) >= 25) {
                valorMargenEl.className = 'text-[#4a7c59] text-lg font-semibold mt-0.5';
            } else if (parseFloat(margen) >= 10) {
                valorMargenEl.className = 'text-[#b8860b] text-lg font-semibold mt-0.5';
            } else {
                valorMargenEl.className = 'text-red-500 text-lg font-semibold mt-0.5';
            }
        } else {
            valorMargenEl.textContent = '—';
            valorMargenEl.className = 'text-[#2a2522] text-lg font-medium mt-0.5';
        }
    }

    costoEl?.addEventListener('input', actualizarMargen);
    precioEl?.addEventListener('input', actualizarMargen);
    actualizarMargen();
</script>
@endsection