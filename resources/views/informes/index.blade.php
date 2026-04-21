@extends('layouts.app')
@section('title', 'Informes')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
    body, * { font-family: 'DM Sans', sans-serif; }
    @media print {
        nav, header, .no-print { display: none !important; }
        body { background: white; }
    }
</style>

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <p class="text-[#8a8280] text-xs tracking-widest uppercase mb-1">Facturación</p>
        <h2 style="font-family:'Playfair Display',serif"
            class="text-[#2d4a35] text-3xl font-semibold">Facturación</h2>
    </div>
</div>

{{-- Pestañas --}}
<div class="flex gap-1 mb-6 bg-[#f0ede8] p-1 rounded-xl w-fit no-print">
    <a href="{{ route('facturas.historial') }}"
        class="px-5 py-2 rounded-lg text-sm font-medium transition-all duration-200
               text-[#9a9390] hover:text-[#2d4a35] hover:bg-white/60">
        <i class="bi bi-receipt mr-1.5"></i> Facturas
    </a>
    <a href="{{ route('informes.index') }}"
        class="px-5 py-2 rounded-lg text-sm font-medium transition-all duration-200
               bg-white text-[#2d4a35] shadow-sm">
        <i class="bi bi-bar-chart-line mr-1.5"></i> Informes
    </a>
</div>

{{-- ═══ CARDS DESTACADOS (siempre visibles, por mes actual si no hay filtro) ═══ --}}
@if(in_array($tipo, ['ventas', 'ambos']))
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

    {{-- Total ventas --}}
    <div class="bg-[#d6e8d0] border border-[#a8c8a0] rounded-2xl p-5">
        <p class="text-[#4a7c59] text-xs tracking-widest uppercase font-medium mb-1">
            <i class="bi bi-graph-up-arrow mr-1"></i> Total Ventas
        </p>
        <p class="text-[#2d4a35] text-2xl font-bold">
            {{ $negocio->moneda }} {{ number_format($totalVentas, 0, ',', '.') }}
        </p>
        <p class="text-[#4a7c59] text-xs mt-1.5">
            {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}
            al
            {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
        </p>
    </div>

    {{-- Mejor día --}}
    <div class="bg-white border border-[#e8e4e0] rounded-2xl p-5">
        <p class="text-[#8a8280] text-xs tracking-widest uppercase font-medium mb-1">
            🏆 Mejor día de ventas
        </p>
        @if($diaMasVentas && $totalVentas > 0)
            <p class="text-[#2a2522] text-xl font-bold">
                {{ \Carbon\Carbon::parse($diaMasVentas->dia)->format('d/m/Y') }}
            </p>
            <p class="text-[#4a7c59] text-sm font-semibold mt-1">
                {{ $negocio->moneda }} {{ number_format($diaMasVentas->total, 0, ',', '.') }}
            </p>
        @else
            <p class="text-[#b0a8a0] text-sm mt-2">Sin ventas en el período</p>
        @endif
    </div>

    {{-- Producto más vendido --}}
    <div class="bg-white border border-[#e8e4e0] rounded-2xl p-5">
        <p class="text-[#8a8280] text-xs tracking-widest uppercase font-medium mb-1">
            🥇 Producto más vendido
        </p>
        @if($productoMasVendido && $productoMasVendido->item)
            <p class="text-[#2a2522] text-base font-bold leading-tight">
                {{ $productoMasVendido->item->nombre }}
            </p>
            <p class="text-[#4a7c59] text-xs mt-1.5">
                {{ number_format($productoMasVendido->total_cantidad, 0) }} unidades —
                {{ $negocio->moneda }} {{ number_format($productoMasVendido->total_monto, 0, ',', '.') }}
            </p>
        @else
            <p class="text-[#b0a8a0] text-sm mt-2">Sin detalle de productos</p>
        @endif
    </div>

</div>
@endif

{{-- Total gastos en modo ambos (junto a ventas) --}}
@if($tipo === 'ambos' && $totalGastos > 0)
<div class="bg-[#f2d8d8] border border-[#e0b0b0] rounded-2xl p-4 mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <span class="w-1.5 h-8 bg-[#e07070] rounded-full"></span>
        <div>
            <p class="text-[#8a3a3a] text-xs tracking-widest uppercase font-medium">Total Gastos del período</p>
            <p class="text-[#8a3a3a] text-xl font-bold">
                {{ $negocio->moneda }} {{ number_format($totalGastos, 0, ',', '.') }}
            </p>
        </div>
    </div>
    <div class="text-right">
        <p class="text-[#b07070] text-xs">Balance neto</p>
        @php $balance = $totalVentas - $totalGastos; @endphp
        <p class="text-lg font-bold {{ $balance >= 0 ? 'text-[#2d4a35]' : 'text-[#8a3a3a]' }}">
            {{ $negocio->moneda }} {{ number_format($balance, 0, ',', '.') }}
        </p>
    </div>
</div>
@endif

{{-- ═══ FILTROS HORIZONTALES + BOTONES ═══ --}}
<div class="no-print bg-white border border-[#e8e4e0] rounded-2xl p-4 mb-6 shadow-sm">
    <form method="GET" action="{{ route('informes.index') }}">
        <div class="flex flex-wrap gap-3 items-end">

            <div class="flex-1 min-w-[140px]">
                <label class="block text-[#8a8280] text-[0.65rem] font-medium tracking-widest uppercase mb-1">
                    Tipo
                </label>
                <select name="tipo"
                    class="w-full px-3 py-2 bg-[#faf9f7] border border-[#e8e4e0] rounded-xl
                           text-sm text-[#2a2522] focus:outline-none focus:border-[#a8c8a0]">
                    <option value="ambos"  {{ $tipo === 'ambos'  ? 'selected' : '' }}>Ventas y Gastos</option>
                    <option value="ventas" {{ $tipo === 'ventas' ? 'selected' : '' }}>Solo Ventas</option>
                    <option value="gastos" {{ $tipo === 'gastos' ? 'selected' : '' }}>Solo Gastos</option>
                </select>
            </div>

            <div class="flex-1 min-w-[140px]">
                <label class="block text-[#8a8280] text-[0.65rem] font-medium tracking-widest uppercase mb-1">
                    Desde
                </label>
                <input type="date" name="fecha_desde" value="{{ $fechaDesde }}"
                    class="w-full px-3 py-2 bg-[#faf9f7] border border-[#e8e4e0] rounded-xl
                           text-sm text-[#2a2522] focus:outline-none focus:border-[#a8c8a0]">
            </div>

            <div class="flex-1 min-w-[140px]">
                <label class="block text-[#8a8280] text-[0.65rem] font-medium tracking-widest uppercase mb-1">
                    Hasta
                </label>
                <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}"
                    class="w-full px-3 py-2 bg-[#faf9f7] border border-[#e8e4e0] rounded-xl
                           text-sm text-[#2a2522] focus:outline-none focus:border-[#a8c8a0]">
            </div>

            <button type="submit"
                class="px-5 py-2 bg-[#2d4a35] text-white rounded-xl text-sm font-medium
                       hover:bg-[#3d5e45] transition-all duration-200 whitespace-nowrap">
                <i class="bi bi-search mr-1"></i> Filtrar
            </button>

            {{-- Separador visual --}}
            <div class="w-px h-8 bg-[#e8e4e0] self-center hidden md:block"></div>

            {{-- Botones descarga inline --}}
            <a href="{{ route('informes.pdf', request()->query()) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#2d4a35] text-white text-sm
                      font-medium rounded-xl hover:bg-[#3d5e45] transition-all duration-200 whitespace-nowrap">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
            <a href="{{ route('informes.excel', request()->query()) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#e8f0e9] text-[#2d4a35] text-sm
                      font-medium rounded-xl hover:bg-[#d0e4d4] transition-all duration-200 whitespace-nowrap">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </a>
            <button type="button" onclick="window.print()"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#faf9f7] border border-[#e8e4e0]
                      text-[#5a5250] text-sm font-medium rounded-xl hover:bg-[#f0ede8]
                      transition-all duration-200 whitespace-nowrap">
                <i class="bi bi-printer"></i> Imprimir
            </button>

        </div>
    </form>
</div>

{{-- ═══ TABLA VENTAS ═══ --}}
@if(in_array($tipo, ['ventas', 'ambos']))
<div class="bg-white border border-[#e8e4e0] rounded-2xl shadow-sm overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-[#f0ede8] flex items-center gap-2">
        <span class="w-1.5 h-4 bg-[#4a7c59] rounded-full"></span>
        <h3 class="text-[#2d4a35] text-sm font-semibold tracking-widest uppercase">Ventas por día</h3>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-[#d6e8d0]">
                <th class="px-5 py-3 text-left text-[#2d4a35] text-xs tracking-widest uppercase font-medium">Fecha</th>
                <th class="px-5 py-3 text-right text-[#2d4a35] text-xs tracking-widest uppercase font-medium">Total Vendido</th>
                <th class="px-5 py-3 text-center text-[#2d4a35] text-xs tracking-widest uppercase font-medium">Destacado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($diasRango as $dia)
            @php
                $venta    = $ventasPorDia[$dia] ?? null;
                $total    = $venta ? $venta->total : 0;
                $esMejorDia = $diaMasVentas && $diaMasVentas->dia === $dia && $total > 0;
            @endphp
            <tr class="border-b border-[#f0ede8] {{ $esMejorDia ? 'bg-[#f0f7f2]' : 'hover:bg-[#faf8f5]' }} transition">
                <td class="px-5 py-3 text-[#5a5250]">
                    {{ \Carbon\Carbon::parse($dia)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}
                </td>
                <td class="px-5 py-3 text-right font-semibold {{ $total > 0 ? 'text-[#2d4a35]' : 'text-[#c0bbb8]' }}">
                    @if($total > 0)
                        {{ $negocio->moneda }} {{ number_format($total, 0, ',', '.') }}
                    @else —
                    @endif
                </td>
                <td class="px-5 py-3 text-center">
                    @if($esMejorDia)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs
                                     font-medium bg-[#d6e8d0] text-[#2d4a35]">
                            🏆 Mejor día
                        </span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-[#2d4a35]">
                <td class="px-5 py-3 text-white font-bold text-sm">TOTAL VENTAS</td>
                <td class="px-5 py-3 text-right text-white font-bold text-sm">
                    {{ $negocio->moneda }} {{ number_format($totalVentas, 0, ',', '.') }}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- ═══ TABLA GASTOS ═══ --}}
@if(in_array($tipo, ['gastos', 'ambos']))
<div class="bg-white border border-[#e8e4e0] rounded-2xl shadow-sm overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-[#f0ede8] flex items-center gap-2">
        <span class="w-1.5 h-4 bg-[#e07070] rounded-full"></span>
        <h3 class="text-[#8a3a3a] text-sm font-semibold tracking-widest uppercase">Gastos por día</h3>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-[#f2d8d8]">
                <th class="px-5 py-3 text-left text-[#8a3a3a] text-xs tracking-widest uppercase font-medium">Fecha</th>
                <th class="px-5 py-3 text-right text-[#8a3a3a] text-xs tracking-widest uppercase font-medium">Total Gastado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($diasRango as $dia)
            @php
                $gasto = $gastosPorDia[$dia] ?? null;
                $total = $gasto ? $gasto->total : 0;
            @endphp
            <tr class="border-b border-[#f0ede8] hover:bg-[#faf8f5] transition">
                <td class="px-5 py-3 text-[#5a5250]">
                    {{ \Carbon\Carbon::parse($dia)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}
                </td>
                <td class="px-5 py-3 text-right font-semibold {{ $total > 0 ? 'text-[#8a3a3a]' : 'text-[#c0bbb8]' }}">
                    @if($total > 0)
                        {{ $negocio->moneda }} {{ number_format($total, 0, ',', '.') }}
                    @else —
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-[#8a3a3a]">
                <td class="px-5 py-3 text-white font-bold text-sm">TOTAL GASTOS</td>
                <td class="px-5 py-3 text-right text-white font-bold text-sm">
                    {{ $negocio->moneda }} {{ number_format($totalGastos, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

@endsection