<?php
namespace App\Http\Services;

interface RecursoService
{
    public function create(
        float $cantidad_asignada,
        int $tipo_combustible_id,
        int $centro_costo_id,
        string $numero_factura,
        float $monto,
        int $emitido_por
    );
}
