<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ImportarInventarioRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'archivo' => 'required|file|mimes:xlsx,xls|max:5120',
        ];
    }

    public function messages()
    {
        return [
            'archivo.required' => 'Debes seleccionar un archivo para importar.',
            'archivo.file'     => 'El archivo subido no es válido.',
            'archivo.mimes'    => 'El archivo debe ser formato Excel (.xlsx o .xls).',
            'archivo.max'      => 'El archivo no puede superar los 5 MB.',
        ];
    }
}
