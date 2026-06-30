<?php

namespace App\Services;

use App\Models\MovimientoCaja;
use App\Models\Negocio;
use App\Http\Requests\RegistrarVentaRequest;

class VentaServicioService
{
    public function registrar(RegistrarVentaRequest $request, Negocio $negocio): MovimientoCaja
    {
        return MovimientoCaja::create([
            'negocio_id'  => $negocio->id,
            'monto'       => $request->monto,
            'descripcion' => $request->descripcion ?? 'Venta',
            'es_venta'    => true,
            'fecha'       => $request->fecha,
        ]);
    }
}
