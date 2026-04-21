<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GastoFijo extends Model
{
    protected $table = 'gastos_fijos';
    
      protected $fillable = [
        'negocio_id',
        'descripcion',
        'monto',
        'activo',
    ];

    public function negocio()
    {
        return $this->belongsTo(Negocio::class, 'negocio_id');
    }
}
