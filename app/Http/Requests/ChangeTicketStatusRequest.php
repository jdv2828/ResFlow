<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeTicketStatusRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'hash' => 'required|string|exists:tickets,hash'
        ];
    }

    public function messages()
    {
        return [
            'hash.required' => 'El campo hash es obligatorio.',
            'hash.string' => 'El campo hash debe ser una cadena de texto.',
            'hash.exists' => 'El ticket no existe.'
        ];
    }
}
