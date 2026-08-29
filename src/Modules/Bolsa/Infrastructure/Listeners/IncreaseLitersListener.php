<?php

namespace Src\Modules\Bolsa\Infrastructure\Listeners;

use Src\Modules\Bolsa\Application\Dtos\FuelLitersDto;
use Src\Modules\Bolsa\Application\Services\IncreaseLitersService;

class IncreaseLitersListener
{
    public function __construct(private IncreaseLitersService $increaseLitersService)
    {}

    public function handle($event):void
    {
        $dto = new FuelLitersDto(
            $event->recursoEntity->litros_disponibles->getLitros(),
            $event->recursoEntity->tipo_combustible_id,
            $event->recursoEntity->centro_costo_id,
            $event->recursoEntity->id
        );

        $this->increaseLitersService->execute($dto);

    }
}

// public LitrosValue $litros,
// public MontoValue $monto,
// public int $tipo_combustible_id,
// public int $estacion_servicio_id,
