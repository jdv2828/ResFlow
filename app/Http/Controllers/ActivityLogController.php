<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Recurso;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use PDF;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->buildQuery($request);

        $activityLogs = $query
            ->paginate(25)
            ->withQueryString();

        $this->decorateActivityLogs($activityLogs);

        return view('auditoria.index', [
            'activityLogs' => $activityLogs,
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'actions' => ActivityLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
            'subjectTypes' => ActivityLog::query()->select('subject_type')->whereNotNull('subject_type')->distinct()->orderBy('subject_type')->pluck('subject_type'),
            'metrics' => $this->metrics(),
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = $this->buildQuery($request);
        $activityLogs = $query->get();

        return response()->streamDownload(function () use ($activityLogs) {
            $output = fopen('php://output', 'w');

            fputcsv($output, [
                'fecha',
                'accion',
                'usuario_id',
                'usuario_nombre',
                'usuario_email',
                'subject_type',
                'subject_id',
                'data',
            ]);

            foreach ($activityLogs as $activityLog) {
                fputcsv($output, [
                    $activityLog->created_at?->format('Y-m-d H:i:s'),
                    $activityLog->action,
                    $activityLog->user_id,
                    $activityLog->user?->name,
                    $activityLog->user?->email,
                    $activityLog->subject_type,
                    $activityLog->subject_id,
                    json_encode($activityLog->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            }

            fclose($output);
        }, 'activity-log.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function recursoTimeline(int $recursoId)
    {
        $recurso = Recurso::query()->with(['tipoCombustible', 'centroCosto'])->findOrFail($recursoId);

        $timelineQuery = $this->recursoTimelineQuery($recursoId);

        $activityLogs = (clone $timelineQuery)
            ->paginate(25)
            ->withQueryString();

        $this->decorateActivityLogs($activityLogs);

        return view('auditoria.timeline', [
            'title' => 'Timeline de recurso',
            'subtitle' => $recurso->numero_factura . ' - ' . ($recurso->tipoCombustible->nombre ?? 'Sin combustible') . ' - ' . ($recurso->centroCosto->nombre ?? 'Sin centro de costo'),
            'backRoute' => route('auditoria.index'),
            'summary' => $this->recursoSummary($recurso, clone $timelineQuery),
            'pdfRoute' => route('auditoria.recursos.timeline.pdf', $recurso->id),
            'activityLogs' => $activityLogs,
        ]);
    }

    public function ticketTimeline(int $ticketId)
    {
        $ticket = Ticket::query()->with(['tipoCombustible', 'centroCosto', 'recurso'])->findOrFail($ticketId);

        $timelineQuery = $this->ticketTimelineQuery($ticket);

        $activityLogs = (clone $timelineQuery)
            ->paginate(25)
            ->withQueryString();

        $this->decorateActivityLogs($activityLogs);

        return view('auditoria.timeline', [
            'title' => 'Timeline de ticket',
            'subtitle' => 'Ticket #' . $ticket->id . ' - ' . ($ticket->tipoCombustible->nombre ?? 'Sin combustible') . ' - Hash: ' . ($ticket->hash ?? 'N/A'),
            'backRoute' => route('auditoria.index'),
            'summary' => $this->ticketSummary($ticket, clone $timelineQuery),
            'pdfRoute' => route('auditoria.tickets.timeline.pdf', $ticket->id),
            'activityLogs' => $activityLogs,
        ]);
    }

    public function recursoTimelinePdf(int $recursoId)
    {
        $recurso = Recurso::query()->with(['tipoCombustible', 'centroCosto'])->findOrFail($recursoId);
        $timelineQuery = $this->recursoTimelineQuery($recursoId);
        $activityLogs = $timelineQuery->get();
        $activityLogs = $this->decorateCollection($activityLogs);

        $pdf = PDF::loadView('auditoria.timeline-pdf', [
            'title' => 'Timeline de recurso',
            'subtitle' => $recurso->numero_factura . ' - ' . ($recurso->tipoCombustible->nombre ?? 'Sin combustible') . ' - ' . ($recurso->centroCosto->nombre ?? 'Sin centro de costo'),
            'summary' => $this->recursoSummary($recurso, clone $timelineQuery),
            'activityLogs' => $activityLogs,
        ]);

        return $pdf->stream('timeline-recurso-' . $recurso->id . '.pdf');
    }

    public function ticketTimelinePdf(int $ticketId)
    {
        $ticket = Ticket::query()->with(['tipoCombustible', 'centroCosto', 'recurso'])->findOrFail($ticketId);
        $timelineQuery = $this->ticketTimelineQuery($ticket);
        $activityLogs = $timelineQuery->get();
        $activityLogs = $this->decorateCollection($activityLogs);

        $pdf = PDF::loadView('auditoria.timeline-pdf', [
            'title' => 'Timeline de ticket',
            'subtitle' => 'Ticket #' . $ticket->id . ' - ' . ($ticket->tipoCombustible->nombre ?? 'Sin combustible') . ' - Hash: ' . ($ticket->hash ?? 'N/A'),
            'summary' => $this->ticketSummary($ticket, clone $timelineQuery),
            'activityLogs' => $activityLogs,
        ]);

        return $pdf->stream('timeline-ticket-' . $ticket->id . '.pdf');
    }

    private function recursoTimelineQuery(int $recursoId): Builder
    {
        return ActivityLog::query()
            ->with(['user', 'subject'])
            ->where(function (Builder $query) use ($recursoId) {
                $query
                    ->where(function (Builder $subjectQuery) use ($recursoId) {
                        $subjectQuery
                            ->where('subject_type', 'App\\Models\\Recurso')
                            ->where('subject_id', $recursoId);
                    })
                    ->orWhere('data->recurso_id', $recursoId);
            })
            ->latest();
    }

    private function ticketTimelineQuery(Ticket $ticket): Builder
    {
        return ActivityLog::query()
            ->with(['user', 'subject'])
            ->where(function (Builder $query) use ($ticket) {
                $query
                    ->where(function (Builder $subjectQuery) use ($ticket) {
                        $subjectQuery
                            ->where('subject_type', 'App\\Models\\Ticket')
                            ->where('subject_id', $ticket->id);
                    })
                    ->orWhere('data->ticket_id', $ticket->id);

                if ($ticket->hash) {
                    $query->orWhere('data->hash', $ticket->hash);
                }
            })
            ->latest();
    }

    private function buildQuery(Request $request)
    {
        $query = ActivityLog::query()
            ->with(['user', 'subject'])
            ->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->string('action'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->string('subject_type'));
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->integer('subject_id'));
        }

        if ($request->filled('recurso_id')) {
            $recursoId = $request->integer('recurso_id');

            $query->where(function (Builder $innerQuery) use ($recursoId) {
                $innerQuery
                    ->where('subject_type', 'App\\Models\\Recurso')
                    ->where('subject_id', $recursoId)
                    ->orWhere('data->recurso_id', $recursoId);
            });
        }

        if ($request->filled('ticket_hash')) {
            $ticketHash = $request->string('ticket_hash')->toString();
            $query->where('data->hash', $ticketHash);
        }

        if ($request->filled('ticket_id')) {
            $ticketId = $request->integer('ticket_id');
            $query->where(function (Builder $innerQuery) use ($ticketId) {
                $innerQuery
                    ->where(function (Builder $subjectQuery) use ($ticketId) {
                        $subjectQuery
                            ->where('subject_type', 'App\\Models\\Ticket')
                            ->where('subject_id', $ticketId);
                    })
                    ->orWhere('data->ticket_id', $ticketId);
            });
        }

        if ($request->filled('numero_factura')) {
            $numeroFactura = $request->string('numero_factura')->toString();

            $query->where(function (Builder $innerQuery) use ($numeroFactura) {
                $innerQuery
                    ->where('data->numero_factura', $numeroFactura)
                    ->orWhereHasMorph('subject', [Recurso::class], function (Builder $subjectQuery) use ($numeroFactura) {
                        $subjectQuery->where('numero_factura', $numeroFactura);
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->string('date_to'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($innerQuery) use ($search) {
                $innerQuery
                    ->where('action', 'like', "%{$search}%")
                    ->orWhere('subject_type', 'like', "%{$search}%")
                    ->orWhere('subject_id', 'like', "%{$search}%")
                    ->orWhereJsonContains('data', $search)
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    private function decorateActivityLogs(LengthAwarePaginator $activityLogs): void
    {
        $activityLogs->setCollection($this->decorateCollection($activityLogs->getCollection()));
    }

    private function decorateCollection($activityLogs)
    {
        return $activityLogs->transform(function (ActivityLog $activityLog) {
            $activityLog->action_label = $this->actionLabel($activityLog->action);
            $activityLog->action_badge_classes = $this->actionBadgeClasses($activityLog->action);
            $activityLog->subject_label = $activityLog->subject_type
                ? class_basename($activityLog->subject_type) . ' #' . ($activityLog->subject_id ?? 'N/A')
                : 'Sin sujeto';
            $activityLog->subject_link = $this->subjectLink($activityLog);
            $activityLog->formatted_data = $this->formatData($activityLog->data ?? []);

            return $activityLog;
        });
    }

    private function metrics(): array
    {
        $today = Carbon::today();
        $last7 = Carbon::now()->subDays(7);
        $last30 = Carbon::now()->subDays(30);

        return [
            'today_count' => ActivityLog::query()->whereDate('created_at', $today)->count(),
            'last_7_days_count' => ActivityLog::query()->where('created_at', '>=', $last7)->count(),
            'last_30_days_count' => ActivityLog::query()->where('created_at', '>=', $last30)->count(),
            'unique_users_count' => ActivityLog::query()->whereNotNull('user_id')->distinct('user_id')->count('user_id'),
        ];
    }

    private function recursoSummary(Recurso $recurso, Builder $timelineQuery): array
    {
        $logs = $timelineQuery->get();

        $ticketsEmitidos = $logs->where('action', 'ticket.created')->count();
        $litrosEmitidos = $logs->where('action', 'ticket.created')->sum(fn (ActivityLog $log) => (float) ($log->data['litros'] ?? 0));
        $litrosDevueltos = $logs
            ->whereIn('action', ['ticket.deleted', 'ticket.anulado', 'ticket.expired'])
            ->sum(fn (ActivityLog $log) => (float) ($log->data['litros_restantes'] ?? $log->data['litros_devueltos'] ?? 0));

        return [
            'type' => 'recurso',
            'items' => [
                ['label' => 'Estado', 'value' => $recurso->activo ? 'Activo' : 'Inactivo'],
                ['label' => 'Litros iniciales', 'value' => $this->formatNumber($recurso->litros) . ' L'],
                ['label' => 'Litros disponibles', 'value' => $this->formatNumber($recurso->litros_disponibles) . ' L'],
                ['label' => 'Monto', 'value' => '$' . $this->formatNumber($recurso->monto)],
                ['label' => 'Tickets emitidos', 'value' => (string) $ticketsEmitidos],
                ['label' => 'Litros emitidos', 'value' => $this->formatNumber($litrosEmitidos) . ' L'],
                ['label' => 'Litros devueltos', 'value' => $this->formatNumber($litrosDevueltos) . ' L'],
                ['label' => 'Orden de consumo', 'value' => '#' . $recurso->orden],
            ],
        ];
    }

    private function ticketSummary(Ticket $ticket, Builder $timelineQuery): array
    {
        $logs = $timelineQuery->get();
        $createdLog = $logs->firstWhere('action', 'ticket.created');
        $initialLitros = (float) ($createdLog->data['litros'] ?? $ticket->litros ?? 0);
        $expedientesConsumidos = $createdLog->data['recursos_consumidos'] ?? [];
        $returnedLitros = $logs
            ->whereIn('action', ['ticket.deleted', 'ticket.anulado', 'ticket.expired', 'ticket.consumed'])
            ->sum(fn (ActivityLog $log) => (float) ($log->data['litros_restantes'] ?? $log->data['litros_devueltos'] ?? 0));

        return [
            'type' => 'ticket',
            'items' => [
                ['label' => 'Estado actual', 'value' => $this->ticketStatusLabel($ticket->ticket_status_id)],
                ['label' => 'Litros emitidos', 'value' => $this->formatNumber($initialLitros) . ' L'],
                ['label' => 'Litros remanentes', 'value' => $this->formatNumber($ticket->litros) . ' L'],
                ['label' => 'Litros devueltos', 'value' => $this->formatNumber($returnedLitros) . ' L'],
                ['label' => 'Recursos usados', 'value' => (string) max(1, count($expedientesConsumidos))],
                ['label' => 'Recurso origen', 'value' => $ticket->recurso?->numero_factura ?? 'Sin recurso'],
                ['label' => 'Combustible', 'value' => $ticket->tipoCombustible->nombre ?? 'N/A'],
                ['label' => 'Centro de costo', 'value' => $ticket->centroCosto->nombre ?? 'N/A'],
                ['label' => 'Hash', 'value' => $ticket->hash ?? 'N/A'],
            ],
        ];
    }

    private function ticketStatusLabel(?int $statusId): string
    {
        return match ($statusId) {
            1 => 'Generado',
            2 => 'Finalizado',
            3 => 'Vencido',
            4 => 'Utilizado',
            5 => 'Eliminado',
            default => 'Desconocido',
        };
    }

    private function formatNumber(float|int $value, int $decimals = 2): string
    {
        return number_format((float) $value, $decimals, ',', '.');
    }

    private function formatData(array $data): array
    {
        return collect($data)
            ->mapWithKeys(function ($value, $key) {
                if ($key === 'recursos_consumidos' && is_array($value)) {
                    return [
                        'Recursos Consumidos' => collect($value)
                            ->map(fn (array $item) => [
                                'recurso_id' => $item['recurso_id'] ?? null,
                                'numero_factura' => $item['numero_factura'] ?? 'N/A',
                                'litros' => $this->formatNumber((float) ($item['litros'] ?? 0)) . ' L',
                            ])
                            ->all(),
                    ];
                }

                return [
                    str($key)->replace('_', ' ')->headline()->toString() => is_array($value)
                        ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                        : (is_bool($value) ? ($value ? 'Sí' : 'No') : ($value ?? 'N/A')),
                ];
            })
            ->all();
    }

    private function actionLabel(string $action): string
    {
        return match ($action) {
            'recurso.created' => 'Recurso creado',
            'recurso.deleted' => 'Recurso eliminado',
            'recurso.activated' => 'Recurso activado',
            'recurso.deactivated' => 'Recurso desactivado',
            'ticket.created' => 'Ticket creado',
            'ticket.anulado' => 'Ticket anulado',
            'ticket.deleted' => 'Ticket anulado',
            'ticket.updated' => 'Ticket modificado',
            'ticket.consumed' => 'Ticket consumido',
            'ticket.expired' => 'Ticket vencido',
            'ticket.marked_as_in_process' => 'Ticket marcado en proceso',
            'ticket.marked_as_pending' => 'Ticket marcado pendiente',
            'user.login' => 'Inicio de sesión',
            'user.logout' => 'Cierre de sesión',
            default => str($action)->replace('.', ' ')->headline()->toString(),
        };
    }

    private function actionBadgeClasses(string $action): string
    {
        $prefix = str($action)->before('.')->toString();

        return match ($prefix) {
            'ticket' => 'bg-blue-100 text-blue-800',
            'recurso' => 'bg-green-100 text-green-800',
            'user' => 'bg-purple-100 text-purple-800',
            'vehiculo' => 'bg-yellow-100 text-yellow-800',
            'personal' => 'bg-orange-100 text-orange-800',
            'centro_costo' => 'bg-gray-200 text-gray-800',
            default => 'bg-red-100 text-red-800',
        };
    }

    private function subjectLink(ActivityLog $activityLog): ?string
    {
        if (!$activityLog->subject_type || !$activityLog->subject_id) {
            return null;
        }

        return match ($activityLog->subject_type) {
            'App\\Models\\Ticket' => route('auditoria.tickets.timeline', $activityLog->subject_id),
            'App\\Models\\Recurso' => route('auditoria.recursos.timeline', $activityLog->subject_id),
            'App\\Models\\CentroCosto' => null,
            'App\\Models\\Personal' => route('personal.edit', $activityLog->subject_id),
            'App\\Models\\Vehiculo' => route('vehiculos.edit', $activityLog->subject_id),
            default => null,
        };
    }
}
