<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
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
            'litros' => 'required|numeric|min:0',
            'personal_id' => 'required|integer|exists:personal,id',
            'tipo_combustible_id' => 'required|integer|exists:tipo_combustibles,id',
            'centro_costo_id' => 'required|integer|exists:centro_costos,id',
            'fecha_caducidad' => 'nullable|date|after:today'
        ];
    }

    public function messages()
    {
        return [
            'litros.required' => 'El campo litros es obligatorio.',
            'litros.numeric' => 'El campo litros debe ser un número.',
            'litros.min' => 'El campo litros debe ser mayor o igual a 0.',
            'personal_id.required' => 'El campo personal es obligatorio.',
            'personal_id.integer' => 'El campo personal debe ser un número entero.',
            'personal_id.exists' => 'El personal seleccionado no existe.',
            'tipo_combustible_id.required' => 'El campo tipo de combustible es obligatorio.',
            'tipo_combustible_id.integer' => 'El campo tipo de combustible debe ser un número entero.',
            'tipo_combustible_id.exists' => 'El tipo de combustible seleccionado no existe.',
            'centro_costo_id.required' => 'El campo centro de costo es obligatorio.',
            'centro_costo_id.integer' => 'El campo centro de costo debe ser un número entero.',
            'centro_costo_id.exists' => 'El centro de costo seleccionado no existe.',
            'fecha_caducidad.date' => 'El campo fecha de caducidad debe ser una fecha válida.',
            'fecha_caducidad.after' => 'La fecha de caducidad debe ser posterior a hoy.'
        ];
    }
}
