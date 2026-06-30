<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AjustarInventarioRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'cantidad' => 'required|numeric|min:0.001',
            'tipo'     => 'required|in:entrada,salida,ajuste',
            'motivo'   => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.numeric'  => 'La cantidad debe ser un número.',
            'cantidad.min'      => 'La cantidad debe ser mayor a cero.',
            'tipo.required'     => 'El tipo de ajuste es obligatorio.',
            'tipo.in'           => 'El tipo de ajuste debe ser: entrada, salida o ajuste.',
            'motivo.max'        => 'El motivo no puede superar los 255 caracteres.',
        ];
    }
}
