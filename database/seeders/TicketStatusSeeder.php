<?php

namespace Database\Seeders;

use App\Models\TicketStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketStatusSeeder extends Seeder
{
    /**
     * Run the database seeds (idempotent — safe to re-run).
     */
    public function run(): void
    {
        foreach ([
            ['id' => 1, 'nombre' => 'generado'],
            ['id' => 2, 'nombre' => 'finalizado'],
            ['id' => 3, 'nombre' => 'vencido'],
            ['id' => 4, 'nombre' => 'utilizado'],
            ['id' => 5, 'nombre' => 'eliminado'],
        ] as $status) {
            TicketStatus::updateOrCreate(
                ['id' => $status['id']],
                ['nombre' => $status['nombre']]
            );
        }
    }
}