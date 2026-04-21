<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Negocio extends Model
{
    protected $table = 'negocios';

    protected $fillable = [
        'usuario_id', 'nombre_comercial', 'pais', 'moneda', 'tipo_negocio','direccion', 'telefono',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function configEstrategica()
    {
        return $this->hasOne(ConfigEstrategica::class);
    }

    public function gastosFijos()
    {
        return $this->hasMany(GastoFijo::class);
    }

    public function movimientosCaja()
    {
        return $this->hasMany(MovimientoCaja::class);
    }

    public function metasMensuales()
    {
        return $this->hasMany(MetaMensual::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public function esServicios(): bool
    {
        return $this->tipo_negocio === 'servicios';
    }

    public function esReventa(): bool
    {
        return $this->tipo_negocio === 'reventa';
    }

    public function tieneInventario(): bool
    {
        return $this->tipo_negocio === 'reventa';
    }
}