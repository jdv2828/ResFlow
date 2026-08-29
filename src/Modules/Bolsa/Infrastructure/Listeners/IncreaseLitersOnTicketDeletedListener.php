<?php

namespace Src\Modules\Bolsa\Infrastructure\Listeners;

use Src\Modules\Bolsa\Application\Dtos\FuelLitersDto;
use Src\Modules\Bolsa\Application\Services\IncreaseLitersService;
use Src\Modules\Tickets\Domain\Events\TicketWasDeleted;

class IncreaseLitersOnTicketDeletedListener
{
    public function __construct(private IncreaseLitersService $increaseLitersService)
    {}

    public function handle(TicketWasDeleted $event): void
    {
        if (!$event->ticketEntity->recurso_id) {
            return;
        }

        $dto = new FuelLitersDto(
            $event->ticketEntity->litros->getLitros(),
            $event->ticketEntity->tipo_combustible_id,
            $event->ticketEntity->centro_costo_id,
            $event->ticketEntity->recurso_id
        );

        $this->increaseLitersService->execute($dto);
    }
}
