<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            padding: 40px 48px;
            background: white;
        }

        /* Badge simulación */
        .badge-simulacion {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
            font-size: 9px;
            padding: 3px 10px;
            border-radius: 20px;
            margin-bottom: 20px;
            letter-spacing: 0.05em;
        }

        /* Encabezado */
        .header {
            width: 100%;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }
        .header-left { float: left; width: 55%; }
        .header-right { float: right; width: 40%; text-align: right; }
        .clearfix::after { content: ''; display: table; clear: both; }

        .negocio-nombre {
            font-size: 18px;
            font-weight: bold;
            color: #2d4a35;
            margin-bottom: 4px;
        }
        .negocio-info {
            font-size: 10px;
            color: #6b7280;
            margin-top: 2px;
        }
        .factura-titulo {
            font-size: 26px;
            font-weight: 900;
            color: #2d4a35;
            letter-spacing: 0.1em;
        }
        .factura-num {
            font-size: 11px;
            color: #4a7c59;
            font-weight: bold;
            margin-top: 4px;
        }
        .factura-fecha {
            font-size: 10px;
            color: #6b7280;
            margin-top: 2px;
        }

        /* Concepto */
        .concepto {
            background: #faf9f7;
            border: 1px solid #f0ede8;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 20px;
        }
        .concepto-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #9a9390;
            margin-bottom: 2px;
        }
        .concepto-valor {
            font-size: 11px;
            color: #2a2522;
        }

        /* Tabla productos */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        thead tr {
            background: #faf9f7;
        }
        th {
            padding: 8px 12px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #8a8280;
            border-bottom: 1px solid #e8e4e0;
        }
        th.right, td.right { text-align: right; }
        th.center, td.center { text-align: center; }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #f0ede8;
            font-size: 11px;
            color: #374151;
        }
        td.producto-nombre { font-weight: 600; color: #2a2522; }
        .categoria-tag {
            font-size: 8px;
            color: #9a9390;
            background: #f0ede8;
            padding: 1px 5px;
            border-radius: 4px;
            margin-left: 4px;
        }

        /* Totales */
        .totales-wrapper { text-align: right; margin-top: 8px; }
        .totales-table { display: inline-block; width: 240px; }
        .total-row {
            width: 100%;
            overflow: hidden;
            padding: 6px 0;
            border-top: 1px solid #e5e7eb;
            font-size: 11px;
            color: #6b7280;
        }
        .total-row .label { float: left; }
        .total-row .valor { float: right; }
        .total-final {
            border-top: 2px solid #2d4a35 !important;
            font-weight: bold;
            font-size: 13px;
            color: #2d4a35;
            margin-top: 4px;
        }

        /* Fila única servicio */
        .servicio-row {
            padding: 14px;
            border: 1px solid #f0ede8;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .servicio-desc { float: left; font-size: 11px; color: #5a5250; }
        .servicio-monto { float: right; font-size: 11px; font-weight: bold; color: #2a2522; }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 14px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            line-height: 1.6;
        }
    </style>
</head>
<body>

    <div class="badge-simulacion">⏳ Factura Simulada — No válida ante la DIAN</div>

    {{-- Encabezado --}}
    <div class="header clearfix">
        <div class="header-left">
            <div class="negocio-nombre">{{ $negocio->nombre_comercial }}</div>
            @if($negocio->direccion)
                <div class="negocio-info">{{ $negocio->direccion }}</div>
            @endif
            @if($negocio->telefono)
                <div class="negocio-info">Tel: {{ $negocio->telefono }}</div>
            @endif
        </div>
        <div class="header-right">
            <div class="factura-titulo">FACTURA</div>
            <div class="factura-num">N° {{ $numero }}</div>
            <div class="factura-fecha">
                Fecha: {{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y') }}
            </div>
        </div>
    </div>

    {{-- Concepto --}}
    @if($venta->descripcion)
        <div class="concepto">
            <div class="concepto-label">Concepto</div>
            <div class="concepto-valor">{{ $venta->descripcion }}</div>
        </div>
    @endif

    {{-- Productos (reventa) o fila simple (servicios) --}}
    @if($venta->ventasDetalle->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="text-align:left">Producto</th>
                    <th class="center">Cant.</th>
                    <th class="right">Precio Unit.</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->ventasDetalle as $detalle)
                    <tr>
                        <td class="producto-nombre">
                            {{ $detalle->item->nombre ?? '—' }}
                            @if($detalle->item->categoria ?? false)
                                <span class="categoria-tag">{{ $detalle->item->categoria }}</span>
                            @endif
                        </td>
                        <td class="center">{{ number_format($detalle->cantidad, 0, ',', '.') }}</td>
                        <td class="right">
                            {{ $negocio->moneda }} {{ number_format($detalle->precio_unitario, 0, ',', '.') }}
                        </td>
                        <td class="right">
                            {{ $negocio->moneda }} {{ number_format($detalle->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="servicio-row clearfix">
            <span class="servicio-desc">{{ $venta->descripcion ?? 'Servicio prestado' }}</span>
            <span class="servicio-monto">
                {{ $negocio->moneda }} {{ number_format($venta->monto, 0, ',', '.') }}
            </span>
        </div>
    @endif

    {{-- Totales --}}
    <div class="totales-wrapper">
        <div class="totales-table">
            <div class="total-row clearfix">
                <span class="label">Subtotal</span>
                <span class="valor">
                    {{ $negocio->moneda }} {{ number_format($venta->monto, 0, ',', '.') }}
                </span>
            </div>
            <div class="total-row total-final clearfix">
                <span class="label">TOTAL</span>
                <span class="valor">
                    {{ $negocio->moneda }} {{ number_format($venta->monto, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>Documento generado por ControlFinancias — Simulación sin validez fiscal.</p>
        <p>Para facturación electrónica válida, habilitar como facturador electrónico ante la DIAN.</p>
    </div>

</body>
</html>