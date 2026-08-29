<?php

namespace Database\Factories;

use App\Models\Personal;
use App\Models\Recurso;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $recurso = Recurso::query()
            ->where('litros_disponibles', '>', 0)
            ->inRandomOrder()
            ->first();

        if (!$recurso) {
            $recurso = Recurso::factory()->create();
        }

        $maxLitros = max(1, (float) $recurso->litros_disponibles);
        $litros = fake()->randomFloat(5, 1, min($maxLitros, 200));

        return [
            'litros' => $litros,
            'personal_id' => Personal::query()->value('id') ?? Personal::factory()->create()->id,
            'tipo_combustible_id' => $recurso->tipo_combustible_id,
            'centro_costo_id' => $recurso->centro_costo_id,
            'recurso_id' => $recurso->id,
            'emitido_por' => User::query()->value('id') ?? User::factory()->create()->id,
            'finalizado_por' => null,
            'fecha_caducidad' => now()->addDays(fake()->numberBetween(1, 30)),
            'ticket_status_id' => TicketStatus::query()->where('nombre', 'generado')->value('id') ?? 1,
            'activo' => true,
        ];
    }

    public function forRecurso(Recurso $recurso): static
    {
        return $this->state(function () use ($recurso) {
            $maxLitros = max(1, (float) $recurso->litros_disponibles);

            return [
                'litros' => fake()->randomFloat(5, 1, min($maxLitros, 200)),
                'tipo_combustible_id' => $recurso->tipo_combustible_id,
                'recurso_id' => $recurso->id,
            ];
        });
    }
}
