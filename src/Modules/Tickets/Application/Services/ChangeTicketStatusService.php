<?php

namespace Src\Modules\Tickets\Application\Services;

use Src\Modules\Tickets\Domain\Contracts\TicketRepository;
use Src\Modules\Tickets\Domain\Events\TicketStatusWasChanged;
use Src\Modules\Tickets\Domain\Exceptions\TicketNotFoundException;

class ChangeTicketStatusService
{
    public function __construct(
        private TicketRepository $repository
    ) {}

    public function markAsInProcess(string $hash)
    {
        $ticketEntity = $this->repository->findByHash($hash);

        if (!$ticketEntity) {
            throw new TicketNotFoundException();
        }

        $previousStatus = $ticketEntity->ticket_status_id?->getValue();
        $ticketEntity->markAsInProcess();
        $this->repository->save($ticketEntity);
        event(new TicketStatusWasChanged(
            $ticketEntity,
            $previousStatus,
            $ticketEntity->ticket_status_id->getValue(),
            'ticket.marked_as_in_process'
        ));

        return $ticketEntity;
    }

    public function markAsPending(string $hash)
    {
        $ticketEntity = $this->repository->findByHash($hash);

        if (!$ticketEntity) {
            throw new TicketNotFoundException();
        }

        $previousStatus = $ticketEntity->ticket_status_id?->getValue();
        $ticketEntity->markAsPending();
        $this->repository->save($ticketEntity);
        event(new TicketStatusWasChanged(
            $ticketEntity,
            $previousStatus,
            $ticketEntity->ticket_status_id->getValue(),
            'ticket.marked_as_pending'
        ));

        return $ticketEntity;
    }
}
