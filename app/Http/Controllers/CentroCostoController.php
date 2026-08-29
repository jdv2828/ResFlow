<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCentroCostoRequest;
use App\Http\Requests\UpdateCentroCostoRequest;
use App\Models\CentroCosto;
use Illuminate\Http\Request;

class CentroCostoController extends Controller
{
    public function index(Request $request)
    {
        $query = CentroCosto::with('centroPadre');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('ubicacion', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        $centroCostos = $query->orderBy('nombre')->paginate(15)->withQueryString();

        return view('centro_costos.index', compact('centroCostos'));
    }

    public function create()
    {
        $centroPadres = CentroCosto::whereNull('centro_padre_id')->orderBy('nombre')->get();
        return view('centro_costos.create', compact('centroPadres'));
    }

    public function store(StoreCentroCostoRequest $request)
    {
        CentroCosto::create($request->validated());

        return redirect()->route('centro_costos.index')
            ->with('success', 'Centro de costo creado exitosamente');
    }

    public function edit(CentroCosto $centroCosto)
    {
        $centroPadres = CentroCosto::whereNull('centro_padre_id')
            ->where('id', '!=', $centroCosto->id)
            ->orderBy('nombre')
            ->get();
        return view('centro_costos.edit', compact('centroCosto', 'centroPadres'));
    }

    public function update(UpdateCentroCostoRequest $request, CentroCosto $centroCosto)
    {
        $centroCosto->update($request->validated());

        return redirect()->route('centro_costos.index')
            ->with('success', 'Centro de costo actualizado exitosamente');
    }

    public function destroy(CentroCosto $centroCosto)
    {
        $centroCosto->delete();

        return redirect()->route('centro_costos.index')
            ->with('success', 'Centro de costo eliminado exitosamente');
    }
}
