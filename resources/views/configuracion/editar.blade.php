@extends('layouts.app')
@section('title', 'Editar Configuración')

@push('styles')
    @vite(['resources/css/inventario.css', 'resources/css/configuracion.css'])
@endpush

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">


{{-- Header --}}
<div class="flex items-center justify-between mb-6 max-w-5xl mx-auto">
    <div>
        <p class="text-slate-500 text-[0.65rem] font-bold tracking-widest uppercase mb-1 drop-shadow-sm">Ajustes</p>
        <h1 class="page-title">Configuración</h1>
    </div>
    <a href="/dashboard"
        class="premium-button-slate py-2.5">
        <i class="bi bi-arrow-left text-xs"></i> Dashboard
    </a>
</div>

@if($errors->any())
<div class="max-w-5xl mx-auto mb-5 bg-[#fdecea] border border-[#f0c0bc]
            text-[#7a2d2d] px-5 py-3 rounded-xl text-sm">
    <ul class="space-y-0.5">
        @foreach($errors->all() as $error)
            <li><i class="bi bi-exclamation-circle mr-1"></i>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Card configuración --}}
<div class="max-w-5xl mx-auto glass-card overflow-hidden mb-6">

    {{-- Header verde --}}
    <div class="bg-[#2d4a35] px-8 py-5 flex items-center gap-4">
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
            <i class="bi bi-gear text-white text-lg"></i>
        </div>
        <div>
            <p class="text-white font-semibold text-base">{{ $negocio->nombre_comercial }}</p>
            <p class="text-[#a8c8a0] text-xs">
                {{ $negocio->esReventa() ? 'Negocio de reventa' : 'Negocio de servicios' }}
                · {{ $negocio->moneda }} · {{ $negocio->pais }}
            </p>
        </div>
    </div>

    <form action="/configuracion/editar" method="POST">
        @csrf
        @method('PUT')

        {{-- GRID 2 COLUMNAS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-200/60">

            {{-- COLUMNA IZQUIERDA: Datos del negocio --}}
            <div class="p-8 space-y-5">
                <div class="section-header">
                    <div class="section-dot"></div>
                    <p class="text-[#2a2522] text-sm font-semibold tracking-wide">Datos del negocio</p>
                </div>

                <div>
                    <label class="premium-label">Nombre del negocio</label>
                    <input type="text" name="nombre_comercial"
                        value="{{ old('nombre_comercial', $negocio->nombre_comercial) }}"
                        class="premium-input">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="premium-label">País</label>
                        <input type="text" name="pais"
                            value="{{ old('pais', $negocio->pais) }}"
                            class="premium-input">
                    </div>
                    <div>
                        <label class="premium-label">Moneda</label>
                        <input type="text" name="moneda"
                            value="{{ old('moneda', $negocio->moneda) }}"
                            class="premium-input">
                    </div>
                </div>

                <div>
                    <label class="premium-label">Dirección</label>
                    <input type="text" name="direccion"
                        value="{{ old('direccion', $negocio->direccion) }}"
                        placeholder="Calle 10 # 5-20, Bogotá"
                        class="premium-input">
                </div>

                <div>
                    <label class="premium-label">Teléfono</label>
                    <input type="text" name="telefono"
                        value="{{ old('telefono', $negocio->telefono) }}"
                        placeholder="+57 300 123 4567"
                        class="premium-input">
                </div>
            </div>

            {{-- COLUMNA DERECHA: Configuración estratégica --}}
            <div class="p-8 space-y-5">
                <div class="section-header">
                    <div class="section-dot"></div>
                    <p class="text-[#2a2522] text-sm font-semibold tracking-wide">Configuración estratégica</p>
                </div>

                {{-- Margen solo para servicios --}}
                @if($negocio->esServicios())
                <div>
                    <label class="label">Margen de ganancia (%)</label>
                    <input type="number" name="margen_operacional"
                        value="{{ old('margen_operacional', $config->margen_operacional) }}"
                        class="input-field">
                    <p class="mt-1 text-[0.62rem] text-[#b0a8a0] italic">
                        Para negocios de reventa el margen se calcula automáticamente desde el inventario.
                    </p>
                </div>
                @else
                <div class="bg-[#f0f7f2] border border-[#c8e0cc] rounded-xl px-4 py-3 flex items-start gap-3">
                    <div class="w-7 h-7 bg-[#2d4a35] rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="bi bi-info-lg text-white text-xs"></i>
                    </div>
                    <div>
                        <p class="text-[#2d4a35] text-xs font-semibold mb-0.5">Margen calculado automáticamente</p>
                        <p class="text-[#5a7a60] text-xs leading-relaxed">
                            Para negocios de reventa, el margen se calcula desde el costo y precio real de cada producto en el inventario.
                        </p>
                    </div>
                </div>
                <input type="hidden" name="margen_operacional" value="0">
                @endif

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="premium-label">Días de operación / mes</label>
                        <input type="number" name="dias_operacion"
                            value="{{ old('dias_operacion', $config->dias_operacion) }}"
                            class="premium-input">
                    </div>
                    <div>
                        <label class="premium-label">Sueldo deseado</label>
                        <input type="number" name="sueldo_dueno"
                            value="{{ old('sueldo_dueno', $config->sueldo_dueno) }}"
                            class="premium-input">
                    </div>
                </div>

                @if($negocio->esServicios())
                <div>
                    <label class="premium-label">Ingresos proyectados</label>
                    <input type="number" name="ingresos_proyectados"
                        value="{{ old('ingresos_proyectados', $config->ingresos_proyectados) }}"
                        class="premium-input">
                    @error('ingresos_proyectados')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @else
                <input type="hidden" name="ingresos_proyectados"
                    value="{{ $config->ingresos_proyectados }}">
                @endif

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="premium-label">Ahorro / reinversión</label>
                        <input type="number" name="utilidad_ahorro_reinversion"
                            value="{{ old('utilidad_ahorro_reinversion', $config->utilidad_ahorro_reinversion) }}"
                            class="premium-input">
                    </div>
                    <div>
                        <label class="premium-label">Dinero en caja / banco</label>
                        <input type="number" name="dinero_disponible"
                            value="{{ old('dinero_disponible', $config->dinero_disponible) }}"
                            class="premium-input">
                    </div>
                </div>

                {{-- Botones --}}
                <div class="flex gap-3 pt-4 border-t border-slate-200/60">
                    <a href="/dashboard"
                        class="flex-1 premium-button-slate">
                        Cancelar
                    </a>
                    <button type="submit" class="premium-button-emerald flex-1">
                        <i class="bi bi-check-lg mr-1"></i> Guardar cambios
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

{{-- Card Gastos Fijos --}}
<div class="max-w-5xl mx-auto glass-card overflow-hidden">

    <div class="bg-[#2d4a35] px-8 py-5 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="bi bi-receipt text-white text-lg"></i>
            </div>
            <div>
                <p class="text-white font-semibold text-base">Gastos Fijos</p>
                <p class="text-[#a8c8a0] text-xs">Gastos recurrentes mensuales del negocio</p>
            </div>
        </div>
        <button onclick="document.getElementById('modalGasto').classList.remove('hidden')"
            class="flex items-center gap-2 bg-white/20 text-white text-xs font-medium
                   px-4 py-2 rounded-xl hover:bg-white/30 transition-all duration-200">
            <i class="bi bi-plus-lg"></i> Nuevo gasto
        </button>
    </div>

    <div class="px-8 py-2 divide-y divide-[#f0ede8]">
        @forelse($gastosFijos as $gasto)
        <div class="flex justify-between items-center py-3.5">
            <span class="text-[#2a2522] text-sm flex-1">{{ $gasto->descripcion }}</span>
            <span class="text-[#4a7c59] text-sm font-semibold mx-6">
                {{ number_format($gasto->monto, 0, ',', '.') }}
            </span>
            <div class="flex items-center gap-2">
                <button onclick="abrirModalEditar({{ $gasto->id }}, '{{ $gasto->descripcion }}', {{ $gasto->monto }})"
                    class="text-[#4a7c59] text-xs border border-[#c8dfc4] px-3 py-1.5 rounded-lg
                           hover:bg-[#d6e8d0] hover:text-[#2d4a35] transition-all duration-200">
                    <i class="bi bi-pencil text-xs"></i>
                </button>
                <form action="/gasto-fijo/{{ $gasto->id }}" method="POST"
                      onsubmit="return confirm('¿Eliminar este gasto fijo?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="text-red-400 text-xs border border-red-200 px-3 py-1.5 rounded-lg
                               hover:bg-red-50 hover:text-red-600 transition-all duration-200">
                        <i class="bi bi-trash3 text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="py-10 text-center">
            <i class="bi bi-inbox text-3xl text-[#c8c4c0]"></i>
            <p class="text-[#b0a8a0] text-sm mt-2">No hay gastos fijos registrados.</p>
        </div>
        @endforelse
    </div>
</div>

{{-- Modal Nuevo Gasto --}}
<div id="modalGasto" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white border border-[#e8e4e0] rounded-2xl p-8 shadow-lg w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-[#2d4a35] text-lg font-semibold">Nuevo Gasto Fijo</h3>
            <button onclick="document.getElementById('modalGasto').classList.add('hidden')"
                class="w-8 h-8 flex items-center justify-center rounded-full bg-[#f5f3ef]
                       text-[#9a9390] hover:bg-[#ede8e2] transition text-sm">✕</button>
        </div>
        <form action="/gasto-fijo" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="label">Descripción</label>
                <input type="text" name="descripcion" value="{{ old('descripcion') }}"
                    class="input-field">
            </div>
            <div>
                <label class="label">Monto</label>
                <input type="number" name="monto" step="0.01" value="{{ old('monto') }}"
                    class="input-field">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Guardar</button>
                <button type="button"
                    onclick="document.getElementById('modalGasto').classList.add('hidden')"
                    class="flex-1 border border-[#e8e4e0] text-[#8a8280] py-2.5 rounded-xl
                           text-sm hover:bg-[#f5f3ef] transition-all duration-200">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Editar Gasto --}}
<div id="modalEditarGasto" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white border border-[#e8e4e0] rounded-2xl p-8 shadow-lg w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-[#2d4a35] text-lg font-semibold">Editar Gasto Fijo</h3>
            <button onclick="document.getElementById('modalEditarGasto').classList.add('hidden')"
                class="w-8 h-8 flex items-center justify-center rounded-full bg-[#f5f3ef]
                       text-[#9a9390] hover:bg-[#ede8e2] transition text-sm">✕</button>
        </div>
        <form id="formEditarGasto" action="" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="label">Descripción</label>
                <input type="text" id="editDescripcion" name="descripcion" class="input-field">
            </div>
            <div>
                <label class="label">Monto</label>
                <input type="number" id="editMonto" name="monto" step="0.01" class="input-field">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Guardar</button>
                <button type="button"
                    onclick="document.getElementById('modalEditarGasto').classList.add('hidden')"
                    class="flex-1 border border-[#e8e4e0] text-[#8a8280] py-2.5 rounded-xl
                           text-sm hover:bg-[#f5f3ef] transition-all duration-200">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalEditar(id, descripcion, monto) {
    document.getElementById('editDescripcion').value = descripcion;
    document.getElementById('editMonto').value = monto;
    document.getElementById('formEditarGasto').action = '/gasto-fijo/' + id;
    document.getElementById('modalEditarGasto').classList.remove('hidden');
}
</script>

@endsection