<?php

namespace Src\Modules\Bolsa\Infrastructure\Listeners;

use Src\Modules\Bolsa\Application\Dtos\FuelLitersDto;
use Src\Modules\Bolsa\Application\Services\DecreaseLitersService;
use Src\Modules\Tickets\Domain\Events\TicketWasCreated;

class DecreaseLitersOnTicketCreatedListener
{
    public function __construct(private DecreaseLitersService $decreaseLitersService)
    {}

    public function handle(TicketWasCreated $event): void
    {
        $dto = new FuelLitersDto(
            $event->ticketEntity->litros->getLitros(),
            $event->ticketEntity->tipo_combustible_id,
            $event->ticketEntity->centro_costo_id,
            $event->ticketEntity->recurso_id
        );

        $this->decreaseLitersService->execute($dto);
    }
}
