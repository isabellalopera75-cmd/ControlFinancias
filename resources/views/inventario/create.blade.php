@extends('layouts.app')
@section('title', 'Nuevo Producto')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

<style>
  body, * { font-family: 'DM Sans', sans-serif; }
  .serif { font-family: 'Playfair Display', serif; }

  .input-field {
    width: 100%; padding: 10px 16px;
    background: #faf9f7; border: 1.5px solid #e8e4e0;
    border-radius: 12px; font-size: 0.875rem; color: #2a2522;
    transition: border-color .2s, box-shadow .2s; outline: none;
  }
  .input-field:focus { border-color: #a8c8a0; box-shadow: 0 0 0 3px rgba(168,200,160,0.15); }
  .input-field-icon { padding-left: 28px; }

  .label {
    display: block; font-size: 0.65rem; font-weight: 600;
    letter-spacing: 0.1em; text-transform: uppercase;
    color: #9a9390; margin-bottom: 6px;
  }
  .section-header {
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 16px; padding-bottom: 10px;
    border-bottom: 1px solid #f0ede8;
  }
  .step-badge {
    width: 22px; height: 22px; background: #2d4a35;
    border-radius: 50%; display: flex; align-items: center;
    justify-content: center; font-size: 0.65rem;
    font-weight: 700; color: white; flex-shrink: 0;
  }
  .pres-card {
    border: 2px solid #e8e4e0; border-radius: 14px;
    padding: 14px 10px; cursor: pointer;
    transition: all .2s; text-align: center;
  }
  .pres-card:hover { border-color: #a8c8a0; background: #faf9f7; }
  .pres-card.active { border-color: #2d4a35; background: #f0f7f2; }
  .pres-card.active .pres-icon { color: #2d4a35; }
  .pres-card.active .pres-label { color: #2d4a35; }
  .pres-icon { font-size: 1.3rem; color: #9a9390; margin-bottom: 4px; transition: color .2s; }
  .pres-label { font-size: 0.7rem; font-weight: 700; color: #5a5250; letter-spacing: 0.04em; transition: color .2s; }
  .pres-sub { font-size: 0.6rem; color: #b0a8a0; margin-top: 2px; line-height: 1.3; }

  .info-box {
    background: #f0f7f2; border: 1px solid #c8e0cc;
    border-radius: 12px; padding: 12px 16px;
    display: flex; align-items: flex-start; gap: 10px;
  }
  .info-box-icon {
    width: 28px; height: 28px; background: #2d4a35;
    border-radius: 50%; display: flex; align-items: center;
    justify-content: center; flex-shrink: 0;
  }
  .btn-submit {
    flex: 1; padding: 11px; background: #2d4a35;
    color: white; font-size: 0.875rem; font-weight: 500;
    border-radius: 12px; border: none; cursor: pointer;
    transition: background .2s, transform .15s;
  }
  .btn-submit:hover { background: #3d5e45; transform: translateY(-1px); }
  .fade-in { animation: fadeIn .25s ease; }
  @keyframes fadeIn { from { opacity:0; transform:translateY(-5px); } to { opacity:1; transform:translateY(0); } }

  .divider-v {
    width: 1px; background: #f0ede8;
    align-self: stretch; margin: 0 8px;
  }
</style>

{{-- Header --}}
<div class="flex items-center justify-between mb-6 max-w-5xl mx-auto">
    <div>
        <p class="text-[#9a9390] text-xs tracking-widest uppercase mb-1">Inventario</p>
        <h1 class="serif text-[#2d4a35] text-3xl font-semibold">Nuevo <em>Producto</em></h1>
    </div>
    <a href="{{ route('inventario.index') }}"
        class="flex items-center gap-2 bg-[#f0ede8] text-[#5a5250] text-sm px-4 py-2.5
               rounded-xl hover:bg-[#e8e4e0] transition-all duration-200">
        <i class="bi bi-arrow-left text-xs"></i> Volver
    </a>
</div>

<div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-sm border border-[#ede8e2] overflow-hidden">

    {{-- Header verde --}}
    <div class="bg-[#2d4a35] px-8 py-5 flex items-center gap-4">
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
            <i class="bi bi-shop text-white text-lg"></i>
        </div>
        <div>
            <p class="text-white font-semibold text-base">Producto de reventa</p>
            <p class="text-[#a8c8a0] text-xs">Completa los datos del producto para agregarlo al inventario.</p>
        </div>
    </div>

    <form action="{{ route('inventario.store') }}" method="POST" id="formProducto">
        @csrf

        {{-- GRID 2 COLUMNAS --}}
        <div style="display:grid; grid-template-columns:1fr 1fr;">

            {{-- COLUMNA IZQUIERDA --}}
            <div class="p-8 space-y-7" style="border-right: 1px solid #f0ede8;">

                {{-- PASO 1: Info --}}
                <div>
                    <div class="section-header">
                        <div class="step-badge">1</div>
                        <p class="text-[#2a2522] text-sm font-semibold">Información del producto</p>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="label">Nombre <span class="text-red-400">*</span></label>
                            <input type="text" name="nombre" value="{{ old('nombre') }}"
                                placeholder="Ej: Coca-Cola 250ml" class="input-field">
                        </div>
                        <div>
                            <label class="label">
                                Categoría
                                <span class="text-[#b0a8a0] font-normal normal-case text-[0.62rem]">(opcional)</span>
                            </label>
                            <input type="text" name="categoria" value="{{ old('categoria') }}"
                                list="listaCategorias" placeholder="Ej: Bebidas, Snacks"
                                class="input-field" autocomplete="off">
                            <datalist id="listaCategorias">
                                @foreach($categoriasExistentes as $cat)
                                    <option value="{{ $cat }}">
                                @endforeach
                            </datalist>
                        </div>
                    </div>
                </div>

                {{-- PASO 2: Presentación --}}
                <div>
                    <div class="section-header">
                        <div class="step-badge">2</div>
                        <p class="text-[#2a2522] text-sm font-semibold">¿Cómo compras este producto?</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="pres-card active" onclick="seleccionarPresentacion('unidad')" id="card-unidad">
                            <div class="pres-icon"><i class="bi bi-box"></i></div>
                            <div class="pres-label">Por unidad</div>
                            <div class="pres-sub">Compras unidad por unidad</div>
                        </div>
                        <div class="pres-card" onclick="seleccionarPresentacion('caja')" id="card-caja">
                            <div class="pres-icon"><i class="bi bi-boxes"></i></div>
                            <div class="pres-label">Por caja</div>
                            <div class="pres-sub">Ej: caja de 24 unidades</div>
                        </div>
                    </div>

                    <input type="hidden" name="unidad_compra" id="unidadCompraInput" value="unidad">

                    <div id="fieldUnidadesPaquete" class="hidden fade-in">
                        <label class="label">
                            Unidades por caja <span class="text-red-400">*</span>
                        </label>
                        <input type="number" name="unidades_por_paquete" id="unidadesPorPaquete"
                            value="{{ old('unidades_por_paquete', 1) }}" min="1" step="1"
                            oninput="actualizarResumen()" class="input-field">
                    </div>
                </div>

                {{-- Excel --}}
                <div class="pt-3 border-t border-[#f0ede8]">
                    <p class="label mb-2">Carga masiva de inventario</p>
                    <div class="flex gap-2">
                        <a href="{{ route('inventario.exportar') }}"
                            class="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-xl text-xs
                                font-medium bg-[#dcedf5] text-[#3a6a9a] border border-[#b8d8f0]
                                hover:bg-[#b8d8f0] transition-all duration-200">
                            <i class="bi bi-file-earmark-excel"></i> Plantilla Excel
                        </a>
                        <button type="button" onclick="abrirModalImportar()"
                            class="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-xl text-xs
                                font-medium bg-[#f0f7f2] text-[#2d4a35] border border-[#c8dfc4]
                                hover:bg-[#d6e8d0] transition-all duration-200">
                            <i class="bi bi-upload"></i> Importar Excel
                        </button>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA --}}
            <div class="p-8 space-y-7">

                {{-- Info Box --}}
                <div class="info-box mb-2">
                    <div class="info-box-icon">
                        <i class="bi bi-info-lg text-white text-xs"></i>
                    </div>
                    <div>
                        <p class="text-[#2d4a35] text-xs font-semibold mb-0.5">
                            El stock siempre se maneja en unidades
                        </p>
                        <p class="text-[#5a7a60] text-xs leading-relaxed">
                            Aunque compres por caja, el sistema convierte y guarda en unidades individuales.
                        </p>
                    </div>
                </div>

                {{-- PASO 3: Stock --}}
                <div>
                    <div class="section-header">
                        <div class="step-badge">3</div>
                        <p class="text-[#2a2522] text-sm font-semibold">Stock</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-3">
                        <div>
                            <label class="label" id="labelStock">Cantidad disponible hoy</label>
                            <input type="number" name="stock_inicial" id="stockInicial"
                                value="{{ old('stock_inicial', 0) }}" step="0.01" min="0"
                                oninput="actualizarResumen()"
                                class="input-field" placeholder="0">
                        </div>
                        <div>
                            <label class="label">
                                Stock mínimo
                                <span class="text-[#b0a8a0] font-normal normal-case text-[0.62rem]">(alerta)</span>
                            </label>
                            <input type="number" name="stock_minimo"
                                value="{{ old('stock_minimo', 0) }}" step="1" min="0"
                                class="input-field" placeholder="0">
                        </div>
                    </div>

                    <p class="text-[0.62rem] text-[#b0a8a0] italic" id="ayudaStock">
                        Ingresa las unidades individuales disponibles hoy.
                    </p>

                    <div id="stockPreview" class="hidden mt-3 fade-in bg-[#f0f7f2] border
                         border-[#c8e0cc] rounded-xl px-4 py-2.5 flex items-center gap-2">
                        <i class="bi bi-check-circle-fill text-[#4a7c59] text-sm"></i>
                        <p id="stockPreviewTexto" class="text-[#2d4a35] text-xs font-medium"></p>
                    </div>
                </div>

                {{-- PASO 4: Precios --}}
                <div>
                    <div class="section-header">
                        <div class="step-badge">4</div>
                        <p class="text-[#2a2522] text-sm font-semibold">Precios</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="label">Costo por unidad <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#9a9390] text-sm">$</span>
                                <input type="number" name="costo_unidad" id="costoInput"
                                    value="{{ old('costo_unidad', 0) }}" step="0.01" min="0"
                                    oninput="actualizarMargen()"
                                    class="input-field input-field-icon" placeholder="0">
                            </div>
                        </div>
                        <div>
                            <label class="label">Precio de venta <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#9a9390] text-sm">$</span>
                                <input type="number" name="precio_venta" id="precioInput"
                                    value="{{ old('precio_venta', 0) }}" step="0.01" min="0"
                                    oninput="actualizarMargen()"
                                    class="input-field input-field-icon" placeholder="0">
                            </div>
                        </div>
                    </div>

                    {{-- Preview margen --}}
                    <div id="previewMargen" class="hidden bg-[#f5f3ef] border border-[#e8e4e0]
                         rounded-xl px-4 py-3">
                        <p class="text-[0.62rem] font-semibold tracking-widest uppercase text-[#9a9390] mb-1">
                            Margen estimado
                        </p>
                        <p id="valorMargen" class="text-base font-semibold text-[#2a2522]">—</p>
                        <div class="mt-2 h-1.5 bg-[#e8e4e0] rounded-full overflow-hidden">
                            <div id="barraMargen" class="h-full rounded-full transition-all duration-500"
                                 style="width:0%"></div>
                        </div>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="flex gap-3 pt-4 border-t border-[#f0ede8] mt-auto">
                    <a href="{{ route('inventario.index') }}"
                        class="flex-1 text-center py-2.5 bg-[#f5f3ef] text-[#9a9390] text-sm
                               rounded-xl hover:bg-[#ede8e2] transition-all duration-200">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check-lg mr-1"></i> Guardar producto
                    </button>
                </div>
            </div> {{-- Fin Columna Derecha --}}
        </div> {{-- Fin Grid --}}
    </form>
</div>

{{-- MODAL IMPORTAR --}}
<div id="modalImportar" class="fixed inset-0 bg-gray-900/20 backdrop-blur-sm hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex justify-between items-center mb-5">
            <div>
                <p class="text-[#9a9390] text-xs tracking-widest uppercase mb-1">Inventario</p>
                <h2 class="text-[#2a2522] text-xl">Importar productos</h2>
            </div>
            <button onclick="cerrarModalImportar()" class="text-[#9a9390] hover:text-black">✕</button>
        </div>
        <form action="{{ route('inventario.importar') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-5">
                <label class="label">Archivo Excel (.xlsx, .xls, .csv)</label>
                <input type="file" name="archivo" accept=".xlsx,.xls,.csv" required class="input-field">
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="cerrarModalImportar()" class="flex-1 py-2.5 bg-[#f5f3ef] text-[#9a9390] text-sm rounded-xl">Cancelar</button>
                <button type="submit" class="flex-1 py-2.5 bg-[#2d4a35] text-white text-sm font-medium rounded-xl">Importar</button>
            </div>
        </form>
    </div>
</div>

<script>
let presentacionActual = 'unidad';

function seleccionarPresentacion(tipo) {
    presentacionActual = tipo;
    document.getElementById('unidadCompraInput').value = tipo;

    ['unidad','caja'].forEach(t => document.getElementById('card-'+t).classList.remove('active'));
    document.getElementById('card-'+tipo).classList.add('active');

    const fieldEmpaque = document.getElementById('fieldUnidadesPaquete');
    const labelStock   = document.getElementById('labelStock');
    const ayuda        = document.getElementById('ayudaStock');

    if (tipo === 'unidad') {
        fieldEmpaque.classList.add('hidden');
        labelStock.textContent = 'Cantidad disponible hoy';
        ayuda.textContent = 'Ingresa las unidades individuales disponibles hoy.';
        document.getElementById('unidadesPorPaquete').value = 1;
    } else {
        fieldEmpaque.classList.remove('hidden');
        labelStock.textContent = '¿Cuántas cajas tienes hoy?';
        ayuda.textContent = 'El sistema convertirá las cajas a unidades automáticamente.';
    }
    actualizarResumen();
}

function actualizarResumen() {
    const stock   = parseFloat(document.getElementById('stockInicial')?.value) || 0;
    const empaque = parseInt(document.getElementById('unidadesPorPaquete')?.value) || 1;
    const preview = document.getElementById('stockPreview');
    const texto   = document.getElementById('stockPreviewTexto');

    if (presentacionActual !== 'unidad' && stock > 0 && empaque > 1) {
        const total = stock * empaque;
        preview.classList.remove('hidden');
        texto.textContent = `${stock} caja(s) × ${empaque} uds = ${total.toLocaleString('es-CO')} unidades en stock`;
    } else {
        preview.classList.add('hidden');
    }
}

function actualizarMargen() {
    const costo  = parseFloat(document.getElementById('costoInput')?.value) || 0;
    const precio = parseFloat(document.getElementById('precioInput')?.value) || 0;
    const prev   = document.getElementById('previewMargen');
    const val    = document.getElementById('valorMargen');
    const barra  = document.getElementById('barraMargen');

    if (costo > 0 && precio > 0) {
        const margen = (precio - costo) / precio * 100;
        const markup = (precio - costo) / costo * 100;
        prev.classList.remove('hidden');
        val.textContent = `${margen.toFixed(1)}% margen · ${markup.toFixed(1)}% markup`;
        const color = margen >= 30 ? '#4a7c59' : (margen >= 10 ? '#856404' : '#e07070');
        val.style.color = color;
        barra.style.width = Math.min(margen, 100) + '%';
        barra.style.background = color;
    } else {
        prev.classList.add('hidden');
    }
}
function abrirModalImportar() {
    const m = document.getElementById('modalImportar');
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function cerrarModalImportar() {
    const m = document.getElementById('modalImportar');
    m.classList.add('hidden');
    m.classList.remove('flex');
}
</script>

@endsection