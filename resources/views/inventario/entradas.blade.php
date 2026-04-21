@extends('layouts.app')
@section('title', 'Entradas de Mercancía')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
    .serif { font-family: 'Playfair Display', serif; }
    body, * { font-family: 'DM Sans', sans-serif; }
</style>

{{-- HEADER --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <p class="text-[#9a9390] text-xs tracking-widests uppercase mb-1">Módulo</p>
        <h1 class="text-[#2a2522] text-3xl leading-tight">
            Gestión de <em class="text-[#4a7c59]">Inventario</em>
        </h1>
    </div>
    <button onclick="abrirModalEntrada()"
        class="flex items-center gap-2 bg-[#2d4a35] text-white text-sm font-medium px-5 py-2.5 rounded-xl hover:bg-[#3d5e45] transition">
        <i class="bi bi-plus-lg"></i> Nueva entrada
    </button>
</div>

{{-- PESTAÑAS --}}
<div class="flex gap-1 mb-6 bg-[#f0ede8] p-1 rounded-xl w-fit">
    <a href="{{ route('inventario.index') }}"
        class="px-5 py-2 rounded-lg text-sm font-medium transition-all duration-200
               text-[#9a9390] hover:text-[#2d4a35] hover:bg-white/60">
        <i class="bi bi-box-seam mr-1.5"></i> Productos
    </a>
    <a href="{{ route('inventario.entradas') }}"
        class="px-5 py-2 rounded-lg text-sm font-medium transition-all duration-200
               bg-white text-[#2d4a35] shadow-sm">
        <i class="bi bi-truck mr-1.5"></i> Entradas
    </a>
</div>

{{-- HISTORIAL DE COMPRAS --}}
<div class="bg-white rounded-2xl shadow-sm border border-[#ede8e2] overflow-hidden">

    @if($compras->isEmpty())
        <div class="p-12 text-center">
            <div class="w-14 h-14 bg-[#f5f3ef] rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-truck text-[#b0a8a0] text-2xl"></i>
            </div>
            <p class="text-[#9a9390] text-sm">No hay entradas de mercancía registradas.</p>
            <button onclick="abrirModalEntrada()"
                class="inline-block mt-4 bg-[#2d4a35] text-white text-sm px-5 py-2.5 rounded-xl hover:bg-[#3d5e45] transition">
                + Registrar primera entrada
            </button>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#d6e8d0]">
                    <th class="px-5 py-3 text-left text-[#2d4a35] text-xs tracking-widest uppercase font-medium">Fecha</th>
                    <th class="px-5 py-3 text-left text-[#2d4a35] text-xs tracking-widest uppercase font-medium">Proveedor / Factura</th>
                    <th class="px-5 py-3 text-left text-[#2d4a35] text-xs tracking-widest uppercase font-medium">Productos</th>
                    <th class="px-5 py-3 text-right text-[#2d4a35] text-xs tracking-widest uppercase font-medium">Total</th>
                    <th class="px-5 py-3 text-center text-[#2d4a35] text-xs tracking-widest uppercase font-medium">Detalle</th>
                </tr>
            </thead>
            <tbody>
                @foreach($compras as $compra)
                <tr class="border-b border-[#f0ede8] hover:bg-[#faf8f5] transition"
                    x-data="{ abierto: false }">

                    {{-- Fecha --}}
                    <td class="px-5 py-3 text-[#9a9390] text-xs">
                        {{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') }}
                    </td>

                    {{-- Proveedor / Factura --}}
                    <td class="px-5 py-3 text-[#2a2522] font-medium">
                        {{ $compra->descripcion ?? 'Sin referencia' }}
                    </td>

                    {{-- Productos --}}
                    <td class="px-5 py-3">
                        @if($compra->comprasDetalle->count() > 0)
                            <div class="flex flex-col gap-0.5">
                                @foreach($compra->comprasDetalle as $detalle)
                                    <span class="text-xs text-[#5a5250]">
                                        {{ $detalle->item->nombre ?? '—' }}
                                        <span class="text-[#9a9390]">
                                            × {{ number_format($detalle->cantidad, 0) }}
                                        </span>
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs text-[#b0a8a0]">Sin detalle</span>
                        @endif
                    </td>

                    {{-- Total --}}
                    <td class="px-5 py-3 text-right font-semibold text-[#2a2522]">
                        {{ $negocio->moneda }} {{ number_format($compra->monto, 0, ',', '.') }}
                    </td>

                    {{-- Detalle expandible --}}
                    <td class="px-5 py-3 text-center">
                        <button onclick="toggleDetalle('detalle-{{ $compra->id }}')"
                            class="bg-[#f0ede8] text-[#5a5250] px-3 py-1.5 rounded-lg text-xs
                                   hover:bg-[#e8e4e0] transition">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </td>
                </tr>

                {{-- Fila expandible con detalle --}}
                <tr id="detalle-{{ $compra->id }}" class="hidden bg-[#faf9f7]">
                    <td colspan="5" class="px-8 py-4">
                        <div class="border border-[#e8e4e0] rounded-xl overflow-hidden">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="bg-[#f0ede8]">
                                        <th class="px-4 py-2 text-left text-[#8a8280] uppercase tracking-wide">Producto</th>
                                        <th class="px-4 py-2 text-center text-[#8a8280] uppercase tracking-wide">Cantidad</th>
                                        <th class="px-4 py-2 text-right text-[#8a8280] uppercase tracking-wide">Costo Unit.</th>
                                        <th class="px-4 py-2 text-right text-[#8a8280] uppercase tracking-wide">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($compra->comprasDetalle as $det)
                                    <tr class="border-t border-[#f0ede8]">
                                        <td class="px-4 py-2 text-[#2a2522] font-medium">
                                            {{ $det->item->nombre ?? '—' }}
                                        </td>
                                        <td class="px-4 py-2 text-center text-[#5a5250]">
                                            {{ number_format($det->cantidad, 0) }}
                                        </td>
                                        <td class="px-4 py-2 text-right text-[#5a5250]">
                                            {{ $negocio->moneda }} {{ number_format($det->costo_unitario, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-2 text-right font-semibold text-[#2a2522]">
                                            {{ $negocio->moneda }} {{ number_format($det->cantidad * $det->costo_unitario, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Paginación --}}
        @if($compras->hasPages())
            <div class="px-5 py-4 border-t border-[#f0ede8]">
                {{ $compras->links() }}
            </div>
        @endif
    @endif
</div>

{{-- ══════════════ MODAL NUEVA ENTRADA ══════════════ --}}
<div id="modalEntrada"
     class="fixed inset-0 bg-gray-900/30 backdrop-blur-sm hidden items-center justify-center z-50">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 p-6 max-h-[90vh] overflow-y-auto">

        {{-- Header modal --}}
        <div class="flex items-start justify-between mb-5">
            <div>
                <p class="text-[#9a9390] text-xs tracking-widest uppercase mb-1">Inventario</p>
                <h2 class="text-[#2a2522] text-xl">Nueva Entrada de Mercancía</h2>
            </div>
            <button onclick="cerrarModalEntrada()"
                class="w-8 h-8 flex items-center justify-center rounded-full bg-[#f5f3ef]
                       text-[#9a9390] hover:bg-[#ede8e2] transition text-sm flex-shrink-0 ml-4">
                ✕
            </button>
        </div>

        <form action="{{ route('compras.store') }}" method="POST" id="formEntrada">
            @csrf

            {{-- Proveedor / Referencia --}}
            <div class="mb-4">
                <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">
                    Proveedor o N° de Factura
                </label>
                <input type="text" name="descripcion"
                       placeholder="Ej: Proveedor ABC — Factura #1234"
                       class="w-full px-4 py-2.5 bg-[#faf9f7] border border-[#e8e4e0] rounded-xl
                              text-[#2a2522] text-sm focus:outline-none focus:border-[#a8c8a0]
                              focus:ring-2 focus:ring-[#a8c8a0]/20 transition-all duration-200">
            </div>

            {{-- Fecha --}}
            <div class="mb-5">
                <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">
                    Fecha de entrada
                </label>
                <input type="date" name="fecha" required value="{{ date('Y-m-d') }}"
                       class="w-full px-4 py-2.5 bg-[#faf9f7] border border-[#e8e4e0] rounded-xl
                              text-[#2a2522] text-sm focus:outline-none focus:border-[#a8c8a0]
                              focus:ring-2 focus:ring-[#a8c8a0]/20 transition-all duration-200">
            </div>

            {{-- Líneas de productos --}}
            <div class="mb-3">
                <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-3">
                    Productos
                </label>
                <div id="lineasEntrada" class="space-y-3"></div>
            </div>

            <button type="button" onclick="agregarLineaEntrada()"
                class="w-full py-2.5 border-2 border-dashed border-[#c8e0cc] text-[#4a7c59]
                       text-sm rounded-xl hover:bg-[#f0f7f2] transition mb-5">
                <i class="bi bi-plus-circle mr-1"></i> Agregar producto
            </button>

            {{-- Preview total --}}
            <div id="previewEntrada" class="hidden bg-[#f0f7f2] border border-[#c8e0cc] rounded-xl px-4 py-3 mb-5">
                <div class="flex justify-between items-center">
                    <span class="text-[#4a7c59] text-xs uppercase tracking-wide font-medium">Total de la entrada</span>
                    <span id="totalEntrada" class="text-[#2d4a35] text-lg font-bold">$0</span>
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 bg-[#2d4a35] text-white py-2.5 rounded-xl text-sm
                               font-medium hover:bg-[#3d5e45] transition-all duration-200
                               flex items-center justify-center gap-2">
                    <i class="bi bi-check-lg"></i> Registrar entrada
                </button>
                <button type="button" onclick="cerrarModalEntrada()"
                        class="flex-1 border border-[#e8e4e0] text-[#8a8280] py-2.5 rounded-xl
                               text-sm font-medium hover:bg-[#f5f3ef] transition-all duration-200">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const productosEntrada = @json($items);
    let contadorEntrada = 0;

    function abrirModalEntrada() {
        const modal = document.getElementById('modalEntrada');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (contadorEntrada === 0) agregarLineaEntrada();
    }

    function cerrarModalEntrada() {
        const modal = document.getElementById('modalEntrada');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function agregarLineaEntrada() {
        contadorEntrada++;
        const idx = contadorEntrada;
        const div = document.createElement('div');
        div.className = 'grid grid-cols-12 gap-2 items-center linea-entrada';
        div.innerHTML = `
            <div class="col-span-5">
                <select name="items[${idx}][item_id]" onchange="calcularTotalEntrada()"
                    class="w-full px-3 py-2.5 bg-[#faf9f7] border border-[#e8e4e0] rounded-xl
                           text-sm focus:outline-none focus:border-[#a8c8a0]">
                    <option value="">— Producto —</option>
                    ${productosEntrada.map(p => `
                        <option value="${p.id}">${p.nombre}</option>
                    `).join('')}
                </select>
            </div>
            <div class="col-span-2">
                <input type="number" name="items[${idx}][cantidad]"
                       step="any" min="0.001" placeholder="Cant."
                       oninput="calcularTotalEntrada()"
                       class="w-full px-3 py-2.5 bg-[#faf9f7] border border-[#e8e4e0]
                              rounded-xl text-sm focus:outline-none focus:border-[#a8c8a0]">
            </div>
            <div class="col-span-3">
                <input type="number" name="items[${idx}][costo_unitario]"
                       step="any" min="0" placeholder="Costo unit."
                       oninput="calcularTotalEntrada()"
                       class="w-full px-3 py-2.5 bg-[#faf9f7] border border-[#e8e4e0]
                              rounded-xl text-sm focus:outline-none focus:border-[#a8c8a0]">
            </div>
            <div class="col-span-1 text-right">
                <span class="subtotal-entrada text-[#9a9390] text-xs font-mono"></span>
            </div>
            <div class="col-span-1 text-center">
                <button type="button"
                        onclick="this.closest('.linea-entrada').remove(); calcularTotalEntrada()"
                        class="text-red-400 hover:text-red-600 transition p-1">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        document.getElementById('lineasEntrada').appendChild(div);
    }

    function calcularTotalEntrada() {
        let total = 0;
        document.querySelectorAll('.linea-entrada').forEach(row => {
            const cantidad = parseFloat(row.querySelector('[name*="cantidad"]')?.value) || 0;
            const costo = parseFloat(row.querySelector('[name*="costo_unitario"]')?.value) || 0;
            const subtotal = cantidad * costo;
            total += subtotal;
            const span = row.querySelector('.subtotal-entrada');
            if (span) span.textContent = subtotal > 0 ? '$' + subtotal.toLocaleString('es-CO') : '';
        });

        document.getElementById('totalEntrada').textContent = '$' + total.toLocaleString('es-CO');
        document.getElementById('previewEntrada').classList.toggle('hidden', total === 0);
    }

    function toggleDetalle(id) {
        const fila = document.getElementById(id);
        fila.classList.toggle('hidden');
        fila.classList.toggle('table-row');
    }
</script>

@endsection
