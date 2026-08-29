<?php

namespace Src\Modules\Recurso\Domain\Entities;

use Src\Modules\Recurso\Domain\Events\RecursoWasDeleted as EventsRecursoWasDeleted;
use Src\Modules\Recurso\Domain\ValueObjects\LitrosValue;
use Src\Modules\Recurso\Domain\ValueObjects\MontoValue;

class RecursoEntity
{
    public function __construct(
        public ?int $id,
        public LitrosValue $litros,
        public LitrosValue $litros_disponibles,
        public MontoValue $monto,
        public int $tipo_combustible_id,
        public int $centro_costo_id,
        public string $numero_factura,
        public int $orden,
        public ?int $emitido_por = null,
        public ?int $finalizado_por = null,
        public ?bool $activo = true,
    ) {}

    public static function create(
        LitrosValue $litros,
        LitrosValue $litros_disponibles,
        MontoValue $monto,
        int $tipo_combustible_id,
        int $centro_costo_id,
        string $numero_factura,
        int $orden,
        ?int $emitido_por = null
    ): self {
        $recurso = new self(
            null,
            $litros,
            $litros_disponibles,
            $monto,
            $tipo_combustible_id,
            $centro_costo_id,
            $numero_factura,
            $orden,
            $emitido_por
        );

        return $recurso;
    }
    public function markAsAnulado(int $userId): self
    {
        $this->activo = false;
        $this->finalizado_por = $userId;
        event(new EventsRecursoWasDeleted($this));
        return $this;
    }

    // public static function markEntityAsDeleted(RecursoEntity $recurso, int $userId): self
    // {
    //     $recurso->activo = false;
    //     $recurso->finalizado_por = $userId;
    //     event(new EventsRecursoWasDeleted($recurso));
    //     return $recurso->markAsDeleted($userId);
    // }
}
