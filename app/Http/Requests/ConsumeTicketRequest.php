<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsumeTicketRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'litros_consumidos' => 'required|numeric|min:0'
        ];
    }

    public function messages()
    {
        return [
            'litros_consumidos.required' => 'El campo litros consumidos es obligatorio.',
            'litros_consumidos.numeric' => 'El campo litros consumidos debe ser un número.',
            'litros_consumidos.min' => 'El campo litros consumidos debe ser mayor o igual a 0.'
        ];
    }
}
