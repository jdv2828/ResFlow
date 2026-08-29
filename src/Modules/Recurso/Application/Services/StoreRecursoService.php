<?php

namespace Src\Modules\Recurso\Application\Services;

use Illuminate\Support\Facades\DB;
use Src\Modules\Recurso\Domain\Events\RecursoWasCreated;
use Src\Modules\Recurso\Application\Dtos\StoreRecursoDto;
use Src\Modules\Recurso\Domain\Contracts\RecursoRepository;
use Src\Modules\Recurso\Domain\Entities\RecursoEntity;
use Src\Modules\Recurso\Domain\Exceptions\CombustibleNotFoundInEstacion;
use Src\Modules\Recurso\Domain\ValueObjects\LitrosValue;
use Src\Modules\Recurso\Domain\ValueObjects\MontoValue;
use Src\Shared\Domain\Contracts\CheckCombustibleInterface;

class StoreRecursoService
{
    public function __construct(
        private RecursoRepository $repository,
        private RebalanceRecursosService $rebalanceRecursosService,
        private CheckCombustibleInterface $checkCombustible,
    ) {}

    public function execute(StoreRecursoDto $dto)
    {
        if(!$this->checkCombustible->tieneCombustibleByIds((int)$dto->centro_costo_id,(int)$dto->tipo_combustible_id)){
            throw new CombustibleNotFoundInEstacion();
        }

        return DB::transaction(function () use ($dto) {
            $nextOrder = $this->repository->getNextOrderForStationAndFuel(
                $dto->centro_costo_id,
                $dto->tipo_combustible_id
            );

            $recurso = RecursoEntity::create(
                new LitrosValue($dto->litros),
                new LitrosValue($dto->litros),
                new MontoValue($dto->monto),
                $dto->tipo_combustible_id,
                $dto->centro_costo_id,
                $dto->numero_factura,
                $nextOrder,
                $dto->emitido_por
            );

            $this->repository->save($recurso);

            $this->rebalanceRecursosService->executeByStationAndFuel(
                $dto->centro_costo_id,
                $dto->tipo_combustible_id
            );

            event(new RecursoWasCreated($recurso));

            return $recurso;
        });
    }
}
