<?php

namespace Database\Factories;

use App\Models\CentroCosto;
use App\Models\Recurso;
use App\Models\TipoCombustible;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recurso>
 */
class RecursoFactory extends Factory
{
    private const VALID_COMBINATIONS = [
        'Av. Perón (YPF Norte)' => ['diesel_500_a', 'infinia_diesel_a'],
        'Plaza Trento (YPF Sur)' => ['diesel_500_b', 'infinia', 'infinia_diesel_b'],
        'Zona Sur' => ['super_max', 'diesel_premium_max', 'diesel_max', 'diesel_Shell', 'regular_shell', 'super_shell', 'v_power_diesel_Shell', 'v_power_nafta_shell'],
        'Rivadavia' => ['axion_super_a', 'diesel_x10_a', 'quantum_diesel_x10_a', 'axion_super_b', 'diesel_x10_b', 'quantum_diesel_x10_b', 'quantum_diesel_x10_flota'],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stationName = fake()->randomElement(array_keys(self::VALID_COMBINATIONS));
        $fuelName = fake()->randomElement(self::VALID_COMBINATIONS[$stationName]);

        $centroCosto = CentroCosto::query()->where('nombre', $stationName)->first();
        if (!$centroCosto) {
            $centroCosto = CentroCosto::create(['nombre' => $stationName, 'ubicacion' => 'Factory auto-created']);
        }

        $combustible = TipoCombustible::query()->where('nombre', $fuelName)->where('centro_costo_id', $centroCosto->id)->first();
        if (!$combustible) {
            $combustible = TipoCombustible::create([
                'nombre' => $fuelName,
                'centro_costo_id' => $centroCosto->id,
                'categoria' => 'combustible',
            ]);
        }

        $centroCostoId = $centroCosto->id;
        $fuelId = $combustible->id;
        $litros = fake()->randomFloat(5, 100, 5000);

        return [
            'numero_factura' => fake()->unique()->bothify('EXP-######'),
            'orden' => Recurso::query()
                ->where('centro_costo_id', $centroCostoId)
                ->where('tipo_combustible_id', $fuelId)
                ->max('orden') + 1,
            'litros' => $litros,
            'litros_disponibles' => $litros,
            'litros_inicial' => $litros,
            'litros_emitidos' => 0,
            'litros_consumidos' => 0,
            'monto' => fake()->randomFloat(5, 1000, 50000),
            'tipo_combustible_id' => $fuelId,
            'centro_costo_id' => $centroCostoId,
            'emitido_por' => User::query()->value('id') ?? User::factory()->create()->id,
            'finalizado_por' => null,
            'activo' => true,
        ];
    }
}
