<?php

namespace App\Http\Controllers;

use App\Models\CentroCosto;
use App\Models\Personal;
use App\Models\Lote;
use App\Models\TipoCombustible;
use App\Models\User;
use Illuminate\Http\Request;
use PDF;

class LoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Lote::with(['centroCosto', 'responsable', 'tipoCombustible']);

        if ($request->filled('search')) {
            $query->where('nombre', 'like', "%{$request->search}%");
        }

        return view('lotes.index', [
            'lotes' => $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString(),
        ]);
    }

    public function create()
    {
        $centroCostos = CentroCosto::with('centroPadre')->whereNull('centro_padre_id')->orderBy('nombre')->get();
        $responsables = User::orderBy('name')->get();
        $combustibles = TipoCombustible::orderBy('nombre')->get();

        return view('lotes.create', compact('centroCostos', 'responsables', 'combustibles'));
    }

    public function edit(int $id)
    {
        $lote = Lote::with('loteEmpleados.personal.centroCosto.centroPadre')->findOrFail($id);
        $centroCostos = CentroCosto::with('centroPadre')->whereNull('centro_padre_id')->orderBy('nombre')->get();
        $responsables = User::orderBy('name')->get();
        $combustibles = TipoCombustible::orderBy('nombre')->get();

        $empleadosLote = $lote->loteEmpleados->map(function ($detalle) {
            $empleado = $detalle->personal;
            if ($empleado) {
                return [
                    'id' => $detalle->id,
                    'personal_id' => $empleado->id,
                    'nombre_completo' => $detalle->nombre_completo ?? $empleado->nombre_completo,
                    'dni' => $detalle->dni ?? $empleado->dni,
                    'patente' => $detalle->patente ?? '',
                    'secretaria' => $empleado->centroCosto->nombre ?? '-',
                    'direccion' => $empleado->centroCosto?->centroPadre?->nombre ?? '-',
                    'litros' => $detalle->litros,
                    'cantidad_vales' => $detalle->cantidad_vales,
                    'fecha_caducidad' => $detalle->fecha_caducidad ? $detalle->fecha_caducidad->format('Y-m-d') : '',
                ];
            }
            return [
                'id' => $detalle->id,
                'personal_id' => null,
                'nombre_completo' => $detalle->nombre_completo ?? '',
                'dni' => $detalle->dni ?? '',
                'patente' => $detalle->patente ?? '',
                'secretaria' => '',
                'direccion' => '',
                'litros' => $detalle->litros,
                'cantidad_vales' => $detalle->cantidad_vales,
                'fecha_caducidad' => $detalle->fecha_caducidad ? $detalle->fecha_caducidad->format('Y-m-d') : '',
            ];
        });

        return view('lotes.edit', compact('lote', 'centroCostos', 'responsables', 'combustibles', 'empleadosLote'));
    }

    public function getEmpleadosByCentroCosto(int $centroCostoId)
    {
        $empleados = Personal::with(['centroCosto.centroPadre', 'vehiculosAsChofer'])
            ->whereHas('centroCosto', function ($query) use ($centroCostoId) {
                $query->where('id', $centroCostoId)
                    ->orWhere(function ($q) use ($centroCostoId) {
                        $ids = $this->getSubCentroCostoIds($centroCostoId);
                        $q->whereIn('id', $ids);
                    });
            })
            ->get()
            ->map(function ($empleado) {
                return [
                    'id' => $empleado->id,
                    'nombre_completo' => $empleado->nombre_completo,
                    'dni' => $empleado->dni,
                    'patente' => $empleado->vehiculosAsChofer->first()->patente ?? '',
                    'secretaria' => $empleado->centroCosto->nombre ?? '-',
                    'direccion' => $empleado->centroCosto?->centroPadre?->nombre ?? '-',
                ];
            });

        return response()->json($empleados);
    }

    private function getSubCentroCostoIds(int $centroCostoId): array
    {
        $ids = [$centroCostoId];
        $children = CentroCosto::where('centro_padre_id', $centroCostoId)->pluck('id')->toArray();

        foreach ($children as $childId) {
            $ids = array_merge($ids, $this->getSubCentroCostoIds($childId));
        }

        return $ids;
    }

    public function print(int $id)
    {
        $lote = Lote::with('loteEmpleados.personal')->findOrFail($id);

        return view('lotes.print', compact('lote'));
    }

    public function downloadPdf(int $id)
    {
        $lote = Lote::with('loteEmpleados.personal')->findOrFail($id);

        $pdf = PDF::loadView('lotes.print', compact('lote'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('lote-' . ($lote->nombre ?: $lote->id) . '.pdf');
    }
}
