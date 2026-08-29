<?php

namespace Src\Modules\Tickets\Application\Services;

use Illuminate\Support\Facades\DB;
use Src\Modules\Bolsa\Domain\Contracts\BolsaRepository;
use Src\Modules\Bolsa\Domain\Entities\BolsaEntity;
use Src\Modules\Bolsa\Domain\ValueObjects\CantidadDisponibleValue;
use Src\Modules\Recurso\Application\Services\RebalanceRecursosService;
use Src\Modules\Tickets\Application\Dtos\UpdateTicketDto;
use Src\Modules\Tickets\Domain\Contracts\TicketRepository;
use Src\Modules\Tickets\Domain\Entities\TicketEntity;
use Src\Modules\Tickets\Domain\Events\TicketWasUpdated;
use Src\Modules\Tickets\Domain\Exceptions\TicketNotFoundException;
use Src\Modules\Tickets\Domain\ValueObjects\LitrosValue;
use Src\Modules\Recurso\Domain\Contracts\RecursoRepository;
use Src\Shared\Domain\Contracts\CheckCombustibleInterface;

class UpdateTicketService
{
    public function __construct(
        private TicketRepository $repository,
        private RecursoRepository $recursoRepository,
        private BolsaRepository $bolsaRepository,
        private RebalanceRecursosService $rebalanceRecursosService,
        private CheckCombustibleInterface $checkCombustible,
    ) {}

    public function execute(UpdateTicketDto $dto): TicketEntity
    {
        $ticketEntity = $this->repository->findLockForUpdate($dto->id);

        if (!$ticketEntity) {
            throw new TicketNotFoundException();
        }

        if (!$ticketEntity->ticket_status_id->isActive()) {
            throw new \InvalidArgumentException('Solo se pueden editar tickets en estado GENERADO.');
        }

        if (($ticketEntity->litros_consumidos ?? 0) > 0) {
            throw new \InvalidArgumentException('No se puede editar un ticket que ya tiene litros consumidos.');
        }

        if (!$this->checkCombustible->tieneCombustibleByIds($dto->centro_costo_id, $dto->tipo_combustible_id)) {
            throw new \InvalidArgumentException('El tipo de combustible no está disponible en el centro de costo seleccionado.');
        }

        return DB::transaction(function () use ($dto, $ticketEntity) {
            $oldLitros = $ticketEntity->litros->getLitros();
            $oldCentroCostoId = $ticketEntity->centro_costo_id;
            $oldTipoCombustibleId = $ticketEntity->tipo_combustible_id;
            $oldRecursoId = $ticketEntity->recurso_id;
            $oldEmpleadoId = $ticketEntity->personal_id;

            $hasCentroChanged = $dto->centro_costo_id !== $oldCentroCostoId;
            $hasTipoChanged = $dto->tipo_combustible_id !== $oldTipoCombustibleId;
            $hasLitrosChanged = $oldLitros !== $dto->litros;

            $newRecursoId = $dto->recurso_id;
            if (!$newRecursoId && ($hasCentroChanged || $hasTipoChanged)) {
                $newRecursoId = $this->bolsaRepository->findCurrentRecursoId(
                    $dto->centro_costo_id,
                    $dto->tipo_combustible_id
                );
            }
            if (!$newRecursoId && !$hasCentroChanged && !$hasTipoChanged) {
                $newRecursoId = $oldRecursoId;
            }

            $hasRecursoChanged = $newRecursoId !== $oldRecursoId;

            if ($oldRecursoId && ($hasRecursoChanged || $hasLitrosChanged)) {
                $oldRecurso = $this->recursoRepository->findLockForUpdate($oldRecursoId);
                if ($oldRecurso && is_null($oldRecurso->finalizado_por)) {
                    $oldRecurso->litros_disponibles += $oldLitros;
                    $oldRecurso->litros_emitidos = max(0, $oldRecurso->litros_emitidos - $oldLitros);
                    $oldRecurso->save();

                    $this->bolsaRepository->increaseLiters(new BolsaEntity(
                        $oldTipoCombustibleId,
                        $oldCentroCostoId,
                        new CantidadDisponibleValue($oldLitros),
                        $oldRecursoId,
                    ));
                }
            }

            if (!$newRecursoId) {
                throw new \InvalidArgumentException('No hay recursos disponibles para esta combinación.');
            }

            if ($hasRecursoChanged || $hasLitrosChanged) {
                $newRecurso = $this->recursoRepository->findLockForUpdate($newRecursoId);
                if (!$newRecurso || !is_null($newRecurso->finalizado_por)) {
                    throw new \InvalidArgumentException('El recurso seleccionado no está disponible.');
                }

                $nowDisponibles = (float) $newRecurso->litros_disponibles;

                if ($newRecursoId === $oldRecursoId && $hasLitrosChanged) {
                    if ($nowDisponibles < $dto->litros) {
                        throw new \InvalidArgumentException('El recurso no tiene litros disponibles suficientes.');
                    }
                    $newRecurso->litros_disponibles = $nowDisponibles - $dto->litros;
                    $newRecurso->litros_emitidos = ($newRecurso->litros_emitidos ?? 0) + $dto->litros;
                } else {
                    if ($nowDisponibles < $dto->litros) {
                        throw new \InvalidArgumentException('El recurso no tiene litros disponibles suficientes.');
                    }
                    $newRecurso->litros_disponibles -= $dto->litros;
                    $newRecurso->litros_emitidos = ($newRecurso->litros_emitidos ?? 0) + $dto->litros;
                }
                $newRecurso->save();

                $this->bolsaRepository->decreaseLiters(new BolsaEntity(
                    $dto->tipo_combustible_id,
                    $dto->centro_costo_id,
                    new CantidadDisponibleValue($dto->litros),
                    $newRecursoId,
                ));
            }

            $ticketEntity->litros = new LitrosValue($dto->litros);
            $ticketEntity->litros_asignados = $dto->litros;
            $ticketEntity->personal_id = $dto->personal_id;
            $ticketEntity->tipo_combustible_id = $dto->tipo_combustible_id;
            $ticketEntity->centro_costo_id = $dto->centro_costo_id;
            $ticketEntity->recurso_id = $newRecursoId;
            $ticketEntity->fecha_caducidad = $dto->fecha_caducidad;

            $this->repository->update($ticketEntity);

            $this->rebalanceRecursosService->executeByStationAndFuel(
                $dto->centro_costo_id,
                $dto->tipo_combustible_id
            );

            if ($hasCentroChanged || $hasTipoChanged) {
                $this->rebalanceRecursosService->executeByStationAndFuel(
                    $oldCentroCostoId,
                    $oldTipoCombustibleId
                );
            }

            event(new TicketWasUpdated(
                ticketEntity: $ticketEntity,
                oldLitros: $oldLitros,
                oldCentroCostoId: $oldCentroCostoId,
                oldTipoCombustibleId: $oldTipoCombustibleId,
                oldRecursoId: $oldRecursoId,
                oldEmpleadoId: $oldEmpleadoId,
            ));

            return $ticketEntity;
        });
    }
}
