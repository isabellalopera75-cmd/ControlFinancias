<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraDetalle extends Model
{
    protected $table = 'compras_detalle';

    protected $fillable = [
        'movimiento_caja_id', 'item_id', 'cantidad', 'costo_unitario',
    ];

    public function movimientoCaja()
    {
        return $this->belongsTo(MovimientoCaja::class, 'movimiento_caja_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}