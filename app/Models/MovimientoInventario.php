<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'negocio_id', 'item_id', 'tipo',
        'cantidad', 'costo_unitario', 'referencia_id', 'fecha',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function negocio()
    {
        return $this->belongsTo(Negocio::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function movimientoCaja()
    {
        return $this->belongsTo(MovimientoCaja::class, 'referencia_id');
    }
}