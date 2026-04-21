<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoCaja extends Model
{
    protected $table = 'movimientos_caja';

    protected $fillable = [
        'negocio_id', 'monto', 'descripcion',
        'es_venta', 'fecha', 'movimiento_inventario_id',
        'metodo_pago',
    ];

    protected $casts = [
        'es_venta' => 'boolean',
        'fecha'    => 'datetime',
    ];

    public function negocio()
    {
        return $this->belongsTo(Negocio::class);
    }

    // Relación nueva
    public function movimientoInventario()
    {
        return $this->belongsTo(MovimientoInventario::class, 'movimiento_inventario_id');
    }

    public function ventasDetalle()
    {
        return $this->hasMany(VentaDetalle::class, 'movimiento_caja_id');
    }

    public function comprasDetalle()
    {
        return $this->hasMany(CompraDetalle::class, 'movimiento_caja_id');
    }
}