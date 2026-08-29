<?php

namespace App\Http\Controllers;

use App\Presenters\ActivityLogPresenter;
use App\Models\ActivityLog;
use App\Models\Bolsa;
use App\Models\CentroCosto;
use App\Models\Personal;
use App\Models\Ticket;
use App\Models\TipoCombustible;
use App\Models\TicketStatus;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['personal.centroCosto', 'tipoCombustible', 'centroCosto', 'recurso'])
            ->where('activo', 1);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('personal', function ($q2) use ($search) {
                      $q2->where('nombre', 'like', "%{$search}%")
                         ->orWhere('apellido', 'like', "%{$search}%");
                  });
            });
        }

        return view('tickets.index', [
            'tickets' => $query->orderBy('id', 'desc')->paginate(20)->withQueryString(),
            'bolsasPorEstacion' => Bolsa::with(['centroCosto', 'tipoCombustible', 'recurso'])
                ->where('cantidad_disponible', '>', 0)
                ->whereHas('recurso', fn($q) => $q->where('activo', true))
                ->get()
                ->groupBy('centro_costo_id'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tipoCombustubles = TipoCombustible::all();
        $centroCostos = CentroCosto::all();
        $empleados = Personal::all();
        return view('tickets.create', [
            'tipoCombustubles' => $tipoCombustubles,
            'centroCostos' => $centroCostos,
            'empleados' => $empleados
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        $vehiculos = Vehiculo::all();
        $tipoCombustubles = TipoCombustible::all();
        $centroCostos = CentroCosto::all();
        $empleados = Personal::all();
        $recentActivityLogs = auth()->user()?->hasRole('admin')
            ? $this->recentTicketActivityLogs($ticket)
            : collect();

        return view('tickets.edit', [
            'ticket' => $ticket,
            'vehiculos' => $vehiculos,
            'tipoCombustubles' => $tipoCombustubles,
            'centroCostos' => $centroCostos,
            'empleados' => $empleados,
            'recentActivityLogs' => $recentActivityLogs,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */

    public function showManageTicket(FormRequest $request)
    {
        // Obtener el ID del usuario actual
        $userId = auth()->user()->id;


        // Usar la Query Builder para obtener los IDs de las estaciones de servicio asociadas al usuario
        $estacionesServicioUsuario = User::find($userId)->centroCostos->pluck('id');


        if ($request->id && !empty($estacionesServicioUsuario)) {
            // Buscar tickets activos que coincidan con el ID del ticket y que estén asociados con alguna de las estaciones de servicio del usuario
            $tickets = Ticket::where('activo', 1)
                ->with(['personal', 'tipoCombustible', 'centroCosto', 'recurso'])
                ->where('id', $request->id)
                ->where('ticket_status_id', 1)
                ->whereIn('centro_costo_id', $estacionesServicioUsuario)
                ->get();
        } else {
            $tickets = collect([]);
        }

        if (auth()->user()->getRoleNames()->first() == 'admin') {
            $tickets = Ticket::where('activo', 1)
                ->with(['personal', 'tipoCombustible', 'centroCosto', 'recurso'])
                ->where('ticket_status_id', 1)
                ->where('id', $request->id)
                ->get();
        }

        $search = $request->input('search');

        $ticketsNoActivos = (auth()->user()->getRoleNames()->first() == 'admin') ?
            Ticket::where('activo', 0)
                ->with(['personal', 'tipoCombustible', 'centroCosto', 'recurso'])
                ->where('ticket_status_id', 4)
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('id', '=', $search);
                        $terms = preg_split('/\s+/', $search);
                        $q->orWhereHas('personal', function ($q2) use ($terms) {
                            foreach ($terms as $term) {
                                $q2->where(function ($q3) use ($term) {
                                    $q3->where('nombre', 'like', "%{$term}%")
                                       ->orWhere('apellido', 'like', "%{$term}%");
                                });
                            }
                        });
                    });
                })
                ->orderBy('updated_at', 'desc')
                ->paginate(15)
                ->withQueryString() :
            new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);


        return view('tickets.gestion', [
            'tickets' => $tickets,
            'ticketsNoActivos' => $ticketsNoActivos,
        ]);
    }


    public function manageTicketConsumption($id, FormRequest $request)
    {

        if (!isset($request->litros_consumidos) || ($request->litros_consumidos) == 0 || ($request->litros_consumidos) < 0) return redirect()->back()->withErrors(['error' => 'Por favor coloque una cantidad mayor a cero']);

        $ticket = Ticket::findOrFail($id);

        $resultado = $ticket->litros - $request->litros_consumidos;

        if ($resultado < 0)  return redirect()->back()->withErrors(['error' => 'No se puede comsumir mas de lo disponible']);

        if ($resultado == 0) {

            $ticket->activo = false;
            $ticket->ticket_status_id = 4;
            $ticket->finalizado_por = auth()->user()->id;
            $ticket->litros_consumidos = $request->litros_consumidos;
            $ticket->litros = $resultado;
            $ticket->save();
            return redirect()->back()->with('success', 'ticker consumido');
        }

        $ticket->ticket_status_id = 4;
        $ticket->activo = false;
        $ticket->litros_consumidos = $request->litros_consumidos;
        $ticket->finalizado_por = auth()->user()->id;
        $ticket->litros = $resultado;
        $ticket->save();
        return redirect()->back()->with('success', 'ticker consumido');
    }


    private function recentTicketActivityLogs(Ticket $ticket)
    {
        return ActivityLog::query()
            ->with('user')
            ->where(function ($query) use ($ticket) {
                $query
                    ->where(function ($subjectQuery) use ($ticket) {
                        $subjectQuery
                            ->where('subject_type', Ticket::class)
                            ->where('subject_id', $ticket->id);
                    })
                    ->orWhere('data->ticket_id', $ticket->id);

                if ($ticket->hash) {
                    $query->orWhere('data->hash', $ticket->hash);
                }
            })
            ->latest()
            ->limit(8)
            ->get()
            ->map(function (ActivityLog $activityLog) {
                $activityLog->action_label = ActivityLogPresenter::actionLabel($activityLog->action);
                $activityLog->action_badge_classes = ActivityLogPresenter::actionBadgeClasses($activityLog->action);
                $activityLog->formatted_data = ActivityLogPresenter::formatData($activityLog->data ?? []);

                return $activityLog;
            });
    }

}
