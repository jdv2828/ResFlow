<?php

namespace Src\Modules\Recurso\Application\Dtos;

class StoreRecursoDto
{
    public function __construct(
        public float $litros,
        public float $litros_disponibles,
        public float $monto,
        public int $tipo_combustible_id,
        public int $centro_costo_id,
        public string $numero_factura,
        public int $orden,
        public int $emitido_por,
        public ?int $finalizado_por=null,
        public ?bool $activo = true
    ) {
    }
}
