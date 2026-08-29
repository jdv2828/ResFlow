<?php

namespace Src\Modules\Tickets\Application\Dtos;

class ConsumeTicketDto
{
    public function __construct(
        public int $id,
        public float $litros_consumidos
    ) {
    }
}
