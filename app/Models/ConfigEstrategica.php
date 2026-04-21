<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigEstrategica extends Model
{
    protected $table = 'config_estrategica';

    protected $fillable = [
        'negocio_id',
        'margen_operacional',
        'ingresos_proyectados',
        'dias_operacion',
        'sueldo_dueno',
        'utilidad_ahorro_reinversion',
        'dinero_disponible',
        'ventas_mes1',
        'ventas_mes2',
        'ventas_mes3',
        'presupuesto_compras_mensual',
    ];

    public function negocio()
    {
        return $this->belongsTo(Negocio::class, 'negocio_id');
    }
}
