<?php

namespace Database\Seeders;

use App\Models\CentroCosto;
use Illuminate\Database\Seeder;

class CentroCostoSeeder extends Seeder
{
    public function run(): void
    {
        $centros = [
            ['nombre' => 'Av. Perón (YPF Norte)', 'ubicacion' => 'Av. Juan Domingo Perón 951'],
            ['nombre' => 'Plaza Trento (YPF Sur)', 'ubicacion' => 'Av. Juan Domingo Perón - Plaza Trento'],
            ['nombre' => 'Zona Sur', 'ubicacion' => 'Barrio Zona Sur y Melipal'],
            ['nombre' => 'Rivadavia', 'ubicacion' => 'Zona Comercial Rivadavia'],
        ];

        foreach ($centros as $centro) {
            CentroCosto::firstOrCreate(
                ['nombre' => $centro['nombre']],
                ['ubicacion' => $centro['ubicacion']]
            );
        }
    }
}
