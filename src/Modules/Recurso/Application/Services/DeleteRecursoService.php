<?php

namespace Src\Modules\Recurso\Application\Services;

use Illuminate\Support\Facades\DB;
use Src\Modules\Recurso\Domain\Contracts\RecursoRepository;

class DeleteRecursoService
{
    public function __construct(
        private RecursoRepository $repository,
        private RebalanceRecursosService $rebalanceRecursosService
    ){}
    public function exectute(int $id)
    {
        DB::transaction(function () use ($id) {
            $recursoEntity = $this->repository->findById($id);
            $recursoEntity->markAsAnulado(auth()->user()->id);
            $this->repository->update($recursoEntity);

            $this->rebalanceRecursosService->executeByStationAndFuel(
                $recursoEntity->centro_costo_id,
                $recursoEntity->tipo_combustible_id,
            );
        });

    }
}
