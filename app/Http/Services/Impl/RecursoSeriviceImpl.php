<?php
namespace App\Http\Services\Impl;

use App\Http\Services\RecursoService;
use App\Models\Recurso;
use Illuminate\Database\QueryException;

class RecursoSeriviceImpl implements RecursoService
{

    public function create(
        float $cantidad_asignada,
        int $tipo_combustible_id,
        int $centro_costo_id,
        string $numero_factura,
        float $monto,
        int $emitido_por
    ) {
        try {
            $recurso = new Recurso();
            $recurso->cantidad_asignada = $cantidad_asignada;
            $recurso->tipo_combustible_id = $tipo_combustible_id;
            $recurso->centro_costo_id = $centro_costo_id;
            $recurso->numero_factura = $numero_factura;
            $recurso->monto = $monto;
            $recurso->emitido_por = $emitido_por;
            $recurso->save();
        } catch (QueryException $e) {
            return back()->withError('Hubo un error al crear el recurso. Por favor, intente de nuevo.')->withInput();
        }
    }
}
