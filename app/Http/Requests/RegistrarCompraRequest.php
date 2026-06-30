<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RegistrarCompraRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    protected function prepareForValidation()
    {
        if ($this->has('item_id')) {
            $this->merge([
                'items' => [
                    [
                        'item_id'        => $this->item_id,
                        'cantidad'       => $this->cantidad,
                        'costo_unitario' => $this->costo_unitario,
                        'precio_venta'   => $this->precio_venta ?? null,
                    ]
                ],
                'fecha'       => $this->fecha ?? now()->toDateString(),
                'descripcion' => $this->referencia ?? 'Compra rápida Dashboard'
            ]);
        }
    }

    public function rules()
    {
        return [
            'fecha'                   => 'required|date',
            'descripcion'             => 'nullable|string|max:255',
            'items'                   => 'required|array|min:1',
            'items.*.item_id'         => 'required|integer|exists:items,id',
            'items.*.cantidad'        => 'required|numeric|min:0.001',
            'items.*.costo_unitario'  => 'required|numeric|min:0',
            'items.*.precio_venta'    => 'nullable|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'fecha.required'                  => 'La fecha es obligatoria.',
            'fecha.date'                      => 'La fecha no tiene un formato válido.',
            'items.required'                  => 'Debes incluir al menos un producto.',
            'items.array'                     => 'El listado de productos no es válido.',
            'items.min'                       => 'Debes incluir al menos un producto.',
            'items.*.item_id.required'        => 'El producto es obligatorio.',
            'items.*.item_id.integer'         => 'El ID del producto debe ser un número entero.',
            'items.*.item_id.exists'          => 'El producto seleccionado no existe.',
            'items.*.cantidad.required'       => 'La cantidad es obligatoria.',
            'items.*.cantidad.numeric'        => 'La cantidad debe ser un número.',
            'items.*.cantidad.min'            => 'La cantidad debe ser mayor a cero.',
            'items.*.costo_unitario.required' => 'El costo unitario es obligatorio.',
            'items.*.costo_unitario.numeric'  => 'El costo unitario debe ser un número.',
            'items.*.costo_unitario.min'      => 'El costo unitario no puede ser negativo.',
            'items.*.precio_venta.numeric'    => 'El precio de venta debe ser un número.',
            'items.*.precio_venta.min'        => 'El precio de venta no puede ser negativo.',
        ];
    }
}
