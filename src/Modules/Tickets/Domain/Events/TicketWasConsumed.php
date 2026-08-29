<?php

namespace Src\Modules\Tickets\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Src\Modules\Tickets\Domain\Entities\TicketEntity;

class TicketWasConsumed
{
    use Dispatchable, SerializesModels;

    public $ticketEntity;

    public function __construct(TicketEntity $ticketEntity)
    {
        $this->ticketEntity = $ticketEntity;
    }
}
