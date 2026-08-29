<?php

namespace Database\Seeders;

use App\Models\CentroCosto;
use App\Models\TipoCombustible;
use Illuminate\Database\Seeder;

class TipoCombustiblesSeeder extends Seeder
{
    /**
     * Map index -> real centro_costo name (the seeder names).
     * Resolves IDs by name so reseeding works on any DB state.
     */
    private const CENTROS = [
        1 => 'Av. Perón (YPF Norte)',
        2 => 'Plaza Trento (YPF Sur)',
        3 => 'Zona Sur',
        4 => 'Rivadavia',
    ];

    public function run(): void
    {
        foreach ([
            // === COMBUSTIBLE ===
            ['nombre' => 'diesel_500_a',        'centro' => 1, 'categoria' => 'combustible'],
            ['nombre' => 'infinia_diesel_a',    'centro' => 1, 'categoria' => 'combustible'],

            ['nombre' => 'diesel_500_b',        'centro' => 2, 'categoria' => 'combustible'],
            ['nombre' => 'infinia',             'centro' => 2, 'categoria' => 'combustible'],
            ['nombre' => 'infinia_diesel_b',    'centro' => 2, 'categoria' => 'combustible'],

            ['nombre' => 'super_max',           'centro' => 3, 'categoria' => 'combustible'],
            ['nombre' => 'diesel_premium_max',  'centro' => 3, 'categoria' => 'combustible'],
            ['nombre' => 'diesel_max',          'centro' => 3, 'categoria' => 'combustible'],
            ['nombre' => 'diesel_Shell',        'centro' => 3, 'categoria' => 'combustible'],
            ['nombre' => 'regular_shell',       'centro' => 3, 'categoria' => 'combustible'],
            ['nombre' => 'super_shell',         'centro' => 3, 'categoria' => 'combustible'],
            ['nombre' => 'v_power_diesel_Shell','centro' => 3, 'categoria' => 'combustible'],
            ['nombre' => 'v_power_nafta_shell', 'centro' => 3, 'categoria' => 'combustible'],

            ['nombre' => 'axion_super_a',        'centro' => 4, 'categoria' => 'combustible'],
            ['nombre' => 'diesel_x10_a',         'centro' => 4, 'categoria' => 'combustible'],
            ['nombre' => 'quantum_diesel_x10_a', 'centro' => 4, 'categoria' => 'combustible'],
            ['nombre' => 'axion_super_b',        'centro' => 4, 'categoria' => 'combustible'],
            ['nombre' => 'diesel_x10_b',         'centro' => 4, 'categoria' => 'combustible'],
            ['nombre' => 'quantum_diesel_x10_b', 'centro' => 4, 'categoria' => 'combustible'],
            ['nombre' => 'quantum_diesel_x10_flota', 'centro' => 4, 'categoria' => 'combustible'],

            // === ACEITES ===
            ['nombre' => 'Aceite 20W50',    'centro' => 1, 'categoria' => 'aceites'],
            ['nombre' => 'Aceite 15W40',    'centro' => 2, 'categoria' => 'aceites'],
            ['nombre' => 'Aceite 10W30',    'centro' => 3, 'categoria' => 'aceites'],

            // === LUBRICANTES ===
            ['nombre' => 'Grasa multiuso',          'centro' => 2, 'categoria' => 'lubricantes'],
            ['nombre' => 'Lubricante en aerosol',   'centro' => 3, 'categoria' => 'lubricantes'],
            ['nombre' => 'Lubricante para cadenas', 'centro' => 4, 'categoria' => 'lubricantes'],

            // === REFRIGERANTES ===
            ['nombre' => 'Refrigerante concentrado',      'centro' => 1, 'categoria' => 'refrigerantes'],
            ['nombre' => 'Refrigerante listo para usar',  'centro' => 4, 'categoria' => 'refrigerantes'],
        ] as $fuel) {
            $centroCostoId = CentroCosto::query()
                ->where('nombre', self::CENTROS[$fuel['centro']])
                ->value('id');

            if (!$centroCostoId) {
                continue;
            }

            TipoCombustible::updateOrCreate(
                ['nombre' => $fuel['nombre']],
                ['centro_costo_id' => $centroCostoId, 'categoria' => $fuel['categoria']]
            );
        }
    }
}