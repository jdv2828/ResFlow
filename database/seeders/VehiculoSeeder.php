<?php

namespace Database\Seeders;

use App\Models\CentroCosto;
use App\Models\Personal;
use App\Models\Vehiculo;
use Illuminate\Database\Seeder;

class VehiculoSeeder extends Seeder
{
    public function run(): void
    {
        $empleados = Personal::all();
        $centroCostos = CentroCosto::all();

        if ($empleados->isEmpty() || $centroCostos->isEmpty()) {
            return;
        }

        $vehiculos = Vehiculo::factory()->count(6)->create();

        foreach ($vehiculos as $i => $vehiculo) {
            $vehiculo->update([
                'id_chofer' => $empleados->get($i % $empleados->count())->id,
                'id_centro_costo' => $centroCostos->get($i % $centroCostos->count())->id,
            ]);
        }
    }
}
