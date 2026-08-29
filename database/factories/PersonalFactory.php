<?php

namespace Database\Factories;

use App\Models\Personal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Personal>
 */
class PersonalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->firstName,
            'apellido' => $this->faker->lastName,
            'legajo' => $this->faker->unique()->randomNumber(5),
            'foto' => $this->faker->imageUrl(200, 200, 'people'),
            'centro_costo_id' => \App\Models\CentroCosto::query()->inRandomOrder()->value('id')
                ?? \App\Models\CentroCosto::create(['nombre' => 'Factory Default CC'])->id,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'email' => $this->faker->unique()->safeEmail,
        ];
    }
}
