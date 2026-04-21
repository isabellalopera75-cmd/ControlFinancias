<?php

namespace App\Http\Controllers;

use App\Models\MovimientoCaja;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AgenteIAController extends Controller
{
    public function analizar(Request $request)
    {
        $negocio = Auth::user()->negocio;
        abort_if(!$negocio->esReventa(), 403);

        $periodo = $request->input('periodo', 'semana');

        // ── Calcular fechas del período actual ──
        if ($periodo === 'semana') {
            $fechaDesde        = now()->subDays(6)->toDateString();
            $fechaHasta        = now()->toDateString();
            $fechaAntDesde     = now()->subDays(13)->toDateString();
            $fechaAntHasta     = now()->subDays(7)->toDateString();
            $labelPeriodo      = 'Semana actual (' . now()->subDays(6)->format('d/m') . ' al ' . now()->format('d/m') . ')';
            $labelAnterior     = 'Semana anterior';
        } else {
            $fechaDesde        = now()->startOfMonth()->toDateString();
            $fechaHasta        = now()->toDateString();
            $fechaAntDesde     = now()->subMonth()->startOfMonth()->toDateString();
            $fechaAntHasta     = now()->subMonth()->endOfMonth()->toDateString();
            $labelPeriodo      = 'Mes actual (' . now()->format('F Y') . ')';
            $labelAnterior     = 'Mes anterior (' . now()->subMonth()->format('F Y') . ')';
        }

        // ── Ventas período actual ──
        $ventasActual = MovimientoCaja::where('negocio_id', $negocio->id)
            ->where('es_venta', true)
            ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
            ->get();

        // ── Ventas período anterior ──
        $ventasAnterior = MovimientoCaja::where('negocio_id', $negocio->id)
            ->where('es_venta', true)
            ->whereBetween('fecha', [$fechaAntDesde, $fechaAntHasta])
            ->sum('monto');

        // ── Gastos período actual ──
        $gastosActual = MovimientoCaja::where('negocio_id', $negocio->id)
            ->where('es_venta', false)
            ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
            ->sum('monto');

        // ── Calcular métricas ──
        $totalActual    = $ventasActual->sum('monto');
        $cantVentas     = $ventasActual->count();
        $efectivo       = $ventasActual->where('metodo_pago', 'efectivo')->sum('monto');
        $transferencia  = $ventasActual->where('metodo_pago', 'transferencia')->sum('monto');
        $ticketPromedio = $cantVentas > 0 ? round($totalActual / $cantVentas) : 0;
        $variacion      = $ventasAnterior > 0
            ? round((($totalActual - $ventasAnterior) / $ventasAnterior) * 100, 1)
            : 0;
        $utilidad       = $totalActual - $gastosActual;
        $signoVariacion = $variacion >= 0 ? '+' : '';

        // ── Producto más vendido del período ──
        $masVendido = VentaDetalle::whereHas('movimientoCaja', function ($q) use ($negocio, $fechaDesde, $fechaHasta) {
                $q->where('negocio_id', $negocio->id)
                  ->whereBetween('fecha', [$fechaDesde, $fechaHasta]);
            })
            ->selectRaw('item_id, SUM(cantidad) as total_cantidad, SUM(subtotal) as total_monto')
            ->groupBy('item_id')
            ->orderByDesc('total_cantidad')
            ->with('item')
            ->first();

        $productoTop = $masVendido
            ? ($masVendido->item->nombre ?? 'Desconocido') . ' (' . number_format($masVendido->total_cantidad) . ' unidades)'
            : 'Sin datos';

        // ── Construir el prompt ──
        $prompt = "Eres un asesor financiero experto en pequeños negocios de reventa colombianos 
(tiendas de barrio, minimercados). Habla en español, de forma directa y sencilla, 
sin tecnicismos innecesarios. El dueño del negocio no tiene formación financiera formal.

DATOS DEL NEGOCIO: {$negocio->nombre_comercial}
MONEDA: {$negocio->moneda}

=== {$labelPeriodo} ===
- Total vendido: {$negocio->moneda} " . number_format($totalActual, 0, ',', '.') . "
- Número de ventas: {$cantVentas}
- Ticket promedio por venta: {$negocio->moneda} " . number_format($ticketPromedio, 0, ',', '.') . "
- Pagos en efectivo: {$negocio->moneda} " . number_format($efectivo, 0, ',', '.') . "
- Pagos por transferencia: {$negocio->moneda} " . number_format($transferencia, 0, ',', '.') . "
- Gastos variables del período: {$negocio->moneda} " . number_format($gastosActual, 0, ',', '.') . "
- Utilidad bruta estimada: {$negocio->moneda} " . number_format($utilidad, 0, ',', '.') . "
- Producto más vendido: {$productoTop}

=== {$labelAnterior} ===
- Total vendido: {$negocio->moneda} " . number_format($ventasAnterior, 0, ',', '.') . "
- Variación vs período anterior: {$signoVariacion}{$variacion}%

Responde EXACTAMENTE con este formato (sin cambiar los títulos):

📊 RESUMEN
[2 oraciones sobre cómo le fue al negocio en este período]

📈 COMPARACIÓN CON EL PERÍODO ANTERIOR
[Explica si mejoró o empeoró y por qué puede ser]

✅ 3 RECOMENDACIONES CONCRETAS
1. [Recomendación específica y accionable para este tipo de negocio]
2. [Recomendación específica y accionable]
3. [Recomendación específica y accionable]

⚠️ ALERTA
[Si hay algo preocupante escríbelo aquí. Si todo está bien escribe: Sin alertas por el momento.]";

        // ── Llamar a OpenAI ──
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('services.openai.key'),
                    'Content-Type'  => 'application/json',
                ])
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'       => 'gpt-4o-mini',
                    'messages'    => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'max_tokens'  => 700,
                    'temperature' => 0.6,
                ]);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'No se pudo conectar con el servicio de IA. Verifica tu conexión e intenta de nuevo.'
                ], 500);
            }

            $analisis = $response->json('choices.0.message.content');

            return response()->json([
                'analisis' => $analisis,
                'meta' => [
                    'periodo'    => $labelPeriodo,
                    'total'      => number_format($totalActual, 0, ',', '.'),
                    'variacion'  => $signoVariacion . $variacion . '%',
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al conectar con la IA. Intenta de nuevo en unos segundos.'
            ], 500);
        }
    }
}