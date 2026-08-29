<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecursoRequest;
use App\Http\Requests\UpdateRecursoRequest;
use App\Presenters\ActivityLogPresenter;
use App\Models\ActivityLog;
use App\Models\Bolsa;
use App\Models\CentroCosto;
use App\Models\Recurso;
use App\Models\TipoCombustible;
use Illuminate\Http\Request;

class RecursoController extends Controller
{
    protected $recursoSerivice;

    public function __construct(
    ) {
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Recurso::with(['tipoCombustible', 'centroCosto']);

        if ($request->filled('search')) {
            $query->where('numero_factura', 'like', "%{$request->search}%");
        }

        return view('recursos.index', [
            'recursos' => $query
                ->orderBy('centro_costo_id')
                ->orderBy('tipo_combustible_id')
                ->orderBy('orden')
                ->orderBy('id')
                ->paginate(15)
                ->withQueryString(),
            'bolsasPorEstacion' => Bolsa::with(['centroCosto', 'tipoCombustible', 'recurso'])
                ->where('cantidad_disponible', '>', 0)
                ->whereHas('recurso', fn($q) => $q->where('activo', true))
                ->orderBy('centro_costo_id')
                ->orderBy('tipo_combustible_id')
                ->orderBy('recurso_id')
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
        return view('recursos.create', [
            'tipoCombustubles' => $tipoCombustubles,
            'centroCostos' => $centroCostos
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(StoreRecursoRequest $request)
    // {

    //     $this->recursoSerivice->create(
    //         $request->litros,
    //         $request->tipo_combustible_id,
    //         $request->centro_costo_id,
    //         $request->numero_factura,
    //         $request->monto,
    //         auth()->user()->id
    //     );

    //     $this->bolsaService->addLitersBolsa(
    //         $request->tipo_combustible_id,
    //         $request->centro_costo_id,
    //         $request->litros
    //     );

    //     return redirect()->route('recursos.index')->with('success', 'Recurso creado exitosamente');
    // }

    /**
     * Display the specified resource.
     */
    public function show(Recurso $recurso)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Recurso $recurso)
    {
        if ($recurso->litros_emitidos > 0 || $recurso->litros_consumidos > 0) {
            return redirect()->route('recursos.index')
                ->with('error', 'No se puede editar un recurso que ya tiene tickets emitidos o consumidos.');
        }

        $tipoCombustubles = TipoCombustible::all();
        $centroCostos = CentroCosto::all();
        $recentActivityLogs = auth()->user()?->hasRole('admin')
            ? $this->recentRecursoActivityLogs($recurso)
            : collect();

        return view('recursos.edit', [
            'recurso' => $recurso,
            'tipoCombustubles' => $tipoCombustubles,
            'centroCostos' => $centroCostos,
            'recentActivityLogs' => $recentActivityLogs,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRecursoRequest $request, Recurso $recurso)
    {
        if ($recurso->litros_emitidos > 0 || $recurso->litros_consumidos > 0) {
            return redirect()->route('recursos.index')
                ->with('error', 'No se puede editar un recurso que ya tiene tickets emitidos o consumidos.');
        }

        $recurso->litros = $request->litros;
        $recurso->litros_inicial = $request->litros;
        $recurso->litros_disponibles = $request->litros;
        $recurso->monto = $request->monto;
        $recurso->tipo_combustible_id = $request->tipo_combustible_id;
        $recurso->centro_costo_id = $request->centro_costo_id;
        $recurso->numero_factura = $request->numero_factura;
        $recurso->emitido_por = auth()->user()->id;
        $recurso->save();

        return redirect()->route('recursos.index')->with('success', 'Recurso actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $recurso = Recurso::findOrFail($id);
        $recurso->finalizado_por = auth()->user()->id;
        $recurso->emitido_por = null;
        $recurso->activo = false;
        $recurso->save();

        return redirect()->route('recursos.index')->with('success', 'Recurso eliminado exitosamente');
    }

    private function recentRecursoActivityLogs(Recurso $recurso)
    {
        return ActivityLog::query()
            ->with('user')
            ->where(function ($query) use ($recurso) {
                $query
                    ->where(function ($subjectQuery) use ($recurso) {
                        $subjectQuery
                            ->where('subject_type', Recurso::class)
                            ->where('subject_id', $recurso->id);
                    })
                    ->orWhere('data->recurso_id', $recurso->id);
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
