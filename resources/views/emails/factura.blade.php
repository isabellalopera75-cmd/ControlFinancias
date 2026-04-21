<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f3ef; margin: 0; padding: 0; }
        .container { max-width: 560px; margin: 40px auto; background: white;
                     border-radius: 16px; overflow: hidden; border: 1px solid #e8e4e0; }
        .header { background: #2d4a35; padding: 32px; text-align: center; }
        .header h1 { color: #f0ede8; font-size: 22px; margin: 0; letter-spacing: 0.05em; }
        .header p { color: #a8c8a0; font-size: 12px; margin: 6px 0 0; }
        .body { padding: 32px; }
        .body p { color: #5a5250; font-size: 14px; line-height: 1.6; }
        .badge { display: inline-block; background: #fef3c7; color: #92400e;
                 border: 1px solid #fcd34d; font-size: 11px; padding: 4px 12px;
                 border-radius: 20px; margin-bottom: 20px; }
        .datos { background: #faf9f7; border: 1px solid #f0ede8; border-radius: 10px;
                 padding: 16px 20px; margin: 20px 0; }
        .datos p { margin: 4px 0; font-size: 13px; color: #2a2522; }
        .datos span { color: #9a9390; }
        .total { font-size: 22px; font-weight: bold; color: #2d4a35;
                 text-align: center; padding: 16px; }
        .footer { background: #faf9f7; border-top: 1px solid #f0ede8;
                  padding: 20px 32px; text-align: center; }
        .footer p { color: #b0a8a0; font-size: 11px; margin: 0; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="container">

        <div class="header">
            <h1>{{ $negocio->nombre_comercial }}</h1>
            <p>{{ $negocio->direccion ?? '' }} {{ $negocio->telefono ? '· ' . $negocio->telefono : '' }}</p>
        </div>

        <div class="body">

            <div class="badge">⏳ Factura Simulada — No válida ante la DIAN</div>

            <p>Hola, adjunto encontrarás la factura correspondiente a tu compra. Gracias por tu preferencia.</p>

            <div class="datos">
                <p><span>N° Factura:</span> <strong>#{{ $numero }}</strong></p>
                <p><span>Fecha:</span> {{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y') }}</p>
                @if($venta->descripcion)
                    <p><span>Concepto:</span> {{ $venta->descripcion }}</p>
                @endif
            </div>

            <div class="total">
                Total: {{ $negocio->moneda }} {{ number_format($venta->monto, 0, ',', '.') }}
            </div>

            <p style="font-size:12px; color:#9a9390; text-align:center;">
                El PDF de tu factura está adjunto en este correo.
            </p>

        </div>

        <div class="footer">
            <p>Este correo fue enviado por ControlFinancias.</p>
            <p>Documento de simulación — sin validez fiscal ante la DIAN.</p>
        </div>

    </div>
</body>
</html>