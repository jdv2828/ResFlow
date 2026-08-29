<?php

namespace Src\Modules\Tickets\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Src\Modules\Tickets\Domain\Entities\TicketEntity;

class TicketStatusWasChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public TicketEntity $ticketEntity,
        public int $previousStatus,
        public int $newStatus,
        public string $action,
    ) {}
}
