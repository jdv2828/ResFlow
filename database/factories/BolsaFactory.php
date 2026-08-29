<?php

namespace Database\Factories;

use App\Models\Bolsa;
use App\Models\Recurso;
use Illuminate\Database\Eloquent\Factories\Factory;

class BolsaFactory extends Factory
{
    protected $model = Bolsa::class;

    public function definition(): array
    {
        return [
            'recurso_id' => Recurso::factory(),
            'centro_costo_id' => fn (array $attrs) => Recurso::find($attrs['recurso_id'])?->centro_costo_id ?? 1,
            'tipo_combustible_id' => fn (array $attrs) => Recurso::find($attrs['recurso_id'])?->tipo_combustible_id ?? 1,
            'cantidad_disponible' => fn (array $attrs) => Recurso::find($attrs['recurso_id'])?->litros_disponibles ?? 1000,
        ];
    }
}
