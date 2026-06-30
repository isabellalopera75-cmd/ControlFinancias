<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarConfiguracionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'                        => 'required',
            'email'                       => 'required|email|unique:users',
            'password'                    => 'required|min:6',
            'nombre_comercial'            => 'required',
            'pais'                        => 'required',
            'moneda'                      => 'required',
            'tipo_negocio'                => 'required|in:servicios,reventa',
            'direccion'                   => 'nullable|string|max:255',
            'telefono'                    => 'nullable|string|max:30',
            'margen_operacional'          => 'nullable|numeric',
            'dias_operacion'              => 'required|numeric',
            'sueldo_dueno'                => 'required|numeric',
            'ingresos_proyectados'        => 'nullable|numeric|min:0',
            'utilidad_ahorro_reinversion' => 'required|numeric',
            'dinero_disponible'           => 'required|numeric',
            'nomina_empleados'            => 'nullable|numeric|min:0',
            'ventas_mes1'                 => 'required|numeric|min:0',
            'ventas_mes2'                 => 'required|numeric|min:0',
            'ventas_mes3'                 => 'required|numeric|min:0',
        ];
    }
}
