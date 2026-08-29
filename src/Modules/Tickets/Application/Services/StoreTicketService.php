<?php

namespace Src\Modules\Tickets\Application\Services;

use Illuminate\Support\Facades\DB;
use Src\Modules\Bolsa\Domain\Contracts\BolsaRepository;
use Src\Modules\Recurso\Application\Services\RebalanceRecursosService;
use Src\Modules\Recurso\Domain\Contracts\RecursoRepository;
use Src\Modules\Tickets\Application\Dtos\StoreTicketDto;
use Src\Modules\Tickets\Domain\Contracts\TicketRepository;
use Src\Modules\Tickets\Domain\Entities\TicketEntity;
use Src\Modules\Tickets\Domain\Events\TicketWasCreated;
use Src\Modules\Tickets\Domain\ValueObjects\LitrosValue;
use Src\Shared\Domain\Contracts\CheckCombustibleInterface;

class StoreTicketService
{
    public function __construct(
        private TicketRepository $repository,
        private BolsaRepository $bolsaRepository,
        private RebalanceRecursosService $rebalanceRecursosService,
        private RecursoRepository $recursoRepository,
        private CheckCombustibleInterface $checkCombustible,
    ) {}

    public function execute(StoreTicketDto $dto)
    {
        if(!$this->checkCombustible->tieneCombustibleByIds((int)$dto->centro_costo_id,(int)$dto->tipo_combustible_id)){
            throw new \Src\Modules\Recurso\Domain\Exceptions\CombustibleNotFoundInEstacion();
        }

        return DB::transaction(function () use ($dto) {
            $remainingLiters = (float) $dto->litros;
            $firstRecursoId = null;
            $consumptions = [];

            while ($remainingLiters > 0) {
                $recursoId = $this->bolsaRepository->findCurrentRecursoId(
                    (int) $dto->centro_costo_id,
                    (int) $dto->tipo_combustible_id
                );

                if (!$recursoId) {
                    throw new \InvalidArgumentException('No hay litros disponibles suficientes entre los recursos activos para este combustible.');
                }

                $recurso = $this->recursoRepository->findLockForUpdate($recursoId);

                if (!$recurso || $recurso->litros_disponibles <= 0) {
                    $this->rebalanceRecursosService->executeByStationAndFuel(
                        $dto->centro_costo_id,
                        $dto->tipo_combustible_id
                    );

                    continue;
                }

                if ($recurso->litros_inicial > 0 && $recurso->litros_emitidos >= $recurso->litros_inicial) {
                    $recurso->litros_disponibles = 0;
                    $recurso->activo = false;
                    $recurso->save();

                    $this->rebalanceRecursosService->executeByStationAndFuel(
                        $dto->centro_costo_id,
                        $dto->tipo_combustible_id
                    );

                    continue;
                }

                $litersToConsume = min((float) $recurso->litros_disponibles, $remainingLiters);

                if ($recurso->litros_inicial > 0) {
                    $maxEmitir = (float) $recurso->litros_inicial - (float) $recurso->litros_emitidos;
                    $litersToConsume = min($litersToConsume, $maxEmitir);
                }

                if ($litersToConsume <= 0) {
                    $recurso->litros_disponibles = 0;
                    $recurso->activo = false;
                    $recurso->save();

                    $this->rebalanceRecursosService->executeByStationAndFuel(
                        $dto->centro_costo_id,
                        $dto->tipo_combustible_id
                    );

                    continue;
                }

                if ($firstRecursoId === null) {
                    $firstRecursoId = $recurso->id;
                }

                $consumptions[] = [
                    'recurso_id' => $recurso->id,
                    'numero_factura' => $recurso->numero_factura,
                    'litros' => $litersToConsume,
                ];

                $recurso->litros_disponibles -= $litersToConsume;
                $recurso->litros_emitidos += $litersToConsume;
                $recurso->save();

                $this->bolsaRepository->decreaseLiters(new \Src\Modules\Bolsa\Domain\Entities\BolsaEntity(
                    $dto->tipo_combustible_id,
                    $dto->centro_costo_id,
                    new \Src\Modules\Bolsa\Domain\ValueObjects\CantidadDisponibleValue($litersToConsume),
                    $recurso->id,
                ));

                $remainingLiters -= $litersToConsume;

                $this->rebalanceRecursosService->executeByStationAndFuel(
                    $dto->centro_costo_id,
                    $dto->tipo_combustible_id
                );
            }

            if ($firstRecursoId === null) {
                throw new \InvalidArgumentException('No se encontró un recurso para emitir el ticket.');
            }

            $ticket = TicketEntity::create(
                new LitrosValue($dto->litros),
                $dto->personal_id,
                $dto->tipo_combustible_id,
                $dto->centro_costo_id,
                $firstRecursoId,
                $dto->emitido_por,
                $dto->fecha_caducidad
            );

            $ticket->consumptions = $consumptions;

            $this->repository->save($ticket);

            event(new TicketWasCreated($ticket));

            $this->rebalanceRecursosService->executeByStationAndFuel(
                $dto->centro_costo_id,
                $dto->tipo_combustible_id
            );

            return $ticket;
        });
    }
}
