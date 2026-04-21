<?php

namespace App\Services;

class FinanzasService
{
    public static function puntoEquilibrio($gastosFijos, $sueldo, $margenSobreCosto)
    {
        if ($margenSobreCosto <= 0) return 0;
        $margenContribucion = $margenSobreCosto / (100 + $margenSobreCosto);
        return ($gastosFijos + $sueldo) / $margenContribucion;
    }

    public static function utilidadMensual($ventas, $gastosFijos, $gastosVariables, $sueldo, $costoVentas = 0)
    {
        return $ventas - $costoVentas - $gastosFijos - $gastosVariables - $sueldo;
    }
}