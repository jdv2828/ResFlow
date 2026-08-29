<?php

namespace Src\Modules\Tickets\Application\Services;

use Illuminate\Support\Facades\DB;
use Src\Modules\Recurso\Domain\Contracts\RecursoRepository;
use Src\Modules\Recurso\Application\Services\RebalanceRecursosService;
use Src\Modules\Tickets\Domain\Contracts\TicketRepository;
use Src\Modules\Tickets\Domain\Exceptions\TicketNotFoundException;

class DeleteTicketService
{
    public function __construct(
        private TicketRepository $repository,
        private RebalanceRecursosService $rebalanceRecursosService,
        private RecursoRepository $recursoRepository
    ) {}

    public function execute(int $id)
    {
        DB::transaction(function () use ($id) {
            $ticketEntity = $this->repository->findById($id);

            if (!$ticketEntity) {
                throw new TicketNotFoundException();
            }

            $ticketEntity->markAsAnulado(auth()->user()->id);
            $this->repository->update($ticketEntity);

            if ($ticketEntity->recurso_id) {
                $recurso = $this->recursoRepository->findLockForUpdate($ticketEntity->recurso_id);

                if ($recurso && is_null($recurso->finalizado_por)) {
                    $recurso->litros_disponibles += $ticketEntity->litros->getLitros();
                    $recurso->litros_emitidos = max(0, $recurso->litros_emitidos - $ticketEntity->litros->getLitros());
                    $recurso->save();

                    $this->rebalanceRecursosService->executeByStationAndFuel(
                        $recurso->centro_costo_id,
                        $recurso->tipo_combustible_id,
                    );
                }
            }
        });
    }
}
