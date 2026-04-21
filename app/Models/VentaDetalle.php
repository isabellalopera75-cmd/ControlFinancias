<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    protected $table = 'ventas_detalle';

    protected $fillable = [
        'movimiento_caja_id', 'item_id', 'cantidad',
        'precio_unitario', 'costo_unitario', 'costo_total',
        'subtotal', 'markup', 'margen_real',
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