<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecursoRequest extends FormRequest
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
            'litros' => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'tipo_combustible_id' => ['required', 'integer', 'exists:tipo_combustibles,id'],
            'centro_costo_id' => ['required', 'integer', 'exists:centro_costos,id'],
            'numero_factura' => ['required', 'string', 'max:50', 'unique:recursos,numero_factura,' . $this->route('recurso')]
        ];
    }

    public function messages(): array
    {
        return [
            'litros.required' => 'El campo litros es obligatorio.',
            'litros.numeric' => 'El campo litros debe ser un número.',
            'litros.min' => 'El campo litros debe ser mayor a 0.',
            'litros.max' => 'El campo litros no puede superar 100000 litros.',
            'monto.required' => 'El campo monto es obligatorio.',
            'monto.numeric' => 'El campo monto debe ser un número.',
            'monto.min' => 'El campo monto debe ser mayor a 0.',
            'tipo_combustible_id.required' => 'El tipo de combustible es obligatorio.',
            'tipo_combustible_id.exists' => 'El tipo de combustible seleccionado no existe.',
            'centro_costo_id.required' => 'El centro de costo es obligatorio.',
            'centro_costo_id.exists' => 'El centro de costo seleccionado no existe.',
            'numero_factura.required' => 'El número de factura es obligatorio.',
            'numero_factura.unique' => 'Ya existe un recurso con ese número de factura.',
        ];
    }
}