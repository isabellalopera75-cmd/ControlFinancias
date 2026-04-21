<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaMensual extends Model
{
    protected $table = 'metas_mensuales';

    protected $fillable = [
        'negocio_id',
        'mes',
        'anio',
        'meta',
        'punto_equilibrio',
        'ventas_real',
        'alerta',
    ];

    public function negocio()
    {
        return $this->belongsTo(Negocio::class, 'negocio_id');
    }
}