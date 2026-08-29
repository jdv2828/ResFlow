<?php

namespace App\Http\Controllers;

use App\Models\CentroCosto;
use App\Http\Requests\StorePersonalRequest;
use App\Http\Requests\UpdatePersonalRequest;
use App\Models\Personal;
use Illuminate\Http\Request;

class PersonalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Personal::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%")
                  ->orWhere('legajo', 'like', "%{$search}%");
            });
        }

        $personas = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('personal.index', [
            'personas' => $personas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('personal.create',['centroCostos'=>CentroCosto::all()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePersonalRequest $request)
    {
        $personal = new Personal();
        $personal->nombre = $request->nombre;
        $personal->apellido = $request->apellido;
        $personal->legajo = $request->legajo;
        $personal->email = $request->email;
        $personal->telefono = $request->telefono;
        $personal->centro_costo_id = $request->centro_costo_id;

        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $nombreFoto = time() . '.' . $foto->getClientOriginalExtension();
            $foto->move(public_path('images'), $nombreFoto);
            $personal->foto = $nombreFoto;
        }

        $personal->save();



        return redirect()->route('personal.index')->with('success', 'Personal creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Personal $personal)
    {
        $centroCostos=CentroCosto::all();
        return view('personal.edit',[
            'personal'=>$personal,
            'centroCostos'=>$centroCostos,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePersonalRequest $request, Personal $personal)
    {


        $personal->apellido = $request->apellido;
        $personal->nombre = $request->nombre;
        $personal->legajo = $request->legajo;
        $personal->email = $request->email;
        $personal->telefono = $request->telefono;
        $personal->centro_costo_id = $request->centro_costo_id;

        if ($request->hasFile('foto')) {

            if ($personal->foto) {
                $rutaAnterior = public_path('images/' . $personal->foto);
                if (file_exists($rutaAnterior)) {
                    unlink($rutaAnterior);
                }
            }

            // Mover la nueva foto al directorio de imágenes
            $foto = $request->file('foto');
            $nombreFoto = time() . '.' . $foto->getClientOriginalExtension();
            $foto->move(public_path('images'), $nombreFoto);
            $personal->foto = $nombreFoto;
        }


        $personal->save();

        return redirect()->route('personal.index')->with('success', 'Personal actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Personal $personal)
    {
        if ($personal->foto) {
            $rutaAnterior = public_path('images/' . $personal->foto);
            if (file_exists($rutaAnterior)) {
                unlink($rutaAnterior);
            }
        }

        $personal->delete();


        return redirect()->route('personal.index')->with('success', 'Personal eliminado exitosamente');
    }
}
