<?php

namespace Src\Modules\Tickets\Domain\Entities;

use Src\Modules\Tickets\Domain\Events\TicketWasCreated;
use Src\Modules\Tickets\Domain\Events\TicketWasDeleted;
use Src\Modules\Tickets\Domain\Events\TicketWasConsumed;
use Src\Modules\Tickets\Domain\Exceptions\InsufficientLitrosException;
use Src\Modules\Tickets\Domain\ValueObjects\LitrosValue;
use Src\Modules\Tickets\Domain\ValueObjects\TicketStatusValue;

class TicketEntity
{
    public function __construct(
        public ?int $id,
        public LitrosValue $litros,
        public int $personal_id,
        public int $tipo_combustible_id,
        public int $centro_costo_id,
        public ?int $recurso_id = null,
        public ?int $emitido_por = null,
        public ?int $finalizado_por = null,
        public ?string $fecha_caducidad = null,
        public ?TicketStatusValue $ticket_status_id = null,
        public ?bool $activo = true,
        public ?string $hash = null,
        public ?string $numero_automatico = null,
        public array $consumptions = [],
        public ?float $litros_asignados = null,
        public ?float $litros_consumidos = null,
    ) {}

    public static function create(
        LitrosValue $litros,
        int $personal_id,
        int $tipo_combustible_id,
        int $centro_costo_id,
        ?int $recurso_id = null,
        ?int $emitido_por = null,
        ?string $fecha_caducidad = null
    ): self {
        $litrosValue = $litros->getLitros();

        $ticket = new self(
            id: null,
            litros: $litros,
            personal_id: $personal_id,
            tipo_combustible_id: $tipo_combustible_id,
            centro_costo_id: $centro_costo_id,
            recurso_id: $recurso_id,
            emitido_por: $emitido_por,
            fecha_caducidad: $fecha_caducidad,
            ticket_status_id: new TicketStatusValue(TicketStatusValue::GENERADO),
            litros_asignados: $litrosValue,
            litros_consumidos: 0.0,
        );

        return $ticket;
    }

    public function markAsAnulado(int $userId): self
    {
        $this->activo = false;
        $this->finalizado_por = $userId;
        $this->emitido_por = null;
        $this->ticket_status_id = new TicketStatusValue(TicketStatusValue::ANULADO);

        event(new TicketWasDeleted($this));

        return $this;
    }

    public function consumeLitros(float $consumed): self
    {
        if ($this->litros->getLitros() < $consumed) {
            throw new InsufficientLitrosException();
        }

        $this->litros = new LitrosValue($this->litros->getLitros() - $consumed);
        $this->litros_consumidos = ($this->litros_consumidos ?? 0) + $consumed;

        if ($this->litros->getLitros() == 0) {
            $this->activo = false;
            $this->ticket_status_id = new TicketStatusValue(TicketStatusValue::UTILIZADO);
            event(new TicketWasConsumed($this));
        }

        return $this;
    }

    public function markAsInProcess(): self
    {
        $this->ticket_status_id = new TicketStatusValue(TicketStatusValue::FINALIZADO);
        return $this;
    }

    public function markAsPending(): self
    {
        $this->ticket_status_id = new TicketStatusValue(TicketStatusValue::VENCIDO);
        return $this;
    }

    public static function markEntityAsAnulado(TicketEntity $ticket, int $userId): self
    {
        $ticket->activo = false;
        $ticket->finalizado_por = $userId;
        return $ticket->markAsAnulado($userId);
    }
}
