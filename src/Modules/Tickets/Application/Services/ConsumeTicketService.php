<?php

namespace Src\Modules\Tickets\Application\Services;

use Illuminate\Support\Facades\DB;
use Src\Modules\Recurso\Domain\Contracts\RecursoRepository;
use Src\Modules\Bolsa\Application\Dtos\FuelLitersDto;
use Src\Modules\Bolsa\Application\Services\IncreaseLitersService;
use Src\Modules\Recurso\Application\Services\RebalanceRecursosService;
use Src\Modules\Tickets\Application\Dtos\ConsumeTicketDto;
use Src\Modules\Tickets\Domain\Contracts\TicketRepository;
use Src\Modules\Tickets\Domain\Events\TicketWasConsumed;
use Src\Modules\Tickets\Domain\Exceptions\TicketNotFoundException;
use Src\Modules\Tickets\Domain\Exceptions\InsufficientLitrosException;
use Src\Modules\Tickets\Domain\ValueObjects\LitrosValue;
use Src\Modules\Tickets\Domain\ValueObjects\TicketStatusValue;

class ConsumeTicketService
{
    public function __construct(
        private TicketRepository $repository,
        private IncreaseLitersService $increaseLitersService,
        private RebalanceRecursosService $rebalanceRecursosService,
        private RecursoRepository $recursoRepository
    ) {}

    public function execute(ConsumeTicketDto $dto)
    {
        return DB::transaction(function () use ($dto) {
            $ticketEntity = $this->repository->findById($dto->id);

            if (!$ticketEntity) {
                throw new TicketNotFoundException();
            }

            try {
                $litrosActuales = $ticketEntity->litros->getLitros();
                $litrosRestantes = $litrosActuales - $dto->litros_consumidos;

                if ($litrosRestantes < 0) {
                    throw new InsufficientLitrosException();
                }

                $ticketEntity->litros = new LitrosValue($litrosRestantes);
                $ticketEntity->litros_consumidos = ($ticketEntity->litros_consumidos ?? 0) + $dto->litros_consumidos;
                $ticketEntity->litros_asignados ??= $litrosActuales + $dto->litros_consumidos;
                $ticketEntity->activo = false;
                $ticketEntity->finalizado_por = auth()->user()->id;
                $ticketEntity->ticket_status_id = new TicketStatusValue(TicketStatusValue::UTILIZADO);

                $this->repository->update($ticketEntity);

                if ($ticketEntity->recurso_id) {
                    $recursoConsumo = $this->recursoRepository->findLockForUpdate($ticketEntity->recurso_id);

                    if ($recursoConsumo && is_null($recursoConsumo->finalizado_por)) {
                        $recursoConsumo->litros_consumidos += $dto->litros_consumidos;
                        $recursoConsumo->save();
                    }
                }

                if ($litrosRestantes > 0 && $ticketEntity->recurso_id) {
                    $recursoRestante = $this->recursoRepository->findLockForUpdate($ticketEntity->recurso_id);

                    if ($recursoRestante && is_null($recursoRestante->finalizado_por)) {
                        $recursoRestante->litros_disponibles += $litrosRestantes;
                        $recursoRestante->litros_emitidos -= $litrosRestantes;
                        $recursoRestante->save();

                        $this->increaseLitersService->execute(new FuelLitersDto(
                            litros: $litrosRestantes,
                            tipo_combustible_id: $ticketEntity->tipo_combustible_id,
                            centro_costo_id: $ticketEntity->centro_costo_id,
                            recurso_id: $ticketEntity->recurso_id,
                        ));
                    }
                }

                if ($ticketEntity->recurso_id) {
                    $this->rebalanceRecursosService->executeByRecursoId($ticketEntity->recurso_id);
                }

                event(new TicketWasConsumed($ticketEntity));

                return $ticketEntity;
            } catch (InsufficientLitrosException $e) {
                throw $e;
            }
        });
    }
}
