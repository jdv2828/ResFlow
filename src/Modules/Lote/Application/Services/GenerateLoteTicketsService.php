<?php

namespace Src\Modules\Lote\Application\Services;

use App\Models\LoteEmpleado;
use Illuminate\Support\Facades\DB;
use Src\Modules\Lote\Domain\Contracts\LoteRepository;
use Src\Modules\Lote\Domain\Events\LoteWasGenerated;
use Src\Modules\Lote\Domain\Entities\LoteEntity;
use Src\Modules\Tickets\Application\Dtos\StoreTicketDto;
use Src\Modules\Tickets\Application\Services\StoreTicketService;

class GenerateLoteTicketsService
{
    public function __construct(
        private LoteRepository $loteRepository,
        private StoreTicketService $storeTicketService
    ) {}

    public function execute(int $loteId): array
    {
        return DB::transaction(function () use ($loteId) {
            $lote = $this->loteRepository->findWithLoteEmpleados($loteId);
            if (!$lote) {
                throw new \InvalidArgumentException('Lote no encontrado.');
            }

            if (!$lote->activo) {
                throw new \InvalidArgumentException('El lote no está activo.');
            }

            if ($lote->loteEmpleados->isEmpty()) {
                throw new \InvalidArgumentException('El lote no tiene empleados asignados.');
            }

            $ticketIds = [];
            $skippedCount = 0;

            foreach ($lote->loteEmpleados as $detalle) {
                $empleado = $detalle->personal;
                if (!$empleado) {
                    $skippedCount++;
                    continue;
                }

                $litrosPorVale = (float) $detalle->litros;
                $cantidadVales = (int) $detalle->cantidad_vales;

                if ($litrosPorVale <= 0 || $cantidadVales <= 0) {
                    continue;
                }

                for ($i = 0; $i < $cantidadVales; $i++) {
                    $dto = new StoreTicketDto(
                        litros: $litrosPorVale,
                        personal_id: $empleado->id,
                        tipo_combustible_id: $lote->tipo_combustible_id,
                        centro_costo_id: $lote->centro_costo_id,
                        emitido_por: auth()->id(),
                        fecha_caducidad: $detalle->fecha_caducidad,
                    );

                    $ticket = $this->storeTicketService->execute($dto);
                    $ticketIds[] = $ticket->id;
                }
            }

            $loteEntity = new LoteEntity(
                id: $lote->id,
                nombre: $lote->nombre,
                responsable_id: $lote->responsable_id,
                centro_costo_id: $lote->centro_costo_id,
                tipo_combustible_id: $lote->tipo_combustible_id,
                activo: $lote->activo,
            );

            event(new LoteWasGenerated($loteEntity, $ticketIds));

            return ['ticket_ids' => $ticketIds, 'skipped_count' => $skippedCount];
        });
    }
}
