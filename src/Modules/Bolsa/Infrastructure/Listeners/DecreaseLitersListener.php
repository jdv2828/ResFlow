<?php

namespace Src\Modules\Bolsa\Infrastructure\Listeners;

use Src\Modules\Bolsa\Application\Dtos\FuelLitersDto;
use Src\Modules\Bolsa\Application\Services\DecreaseLitersService;
class DecreaseLitersListener
{
    public function __construct(private DecreaseLitersService $decreaseLitersService)
    {}

    public function handle($event):void
    {
        $dto = new FuelLitersDto(
            $event->recursoEntity->litros_disponibles->getLitros(),
            $event->recursoEntity->tipo_combustible_id,
            $event->recursoEntity->centro_costo_id,
            $event->recursoEntity->id
        );

        $this->decreaseLitersService->execute($dto);

    }
}
