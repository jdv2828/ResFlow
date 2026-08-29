<?php

namespace Src\Modules\Tickets\Application\Dtos;

class UpdateTicketDto
{
    public function __construct(
        public readonly int $id,
        public readonly float $litros,
        public readonly int $personal_id,
        public readonly int $tipo_combustible_id,
        public readonly int $centro_costo_id,
        public readonly ?int $recurso_id = null,
        public readonly ?string $fecha_caducidad = null,
        public readonly ?string $hash = null,
        public readonly ?string $numero_automatico = null,
    ) {}
}
