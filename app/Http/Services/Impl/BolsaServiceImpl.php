<?php
namespace App\Http\Services\Impl;

use App\Http\Services\BolsaService;
use App\Models\Bolsa;
use Illuminate\Database\QueryException;

class BolsaServiceImpl implements BolsaService
{
    public function addLitersBolsa(int $tipo_combustible_id, int $centro_costo_id, float $litros)
    {
        try {
            $bolsa = Bolsa::firstOrCreate(
                [
                    'tipo_combustible_id' => $tipo_combustible_id,
                    'centro_costo_id' => $centro_costo_id
                ],
                ['cantidad_disponible' => 0]
            );

            $bolsa->cantidad_disponible += $litros;
            $bolsa->save();
        } catch (QueryException $e) {
            return back()->withError('Hubo un error al actualizar la bolsa de combustible. Por favor, intente de nuevo.')->withInput();
        }
    }
}

