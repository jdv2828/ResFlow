<?php

namespace Src\Modules\Tickets\Application\Dtos;

class StoreTicketDto
{
    public function __construct(
        public float $litros,
        public int $personal_id,
        public int $tipo_combustible_id,
        public int $centro_costo_id,
        public ?int $recurso_id = null,
        public ?int $emitido_por = null,
        public ?string $fecha_caducidad = null,
        public ?string $hash = null,
        public ?string $numero_automatico = null
    ) {
    }
}
