<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $fillable = [
        'negocio_id',
        'movimiento_caja_id',
        'numero',
        'estado',
        'email_comprador',
        'enviada_at',
    ];

    protected $casts = [
        'enviada_at' => 'datetime',
    ];

    public function negocio()
    {
        return $this->belongsTo(Negocio::class);
    }

    public function movimientoCaja()
    {
        return $this->belongsTo(MovimientoCaja::class);
    }
}
