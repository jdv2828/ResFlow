<?php

namespace Src\Modules\Tickets\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Src\Modules\Tickets\Domain\Entities\TicketEntity;

class TicketWasUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public TicketEntity $ticketEntity,
        public ?float $oldLitros = null,
        public ?int $oldCentroCostoId = null,
        public ?int $oldTipoCombustibleId = null,
        public ?int $oldRecursoId = null,
        public ?int $oldEmpleadoId = null,
    ) {}
}
