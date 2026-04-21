<?php
// Controlador encargado de la generación, vista previa y envío de facturas PDF a clientes.

namespace App\Http\Controllers;

use App\Models\MovimientoCaja;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\FacturaEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class FacturaController extends Controller
{
    /**
     * Historial de ventas con opción de ver/descargar factura
     */
    public function historial()
    {
        $negocio = Auth::user()->negocio;

        $ventas = MovimientoCaja::where('negocio_id', $negocio->id)
            ->where('es_venta', true)
            ->with(['ventasDetalle.item'])
            ->orderBy('fecha', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('facturas.historial', compact('ventas', 'negocio'));
    }

    /**
     * Vista HTML de la factura
     */
    public function show($id)
    {
        $negocio = Auth::user()->negocio;

        $venta = MovimientoCaja::where('negocio_id', $negocio->id)
            ->where('es_venta', true)
            ->with(['ventasDetalle.item'])
            ->findOrFail($id);

        $numero = str_pad($venta->id, 6, '0', STR_PAD_LEFT);

        return view('facturas.show', compact('venta', 'negocio', 'numero'));
    }

    /**
     * Descarga PDF
     */
    public function descargarPdf($id)
    {
        $negocio = Auth::user()->negocio;

        $venta = MovimientoCaja::where('negocio_id', $negocio->id)
            ->where('es_venta', true)
            ->with(['ventasDetalle.item'])
            ->findOrFail($id);

        $numero = str_pad($venta->id, 6, '0', STR_PAD_LEFT);

        $pdf = Pdf::loadView('facturas.pdf', compact('venta', 'negocio', 'numero'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("factura-{$numero}.pdf");
    }
        public function enviarCorreo(Request $request, $id)
    {
        $request->validate([
            'email_comprador' => 'required|email',
        ]);

        $negocio = Auth::user()->negocio;

        $venta = MovimientoCaja::where('negocio_id', $negocio->id)
            ->where('es_venta', true)
            ->with(['ventasDetalle.item'])
            ->findOrFail($id);

        $numero = str_pad($venta->id, 6, '0', STR_PAD_LEFT);

        Mail::to($request->email_comprador)
            ->send(new FacturaEmail($venta, $negocio, $numero));

        return redirect()->route('dashboard')
        ->with('success', '📧 Factura enviada correctamente a ' . $request->email_comprador);
    }
}