<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'marca' => 'required',
            'modelo' => 'required',
            'patente' => 'required',
            'numero_identificacion' => 'nullable|string|max:50',
            'id_chofer' => 'nullable|integer|exists:personal,id',
            'id_centro_costo' => 'nullable|integer|exists:centro_costos,id',
            'foto' => 'nullable|image|max:2048',
        ];
    }
}
