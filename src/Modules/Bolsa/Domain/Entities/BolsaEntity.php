<?php
namespace Src\Modules\Bolsa\Domain\Entities;

use Src\Modules\Bolsa\Domain\ValueObjects\CantidadDisponibleValue;

class BolsaEntity
{
    public function __construct(
        public int $tipo_combustible_id,
        public int $centro_costo_id,
        public CantidadDisponibleValue $cantidad_disponible,
        public ?int $recurso_id = null
    ) {}
}
