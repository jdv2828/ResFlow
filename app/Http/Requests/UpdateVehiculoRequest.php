<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehiculoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'marca' => 'required',
            'modelo' => 'required',
            'patente' => 'required',
            'numero_identificacion' => 'nullable|string|max:50',
            'id_chofer' => 'nullable|integer|exists:personal,id',
            'id_centro_costo' => 'nullable|integer|exists:centro_costos,id',
            'foto' => 'nullable|image',
        ];
    }
}
