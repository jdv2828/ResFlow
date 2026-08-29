<?php

namespace App\Http\Controllers;

use App\Models\CentroCosto;
use App\Models\Personal;
use App\Http\Requests\StoreVehiculoRequest;
use App\Http\Requests\UpdateVehiculoRequest;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehiculoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Vehiculo::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('marca', 'like', "%{$search}%")
                  ->orWhere('modelo', 'like', "%{$search}%")
                  ->orWhere('patente', 'like', "%{$search}%");
            });
        }

        return view('vehiculo.index', [
            'vehiculos' => $query->orderBy('id', 'desc')->paginate(15)->withQueryString(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('vehiculo.create', [
            'personal' => Personal::all(),
            'centroCostos' => CentroCosto::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVehiculoRequest $request)
    {
        $vehiculo = new Vehiculo;
        $vehiculo->marca = $request->marca;
        $vehiculo->modelo = $request->modelo;
        $vehiculo->patente = $request->patente;
        $vehiculo->numero_identificacion = $request->numero_identificacion;
        $vehiculo->id_chofer = $request->id_chofer;
        $vehiculo->id_centro_costo = $request->id_centro_costo;

        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $nombreFoto = time() . '.' . $foto->getClientOriginalExtension();
            $foto->move(public_path('images'), $nombreFoto);
            $vehiculo->foto = $nombreFoto;
        }

        $vehiculo->save();

        return redirect()->route('vehiculos.index')->with('success', 'Vehículo creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehiculo $vehiculo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehiculo $vehiculo)
    {
        return view('vehiculo.edit', [
            'vehiculo' => $vehiculo,
            'personal' => Personal::all(),
            'centroCostos' => CentroCosto::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVehiculoRequest $request, Vehiculo $vehiculo)
    {
        // Actualizar los atributos del vehículo
        $vehiculo->marca = $request->marca;
        $vehiculo->modelo = $request->modelo;
        $vehiculo->patente = $request->patente;
        $vehiculo->numero_identificacion = $request->numero_identificacion;
        $vehiculo->id_chofer = $request->id_chofer;
        $vehiculo->id_centro_costo = $request->id_centro_costo;

        if ($request->hasFile('foto')) {
            // Eliminar la foto anterior si existe
            if ($vehiculo->foto) {
                $rutaAnterior = public_path('images/' . $vehiculo->foto);
                if (file_exists($rutaAnterior)) {
                    unlink($rutaAnterior);
                }
            }

            // Mover la nueva foto al directorio de imágenes
            $foto = $request->file('foto');
            $nombreFoto = time() . '.' . $foto->getClientOriginalExtension();
            $foto->move(public_path('images'), $nombreFoto);
            $vehiculo->foto = $nombreFoto;
        }

        // Guardar los cambios en la base de datos
        $vehiculo->save();




        // Redirigir al usuario con un mensaje de éxito
        return redirect()->route('vehiculos.index')->with('success', 'Vehículo actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehiculo $vehiculo)
    {
        // Eliminar la foto del vehículo si existe
        if ($vehiculo->foto) {
            $rutaAnterior = public_path('images/' . $vehiculo->foto);
            if (file_exists($rutaAnterior)) {
                unlink($rutaAnterior);
            }
        }

        // Eliminar el vehículo de la base de datos
        $vehiculo->delete();

        // Redirigir al usuario con un mensaje de éxito
        return redirect()->route('vehiculos.index')->with('success', 'Vehículo eliminado exitosamente');
    }
}
