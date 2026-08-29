<?php

namespace Src\Modules\Tickets\Infrastructure\Repositories;

use App\Models\Ticket as EloquentTicket;
use Src\Modules\Tickets\Domain\Contracts\TicketRepository;
use Src\Modules\Tickets\Domain\Entities\TicketEntity;
use Src\Modules\Tickets\Domain\Exceptions\TicketNotFoundException;
use Src\Modules\Tickets\Domain\ValueObjects\LitrosValue;
use Src\Modules\Tickets\Domain\ValueObjects\TicketStatusValue;
use Illuminate\Support\Facades\DB;

class TicketRepositoryImpl implements TicketRepository
{
    public function findByHash(string $hash): ?TicketEntity
    {
        $ticket = DB::table('tickets')->where('hash', $hash)->first();

        if (!$ticket) {
            return null;
        }

        return new TicketEntity(
            $ticket->id,
            new LitrosValue($ticket->litros),
            $ticket->personal_id,
            $ticket->tipo_combustible_id,
            $ticket->centro_costo_id,
            $ticket->recurso_id,
            $ticket->emitido_por,
            $ticket->finalizado_por,
            $ticket->fecha_caducidad,
            new TicketStatusValue($ticket->ticket_status_id),
            $ticket->activo,
            $ticket->hash,
            $ticket->numero_automatico,
            [],
            (float) ($ticket->litros_asignados ?? 0),
            (float) ($ticket->litros_consumidos ?? 0),
        );
    }

    public function save(TicketEntity $ticket): void
    {
        $eloquentTicket = $ticket->id
            ? EloquentTicket::find($ticket->id)
            : EloquentTicket::where('hash', $ticket->hash)->first();

        $eloquentTicket ??= new EloquentTicket();

        $eloquentTicket->litros = $ticket->litros->getLitros();
        $eloquentTicket->litros_asignados = $ticket->litros_asignados ?? $ticket->litros->getLitros();
        $eloquentTicket->litros_consumidos = $ticket->litros_consumidos ?? 0;
        $eloquentTicket->personal_id = $ticket->personal_id;
        $eloquentTicket->tipo_combustible_id = $ticket->tipo_combustible_id;
        $eloquentTicket->centro_costo_id = $ticket->centro_costo_id;
        $eloquentTicket->recurso_id = $ticket->recurso_id;
        $eloquentTicket->emitido_por = $ticket->emitido_por;
        $eloquentTicket->finalizado_por = $ticket->finalizado_por;
        $eloquentTicket->fecha_caducidad = $ticket->fecha_caducidad;
        $eloquentTicket->ticket_status_id = $ticket->ticket_status_id->getValue();
        $eloquentTicket->activo = $ticket->activo;
        $eloquentTicket->hash = $ticket->hash;
        $eloquentTicket->numero_automatico = $ticket->numero_automatico;

        $eloquentTicket->save();

        $ticket->id = $eloquentTicket->id;
        $ticket->hash = $eloquentTicket->hash;
        $ticket->numero_automatico = $eloquentTicket->numero_automatico;
    }

    public function findById(string $id): ?TicketEntity
    {
        $ticket = DB::table('tickets')->where('id', $id)->first();

        if (!$ticket) {
            return null;
        }

        return new TicketEntity(
            $ticket->id,
            new LitrosValue($ticket->litros),
            $ticket->personal_id,
            $ticket->tipo_combustible_id,
            $ticket->centro_costo_id,
            $ticket->recurso_id,
            $ticket->emitido_por,
            $ticket->finalizado_por,
            $ticket->fecha_caducidad,
            new TicketStatusValue($ticket->ticket_status_id),
            $ticket->activo,
            $ticket->hash,
            $ticket->numero_automatico,
            [],
            (float) ($ticket->litros_asignados ?? 0),
            (float) ($ticket->litros_consumidos ?? 0),
        );
    }

    public function findLockForUpdate(int $id): ?TicketEntity
    {
        $ticket = EloquentTicket::query()->whereKey($id)->lockForUpdate()->first();

        if (!$ticket) {
            return null;
        }

        return new TicketEntity(
            $ticket->id,
            new LitrosValue($ticket->litros),
            $ticket->personal_id,
            $ticket->tipo_combustible_id,
            $ticket->centro_costo_id,
            $ticket->recurso_id,
            $ticket->emitido_por,
            $ticket->finalizado_por,
            $ticket->fecha_caducidad,
            new TicketStatusValue($ticket->ticket_status_id),
            $ticket->activo,
            $ticket->hash,
            $ticket->numero_automatico,
            [],
            (float) ($ticket->litros_asignados ?? 0),
            (float) ($ticket->litros_consumidos ?? 0),
        );
    }


    public function update(TicketEntity $ticket): void
    {
        $eloquentTicket = EloquentTicket::where('hash', $ticket->hash)->first();

        if (!$eloquentTicket) {
            throw new TicketNotFoundException();
        }

        $eloquentTicket->litros = $ticket->litros->getLitros();
        $eloquentTicket->litros_asignados = $ticket->litros_asignados ?? $ticket->litros->getLitros();
        $eloquentTicket->litros_consumidos = $ticket->litros_consumidos ?? 0;
        $eloquentTicket->personal_id = $ticket->personal_id;
        $eloquentTicket->tipo_combustible_id = $ticket->tipo_combustible_id;
        $eloquentTicket->centro_costo_id = $ticket->centro_costo_id;
        $eloquentTicket->recurso_id = $ticket->recurso_id;
        $eloquentTicket->emitido_por = $ticket->emitido_por;
        $eloquentTicket->finalizado_por = $ticket->finalizado_por;
        $eloquentTicket->fecha_caducidad = $ticket->fecha_caducidad;
        $eloquentTicket->ticket_status_id = $ticket->ticket_status_id->getValue();
        $eloquentTicket->activo = $ticket->activo;
        $eloquentTicket->hash = $ticket->hash;
        $eloquentTicket->numero_automatico = $ticket->numero_automatico;

        $eloquentTicket->save();

        $ticket->id = $eloquentTicket->id;
    }
}
