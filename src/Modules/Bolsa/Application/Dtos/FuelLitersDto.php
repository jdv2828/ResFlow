<?php

namespace Src\Modules\Bolsa\Application\Dtos;

class FuelLitersDto
{
    public function __construct(
        public float $litros,
        public int $tipo_combustible_id,
        public int $centro_costo_id,
        public ?int $recurso_id = null,
    ) {
    }
}
