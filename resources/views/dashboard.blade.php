{{-- Vista principal (Dashboard) donde se muestran las métricas financieras e interacciones clave --}}
@extends('layouts.app')

@push('styles')
    @vite(['resources/css/dashboard.css'])
@endpush

@section('title', 'Dashboard')

@section('content')

@php
    $colorBarra = $ventasMes >= $puntoEquilibrio ? '#4a7c59' : '#e07070';
    $colorDias = $diasSupervivencia < 15 ? 'text-red-400' : ($diasSupervivencia < 30 ? 'text-gray-400' : 'text-[#4a7c59]');
    if ($metaMes && $avanceReal >= $metaMes->meta) {
        $mensajeEquilibrio = '✓ ¡Meta cumplida!';
        $colorMensaje = 'text-[#4a7c59]';
    } elseif ($avanceReal >= $puntoEquilibrio) {
        $mensajeEquilibrio = '✓ Punto de equilibrio completado';
        $colorMensaje = 'text-[#4a7c59]';
    } else {
        $mensajeEquilibrio = 'En progreso hacia la meta';
        $colorMensaje = 'text-[#9a9390]';
}
    $colorUtilidad = $utilidadMes >= 0 ? 'text-[#4a7c59]' : 'text-red-400';
    $mensajeUtilidad = $utilidadMes >= 0 ? 'Estás generando ganancia' : 'Estás en pérdida este mes';

    $radio = 90;
    $circunferencia = 2 * pi() * $radio;
    $offset = $circunferencia - ($porcentajeAvance / 100) * $circunferencia;
@endphp

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>




{{-- ══════════════ HEADER ══════════════ --}}
<div class="flex flex-col xl:flex-row items-start xl:items-center justify-between mb-8 animate-fade-in gap-5 xl:gap-0">
  <div>
    <p class="text-slate-500 text-xs font-semibold tracking-widest uppercase mb-1 drop-shadow-sm">Panel principal</p>
    <h1 class="text-slate-800 text-2xl md:text-3xl font-light tracking-tight leading-tight drop-shadow-sm">
      Buenos días, <em class="text-[#4a7c59] font-medium not-italic drop-shadow-[0_2px_10px_rgba(74,124,89,0.2)]">{{ $negocio->nombre_comercial ?? 'Negocio' }}.</em>
    </h1>
  </div>
    <div class="flex flex-wrap gap-2 lg:gap-3 w-full xl:w-auto">
        @if($negocio->tieneInventario())
        <a href="{{ route('inventario.index') }}"
        class="flex items-center justify-center gap-2 bg-white/60 backdrop-blur-md text-[#2d4a35] text-xs md:text-sm font-medium px-4 md:px-5 py-2.5 rounded-xl hover:bg-white hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 border border-emerald-100/50 shadow-sm flex-1 md:flex-none">
        <i class="bi bi-box-seam"></i><span>Inventario</span>
        </a>
        @endif
        @if($negocio->esReventa())
        <button onclick="abrirCierreCaja()"
        class="flex items-center justify-center gap-2 bg-white/60 backdrop-blur-md text-[#2d4a35] text-xs md:text-sm font-medium px-4 md:px-5 py-2.5 rounded-xl hover:bg-white hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 border border-emerald-100/50 shadow-sm flex-1 md:flex-none">
        <i class="bi bi-cash-stack"></i><span class="whitespace-nowrap">Cierre caja</span>
        </button>
        @endif
        @if($negocio->esReventa())
        <button onclick="abrirAgenteIA()"
            class="flex items-center justify-center gap-2 bg-gradient-to-r from-[#2d4a35] to-[#3d5e45] text-white text-xs md:text-sm font-medium
                px-4 md:px-5 py-2.5 rounded-xl shadow-md hover:shadow-xl hover:shadow-[#4a7c59]/30 hover:-translate-y-0.5 transition-all duration-300 flex-1 md:flex-none">
            <i class="bi bi-stars"></i><span class="whitespace-nowrap">Análisis IA</span>
        </button>
        @endif
        <button onclick="abrirHistorial()"
        class="btn-danger flex items-center justify-center gap-2 bg-gradient-to-r from-red-50 to-rose-50 border border-red-100/50 text-red-600 shadow-sm text-xs md:text-sm font-medium px-4 md:px-5 py-2.5 rounded-xl hover:shadow-md transition-all duration-300 flex-1 md:flex-none">
        <i class="bi bi-clock-history"></i><span>Historial</span>
        </button>
    </div>
</div>

{{-- ══════════════ PROYECCIÓN DE CIERRE ══════════════ --}}
@if($proyeccionCierre !== null && now()->day > 3)
<div class="mb-5 px-5 py-4 rounded-2xl flex items-center justify-between shadow-sm backdrop-blur-md animate-fade-in
    {{ $proyeccionCierre >= $metaMes->meta ? 'bg-gradient-to-r from-emerald-50/80 to-[#d6e8d0]/80 border border-emerald-200/60' : 'bg-white/80 border border-slate-200/60' }}">
    <div class="flex items-center gap-4">
        <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $proyeccionCierre >= $metaMes->meta ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-500' }} shadow-inner">
            <span class="text-xl">{{ $proyeccionCierre >= $metaMes->meta ? '📈' : '📉' }}</span>
        </div>
        <div>
            <p class="text-[0.65rem] text-slate-500 font-bold tracking-widest uppercase mb-0.5">Proyección de cierre</p>
            <p class="text-sm font-medium {{ $proyeccionCierre >= $metaMes->meta ? 'text-emerald-900' : 'text-slate-700' }}">
                A este ritmo cerrarás el mes en
                <strong class="font-semibold px-1">{{ $negocio->moneda }} {{ number_format($proyeccionCierre, 0, ',', '.') }}</strong>
                <span class="{{ $proyeccionCierre >= $metaMes->meta ? 'text-emerald-700' : 'text-slate-500' }} text-xs ml-1">
                    {{ $proyeccionCierre >= $metaMes->meta ? '✓ Superarás tu proyección' : '— Por debajo de tu proyección' }}
                </span>
            </p>
        </div>
    </div>
</div>
@endif

  {{-- ══════════════ BANNER CIERRE DE MES ══════════════ --}}
  @if($mostrarBanner && $bannerData)
  <div id="bannerCierreMes"
      class="mb-6 rounded-2xl p-6 shadow-md backdrop-blur-md animate-fade-in border {{ $bannerData->ventas_real >= $bannerData->meta ? 'bg-gradient-to-br from-emerald-50/90 to-teal-50/90 border-emerald-200/50' : 'bg-gradient-to-br from-rose-50/90 to-red-50/90 border-rose-200/50' }}">
      <div class="flex items-start justify-between">
          <div class="w-full">
              <p class="text-xs font-bold tracking-widest uppercase mb-1.5 {{ $bannerData->ventas_real >= $bannerData->meta ? 'text-emerald-700/70' : 'text-rose-700/70' }}">
                  Resumen del mes anterior
              </p>
              <h3 class="text-xl font-semibold mb-4 {{ $bannerData->ventas_real >= $bannerData->meta ? 'text-emerald-900 drop-shadow-sm' : 'text-rose-900 drop-shadow-sm' }}">
                  {{ $bannerData->ventas_real >= $bannerData->meta ? '🎉 ¡Superaste tu proyección!' : '⚠️ No alcanzaste tu proyección' }}
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 bg-white/40 rounded-xl p-4 border border-white/50 mt-4 md:mt-0">
                  <div>
                      <p class="text-[0.65rem] text-slate-500 font-bold uppercase tracking-widest mb-1">Ventas reales</p>
                      <p class="font-bold text-slate-800 text-lg">{{ $negocio->moneda }} {{ number_format($bannerData->ventas_real, 0, ',', '.') }}</p>
                  </div>
                  <div class="border-t md:border-t-0 md:border-l border-white/40 pt-3 md:pt-0 md:pl-6">
                      <p class="text-[0.65rem] text-slate-500 font-bold uppercase tracking-widest mb-1">Proyección era</p>
                      <p class="font-bold text-slate-800 text-lg">{{ $negocio->moneda }} {{ number_format($bannerData->meta, 0, ',', '.') }}</p>
                  </div>
                  <div class="border-t md:border-t-0 md:border-l border-white/40 pt-3 md:pt-0 md:pl-6">
                      <p class="text-[0.65rem] text-emerald-600 font-bold uppercase tracking-widest mb-1">Nueva proyección</p>
                      <p class="font-bold text-emerald-900 text-lg">{{ $negocio->moneda }} {{ number_format($metaMes->meta, 0, ',', '.') }}</p>
                  </div>
              </div>
          </div>
          <button onclick="cerrarBanner()"
              class="w-8 h-8 flex items-center justify-center rounded-full bg-white/60 text-slate-400 hover:bg-white hover:text-slate-600 transition-all shadow-sm ml-6 flex-shrink-0">
              ✕
          </button>
      </div>
  </div>
  @endif

  {{-- ══════════════ REGISTROS ══════════════ --}}
@if($negocio->esServicios())

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

    <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white/60 p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
        <p class="text-slate-500 text-[0.65rem] font-bold tracking-widest uppercase mb-1 drop-shadow-sm">Registrar</p>
        <h2 class="text-slate-800 text-xl font-medium tracking-tight mb-5 drop-shadow-sm">Nueva Venta</h2>
        <form action="/venta" method="POST">
            @csrf
            <input type="hidden" name="fecha" value="{{ now()->toDateString() }}">
            <input type="text" name="descripcion"
                class="w-full bg-white/70 backdrop-blur-sm border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 mb-3 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500/50 transition-all hover:bg-white"
                placeholder="Descripción de la venta (opcional)">
            <input type="number" name="monto" step="0.01" min="0"
                class="w-full bg-white/70 backdrop-blur-sm border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 mb-4 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500/50 transition-all hover:bg-white"
                placeholder="Valor vendido">
            <button class="btn-primary w-full bg-gradient-to-r from-emerald-100 to-emerald-200 border border-emerald-300/50 text-emerald-800 font-medium text-sm py-3 rounded-xl hover:bg-emerald-300 hover:shadow-md transition-all duration-300">
                + Registrar venta
            </button>
        </form>
    </div>

    <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white/60 p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
        <p class="text-slate-500 text-[0.65rem] font-bold tracking-widest uppercase mb-1 drop-shadow-sm">Registrar</p>
        <h2 class="text-slate-800 text-xl font-medium tracking-tight mb-5 drop-shadow-sm">Nuevo Gasto</h2>
        <form action="/gasto" method="POST">
            @csrf
            <input type="hidden" name="fecha" value="{{ now()->toDateString() }}">
            <input type="text" name="descripcion"
                class="w-full bg-white/70 backdrop-blur-sm border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 mb-3 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-400/50 transition-all hover:bg-white"
                placeholder="Descripción del gasto">
            <input type="number" name="monto" step="0.01" min="0"
                class="w-full bg-white/70 backdrop-blur-sm border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 mb-4 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-400/50 transition-all hover:bg-white"
                placeholder="0">
            <button class="btn-danger w-full bg-gradient-to-r from-red-50 to-rose-100 border border-red-200 text-red-700 font-medium text-sm py-3 rounded-xl hover:shadow-md transition-all duration-300">
                + Agregar gasto
            </button>
        </form>
    </div>

</div>

@elseif($negocio->esReventa())
<div class="mb-6 space-y-4">

    {{-- FILA 1: Registrar Venta (ancho completo) --}}
    <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white/60 p-5 transition-all duration-300 hover:shadow-lg relative z-20">
        <p class="text-slate-500 text-[0.65rem] font-bold tracking-widest uppercase mb-1 drop-shadow-sm">Operación diaria</p>
        <h2 class="text-slate-800 text-lg font-medium tracking-tight mb-3 drop-shadow-sm">Registrar Venta</h2>

        <form action="{{ route('venta.registrar') }}" method="POST" id="formVentaInventario">
            @csrf
            <input type="hidden" name="fecha" value="{{ now()->toDateString() }}">
            <input type="hidden" name="descripcion" value="Venta del día">

            <div id="lineasVenta"
                 class="space-y-2 mb-2 max-h-[170px] overflow-y-auto pr-1 custom-scrollbar"
                 style="min-height: 50px;">
            </div>

            <div class="flex flex-wrap gap-2 items-center">
                <button type="button" onclick="agregarLineaVenta()"
                    class="flex-1 min-w-[160px] py-2 border border-dashed border-emerald-300 bg-emerald-50/50 text-emerald-700 text-sm font-medium rounded-xl hover:bg-emerald-100/60 hover:shadow-sm transition-all duration-200">
                    <i class="bi bi-plus-circle mr-1"></i> Agregar producto
                </button>

                <div id="previewVenta" class="hidden flex-1 min-w-[160px] bg-slate-50/80 backdrop-blur-sm border
                     border-slate-200/60 rounded-xl px-3 py-2 shadow-inner">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 text-[0.65rem] uppercase tracking-wide font-bold">Total</span>
                        <span id="totalVenta" class="text-slate-800 text-sm font-semibold">$0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 text-[0.65rem] uppercase tracking-wide font-bold">Margen</span>
                        <span id="margenVenta" class="text-[0.7rem] font-medium text-emerald-600">—</span>
                    </div>
                </div>

                <select name="metodo_pago" id="inputMetodoPago"
                    class="appearance-none flex-shrink-0 pl-4 pr-10 py-2 bg-white/70 backdrop-blur-md border border-emerald-100/50
                           rounded-xl text-sm font-medium text-[#2d4a35] focus:outline-none focus:ring-2 focus:ring-[#4a7c59]/40 focus:border-[#4a7c59]/50 transition-all shadow-sm hover:shadow-md cursor-pointer bg-no-repeat bg-[right_1rem_center] bg-[length:1em_1em]" 
                    style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%232d4a35%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E')">
                    <option value="efectivo" class="bg-white text-slate-800 font-medium py-1"> Efectivo</option>
                    <option value="transferencia" class="bg-white text-slate-800 font-medium py-1"> Transferencia</option>
                </select>

                <button type="submit"
                    class="btn-primary flex-shrink-0 w-full md:w-auto bg-gradient-to-r from-[#2d4a35] to-[#3d5e45] text-white font-medium
                           text-sm px-6 py-2 rounded-xl shadow-md cursor-pointer mt-2 md:mt-0">
                    <i class="bi bi-check-lg mr-1"></i> Registrar venta
                </button>
            </div>
        </form>
    </div>
    {{-- FILA 2: Entrada de Mercancía + Gasto variable --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- Entrada mercancía --}}
        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white/60 p-5 transition-all duration-300 hover:shadow-lg">
            <p class="text-slate-500 text-[0.65rem] font-bold tracking-widest uppercase mb-1 drop-shadow-sm">Inventario</p>
            <h2 class="text-slate-800 text-base font-medium tracking-tight mb-3 drop-shadow-sm">Entrada de Mercancía</h2>
            <form action="{{ route('compras.store') }}" method="POST" class="space-y-2.5">
                @csrf
                <input type="hidden" name="fecha" value="{{ now()->toDateString() }}">

                <div class="relative z-50">
                    <select name="item_id" required id="selEntradaMercancia"
                        class="w-full px-3 py-2.5 bg-white/70 backdrop-blur-sm border border-slate-200 rounded-xl
                               text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/30 transition-all hover:bg-white">
                        <option value="">— Seleccionar producto —</option>
                        @foreach($itemsConStock as $item)
                            <option value="{{ $item->id }}">
                                {{ $item->nombre }} ({{ number_format($item->stock, 0) }} ud)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <input type="number" name="cantidad" step="any" min="0.001" required
                        placeholder="Cantidad"
                        class="px-3 py-2.5 bg-white/70 backdrop-blur-sm border border-slate-200 rounded-xl text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500/30 transition-all hover:bg-white">
                    <input type="number" name="costo_unitario" step="any" min="0" required
                        placeholder="Costo unit."
                        class="px-3 py-2.5 bg-white/70 backdrop-blur-sm border border-slate-200 rounded-xl text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500/30 transition-all hover:bg-white">
                </div>

                <input type="text" name="descripcion" placeholder="Proveedor o Factura #"
                    class="w-full px-3 py-2.5 bg-white/70 backdrop-blur-sm border border-slate-200 rounded-xl text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500/30 transition-all hover:bg-white">

                <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-50 to-sky-100 border border-blue-200 text-blue-700 text-sm font-medium py-2.5 rounded-xl hover:shadow-md transition-all duration-200 cursor-pointer">
                    <i class="bi bi-box-arrow-in-down mr-1"></i> Registrar compra
                </button>
            </form>
        </div>

        {{-- Gasto variable --}}
        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white/60 p-5 transition-all duration-300 hover:shadow-lg">
            <p class="text-slate-500 text-[0.65rem] font-bold tracking-widest uppercase mb-1 drop-shadow-sm">Finanzas</p>
            <h2 class="text-slate-800 text-base font-medium tracking-tight mb-3 drop-shadow-sm">Gasto variable</h2>
            <form action="/gasto" method="POST" class="space-y-2.5">
                @csrf
                <input type="hidden" name="fecha" value="{{ now()->toDateString() }}">
                <input type="text" name="descripcion" placeholder="Descripción del gasto"
                    class="w-full px-3 py-2.5 bg-white/70 backdrop-blur-sm border border-slate-200 rounded-xl text-sm
                           focus:outline-none focus:ring-2 focus:ring-red-500/20 transition-all hover:bg-white">
                <input type="number" name="monto" step="0.01" min="0" placeholder="Monto"
                    class="w-full px-3 py-2.5 bg-white/70 backdrop-blur-sm border border-slate-200 rounded-xl text-sm
                           focus:outline-none focus:ring-2 focus:ring-red-500/20 transition-all hover:bg-white">
                <button type="submit" class="w-full bg-gradient-to-r from-red-50 to-rose-100 border border-red-200 text-red-700 text-sm font-medium py-2.5 rounded-xl
                               hover:shadow-md transition-all duration-200 cursor-pointer">
                    <i class="bi bi-dash-circle mr-1"></i> Agregar gasto
                </button>
            </form>
        </div>

    </div>
</div>
@endif

{{-- ══════════════ CARDS MÉTRICAS ══════════════ --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

    <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white/60 p-5 transition-transform duration-300 hover:-translate-y-1 hover:shadow-xl">
        <p class="text-slate-500 text-[0.65rem] font-bold tracking-widest uppercase mb-2 drop-shadow-sm">Ventas Proyectadas</p>
        <p class="text-slate-800 text-2xl tracking-tight leading-tight font-medium">
            {{ $negocio->moneda }} {{ number_format($metaMes->meta,0,',','.') }}
        </p>
    </div>

    <div class="backdrop-blur-md rounded-2xl p-5 transition-transform duration-300 hover:-translate-y-1 hover:shadow-xl border {{ $avanceReal >= $puntoEquilibrio ? 'bg-gradient-to-br from-emerald-50/90 to-teal-50/90 border-emerald-200/50 shadow-sm' : 'bg-white/80 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border-white/60' }}">
        <p class="text-slate-500 text-[0.65rem] font-bold tracking-widest uppercase mb-2 drop-shadow-sm">Punto de Equilibrio</p>
        <p class="{{ $avanceReal >= $puntoEquilibrio ? 'text-emerald-900 font-semibold' : 'text-slate-800 font-medium' }} text-2xl tracking-tight leading-tight">
            {{ $negocio->moneda }} {{ number_format($puntoEquilibrio,0,',','.') }}
        </p>
    </div>

    <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white/60 p-5 transition-transform duration-300 hover:-translate-y-1 hover:shadow-xl">
        <p class="text-slate-500 text-[0.65rem] font-bold tracking-widest uppercase mb-2 drop-shadow-sm">Días Supervivencia</p>
        <p class="text-2xl tracking-tight leading-tight font-medium {{ $colorDias }}">
            {{ $diasSupervivencia }} días
        </p>
    </div>

    <div class="backdrop-blur-md rounded-2xl p-5 transition-transform duration-300 hover:-translate-y-1 hover:shadow-xl border {{ $utilidadMes >= 0 ? 'bg-gradient-to-br from-emerald-50/90 to-teal-50/90 border-emerald-200/50 shadow-sm' : 'bg-gradient-to-br from-rose-50/90 to-red-50/90 border-rose-200/50 shadow-sm' }}">
        <p class="text-slate-500 text-[0.65rem] font-bold tracking-widest uppercase mb-2 drop-shadow-sm">Utilidad del Mes</p>
        <p class="text-2xl tracking-tight leading-tight font-semibold {{ $colorUtilidad }}">
            {{ $negocio->moneda }} {{ number_format($utilidadMes,0,',','.') }}
        </p>
        <p class="text-[0.65rem] font-medium text-slate-500/80 mt-1 uppercase tracking-wide">{{ $mensajeUtilidad }}</p>
    </div>

</div>

{{-- ══════════════ TERMÓMETROS CIRCULARES ══════════════ --}}
@php
    $metaVentas = $metaMes->meta ?? 1;
    $pctVentas  = $metaVentas > 0 ? min(($ventasMes / $metaVentas) * 100, 100) : 0;

    // Gastos variables del mes
    $gastosVariablesMes = \App\Models\MovimientoCaja::where('negocio_id', $negocio->id)
        ->where('es_venta', false)
        ->whereMonth('fecha', now()->month)
        ->whereYear('fecha', now()->year)
        ->sum('monto');

    // Para reventa: compras de inventario. Para servicios: gastos variables
    $comprasMes = 0;
    $promedioCompras = 0;
    if ($negocio->esReventa()) {
        $comprasMes = \App\Models\MovimientoCaja::where('negocio_id', $negocio->id)
            ->where('es_venta', false)
            ->whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->sum('monto');

        $promedioCompras = \App\Models\MovimientoCaja::where('negocio_id', $negocio->id)
            ->where('es_venta', false)
            ->where('fecha', '>=', now()->subMonths(3)->startOfMonth())
            ->where('fecha', '<', now()->startOfMonth())
            ->selectRaw('MONTH(fecha) as mes, SUM(monto) as total')
            ->groupBy('mes')
            ->pluck('total')
            ->avg() ?? 0;
    } else {
        // Servicios: gastos variables vs promedio 3 meses
        $comprasMes = $gastosVariablesMes;
        $promedioCompras = \App\Models\MovimientoCaja::where('negocio_id', $negocio->id)
            ->where('es_venta', false)
            ->where('fecha', '>=', now()->subMonths(3)->startOfMonth())
            ->where('fecha', '<', now()->startOfMonth())
            ->selectRaw('MONTH(fecha) as mes, SUM(monto) as total')
            ->groupBy('mes')
            ->pluck('total')
            ->avg() ?? 0;
    }

    $guiaCompras = $config->presupuesto_compras_mensual ?? 0;
    $etiquetaGuia = 'Promedio 3m';
    
    // Si tienen una guía explícita (Reventa), predomina frente al histórico
    if ($guiaCompras > 0) {
        $promedioCompras = $guiaCompras;
        $etiquetaGuia = 'Guía mensual';
    } elseif ($negocio->esServicios() && $promedioCompras == 0) {
        // Para servicios sin historial: tomar Otros Gastos Fijos "un poquito más arriba" (ej. + 15%)
        $otrosGastos = \App\Models\GastoFijo::where('negocio_id', $negocio->id)
            ->where('descripcion', 'Otros gastos fijos')
            ->value('monto') ?? 0;
            
        if ($otrosGastos > 0) {
            $promedioCompras = $otrosGastos * 1.15; // 15% arriba como colchón
            $etiquetaGuia = 'Guía base (Otros gastos)';
        }
    }

    $refCompras   = max($promedioCompras, $comprasMes, 1);
    $pctCompras   = min(($comprasMes / $refCompras) * 100, 100);

    $colorVentasCirc = $ventasMes >= $puntoEquilibrio ? '#4a7c59'
        : ($pctVentas >= 50 ? '#856404' : '#e07070');
    $colorComprasCirc = $negocio->esReventa() ? '#3a6a9a' : '#e07070';

    $radioC = 70;
    $circC  = 2 * pi() * $radioC;
    $offsetVentas  = $circC - ($pctVentas  / 100) * $circC;
    $offsetCompras = $circC - ($pctCompras / 100) * $circC;
    $offsetRent    = $circC - ($porcentajeAvance / 100) * $circC;
@endphp

<div class="grid {{ $negocio->esServicios() ? 'grid-cols-1 md:grid-cols-2' : 'grid-cols-1 md:grid-cols-3' }} gap-5 mb-6">

    @if($negocio->esReventa())
    {{-- Termómetro 1: Rentabilidad --}}
    <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white/60 p-5 flex flex-col items-center transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
        <p class="text-slate-500 text-[0.65rem] font-bold tracking-widest uppercase mb-4 drop-shadow-sm">Rentabilidad</p>
        <div class="relative flex items-center justify-center">
            <svg width="150" height="150" class="-rotate-90">
                <circle cx="75" cy="75" r="{{ $radioC }}" stroke="#f1f5f9" stroke-width="13" fill="transparent"/>
                <circle cx="75" cy="75" r="{{ $radioC }}"
                    stroke="{{ $colorBarra }}" stroke-width="13" fill="transparent"
                    stroke-dasharray="{{ $circC }}" stroke-dashoffset="{{ $offsetRent }}"
                    stroke-linecap="round" class="transition-all duration-700 drop-shadow-sm"/>
            </svg>
            <div class="absolute flex flex-col items-center">
                <span class="text-slate-800 text-xl font-bold tracking-tight">{{ number_format($porcentajeAvance,1) }}%</span>
                <span class="text-slate-400 text-[0.6rem] font-semibold uppercase tracking-wider mt-0.5">completado</span>
                <span class="text-emerald-700 text-[0.62rem] mt-0.5 font-bold tracking-wide">
                    {{ $negocio->moneda }} {{ number_format($avanceReal, 0, ',', '.') }}
                </span>
            </div>
        </div>
        <p class="mt-3 text-[0.65rem] font-bold uppercase tracking-wider {{ $colorMensaje }} text-center drop-shadow-sm">{{ $mensajeEquilibrio }}</p>
    </div>
    @endif


    {{-- Termómetro 2: Ventas del mes --}}
    <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white/60 p-5 flex flex-col items-center transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
        <p class="text-slate-500 text-[0.65rem] font-bold tracking-widest uppercase mb-4 drop-shadow-sm">Ventas del mes</p>
        <div class="relative flex items-center justify-center">
            <svg width="150" height="150" class="-rotate-90">
                <circle cx="75" cy="75" r="{{ $radioC }}" stroke="#f1f5f9" stroke-width="13" fill="transparent"/>
                <circle cx="75" cy="75" r="{{ $radioC }}"
                    stroke="{{ $colorVentasCirc }}" stroke-width="13" fill="transparent"
                    stroke-dasharray="{{ $circC }}" stroke-dashoffset="{{ $offsetVentas }}"
                    stroke-linecap="round" class="transition-all duration-700 drop-shadow-sm"/>
            </svg>
            <div class="absolute flex flex-col items-center">
                <span class="text-slate-800 text-xl font-bold tracking-tight">{{ number_format($pctVentas,1) }}%</span>
                <span class="text-slate-400 text-[0.6rem] font-semibold uppercase tracking-wider mt-0.5">de la meta</span>
                <span class="text-[0.62rem] mt-0.5 font-bold tracking-wide" style="color:{{ $colorVentasCirc }}">
                    {{ $negocio->moneda }} {{ number_format($ventasMes, 0, ',', '.') }}
                </span>
            </div>
        </div>
        <div class="mt-3 text-center">
            @if($ventasMes >= $puntoEquilibrio)
                <p class="text-[0.65rem] text-emerald-600 font-bold tracking-wide uppercase drop-shadow-sm">✓ PE cubierto</p>
            @else
                <p class="text-[0.65rem] text-rose-500 font-bold tracking-wide uppercase drop-shadow-sm">
                    Faltan {{ $negocio->moneda }} {{ number_format($puntoEquilibrio - $ventasMes, 0, ',', '.') }} para PE
                </p>
            @endif
            <p class="text-[0.6rem] text-slate-400 font-medium tracking-wide uppercase mt-1">
                Meta: {{ $negocio->moneda }} {{ number_format($metaVentas, 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Termómetro 3: Gastos variables / Compras --}}
    <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white/60 p-5 flex flex-col items-center transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
        <p class="text-slate-500 text-[0.65rem] font-bold tracking-widest uppercase mb-4 drop-shadow-sm">
            {{ $negocio->esReventa() ? 'Compras inventario' : 'Gastos variables' }}
        </p>
        <div class="relative flex items-center justify-center">
            <svg width="150" height="150" class="-rotate-90">
                <circle cx="75" cy="75" r="{{ $radioC }}" stroke="#f1f5f9" stroke-width="13" fill="transparent"/>
                <circle cx="75" cy="75" r="{{ $radioC }}"
                    stroke="{{ $colorComprasCirc }}" stroke-width="13" fill="transparent"
                    stroke-dasharray="{{ $circC }}" stroke-dashoffset="{{ $offsetCompras }}"
                    stroke-linecap="round" class="transition-all duration-700 drop-shadow-sm"/>
            </svg>
            <div class="absolute flex flex-col items-center">
                <span class="text-slate-800 text-xl font-bold tracking-tight">{{ number_format($pctCompras,1) }}%</span>
                <span class="text-slate-400 text-[0.6rem] font-semibold uppercase tracking-wider mt-0.5">de la guía</span>
                <span class="text-[0.62rem] mt-0.5 font-bold tracking-wide" style="color:{{ $colorComprasCirc }}">
                    {{ $negocio->moneda }} {{ number_format($comprasMes, 0, ',', '.') }}
                </span>
            </div>
        </div>
        <div class="mt-3 text-center">
            @if($comprasMes > $promedioCompras && $promedioCompras > 0)
                <p class="text-[0.65rem] text-amber-600 font-bold tracking-wide uppercase drop-shadow-sm">↑ Sobre la guía</p>
            @elseif($comprasMes > 0)
                <p class="text-[0.65rem] text-emerald-600 font-bold tracking-wide uppercase drop-shadow-sm">✓ Dentro de la guía</p>
            @else
                <p class="text-[0.65rem] text-slate-400 font-bold tracking-wide uppercase">Sin gastos este mes</p>
            @endif
            <p class="text-[0.6rem] text-slate-400 font-medium tracking-wide uppercase mt-1">
                {{ $etiquetaGuia }}: {{ $negocio->moneda }} {{ number_format($promedioCompras, 0, ',', '.') }}
            </p>
        </div>
    </div>

</div>

  {{-- ══════════════ SIMULADOR ══════════════ --}}
  <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white/60 p-6 mb-6 transition-all duration-300 hover:shadow-lg">

    <p class="text-slate-500 text-[0.65rem] font-bold tracking-widest uppercase mb-1 drop-shadow-sm">Herramienta</p>
    <h2 class="text-slate-800 text-xl font-medium tracking-tight mb-6 drop-shadow-sm">Simulador What-If</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

      <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-100/50">
        <label class="text-[0.65rem] font-bold text-slate-500 tracking-widest uppercase mb-1 font-mono">
          Margen operacional
        </label>
        <div class="flex items-center justify-between mt-1 mb-3">
          <span class="text-slate-800 text-sm font-medium">Margen</span>
          <span id="valorMargen" class="text-emerald-700 font-bold text-sm bg-emerald-100/50 px-2 rounded-md">{{ $negocio->esReventa() ? number_format($margenPonderado * 100, 1) : $config->margen_operacional }}%</span>
        </div>
        <input type="range" id="margen" min="1" max="100"
          value="{{ $negocio->esReventa() ? number_format($margenPonderado * 100, 1, '.', '') : $config->margen_operacional }}" class="w-full">
      </div>

      <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-100/50">
        <label class="text-[0.65rem] font-bold text-slate-500 tracking-widest uppercase mb-1 font-mono">
          Gastos fijos
        </label>
        <div class="flex items-center justify-between mt-1 mb-3">
          <span class="text-slate-800 text-sm font-medium">Gastos</span>
          <span id="valorGastos" class="text-rose-600 font-bold text-sm bg-rose-50 px-2 rounded-md">{{ number_format($gastosFijos,0,',','.') }}</span>
        </div>
        <input type="range" id="gastos" min="0"
          max="{{ $gastosFijos * 3 }}" value="{{ $gastosFijos }}" class="w-full">
      </div>

      <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-100/50">
        <label class="text-[0.65rem] font-bold text-slate-500 tracking-widest uppercase mb-1 font-mono">
          Sueldo dueño
        </label>
        <div class="flex items-center justify-between mt-1 mb-3">
          <span class="text-slate-800 text-sm font-medium">Sueldo</span>
          <span id="valorSueldo" class="text-emerald-700 font-bold text-sm bg-emerald-100/50 px-2 rounded-md">{{ number_format($config->sueldo_dueno,0,',','.') }}</span>
        </div>
        <input type="range" id="sueldo" min="0"
          max="{{ $config->sueldo_dueno * 3 }}" value="{{ $config->sueldo_dueno }}" class="w-full">
      </div>

    </div>

    <div class="mt-6 bg-gradient-to-r from-slate-50 to-slate-100 rounded-xl p-5 border border-slate-200/60 shadow-inner flex items-center justify-between">
      <div>
        <p class="text-slate-500 text-[0.65rem] font-bold tracking-widest uppercase mb-1">Resultado Simulado</p>
        <p id="resultadoSimulador" class="text-slate-800 text-2xl font-semibold tracking-tight">Mueve un slider</p>
      </div>
      <div class="text-4xl opacity-20 mix-blend-multiply filter grayscale">📈</div>
    </div>

  </div>

    {{-- ══════════════ MODAL CIERRE DE CAJA ══════════════ --}}
    @if($negocio->esReventa())
    <div id="modalCierreCaja"
    class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden items-center justify-center z-50 transition-all duration-300">

    <div class="bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl w-full max-w-xl mx-4 p-6 border border-white/50 animate-fade-in">

        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-slate-500 text-[0.65rem] font-bold tracking-widest uppercase mb-1">Resumen del día</p>
            <h2 class="text-slate-800 text-xl font-medium tracking-tight">Cierre de Caja</h2>
            <p class="text-slate-400 text-xs mt-0.5 font-medium">{{ now()->format('d/m/Y') }}</p>
        </div>
        <button onclick="cerrarCierreCaja()"
            class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100/80
                text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition-all shadow-sm text-sm">✕</button>
        </div>

        @php
        $hoy = now()->toDateString();

        $ventasHoy = \App\Models\MovimientoCaja::where('negocio_id', $negocio->id)
            ->where('es_venta', true)
            ->whereDate('fecha', $hoy)
            ->get();

        $totalHoy        = $ventasHoy->sum('monto');
        $totalEfectivo   = $ventasHoy->where('metodo_pago', 'efectivo')->sum('monto');
        $totalTransfer   = $ventasHoy->where('metodo_pago', 'transferencia')->sum('monto');
        $cantidadVentas  = $ventasHoy->count();
        @endphp

        {{-- Cards resumen --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3 text-center">
            <p class="text-emerald-700 text-[0.65rem] uppercase tracking-widest font-bold mb-1">Total vendido</p>
            <p class="text-emerald-900 text-lg font-bold">
            {{ $negocio->moneda }} {{ number_format($totalHoy, 0, ',', '.') }}
            </p>
            <p class="text-emerald-600 text-[0.6rem] mt-0.5 font-medium">{{ $cantidadVentas }} {{ $cantidadVentas === 1 ? 'venta' : 'ventas' }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-3 text-center shadow-sm">
            <p class="text-slate-500 text-[0.65rem] uppercase tracking-widest font-bold mb-1">
            <i class="bi bi-cash mr-1"></i>Efectivo
            </p>
            <p class="text-slate-800 text-lg font-bold">
            {{ $negocio->moneda }} {{ number_format($totalEfectivo, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-3 text-center shadow-sm">
            <p class="text-slate-500 text-[0.65rem] uppercase tracking-widest font-bold mb-1">
            <i class="bi bi-phone mr-1"></i>Transferencia
            </p>
            <p class="text-slate-800 text-lg font-bold">
            {{ $negocio->moneda }} {{ number_format($totalTransfer, 0, ',', '.') }}
            </p>
        </div>
        </div>

        {{-- Detalle de ventas del día --}}
        @if($ventasHoy->count() > 0)
        <div class="border border-slate-200 rounded-xl overflow-hidden mb-4 shadow-sm">
        <div class="bg-slate-50 px-4 py-2 border-b border-slate-200">
            <p class="text-slate-500 text-[0.65rem] font-bold tracking-widest uppercase">Detalle de ventas de hoy</p>
        </div>
        <div class="max-h-52 overflow-y-auto overflow-x-auto w-full">
            <table class="w-full text-sm min-w-[400px] border-collapse">
            <thead>
                <tr class="text-white">
                <th class="bg-[#2d4a35] px-4 py-2.5 text-left text-xs uppercase tracking-widest font-semibold border-r border-[#3d5e45]">Hora</th>
                <th class="bg-[#2d4a35] px-4 py-2.5 text-left text-xs uppercase tracking-widest font-semibold border-r border-[#3d5e45]">Concepto</th>
                <th class="bg-[#2d4a35] px-4 py-2.5 text-center text-xs uppercase tracking-widest font-semibold border-r border-[#3d5e45]">Método</th>
                <th class="bg-[#2d4a35] px-4 py-2.5 text-right text-xs uppercase tracking-widest font-semibold">Monto</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($ventasHoy->sortByDesc('created_at') as $venta)
                <tr class="hover:bg-slate-50 transition">
                <td class="px-4 py-2.5 text-slate-500 text-xs border-r border-slate-100">
                    {{ \Carbon\Carbon::parse($venta->created_at)->format('H:i') }}
                </td>
                <td class="px-4 py-2.5 text-slate-800 border-r border-slate-100">
                    {{ $venta->descripcion ?? 'Venta' }}
                </td>
                <td class="px-4 py-2.5 text-center border-r border-slate-100">
                    @if($venta->metodo_pago === 'efectivo')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[0.65rem]
                                bg-emerald-100 text-emerald-800 font-medium">
                        <i class="bi bi-cash"></i> Efectivo
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[0.65rem]
                                bg-blue-100 text-blue-800 font-medium">
                        <i class="bi bi-phone"></i> Transf.
                    </span>
                    @endif
                </td>
                <td class="px-4 py-2.5 text-right font-semibold text-slate-900">
                    {{ $negocio->moneda }} {{ number_format($venta->monto, 0, ',', '.') }}
                </td>
                </tr>
                @endforeach
            </tbody>
            </table>
        </div>
        </div>
        @else
        <div class="text-center py-6 text-[#b0a8a0] text-sm mb-4">
            <i class="bi bi-cash-stack text-2xl block mb-2 opacity-40"></i>
            No hay ventas registradas hoy todavía.
        </div>
        @endif

        {{-- Botón imprimir --}}
        <button onclick="imprimirCierreCaja()"
        class="w-full flex items-center justify-center gap-2 border border-[#e8e4e0]
                text-[#5a5250] py-2.5 rounded-xl text-sm font-medium
                hover:bg-[#f5f3ef] transition-all duration-200">
        <i class="bi bi-printer"></i> Imprimir cierre de caja
        </button>

    </div>
    </div>
    @endif
{{-- ══════════════ MODAL HISTORIAL ══════════════ --}}
<div id="modalHistorial"
  class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden items-center justify-center z-50 transition-all duration-300">

  <div class="bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl w-full max-w-3xl mx-4 p-6 relative border border-white/50 animate-fade-in">

    <div class="flex justify-between items-center mb-6">
      <div>
        <p class="text-slate-500 text-[0.65rem] font-bold tracking-widest uppercase mb-1">Registro</p>
        <h2 class="text-slate-800 text-xl font-medium tracking-tight">Historial de Movimientos</h2>
      </div>
      <button onclick="cerrarHistorial()"
        class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100/80 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition-all shadow-sm text-sm">
        ✕
      </button>
    </div>
    <div class="flex gap-2 mb-4">
        <button onclick="filtrarMovimientos('todos')" id="btn-todos"
            class="px-4 py-1.5 rounded-xl text-xs font-medium bg-[#2d4a35] text-white transition-all duration-200">
            Todos
        </button>
        <button onclick="filtrarMovimientos('ventas')" id="btn-ventas"
            class="px-4 py-1.5 rounded-xl text-xs font-medium bg-[#f5f3ef] text-[#9a9390] transition-all duration-200">
            Ventas
        </button>
        <button onclick="filtrarMovimientos('gastos')" id="btn-gastos"
            class="px-4 py-1.5 rounded-xl text-xs font-medium bg-[#f5f3ef] text-[#9a9390] transition-all duration-200">
            Gastos
        </button>
    </div>
    <div class="max-h-96 overflow-y-auto overflow-x-auto w-full">
      <table class="w-full text-sm min-w-[500px]">
        <thead>
          <tr class="text-white">
          <th class="bg-[#2d4a35] px-4 py-3 text-left text-xs tracking-widest uppercase font-semibold rounded-tl-xl whitespace-nowrap">Fecha</th>
          <th class="bg-[#2d4a35] px-4 py-3 text-left text-xs tracking-widest uppercase font-semibold">Descripción</th>
          <th class="bg-[#2d4a35] px-4 py-3 text-left text-xs tracking-widest uppercase font-semibold whitespace-nowrap">Monto</th>
          <th class="bg-[#2d4a35] px-4 py-3 text-left text-xs tracking-widest uppercase font-semibold rounded-tr-xl whitespace-nowrap">Acciones</th>
          </tr>
        </thead>
        <tbody>
    @php
        $ayerStart = now()->subDay()->startOfDay();
    @endphp
    @foreach($movimientos as $movimiento)
    @if(\Carbon\Carbon::parse($movimiento->fecha)->startOfDay()->gte($ayerStart))
    <tr class="border-b border-[#f0ede8] hover:bg-[#faf8f5] transition fila-movimiento"
        data-tipo="{{ $movimiento->es_venta ? 'ventas' : 'gastos' }}">
        <td class="px-4 py-3 text-[#9a9390] text-xs whitespace-nowrap">
            {{ \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y') }}
        </td>
        <td class="px-4 py-3 text-[#2a2522] min-w-[150px]">
            {{ $movimiento->descripcion ?? 'Venta' }}
        </td>
        <td class="px-4 py-3 font-medium text-[#2a2522] whitespace-nowrap">
            {{ $negocio->moneda }} {{ number_format($movimiento->monto,2,',','.') }}
        </td>
        <td class="px-4 py-3 flex gap-2 whitespace-nowrap">
            @php
                $tieneDetalles = ($movimiento->es_venta && $movimiento->ventas_detalle_count > 0) || (!$movimiento->es_venta && $movimiento->compras_detalle_count > 0);
            @endphp
            @if(!$tieneDetalles)
            <button
                class="bg-[#dcedf5] text-[#3a6a9a] px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-[#b8d8f0] transition btn-editar"
                data-id="{{ $movimiento->id }}"
                data-descripcion="{{ $movimiento->descripcion }}"
                data-monto="{{ $movimiento->monto }}"
                data-fecha="{{ \Carbon\Carbon::parse($movimiento->fecha)->format('Y-m-d') }}">
                <i class="bi bi-pencil-square"></i>
            </button>
            @endif
            <form action="{{ route('movimiento.eliminar', $movimiento->id) }}"
                method="POST"
                onsubmit="return confirm('¿Eliminar este movimiento?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="bg-[#f2d8d8] text-[#8a3a3a] px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-[#e0b0b0] transition">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </td>
    </tr>
    @endif
    @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>


{{-- ══════════════ MODAL EDITAR ══════════════ --}}
<div id="modalEditar"
  class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden items-center justify-center z-50 transition-all duration-300">

  <div class="bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 border border-white/50 animate-fade-in">

    <div class="mb-5">
      <p class="text-slate-500 text-[0.65rem] font-bold tracking-widest uppercase mb-1">Modificar registro</p>
      <h2 class="text-slate-800 text-xl font-medium tracking-tight">Editar Movimiento</h2>
    </div>

    <form id="formEditar" method="POST">
      @csrf
      @method('PUT')

      <div class="mb-4">
        <label class="block text-[#9a9390] text-xs tracking-widest uppercase mb-2">Descripción</label>
        <input id="editDescripcion" name="descripcion"
          class="w-full border border-[#e8e4e0] rounded-xl px-4 py-3 text-sm text-[#2a2522] focus:outline-none focus:ring-2 focus:ring-[#b8d8b0] focus:border-transparent">
      </div>

      <div class="mb-4">
        <label class="block text-[#9a9390] text-xs tracking-widest uppercase mb-2">Fecha (No editable)</label>
        <input id="editFecha" name="fecha" type="date" required readonly
          class="w-full bg-slate-50 border border-[#e8e4e0] rounded-xl px-4 py-3 text-sm text-[#9a9390] cursor-not-allowed focus:outline-none">
      </div>

      <div class="mb-6">
        <label class="block text-[#9a9390] text-xs tracking-widest uppercase mb-2">Monto</label>
        <input id="editMonto" name="monto" type="number"
          class="w-full border border-[#e8e4e0] rounded-xl px-4 py-3 text-sm text-[#2a2522] focus:outline-none focus:ring-2 focus:ring-[#b8d8b0] focus:border-transparent">
      </div>

      <div class="flex justify-end gap-3">
        <button type="button" onclick="cerrarModal()"
          class="px-5 py-2.5 bg-[#f5f3ef] text-[#9a9390] text-sm rounded-xl hover:bg-[#ede8e2] transition">
          Cancelar
        </button>
        <button type="submit"
          class="btn-primary px-5 py-2.5 bg-[#2d4a35] text-[#f0ede8] text-sm font-medium rounded-xl">
          Guardar cambios
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ══════════════ PANEL RECOMENDACIONES ══════════════ --}}
<div id="panelRecomendaciones"
    class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden items-center justify-center z-50 transition-all duration-300">

    <div class="bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl w-full max-w-lg mx-4 p-6 border border-white/50 animate-fade-in">

        <div class="flex justify-between items-center mb-6">
            <div>
                <p class="text-emerald-600 text-[0.65rem] font-bold tracking-widest uppercase mb-1 drop-shadow-sm">Asesor financiero</p>
                <h2 class="text-slate-800 text-xl font-medium tracking-tight">Recomendaciones</h2>
            </div>
            <button onclick="cerrarRecomendaciones()"
                class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100/80 text-slate-500 hover:bg-slate-200 hover:text-slate-700 shadow-sm transition-all text-sm">
                ✕
            </button>
        </div>

        <div class="space-y-3 max-h-96 overflow-y-auto">
            @foreach($recomendaciones as $rec)
            <div class="rounded-xl p-4 border
                {{ $rec['tipo'] === 'verde' ? 'bg-[#d6e8d0] border-[#a8c8a0]' : '' }}
                {{ $rec['tipo'] === 'rojo' ? 'bg-[#f2d8d8] border-[#e0b0b0]' : '' }}
                {{ $rec['tipo'] === 'amarillo' ? 'bg-[#fff3cd] border-[#ffc107]' : '' }}
                {{ $rec['tipo'] === 'info' ? 'bg-[#faf9f7] border-[#e8e4e0]' : '' }}
                {{ $rec['tipo'] === 'accion' ? 'bg-[#f0ede8] border-[#e0dbd4]' : '' }}">

                <p class="text-sm text-[#2a2522] mb-2">{{ $rec['mensaje'] }}</p>

              @if($rec['accion'])
                      <a href="{{ $rec['accion']['url'] }}"
                          class="inline-flex items-center gap-1.5 text-xs font-medium text-[#2d4a35] border border-[#a8c8a0] px-3 py-1.5 rounded-lg hover:bg-[#d6e8d0] transition-all duration-200">
                          <i class="bi bi-arrow-right-circle"></i>
                          {{ $rec['accion']['texto'] }}
                      </a>
                      @endif
                  </div>
                  @endforeach
              </div>

              <div class="mt-4 pt-4 border-t border-[#f0ede8]">
                  <p class="text-[#9a9390] text-xs">Actualizado: {{ now()->format('d/m/Y H:i') }}</p>
              </div>
              </div>
</div>

{{-- ══════════════ MODAL FACTURA ELECTRÓNICA ══════════════ --}}
@if(session('nueva_venta_id') && $negocio->esReventa())
<div id="modalFactura"
     class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center z-50 transition-all duration-300">

    <div class="bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 border border-white/50 animate-fade-in">

        {{-- Header --}}
        <div class="flex items-start justify-between mb-6">
            <div>
                <p class="text-emerald-600 text-[0.65rem] font-bold tracking-widest uppercase mb-1">Venta registrada ✓</p>
                <h2 class="text-slate-800 text-xl font-medium tracking-tight">¿Enviar factura al comprador?</h2>
            </div>
            <button onclick="cerrarModalFactura()"
                class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100/80
                       text-slate-500 hover:bg-slate-200 hover:text-slate-700 shadow-sm transition-all text-sm ml-4 flex-shrink-0">
                ✕
            </button>
        </div>

        <p class="text-sm text-[#8a8280] mb-5 leading-relaxed">
            Puedes enviarle la factura simulada al correo del comprador.
            Si no la necesitas, simplemente cierra este aviso.
        </p>

        {{-- Formulario correo --}}
        <form action="{{ route('facturas.enviar', session('nueva_venta_id')) }}"
              method="POST" id="formEnviarFactura">
            @csrf

            <div id="campoCorreo">
                <label class="block text-[#8a8280] text-[0.7rem] font-medium tracking-widest uppercase mb-1.5">
                    Correo del comprador
                </label>
                <input type="email" name="email_comprador"
                       id="inputCorreoComprador"
                       placeholder="comprador@correo.com"
                       class="w-full px-4 py-2.5 bg-[#faf9f7] border border-[#e8e4e0] rounded-xl
                              text-[#2a2522] text-sm focus:outline-none focus:border-[#a8c8a0]
                              focus:ring-2 focus:ring-[#a8c8a0]/20 transition-all duration-200 mb-4">
            </div>

            {{-- Botones --}}
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 bg-[#2d4a35] text-[#f0ede8] py-2.5 rounded-xl text-sm
                               font-medium hover:bg-[#3d5e45] transition-all duration-200
                               flex items-center justify-center gap-2">
                    <i class="bi bi-send"></i> Enviar factura
                </button>
                <button type="button"
                        onclick="cerrarModalFactura()"
                        class="flex-1 border border-[#e8e4e0] text-[#8a8280] py-2.5 rounded-xl
                               text-sm font-medium hover:bg-[#f5f3ef] transition-all duration-200">
                    Ahora no
                </button>
            </div>

        </form>

    </div>
</div>
@endif

{{-- ══════════════ MODAL AGENTE IA ══════════════ --}}
@if($negocio->esReventa())
<div id="modalAgenteIA"
    class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden items-center justify-center z-50 transition-all duration-300">

    <div class="bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl w-full max-w-xl mx-4 p-6 border border-white/50 animate-fade-in">

        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <p class="text-emerald-700 text-[0.65rem] font-bold tracking-widest uppercase mb-1">
                    <i class="bi bi-stars text-emerald-500 mr-1 shadow-sm"></i> Asistente IA
                </p>
                <h2 class="text-slate-800 text-xl font-medium tracking-tight">Análisis de ventas</h2>
            </div>
            <button onclick="cerrarAgenteIA()"
                class="w-8 h-8 flex items-center justify-center rounded-full
                       bg-slate-100/80 text-slate-500 hover:bg-slate-200 hover:text-slate-700 shadow-sm transition-all text-sm">✕
            </button>
        </div>

        {{-- Selector de período --}}
        <div class="flex gap-2 mb-5">
            <button onclick="analizarPeriodo('semana')" id="ia-btn-semana"
                class="flex-1 py-2 rounded-xl text-sm font-medium transition
                       bg-[#2d4a35] text-white">
                <i class="bi bi-calendar-week mr-1"></i> Esta semana
            </button>
            <button onclick="analizarPeriodo('mes')" id="ia-btn-mes"
                class="flex-1 py-2 rounded-xl text-sm font-medium transition
                       bg-[#f5f3ef] text-[#9a9390]">
                <i class="bi bi-calendar-month mr-1"></i> Este mes
            </button>
        </div>

        {{-- Spinner de carga --}}
        <div id="iaLoading" class="hidden text-center py-10">
            <div class="inline-block w-9 h-9 border-4 border-[#d6e8d0]
                        border-t-[#2d4a35] rounded-full animate-spin mb-3"></div>
            <p class="text-[#9a9390] text-sm">Analizando tus ventas con IA...</p>
            <p class="text-[#c8c4c0] text-xs mt-1">Esto puede tomar unos segundos</p>
        </div>

        {{-- Error --}}
        <div id="iaError" class="hidden bg-[#fdecea] border border-[#f0c0bc]
                                  rounded-xl px-4 py-3 text-[#8a3a3a] text-sm">
        </div>

        {{-- Resultado --}}
        <div id="iaResultado" class="hidden">

            {{-- Mini resumen de cabecera --}}
            <div id="iaMeta"
                class="flex gap-3 mb-4 p-3 bg-[#f0f7f2] border border-[#c8e0cc] rounded-xl">
            </div>

            {{-- Texto del análisis --}}
            <div class="bg-[#faf9f7] border border-[#e8e4e0] rounded-xl p-4
                        max-h-80 overflow-y-auto">
                <div id="iaTexto"
                     class="text-[#2a2522] text-sm leading-relaxed whitespace-pre-wrap">
                </div>
            </div>

            <p class="text-[0.62rem] text-[#c8c4c0] mt-3 text-center">
                Análisis generado por IA · Solo orientativo · No reemplaza asesoría profesional
            </p>
        </div>

    </div>
</div>
@endif
<script>
    const ventasMes = {{ $ventasMes }};
    const esServicios = {{ $negocio->esServicios() ? 'true' : 'false' }};
</script>
<script src="{{ asset('js/simulador.js') }}?v={{ time() }}"></script>

@if($negocio->tieneInventario())
<script>
    const ventasMesActual = {{ $ventasMes }};
    const gastosFijosBase = {{ $gastosFijos }};
    const productosDisponibles = @json($productosVendibles);
    
    let contadorLinea = 0;

    function agregarLineaVenta() {
        const selects = document.querySelectorAll('.linea-venta select');
        if (selects.length > 0) {
            const lastSelect = selects[selects.length - 1];
            if (!lastSelect.value) {
                alert('Por favor selecciona un producto en la línea actual antes de agregar otro.');
                lastSelect.focus();
                return;
            }
        }
        
        contadorLinea++;
        const div = document.createElement('div');
        div.className = 'flex gap-2 items-center linea-venta';
        const selectId = 'sel-prod-' + contadorLinea;
        div.innerHTML = `
            <select id="${selectId}" name="items[${contadorLinea}][item_id]" onchange="onProductoChange(this)"
                class="flex-1 px-3 py-2 bg-[#faf9f7] border border-[#e8e4e0] rounded-xl text-sm focus:outline-none focus:border-[#a8c8a0]">
                <option value="">-- Producto --</option>
                ${productosDisponibles.map(p => `
                    <option value="${p.id}" data-precio="${p.precio_venta}" data-costo="${p.costo_compra}">
                        ${p.nombre}
                    </option>`).join('')}
            </select>
            <input type="number" name="items[${contadorLinea}][cantidad]" step="any" min="1" value="1"
                oninput="calcularTotalVenta()"
                class="w-16 px-2 py-2 bg-[#faf9f7] border border-[#e8e4e0] rounded-xl text-sm focus:outline-none">
            <span class="subtotal-label text-[#9a9390] text-xs w-20 text-right font-mono"></span>
            <button type="button" onclick="this.closest('.linea-venta').remove(); calcularTotalVenta()"
                class="text-red-400 hover:text-red-600 transition p-1 cursor-pointer z-10 flex-shrink-0">
                <i class="bi bi-trash"></i>
            </button>
        `;
        const contenedor = document.getElementById('lineasVenta');
        contenedor.appendChild(div);

        new TomSelect('#' + selectId, {
            create: false,
            dropdownParent: 'body',
            sortField: {
                field: "text",
                direction: "asc"
            }
        });

        calcularTotalVenta();
        contenedor.scrollTop = contenedor.scrollHeight;
    }

    function onProductoChange(select) {
        calcularTotalVenta();
    }

    function calcularTotalVenta() {
        let totalVenta = 0;
        let gananciaTotal = 0;
        document.querySelectorAll('.linea-venta').forEach(row => {
            const sel = row.querySelector('select');
            const opt = sel.options[sel.selectedIndex];
            const cantInput = row.querySelector('input[name*="[cantidad]"]');
            if (opt && opt.value !== "") {
                const precio = parseFloat(opt.dataset.precio) || 0;
                const costo = parseFloat(opt.dataset.costo) || 0;
                const cantidad = parseFloat(cantInput.value) || 0;
                const subtotal = precio * cantidad;
                totalVenta += subtotal;
                gananciaTotal += (precio - costo) * cantidad;
                row.querySelector('.subtotal-label').textContent = '$' + subtotal.toLocaleString('es-CO');
            }
        });
        document.getElementById('totalVenta').textContent = '$' + totalVenta.toLocaleString('es-CO');
        const margenVenta = totalVenta > 0 ? ((gananciaTotal / totalVenta) * 100).toFixed(1) : 0;
        const margenEl = document.getElementById('margenVenta');
        margenEl.textContent = margenVenta + '%';
        margenEl.className = margenVenta >= 25 ? 'text-[#4a7c59] font-bold' : 'text-orange-500 font-bold';
        document.getElementById('previewVenta').classList.toggle('hidden', totalVenta === 0);
    }

    document.getElementById('formVentaInventario')?.addEventListener('submit', function(e) {
        this.querySelectorAll('.linea-venta').forEach(row => {
            const select = row.querySelector('select');
            if (!select || !select.value) row.remove();
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        agregarLineaVenta();
        
        if (document.getElementById('selEntradaMercancia')) {
            new TomSelect('#selEntradaMercancia', {
                create: false,
                dropdownParent: 'body',
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        }
    });

</script>
@endif

<script>
function cerrarModalFactura() {
    const modal = document.getElementById('modalFactura');
    if (modal) modal.classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    const formFactura = document.getElementById('formEnviarFactura');
    if (formFactura) {
        formFactura.addEventListener('submit', function(e) {
            const correo = document.getElementById('inputCorreoComprador').value.trim();
            if (!correo) {
                e.preventDefault();
                document.getElementById('inputCorreoComprador').style.borderColor = '#e07070';
                document.getElementById('inputCorreoComprador').focus();
            }
        });
    }
});
function abrirCierreCaja() {
    const modal = document.getElementById('modalCierreCaja');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function cerrarCierreCaja() {
    const modal = document.getElementById('modalCierreCaja');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function imprimirCierreCaja() {
    const contenido = document.getElementById('modalCierreCaja').innerHTML;
    const ventana = window.open('', '_blank', 'width=500,height=700');
    ventana.document.write(`
        <html><head><title>Cierre de Caja</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <style>
            body { font-family: 'Inter', Arial, sans-serif; padding: 40px; font-size: 14px; color: #0f172a; background: #ffffff; }
            button { display: none !important; }
            .rounded-2xl, .rounded-xl { border-radius: 12px; }
            .grid { display: grid; gap: 15px; }
            .grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th { text-transform: uppercase; font-size: 10px; color: #64748b; font-weight: 700; text-align: left; padding: 8px 12px; border-bottom: 2px solid #e2e8f0; }
            td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .bg-\\[\\#d6e8d0\\] { background: #f8fafc; border: 1px solid #e2e8f0; }
            .bg-\\[\\#faf9f7\\] { background: transparent; }
            .border { border: 1px solid #e2e8f0; }
        </style>
        </head><body>${contenido}</body></html>
    `);
    ventana.document.close();
    ventana.print();
}
function abrirAgenteIA() {
    const modal = document.getElementById('modalAgenteIA');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    analizarPeriodo('semana');
}

function cerrarAgenteIA() {
    const modal = document.getElementById('modalAgenteIA');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('iaLoading').classList.add('hidden');
    document.getElementById('iaResultado').classList.add('hidden');
    document.getElementById('iaError').classList.add('hidden');
}

async function analizarPeriodo(periodo) {
    // Botones activo/inactivo
    const btnS = document.getElementById('ia-btn-semana');
    const btnM = document.getElementById('ia-btn-mes');
    const activo   = 'flex-1 py-2 rounded-xl text-sm font-medium transition bg-[#2d4a35] text-white';
    const inactivo = 'flex-1 py-2 rounded-xl text-sm font-medium transition bg-[#f5f3ef] text-[#9a9390]';
    btnS.className = periodo === 'semana' ? activo : inactivo;
    btnM.className = periodo === 'mes'    ? activo : inactivo;
    btnS.innerHTML = (periodo === 'semana' ? '<i class="bi bi-calendar-week mr-1"></i>' : '<i class="bi bi-calendar-week mr-1"></i>') + ' Esta semana';
    btnM.innerHTML = '<i class="bi bi-calendar-month mr-1"></i> Este mes';

    document.getElementById('iaLoading').classList.remove('hidden');
    document.getElementById('iaResultado').classList.add('hidden');
    document.getElementById('iaError').classList.add('hidden');

    try {
        const resp = await fetch('/agente-ia/analizar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ periodo })
        });

        const data = await resp.json();
        document.getElementById('iaLoading').classList.add('hidden');

        if (data.error) {
            document.getElementById('iaError').textContent = data.error;
            document.getElementById('iaError').classList.remove('hidden');
            return;
        }

        // Mini resumen de cabecera
        document.getElementById('iaMeta').innerHTML = `
            <div class="flex-1 text-center">
                <p class="text-[0.62rem] text-[#9a9390] uppercase tracking-wide">Total vendido</p>
                <p class="text-[#2d4a35] font-semibold text-sm">{{ $negocio->moneda }} ${data.meta.total}</p>
            </div>
            <div class="w-px bg-[#c8e0cc]"></div>
            <div class="flex-1 text-center">
                <p class="text-[0.62rem] text-[#9a9390] uppercase tracking-wide">Variación</p>
                <p class="font-semibold text-sm ${data.meta.variacion.startsWith('+') ? 'text-[#4a7c59]' : 'text-[#e07070]'}">${data.meta.variacion}</p>
            </div>
            <div class="w-px bg-[#c8e0cc]"></div>
            <div class="flex-1 text-center">
                <p class="text-[0.62rem] text-[#9a9390] uppercase tracking-wide">Período</p>
                <p class="text-[#2a2522] text-xs font-medium">${data.meta.periodo}</p>
            </div>
        `;

        document.getElementById('iaTexto').textContent = data.analisis;
        document.getElementById('iaResultado').classList.remove('hidden');

    } catch (err) {
        document.getElementById('iaLoading').classList.add('hidden');
        document.getElementById('iaError').textContent =
            'No se pudo conectar. Verifica tu conexión e intenta de nuevo.';
        document.getElementById('iaError').classList.remove('hidden');
    }
}
</script>
@endsection
                
