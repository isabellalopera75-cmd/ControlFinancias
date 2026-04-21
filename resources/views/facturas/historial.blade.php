@extends('layouts.app')

@section('title', 'Historial de Ventas')

@section('content')

<div class="max-w-5xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-[#8a8280] text-xs tracking-widests uppercase mb-1">Facturación</p>
            <h2 style="font-family:'Playfair Display',serif"
                class="text-[#2d4a35] text-3xl font-semibold">
                Facturación
            </h2>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium
                    bg-amber-50 text-amber-700 border border-amber-200">
            ⏳ Simulación — Conexión DIAN próximamente
        </span>
    </div>

    {{-- Pestañas --}}
    <div class="flex gap-1 mb-6 bg-[#f0ede8] p-1 rounded-xl w-fit">
        <a href="{{ route('facturas.historial') }}"
            class="px-5 py-2 rounded-lg text-sm font-medium transition-all duration-200
                bg-white text-[#2d4a35] shadow-sm">
            <i class="bi bi-receipt mr-1.5"></i> Facturas
        </a>
        <a href="{{ route('informes.index') }}"
            class="px-5 py-2 rounded-lg text-sm font-medium transition-all duration-200
                text-[#9a9390] hover:text-[#2d4a35] hover:bg-white/60">
            <i class="bi bi-bar-chart-line mr-1.5"></i> Informes
        </a>
    </div>

    {{-- Tabla --}}
    <div class="bg-white border border-[#e8e4e0] rounded-2xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-[#f0ede8]">
            <thead class="bg-[#faf9f7]">
                <tr>
                    <th class="px-6 py-3 text-left text-[0.65rem] font-semibold text-[#8a8280] tracking-widest uppercase">
                        N° Factura
                    </th>
                    <th class="px-6 py-3 text-left text-[0.65rem] font-semibold text-[#8a8280] tracking-widest uppercase">
                        Fecha
                    </th>
                    <th class="px-6 py-3 text-left text-[0.65rem] font-semibold text-[#8a8280] tracking-widest uppercase">
                        Concepto
                    </th>
                    <th class="px-6 py-3 text-left text-[0.65rem] font-semibold text-[#8a8280] tracking-widest uppercase">
                        Productos
                    </th>
                    <th class="px-6 py-3 text-right text-[0.65rem] font-semibold text-[#8a8280] tracking-widest uppercase">
                        Total
                    </th>
                    <th class="px-6 py-3 text-center text-[0.65rem] font-semibold text-[#8a8280] tracking-widest uppercase">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#f0ede8]">
                @forelse ($ventas as $venta)
                    <tr class="hover:bg-[#faf9f7] transition-colors duration-150">

                        {{-- Número --}}
                        <td class="px-6 py-4">
                            <span class="font-mono text-sm font-semibold text-[#4a7c59]">
                                #{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}
                            </span>
                        </td>

                        {{-- Fecha --}}
                        <td class="px-6 py-4 text-sm text-[#5a5250]">
                            {{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y') }}
                        </td>

                        {{-- Concepto --}}
                        <td class="px-6 py-4 text-sm text-[#2a2522]">
                            {{ $venta->descripcion ?? 'Venta' }}
                        </td>

                        {{-- Cantidad de productos --}}
                        <td class="px-6 py-4">
                            @if($venta->ventasDetalle->count() > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs
                                             bg-[#e8f0e9] text-[#2d4a35] font-medium">
                                    {{ $venta->ventasDetalle->count() }}
                                    {{ $venta->ventasDetalle->count() === 1 ? 'producto' : 'productos' }}
                                </span>
                            @else
                                <span class="text-xs text-[#b0a8a0]">Servicio</span>
                            @endif
                        </td>

                        {{-- Total --}}
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-semibold text-[#2a2522]">
                                {{ $negocio->moneda }} {{ number_format($venta->monto, 0, ',', '.') }}
                            </span>
                        </td>

                        {{-- Acciones --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('facturas.show', $venta->id) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                                          bg-[#e8f0e9] text-[#2d4a35] text-xs font-medium
                                          hover:bg-[#d0e4d4] transition-colors duration-150">
                                    <i class="bi bi-eye text-xs"></i> Ver
                                </a>
                                <a href="{{ route('facturas.pdf', $venta->id) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                                          bg-[#faf9f7] border border-[#e8e4e0] text-[#5a5250] text-xs font-medium
                                          hover:bg-[#f0ede8] transition-colors duration-150">
                                    <i class="bi bi-download text-xs"></i> PDF
                                </a>
                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <i class="bi bi-receipt text-4xl text-[#d0ccc8]"></i>
                            <p class="text-[#b0a8a0] text-sm mt-3">No hay ventas registradas aún.</p>
                            <a href="{{ route('dashboard') }}"
                               class="inline-flex items-center gap-1.5 mt-4 text-xs text-[#4a7c59]
                                      hover:text-[#2d4a35] font-medium transition-colors">
                                <i class="bi bi-arrow-left text-xs"></i> Ir al dashboard
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    @if($ventas->hasPages())
        <div class="mt-4">
            {{ $ventas->links() }}
        </div>
    @endif

</div>

@endsection