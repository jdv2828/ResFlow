<?php

namespace Src\Modules\Bolsa\Domain\Contracts;

use Src\Modules\Bolsa\Domain\Entities\BolsaEntity;

interface BolsaRepository
{
    public function increaseLiters(BolsaEntity $bolsaEntity):void;
    public function decreaseLiters(BolsaEntity $bolsaEntity):void;

    public function findCurrentRecursoId(int $centroCostoId, int $tipoCombustibleId): ?int;
}
