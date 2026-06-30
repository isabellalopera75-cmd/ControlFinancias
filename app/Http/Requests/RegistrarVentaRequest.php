<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RegistrarVentaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $negocio = $this->user() ? $this->user()->negocio : null;

        if ($negocio && $negocio->esReventa()) {
            return [
                'items'                   => 'required|array|min:1',
                'items.*.item_id'         => 'required|exists:items,id',
                'items.*.cantidad'        => 'required|numeric|min:0.001',
                'items.*.precio_unitario' => 'nullable|numeric|min:0',
                'fecha'                   => 'required|date',
                'descripcion'             => 'nullable|string|max:255',
            ];
        }

        // Default rules (Servicios: monto libre)
        return [
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
        ];
    }
}
