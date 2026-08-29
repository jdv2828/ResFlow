<?php

namespace Src\Modules\ActivityLog\Infrastructure\Listeners;

use App\Models\ActivityLog;
use Src\Modules\Recurso\Domain\Events\RecursoActivated;
use Src\Modules\Recurso\Domain\Events\RecursoDeactivated;
use Src\Modules\Recurso\Domain\Events\RecursoWasCreated;
use Src\Modules\Recurso\Domain\Events\RecursoWasDeleted;
use Src\Modules\Tickets\Domain\Events\TicketStatusWasChanged;
use Src\Modules\Tickets\Domain\Events\TicketWasConsumed;
use Src\Modules\Tickets\Domain\Events\TicketWasCreated;
use Src\Modules\Tickets\Domain\Events\TicketWasDeleted;
use Src\Modules\Tickets\Domain\Events\TicketWasUpdated;

class RecordActivityListener
{
    public function handle(object $event): void
    {
        [$action, $userId, $subjectType, $subjectId, $data] = match (true) {
            $event instanceof RecursoWasCreated => [
                'recurso.created',
                $event->recursoEntity->emitido_por,
                'App\\Models\\Recurso',
                $event->recursoEntity->id,
                [
                    'numero_factura' => $event->recursoEntity->numero_factura,
                    'litros' => $event->recursoEntity->litros->getLitros(),
                    'litros_disponibles' => $event->recursoEntity->litros_disponibles->getLitros(),
                    'monto' => $event->recursoEntity->monto->getMonto(),
                    'tipo_combustible_id' => $event->recursoEntity->tipo_combustible_id,
                    'centro_costo_id' => $event->recursoEntity->centro_costo_id,
                    'orden' => $event->recursoEntity->orden,
                ],
            ],
            $event instanceof RecursoWasDeleted => [
                'recurso.deleted',
                $event->recursoEntity->finalizado_por,
                'App\\Models\\Recurso',
                $event->recursoEntity->id,
                [
                    'numero_factura' => $event->recursoEntity->numero_factura,
                    'litros_disponibles' => $event->recursoEntity->litros_disponibles->getLitros(),
                    'tipo_combustible_id' => $event->recursoEntity->tipo_combustible_id,
                    'centro_costo_id' => $event->recursoEntity->centro_costo_id,
                ],
            ],
            $event instanceof RecursoActivated => [
                'recurso.activated',
                auth()->id(),
                'App\\Models\\Recurso',
                $event->recursoId,
                [
                    'numero_factura' => $event->numeroFactura,
                    'litros_disponibles' => $event->litrosDisponibles,
                    'tipo_combustible_id' => $event->tipoCombustibleId,
                    'centro_costo_id' => $event->centroCostoId,
                ],
            ],
            $event instanceof RecursoDeactivated => [
                'recurso.deactivated',
                auth()->id(),
                'App\\Models\\Recurso',
                $event->recursoId,
                [
                    'numero_factura' => $event->numeroFactura,
                    'litros_disponibles' => $event->litrosDisponibles,
                    'tipo_combustible_id' => $event->tipoCombustibleId,
                    'centro_costo_id' => $event->centroCostoId,
                ],
            ],
            $event instanceof TicketWasCreated => [
                'ticket.created',
                $event->ticketEntity->emitido_por,
                'App\\Models\\Ticket',
                $event->ticketEntity->id,
                [
                    'ticket_id' => $event->ticketEntity->id,
                    'hash' => $event->ticketEntity->hash,
                    'litros' => $event->ticketEntity->litros->getLitros(),
                    'personal_id' => $event->ticketEntity->personal_id,
                    'tipo_combustible_id' => $event->ticketEntity->tipo_combustible_id,
                    'centro_costo_id' => $event->ticketEntity->centro_costo_id,
                    'recurso_id' => $event->ticketEntity->recurso_id,
                    'recursos_consumidos' => $event->ticketEntity->consumptions,
                ],
            ],
            $event instanceof TicketWasDeleted => [
                'ticket.anulado',
                $event->ticketEntity->finalizado_por,
                'App\\Models\\Ticket',
                $event->ticketEntity->id,
                [
                    'ticket_id' => $event->ticketEntity->id,
                    'hash' => $event->ticketEntity->hash,
                    'litros_restantes' => $event->ticketEntity->litros->getLitros(),
                    'personal_id' => $event->ticketEntity->personal_id,
                    'tipo_combustible_id' => $event->ticketEntity->tipo_combustible_id,
                    'centro_costo_id' => $event->ticketEntity->centro_costo_id,
                    'recurso_id' => $event->ticketEntity->recurso_id,
                ],
            ],
            $event instanceof TicketWasUpdated => [
                'ticket.updated',
                auth()->id(),
                'App\\Models\\Ticket',
                $event->ticketEntity->id,
                [
                    'ticket_id' => $event->ticketEntity->id,
                    'hash' => $event->ticketEntity->hash,
                    'litros' => $event->ticketEntity->litros->getLitros(),
                    'personal_id' => $event->ticketEntity->personal_id,
                    'tipo_combustible_id' => $event->ticketEntity->tipo_combustible_id,
                    'centro_costo_id' => $event->ticketEntity->centro_costo_id,
                    'recurso_id' => $event->ticketEntity->recurso_id,
                    'old_litros' => $event->oldLitros,
                    'old_centro_costo_id' => $event->oldCentroCostoId,
                    'old_tipo_combustible_id' => $event->oldTipoCombustibleId,
                    'old_recurso_id' => $event->oldRecursoId,
                    'old_empleado_id' => $event->oldEmpleadoId,
                ],
            ],
            $event instanceof TicketWasConsumed => [
                'ticket.consumed',
                $event->ticketEntity->finalizado_por ?? auth()->id(),
                'App\\Models\\Ticket',
                $event->ticketEntity->id,
                [
                    'ticket_id' => $event->ticketEntity->id,
                    'hash' => $event->ticketEntity->hash,
                    'litros_restantes' => $event->ticketEntity->litros->getLitros(),
                    'personal_id' => $event->ticketEntity->personal_id,
                    'tipo_combustible_id' => $event->ticketEntity->tipo_combustible_id,
                    'centro_costo_id' => $event->ticketEntity->centro_costo_id,
                    'recurso_id' => $event->ticketEntity->recurso_id,
                ],
            ],
            $event instanceof TicketStatusWasChanged => [
                $event->action,
                auth()->id(),
                'App\\Models\\Ticket',
                $event->ticketEntity->id,
                [
                    'ticket_id' => $event->ticketEntity->id,
                    'hash' => $event->ticketEntity->hash,
                    'previous_status_id' => $event->previousStatus,
                    'new_status_id' => $event->newStatus,
                    'recurso_id' => $event->ticketEntity->recurso_id,
                ],
            ],
            default => [null, null, null, null, []],
        };

        if (!$action) {
            return;
        }

        ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'data' => $data,
        ]);
    }
}
