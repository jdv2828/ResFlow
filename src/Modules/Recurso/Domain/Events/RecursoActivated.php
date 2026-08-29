<?php

namespace Src\Modules\Recurso\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RecursoActivated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $recursoId,
        public string $numeroFactura,
        public float $litrosDisponibles,
        public int $tipoCombustibleId,
        public int $centroCostoId,
    ) {}
}
