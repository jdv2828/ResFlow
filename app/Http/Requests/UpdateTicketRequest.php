<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'litros' => 'required|numeric|min:0',
            'personal_id' => 'required|integer|exists:personal,id',
            'tipo_combustible_id' => 'required|integer|exists:tipo_combustibles,id',
            'centro_costo_id' => 'required|integer|exists:centro_costos,id',
            'fecha_caducidad' => 'nullable|date',
        ];
    }
}
