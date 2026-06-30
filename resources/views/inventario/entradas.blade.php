@extends('layouts.app')
@section('title', 'Entradas de Mercancía')

@push('styles')
    @vite(['resources/css/inventario.css'])
@endpush

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

{{-- HEADER DE LA PÁGINA --}}
<div class="max-w-6xl mx-auto flex items-center justify-between mb-6 px-4">
    <div>
        <p class="text-[#9a9390] text-xs tracking-widest uppercase mb-1">Módulo</p>
        <h1 class="text-[#2a2522] text-3xl leading-tight">
            Gestión de <em class="text-[#4a7c59]">Inventario</em>
        </h1>
    </div>
    <div class="flex gap-3">
        <button id="btnNuevaEntrada" onclick="toggleFormulario(true)"
            class="premium-button-emerald">
            <i class="bi bi-plus-lg mr-1"></i> Registrar Factura
        </button>
        <button id="btnVolverHistorial" onclick="toggleFormulario(false)"
            class="premium-button-slate hidden py-2.5">
            <i class="bi bi-arrow-left text-xs mr-1"></i> Ver Historial
        </button>
    </div>
</div>

{{-- SECCIÓN: FORMULARIO --}}
<div id="sectionFormEntrada" class="hidden max-w-6xl mx-auto px-4 mb-12 animate-fade-in">
    <div class="glass-card overflow-hidden">
        
        {{-- Header Factura --}}
        <div class="bg-[#2d4a35] px-8 py-5 flex items-center gap-4">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="bi bi-receipt text-white text-lg"></i>
            </div>
            <div>
                <p class="text-white font-semibold text-base">Nueva Factura</p>
                <p class="text-[#a8c8a0] text-xs">Ingresa los productos recibidos hoy.</p>
            </div>
        </div>

        <form action="{{ route('compras.store') }}" method="POST" id="formEntrada" class="bg-white">
            @csrf
            
            {{-- DATOS CABECERA --}}
            <div class="p-8 border-b border-slate-200/60">
                <div class="section-header">
                    <div class="step-badge">1</div>
                    <p class="text-[#2a2522] text-sm font-semibold">Datos de la Factura</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-4">
                    <div>
                        <label class="premium-label">Proveedor / Factura #</label>
                        <input type="text" name="descripcion" placeholder="Ej: Distribuidora XYZ"
                               class="premium-input">
                    </div>
                    <div>
                        <label class="premium-label">Fecha de Recepción <span class="text-red-400">*</span></label>
                        <input type="date" name="fecha" required value="{{ date('Y-m-d') }}"
                               class="premium-input">
                    </div>
                </div>
            </div>

            {{-- LISTADO DE PRODUCTOS --}}
            <div class="p-8">
                <div class="flex items-center justify-between mb-4">
                    <div class="section-header mb-0">
                        <div class="step-badge">2</div>
                        <p class="text-[#2a2522] text-sm font-semibold">Detalle de Productos</p>
                    </div>
                    <span class="text-[0.62rem] text-[#b0a8a0] font-semibold tracking-widest uppercase bg-[#f5f3ef] px-3 py-1.5 rounded-lg" id="contadorLineas">0 ítems</span>
                </div>

                <div id="lineasEntrada" class="space-y-3 mb-6">
                    {{-- Filas dinámicas --}}
                </div>

                <button type="button" onclick="agregarLineaEntrada()"
                    class="w-full py-3.5 border-2 border-dashed border-[#e8e4e0] text-[#9a9390]
                           text-xs font-medium rounded-2xl hover:bg-[#f0f7f2] hover:border-[#c8e0cc] hover:text-[#4a7c59]
                           transition-all duration-300 flex items-center justify-center gap-2 group">
                    <i class="bi bi-plus-circle text-base"></i> 
                    Agregar producto a la lista
                </button>
            </div>

            {{-- FOOTER --}}
            <div class="p-8 border-t border-slate-200/60 mt-auto bg-white">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div id="previewEntrada" class="hidden bg-[#f0f7f2] border border-[#c8e0cc] rounded-xl px-6 py-4 flex items-center gap-4">
                        <div>
                            <span class="text-[#2d4a35] text-[0.62rem] uppercase tracking-widest font-semibold block mb-0.5">Total Factura</span>
                            <span id="totalEntrada" class="text-[#2a2522] text-xl font-semibold">$0</span>
                        </div>
                    </div>

                    <div class="flex gap-3 w-full md:w-auto ml-auto">
                        <button type="button" onclick="toggleFormulario(false)"
                                class="premium-button-slate flex-1 md:flex-none py-3">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="premium-button-emerald flex-1 md:flex-none py-3">
                            <i class="bi bi-check-lg mr-1"></i> Guardar Ingreso
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- HISTORIAL --}}
<div id="sectionHistorial" class="max-w-6xl mx-auto px-4 mb-12">
    {{-- PESTAÑAS --}}
    <div class="mb-6">
        <div class="flex gap-1 bg-[#f0ede8] p-1 rounded-xl w-fit">
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
    </div>

    <div class="glass-card overflow-hidden">
        <div class="max-h-[600px] overflow-y-auto custom-scrollbar">
            @if($compras->isEmpty())
                <div class="p-16 text-center">
                    <p class="text-slate-400 text-sm font-medium">No hay facturas registradas.</p>
                </div>
            @else
                <table class="w-full text-sm border-collapse">
                    <thead class="sticky top-0 z-10">
                        <tr class="text-white">
                            <th class="bg-[#2d4a35] px-5 py-4 text-left text-xs tracking-widest uppercase font-semibold rounded-tl-xl">Fecha</th>
                            <th class="bg-[#2d4a35] px-5 py-4 text-left text-xs tracking-widest uppercase font-semibold">Proveedor / Factura</th>
                            <th class="bg-[#2d4a35] px-5 py-4 text-right text-xs tracking-widest uppercase font-semibold">Monto</th>
                            <th class="bg-[#2d4a35] px-5 py-4 text-center text-xs tracking-widest uppercase font-semibold rounded-tr-xl">Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($compras as $compra)
                        <tr class="border-b border-slate-100 hover:bg-emerald-50/30 transition fila-item">
                            <td class="px-5 py-3 text-slate-500 font-medium text-sm border-r border-slate-100">{{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') }}</td>
                            <td class="px-5 py-3 text-[#2a2522] font-semibold text-sm border-r border-slate-100">{{ $compra->descripcion ?? '—' }}</td>
                            <td class="px-5 py-3 text-right font-bold text-[#2a2522] text-sm border-r border-slate-100">{{ $negocio->moneda }} {{ number_format($compra->monto, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-center">
                                <button onclick="toggleDetalle('detalle-{{ $compra->id }}', this)"
                                    class="w-8 h-8 flex items-center justify-center rounded-xl bg-slate-100 text-slate-600 hover:bg-emerald-100 hover:text-emerald-700 transition mx-auto group border border-transparent hover:border-emerald-200">
                                    <i class="bi bi-chevron-down transition-transform group-[.active]:rotate-180"></i>
                                </button>
                            </td>
                        </tr>
                        <tr id="detalle-{{ $compra->id }}" class="hidden bg-slate-50/50">
                            <td colspan="4" class="px-8 py-6">
                                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="bg-slate-50 text-slate-400 font-bold text-[0.65rem] uppercase tracking-widest border-b border-slate-100">
                                                <th class="px-6 py-3 text-left">Producto</th>
                                                <th class="px-6 py-3 text-center">Cant.</th>
                                                <th class="px-6 py-3 text-right">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-50">
                                            @foreach($compra->comprasDetalle as $det)
                                            <tr>
                                                <td class="px-6 py-3 text-[#2a2522] font-semibold">{{ $det->item->nombre ?? '—' }}</td>
                                                <td class="px-6 py-3 text-center text-slate-600 font-medium">{{ number_format($det->cantidad, 0) }}</td>
                                                <td class="px-6 py-3 text-right font-bold text-[#2a2522]">{{ number_format($det->cantidad * $det->costo_unitario, 0) }}</td>
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
            @endif
        </div>
    </div>

    @if($compras->hasPages())
    <div class="mt-4 px-2">
        {{ $compras->links() }}
    </div>
    @endif
</div>

<script>
    const productosEntrada = @json($items);
    let contadorEntrada = 0;

    function toggleFormulario(show) {
        document.getElementById('sectionFormEntrada').classList.toggle('hidden', !show);
        document.getElementById('sectionHistorial').classList.toggle('hidden', show);
        document.getElementById('btnNuevaEntrada').classList.toggle('hidden', show);
        document.getElementById('btnVolverHistorial').classList.toggle('hidden', !show);
        if (show && document.querySelectorAll('.linea-entrada').length === 0) agregarLineaEntrada();
    }

    function agregarLineaEntrada() {
        contadorEntrada++;
        actualizarContador();
        const idx = contadorEntrada;
        const div = document.createElement('div');
        div.className = 'linea-entrada animate-fade-in bg-[#fbfaf9] p-3 rounded-xl border border-[#e8e4e0] hover:border-[#c8e0cc] transition-all';
        div.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                <div class="col-span-12 md:col-span-5 relative">
                    <select name="items[${idx}][item_id]" onchange="calcularTotalEntrada()"
                        class="premium-input !py-2.5 !pl-3 !pr-8 appearance-none !bg-white">
                        <option value="">— Seleccionar Producto —</option>
                        ${productosEntrada.map(p => `<option value="${p.id}">${p.nombre}</option>`).join('')}
                    </select>
                    <i class="bi bi-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-[#9a9390] pointer-events-none text-xs"></i>
                </div>
                
                <div class="col-span-5 md:col-span-2">
                    <input type="number" name="items[${idx}][cantidad]" step="any" min="0.001" placeholder="Cant."
                           oninput="calcularTotalEntrada()"
                           class="premium-input !py-2.5 !bg-white text-center">
                </div>
                
                <div class="col-span-5 md:col-span-2 relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#9a9390] text-sm">$</span>
                    <input type="number" name="items[${idx}][costo_unitario]" step="any" min="0" placeholder="Costo (Opc)"
                           oninput="calcularTotalEntrada()"
                           class="premium-input !py-2.5 !pl-6 !bg-white">
                </div>

                <div class="col-span-10 md:col-span-2 text-right px-1">
                    <span class="subtotal-entrada text-[#2d4a35] text-sm font-semibold tracking-tight"></span>
                </div>

                <div class="col-span-2 md:col-span-1 text-right">
                    <button type="button" onclick="this.closest('.linea-entrada').remove(); calcularTotalEntrada(); actualizarContador()"
                            class="w-8 h-8 flex items-center justify-center rounded-xl bg-[#f5f3ef] text-[#9a9390] hover:bg-[#ffe5e5] hover:text-[#d32f2f] transition-all mx-auto">
                        <i class="bi bi-trash3 text-sm"></i>
                    </button>
                </div>
            </div>
        `;
        document.getElementById('lineasEntrada').appendChild(div);
    }

    function actualizarContador() {
        const n = document.querySelectorAll('.linea-entrada').length;
        const span = document.getElementById('contadorLineas');
        if (span) span.textContent = n + (n === 1 ? ' ítem' : ' ítems');
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

    function toggleDetalle(id, btn) {
        document.getElementById(id).classList.toggle('hidden');
        if (btn) btn.classList.toggle('active');
    }
</script>
@endsection
