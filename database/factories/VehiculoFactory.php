<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehiculo>
 */
class VehiculoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'marca' => fake()->randomElement(['Toyota', 'Ford', 'Chevrolet', 'Volkswagen', 'Renault', 'Peugeot', 'Fiat']),
            'modelo' => fake()->randomElement(['Hilux', 'Ranger', 'S10', 'Amarok', 'Kangoo', 'Partner', 'Cronos', 'Logan']),
            'anio' => (string) fake()->numberBetween(2008, 2024),
            'color' => fake()->safeColorName(),
            'patente' => fake()->unique()->bothify('???###'),
            'foto' => null,
            'numero_identificacion' => fake()->unique()->bothify('VH-####'),
            'id_chofer' => null,
            'id_centro_costo' => null,
        ];
    }
}
