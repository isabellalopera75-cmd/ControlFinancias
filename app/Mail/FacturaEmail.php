<?php

namespace App\Mail;

use App\Models\MovimientoCaja;
use App\Models\Negocio;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class FacturaEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MovimientoCaja $venta,
        public Negocio $negocio,
        public string $numero
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Factura #' . $this->numero . ' — ' . $this->negocio->nombre_comercial,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.factura',
        );
    }

    public function attachments(): array
    {
        $pdf = Pdf::loadView('facturas.pdf', [
            'venta'   => $this->venta,
            'negocio' => $this->negocio,
            'numero'  => $this->numero,
        ])->setPaper('a4', 'portrait');

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                'factura-' . $this->numero . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}