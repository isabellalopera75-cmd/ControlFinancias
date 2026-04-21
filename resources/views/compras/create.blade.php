@extends('layouts.app')
@section('title', 'Registrar Compra')

@section('content')

<div class="flex items-center justify-between mb-8">
    <div>
        <p class="text-[#9a9390] text-xs tracking-widest uppercase mb-1">Inventario</p>
        <h1 class="text-[#2a2522] text-3xl">Registrar <em class="text-[#4a7c59]">Compra</em></h1>
    </div>
    <a href="{{ route('dashboard') }}"
        class="flex items-center gap-2 bg-[#f0ede8] text-[#5a5250] text-sm px-4 py-2.5 rounded-xl hover:bg-[#e8e4e0] transition">
        <i class="bi bi-arrow-left"></i> Dashboard
    </a>
</div>
HOLA
@if($errors->any())
<div class="mb-5 bg-[#fdecea] border border-[#f0c0bc] text-[#7a2d2d] px-5 py-3 rounded-xl text-sm">
    <ul>@foreach($errors->all() as $e)<li><i class="bi bi-exclamation-circle mr-1"></i>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="bg-white rounded-2xl shadow-sm border border-[#ede8e2] p-8 max-w-2xl">
    <form action="{{ route('compras.store') }}" method="POST" id="formCompra">
        @csrf

        {{-- Fecha y descripción --}}
        <div class="grid grid-cols-2 gap-4 mb-5">
            <div>
                <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Fecha *</label>
                <input type="date" name="fecha" required value="{{ date('Y-m-d') }}"
                    class="w-full px-4 py-2.5 bg-[#faf9f7] border border-[#e8e4e0] rounded-xl text-[#2a2522] text-sm focus:outline-none focus:border-[#a8c8a0]">
            </div>
            <div>
                <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">Descripción (opcional)</label>
                <input type="text" name="descripcion" value="{{ old('descripcion') }}" placeholder="Ej: Compra semanal mercado"
                    class="w-full px-4 py-2.5 bg-[#faf9f7] border border-[#e8e4e0] rounded-xl text-[#2a2522] text-sm focus:outline-none focus:border-[#a8c8a0]">
            </div>
        </div>

        {{-- Ítems de la compra --}}
        <div class="mb-5">
            <div class="flex items-center justify-between mb-3">
                <label class="text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase">Ítems comprados *</label>
                <button type="button" onclick="agregarItem()"
                    class="flex items-center gap-1 bg-[#d6e8d0] text-[#2d4a35] text-xs px-3 py-1.5 rounded-lg hover:bg-[#c0d8c0] transition">
                    <i class="bi bi-plus-lg"></i> Agregar ítem
                </button>
            </div>

            <div id="items-container" class="space-y-3"></div>
        </div>

        {{-- Total --}}
        <div id="resumenCompra" class="hidden mb-5 bg-[#f5f3ef] border border-[#e8e4e0] rounded-xl px-5 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[#9a9390] text-xs tracking-widest uppercase">Total de la compra</p>
                    <p id="totalCompra" class="text-[#2a2522] text-2xl font-medium mt-0.5">0</p>
                </div>
                <div class="text-right">
                    <p class="text-[#9a9390] text-xs tracking-widest uppercase">Ítems</p>
                    <p id="cantidadItems" class="text-[#2a2522] text-2xl font-medium mt-0.5">0</p>
                </div>
            </div>
        </div>

        <div class="flex gap-3 pt-4 border-t border-[#f0ede8]">
            <a href="{{ route('dashboard') }}"
                class="flex-1 text-center py-2.5 bg-[#f5f3ef] text-[#9a9390] text-sm rounded-xl hover:bg-[#ede8e2] transition">Cancelar</a>
            <button type="submit"
                class="flex-1 py-2.5 bg-[#2d4a35] text-white text-sm font-medium rounded-xl hover:bg-[#3d5e45] transition">
                <i class="bi bi-bag-check mr-1"></i> Registrar compra
            </button>
        </div>
    </form>
</div>

<script>
    const itemsDisponibles = @json($items);
    let contador = 0;

    function agregarItem() {
        contador++;
        const container = document.getElementById('items-container');
        const div = document.createElement('div');
        div.className = 'grid grid-cols-12 gap-2 items-center item-compra-row';
        div.innerHTML = `
            <div class="col-span-5">
                <select name="items[${contador}][item_id]" required onchange="actualizarTotal()"
                    class="w-full px-3 py-2.5 bg-[#faf9f7] border border-[#e8e4e0] rounded-xl text-[#2a2522] text-sm focus:outline-none focus:border-[#a8c8a0]">
                    <option value="">Selecciona ítem</option>
                    ${itemsDisponibles.map(i => `
                        <option value="${i.id}" data-costo="${i.costo_compra}" data-unidad="${i.unidad_base || i.unidad}">
                            ${i.nombre} (Stock: ${parseFloat(i.stock).toFixed(1)} ${i.unidad_base || i.unidad})
                        </option>`).join('')}
                </select>
            </div>
            <div class="col-span-3">
                <input type="number" name="items[${contador}][cantidad]" placeholder="Cantidad"
                    step="0.001" min="0.001" required oninput="actualizarTotal()"
                    class="w-full px-3 py-2.5 bg-[#faf9f7] border border-[#e8e4e0] rounded-xl text-[#2a2522] text-sm focus:outline-none focus:border-[#a8c8a0]">
            </div>
            <div class="col-span-3">
                <input type="number" name="items[${contador}][costo_unitario]" placeholder="Costo unit."
                    step="0.01" min="0" required oninput="actualizarTotal()"
                    class="w-full px-3 py-2.5 bg-[#faf9f7] border border-[#e8e4e0] rounded-xl text-[#2a2522] text-sm focus:outline-none focus:border-[#a8c8a0]">
            </div>
            <div class="col-span-1 flex justify-center">
                <button type="button" onclick="this.closest('.item-compra-row').remove(); actualizarTotal();"
                    class="w-8 h-8 flex items-center justify-center bg-[#f2d8d8] text-[#8a3a3a] rounded-lg hover:bg-[#e0b0b0] transition text-xs">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(div);

        // Headers si es el primero
        if (contador === 1) {
            container.insertAdjacentHTML('afterbegin', `
                <div class="grid grid-cols-12 gap-2 mb-1">
                    <div class="col-span-5 text-[#9a9390] text-[0.65rem] uppercase tracking-wide px-1">Ítem</div>
                    <div class="col-span-3 text-[#9a9390] text-[0.65rem] uppercase tracking-wide px-1">Cantidad</div>
                    <div class="col-span-3 text-[#9a9390] text-[0.65rem] uppercase tracking-wide px-1">Costo unitario</div>
                </div>
            `);
        }

        // Rellenar costo unitario con el costo actual al seleccionar
        const select = div.querySelector('select');
        const costoInput = div.querySelector('input[name$="[costo_unitario]"]');
        select.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt.dataset.costo) costoInput.value = opt.dataset.costo;
            actualizarTotal();
        });
    }

    function actualizarTotal() {
        let total = 0;
        let items = 0;
        document.querySelectorAll('.item-compra-row').forEach(row => {
            const cantidad = parseFloat(row.querySelector('[name$="[cantidad]"]')?.value) || 0;
            const costo    = parseFloat(row.querySelector('[name$="[costo_unitario]"]')?.value) || 0;
            if (cantidad > 0 && costo > 0) {
                total += cantidad * costo;
                items++;
            }
        });

        const resumen = document.getElementById('resumenCompra');
        if (total > 0) {
            resumen.classList.remove('hidden');
            document.getElementById('totalCompra').textContent = total.toLocaleString('es-CO', { maximumFractionDigits: 2 });
            document.getElementById('cantidadItems').textContent = items;
        } else {
            resumen.classList.add('hidden');
        }
    }

    // Agregar primera fila al cargar
    agregarItem();
</script>
@endsection