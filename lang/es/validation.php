<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'numeric'  => 'El campo :attribute debe ser un número.',
    'email'    => 'El campo :attribute debe ser un correo válido.',
    'unique'   => 'El :attribute ya está registrado.',
    'min'      => [
        'numeric' => 'El campo :attribute debe ser mínimo :min.',
        'string'  => 'El campo :attribute debe tener mínimo :min caracteres.',
    ],
    'max'      => [
        'numeric' => 'El campo :attribute no puede ser mayor a :max.',
        'string'  => 'El campo :attribute no puede tener más de :max caracteres.',
    ],

    'attributes' => [
        'name'                        => 'nombre',
        'email'                       => 'correo electrónico',
        'password'                    => 'contraseña',
        'monto'                       => 'monto',
        'descripcion'                 => 'descripción',
        'nombre_comercial'            => 'nombre del negocio',
        'pais'                        => 'país',
        'moneda'                      => 'moneda',
        'margen_operacional'          => 'margen de ganancia',
        'dias_operacion'              => 'días de operación',
        'sueldo_dueno'                => 'sueldo del dueño',
        'ingresos_proyectados'        => 'ingresos proyectados',
        'utilidad_ahorro_reinversion' => 'utilidad para ahorro',
        'dinero_disponible'           => 'dinero disponible',
        'ventas_mes1'                 => 'ventas del mes anterior',
        'ventas_mes2'                 => 'ventas de hace 2 meses',
        'ventas_mes3'                 => 'ventas de hace 3 meses',
    ],
];