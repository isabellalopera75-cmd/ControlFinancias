<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; padding: 36px 44px; }

        .header { margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #d6e8d0; }
        .header-top { overflow: hidden; margin-bottom: 8px; }
        .negocio { float: left; }
        .negocio h1 { font-size: 18px; font-weight: bold; color: #2d4a35; }
        .negocio p  { font-size: 10px; color: #6b7280; margin-top: 2px; }
        .informe-titulo { float: right; text-align: right; }
        .informe-titulo h2 { font-size: 20px; font-weight: 900; color: #2d4a35; }
        .informe-titulo p  { font-size: 10px; color: #6b7280; margin-top: 2px; }
        .clearfix::after { content: ''; display: table; clear: both; }

        .destacados { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .destacados td { padding: 10px 14px; font-size: 11px; border: 1px solid #e5e7eb; }
        .dest-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af; margin-bottom: 3px; }
        .dest-valor { font-weight: bold; color: #2d4a35; font-size: 13px; }
        .dest-sub   { font-size: 10px; color: #4a7c59; margin-top: 2px; }

        table.datos { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase;
                         letter-spacing: 0.08em; padding: 8px 12px; margin-bottom: 0; }
        .ventas-title { background: #d6e8d0; color: #2d4a35; }
        .gastos-title { background: #f2d8d8; color: #8a3a3a; }
        thead tr th { padding: 7px 12px; font-size: 9px; text-transform: uppercase;
                      letter-spacing: 0.06em; font-weight: bold; border-bottom: 1px solid #e5e7eb; }
        .th-ventas { background: #f0f7f2; color: #4a7c59; }
        .th-gastos { background: #fdf0f0; color: #8a3a3a; }
        td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; }
        .td-right { text-align: right; }
        .td-center { text-align: center; }
        .mejor-dia { background: #f0f7f2; }
        .badge { background: #d6e8d0; color: #2d4a35; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .sin-movimiento { color: #c0bbb8; }

        .fila-total-ventas { background: #2d4a35; }
        .fila-total-ventas td { color: white; font-weight: bold; font-size: 12px; border: none; }
        .fila-total-gastos { background: #8a3a3a; }
        .fila-total-gastos td { color: white; font-weight: bold; font-size: 12px; border: none; }

        .footer { margin-top: 32px; padding-top: 12px; border-top: 1px solid #e5e7eb;
                  text-align: center; font-size: 9px; color: #9ca3af; }
    </style>
</head>
<body>

    {{-- Encabezado --}}
    <div class="header">
        <div class="header-top clearfix">
            <div class="negocio">
                <h1>{{ $negocio->nombre_comercial }}</h1>
                @if($negocio->direccion) <p>{{ $negocio->direccion }}</p> @endif
                @if($negocio->telefono)  <p>Tel: {{ $negocio->telefono }}</p> @endif
            </div>
            <div class="informe-titulo">
                <h2>INFORME</h2>
                <p>{{ ucfirst($tipo === 'ambos' ? 'Ventas y Gastos' : $tipo) }}</p>
                <p>{{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>

    {{-- Destacados --}}
    @if(in_array($tipo, ['ventas', 'ambos']) && $totalVentas > 0)
    <table class="destacados">
        <tr>
            <td style="width:33%">
                <div class="dest-label">Total Ventas</div>
                <div class="dest-valor">{{ $negocio->moneda }} {{ number_format($totalVentas, 0, ',', '.') }}</div>
            </td>
            @if($diaMasVentas)
            <td style="width:33%">
                <div class="dest-label">🏆 Mejor día de ventas</div>
                <div class="dest-valor">{{ \Carbon\Carbon::parse($diaMasVentas->dia)->format('d/m/Y') }}</div>
                <div class="dest-sub">{{ $negocio->moneda }} {{ number_format($diaMasVentas->total, 0, ',', '.') }}</div>
            </td>
            @endif
            @if($productoMasVendido && $productoMasVendido->item)
            <td style="width:33%">
                <div class="dest-label">🥇 Producto más vendido</div>
                <div class="dest-valor">{{ $productoMasVendido->item->nombre }}</div>
                <div class="dest-sub">{{ number_format($productoMasVendido->total_cantidad, 0) }} uds — {{ $negocio->moneda }} {{ number_format($productoMasVendido->total_monto, 0, ',', '.') }}</div>
            </td>
            @endif
        </tr>
    </table>
    @endif

    {{-- Tabla ventas --}}
    @if(in_array($tipo, ['ventas', 'ambos']))
    <div class="section-title ventas-title">Ventas por día</div>
    <table class="datos">
        <thead>
            <tr>
                <th class="th-ventas" style="text-align:left">Fecha</th>
                <th class="th-ventas td-right">Total Vendido</th>
                <th class="th-ventas td-center">Destacado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($diasRango as $dia)
            @php
                $venta = $ventasPorDia[$dia] ?? null;
                $total = $venta ? $venta->total : 0;
                $esMejorDia = $diaMasVentas && $diaMasVentas->dia === $dia && $total > 0;
            @endphp
            <tr class="{{ $esMejorDia ? 'mejor-dia' : '' }}">
                <td>{{ \Carbon\Carbon::parse($dia)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($dia)->locale('es')->isoFormat('dddd') }}</td>
                <td class="td-right {{ $total > 0 ? '' : 'sin-movimiento' }}">
                    @if($total > 0) {{ $negocio->moneda }} {{ number_format($total, 0, ',', '.') }}
                    @else —
                    @endif
                </td>
                <td class="td-center">
                    @if($esMejorDia)<span class="badge">🏆 Mejor día</span>@endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="fila-total-ventas">
                <td>TOTAL VENTAS</td>
                <td class="td-right">{{ $negocio->moneda }} {{ number_format($totalVentas, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @endif

    {{-- Tabla gastos --}}
    @if(in_array($tipo, ['gastos', 'ambos']))
    <div class="section-title gastos-title">Gastos por día</div>
    <table class="datos">
        <thead>
            <tr>
                <th class="th-gastos" style="text-align:left">Fecha</th>
                <th class="th-gastos td-right">Total Gastado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($diasRango as $dia)
            @php
                $gasto = $gastosPorDia[$dia] ?? null;
                $total = $gasto ? $gasto->total : 0;
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($dia)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($dia)->locale('es')->isoFormat('dddd') }}</td>
                <td class="td-right {{ $total > 0 ? '' : 'sin-movimiento' }}">
                    @if($total > 0) {{ $negocio->moneda }} {{ number_format($total, 0, ',', '.') }}
                    @else —
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="fila-total-gastos">
                <td>TOTAL GASTOS</td>
                <td class="td-right">{{ $negocio->moneda }} {{ number_format($totalGastos, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    <div class="footer">
        <p>Informe generado por ControlFinancias — {{ now()->format('d/m/Y H:i') }}</p>
    </div>

</body>
</html>
