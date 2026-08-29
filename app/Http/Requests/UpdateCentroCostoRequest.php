<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCentroCostoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'ubicacion' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'centro_padre_id' => 'nullable|integer|exists:centro_costos,id',
        ];
    }
}
