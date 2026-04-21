<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'items';

    protected $fillable = [
        'negocio_id', 'nombre', 'categoria', 'tipo', 'costo_compra', 'precio_venta',
        'stock', 'unidad', 'unidad_base', 'factor_conversion',
        'stock_minimo', 'tiene_stock', 'activo',
        'presentacion_compra', 'unidades_por_caja',
    ];

    protected $casts = [
        'tiene_stock' => 'boolean',
        'activo'      => 'boolean',
    ];

    public function negocio()
    {
        return $this->belongsTo(Negocio::class);
    }

    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public function ventasDetalle()
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function comprasDetalle()
    {
        return $this->hasMany(CompraDetalle::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeStockBajo($query)
    {
        return $query->where('tiene_stock', true)
                     ->whereColumn('stock', '<=', 'stock_minimo');
    }
    
}