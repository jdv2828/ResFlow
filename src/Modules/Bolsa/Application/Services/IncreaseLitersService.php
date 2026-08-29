<?php

namespace Src\Modules\Bolsa\Application\Services;

use Src\Modules\Bolsa\Application\Dtos\FuelLitersDto;
use Src\Modules\Bolsa\Domain\Contracts\BolsaRepository;
use Src\Modules\Bolsa\Domain\Entities\BolsaEntity;
use Src\Modules\Bolsa\Domain\ValueObjects\CantidadDisponibleValue;

class IncreaseLitersService
{
    public function __construct(
        private BolsaRepository $repository
    )
    {

    }
    public function execute(FuelLitersDto $dto)
    {

        $bolsaEntity = new BolsaEntity(
            $dto->tipo_combustible_id,
            $dto->centro_costo_id,
            new CantidadDisponibleValue($dto->litros),
            $dto->recurso_id
        );
        $this->repository->increaseLiters($bolsaEntity);

    }
}
