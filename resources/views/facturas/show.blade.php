@extends('layouts.app')

@section('title', 'Factura #' . $numero)

@section('content')

<div class="max-w-3xl mx-auto">

    {{-- Barra de acciones --}}
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('facturas.historial') }}"
           class="inline-flex items-center gap-1.5 text-sm text-[#8a8280]
                  hover:text-[#2d4a35] transition-colors duration-150">
            <i class="bi bi-arrow-left text-xs"></i> Volver al historial
        </a>
        <div class="flex gap-2">
            <a href="{{ route('facturas.pdf', $venta->id) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-[#2d4a35] text-[#f0ede8]
                      text-sm font-medium rounded-xl hover:bg-[#3d5e45] transition-all duration-200">
                <i class="bi bi-download"></i> Descargar PDF
            </a>
            <button onclick="window.print()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-[#faf9f7]
                           border border-[#e8e4e0] text-[#5a5250] text-sm font-medium
                           rounded-xl hover:bg-[#f0ede8] transition-all duration-200">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
    </div>
    {{-- Enviar por correo --}}
    <div class="mt-4 bg-white border border-[#e8e4e0] rounded-2xl p-5">
        <p class="text-[#8a8280] text-xs tracking-widest uppercase mb-3">
            <i class="bi bi-envelope mr-1"></i> Enviar factura por correo
        </p>
        <form action="{{ route('facturas.enviar', $venta->id) }}" method="POST"
              class="flex gap-3">
            @csrf
            <input type="email" name="email_comprador"
                   placeholder="correo@delcomprador.com"
                   required
                   class="flex-1 px-4 py-2.5 bg-[#faf9f7] border border-[#e8e4e0] rounded-xl
                          text-sm text-[#2a2522] focus:outline-none focus:border-[#a8c8a0]
                          focus:ring-2 focus:ring-[#a8c8a0]/20 transition-all duration-200">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#2d4a35] text-[#f0ede8]
                           text-sm font-medium rounded-xl hover:bg-[#3d5e45] transition-all duration-200">
                <i class="bi bi-send"></i> Enviar
            </button>
        </form>
    </div>

    {{-- Documento factura --}}
    <div class="bg-white border border-[#e8e4e0] rounded-2xl shadow-sm p-8 print:shadow-none print:border-none">

        {{-- Badge simulación --}}
        <div class="flex justify-end mb-5">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium
                         bg-amber-50 text-amber-700 border border-amber-200">
                ⏳ Factura Simulada
            </span>
        </div>

        {{-- Encabezado --}}
        <div class="flex justify-between items-start pb-6 mb-6 border-b border-[#f0ede8]">
            <div>
                <h2 style="font-family:'Playfair Display',serif"
                    class="text-2xl font-semibold text-[#2d4a35]">
                    {{ $negocio->nombre_comercial }}
                </h2>
                @if($negocio->direccion)
                    <p class="text-sm text-[#8a8280] mt-1">
                        <i class="bi bi-geo-alt text-xs mr-1"></i>{{ $negocio->direccion }}
                    </p>
                @endif
                @if($negocio->telefono)
                    <p class="text-sm text-[#8a8280]">
                        <i class="bi bi-telephone text-xs mr-1"></i>{{ $negocio->telefono }}
                    </p>
                @endif
            </div>
            <div class="text-right">
                <p class="text-3xl font-black tracking-widest text-[#2d4a35]">FACTURA</p>
                <p class="font-mono text-sm text-[#4a7c59] font-semibold mt-1">
                    N° {{ $numero }}
                </p>
                <p class="text-xs text-[#8a8280] mt-1">
                    Fecha: {{ \Carbon\Carbon::parse($venta->fecha)->format('d \d\e F \d\e Y') }}
                </p>
            </div>
        </div>

        {{-- Concepto --}}
        @if($venta->descripcion)
            <div class="mb-6 px-4 py-3 bg-[#faf9f7] rounded-xl border border-[#f0ede8]">
                <p class="text-[0.65rem] font-semibold tracking-widest uppercase text-[#8a8280] mb-0.5">Concepto</p>
                <p class="text-sm text-[#2a2522]">{{ $venta->descripcion }}</p>
            </div>
        @endif

        {{-- Tabla de productos --}}
        @if($venta->ventasDetalle->count() > 0)
            <table class="w-full text-sm mb-6">
                <thead>
                    <tr class="bg-[#faf9f7]">
                        <th class="text-left py-2.5 px-4 text-[0.65rem] font-semibold tracking-widest
                                   uppercase text-[#8a8280] rounded-l-lg">
                            Producto
                        </th>
                        <th class="text-center py-2.5 px-4 text-[0.65rem] font-semibold tracking-widest
                                   uppercase text-[#8a8280]">
                            Cant.
                        </th>
                        <th class="text-right py-2.5 px-4 text-[0.65rem] font-semibold tracking-widest
                                   uppercase text-[#8a8280]">
                            Precio Unit.
                        </th>
                        <th class="text-right py-2.5 px-4 text-[0.65rem] font-semibold tracking-widest
                                   uppercase text-[#8a8280] rounded-r-lg">
                            Subtotal
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0ede8]">
                    @foreach($venta->ventasDetalle as $detalle)
                        <tr>
                            <td class="py-3 px-4 text-[#2a2522] font-medium">
                                {{ $detalle->item->nombre ?? '—' }}
                                @if($detalle->item->categoria ?? false)
                                    <span class="ml-2 text-[0.6rem] text-[#9a9390] font-normal
                                                 bg-[#f0ede8] px-1.5 py-0.5 rounded">
                                        {{ $detalle->item->categoria }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center text-[#5a5250]">
                                {{ number_format($detalle->cantidad, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right text-[#5a5250]">
                                {{ $negocio->moneda }} {{ number_format($detalle->precio_unitario, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-semibold text-[#2a2522]">
                                {{ $negocio->moneda }} {{ number_format($detalle->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            {{-- Servicios: solo muestra el monto total --}}
            <div class="mb-6 py-4 px-4 border border-[#f0ede8] rounded-xl">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-[#5a5250]">{{ $venta->descripcion ?? 'Servicio prestado' }}</span>
                    <span class="text-sm font-semibold text-[#2a2522]">
                        {{ $negocio->moneda }} {{ number_format($venta->monto, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        @endif

        {{-- Totales --}}
        <div class="flex justify-end">
            <div class="w-64">
                <div class="flex justify-between py-2.5 text-sm text-[#5a5250]
                            border-t border-[#f0ede8]">
                    <span>Subtotal</span>
                    <span>{{ $negocio->moneda }} {{ number_format($venta->monto, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-3 text-base font-bold text-[#2d4a35]
                            border-t-2 border-[#2d4a35] mt-1">
                    <span>TOTAL</span>
                    <span>{{ $negocio->moneda }} {{ number_format($venta->monto, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Pie de página --}}
        <div class="mt-10 pt-6 border-t border-[#f0ede8] text-center">
            <p class="text-xs text-[#b0a8a0]">
                Documento generado por <strong>ControlFinancias</strong> — Simulación.
            </p>
            <p class="text-xs text-[#b0a8a0] mt-0.5">
                Para facturación electrónica válida pregunta al comprador.
            </p>
        </div>

    </div>
</div>

<style>
@media print {
    nav, header, .print\:hidden { display: none !important; }
    body { background: white; }
}
</style>

@endsection