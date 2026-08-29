@extends('layouts.layout')
@can('puede_editar_centro_costo')
@section('content')
<div class="container mx-auto px-4">
    @if ($errors->any())
        <div class="bg-red-500 text-white px-6 py-4 border-0 rounded relative mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h1 class="text-2xl font-bold mb-4">Editar Centro de Costo</h1>

    <form action="{{ route('centro_costos.update', $centroCosto->id) }}" method="POST"
        class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="nombre">Nombre:</label>
            <input
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                type="text" id="nombre" name="nombre" value="{{ old('nombre', $centroCosto->nombre) }}" required>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="ubicacion">Ubicación:</label>
            <input
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                type="text" id="ubicacion" name="ubicacion" value="{{ old('ubicacion', $centroCosto->ubicacion) }}">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="descripcion">Descripción:</label>
            <textarea
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $centroCosto->descripcion) }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="centro_padre_id">Centro Padre:</label>
            <select
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                id="centro_padre_id" name="centro_padre_id">
                <option value="">Sin centro padre</option>
                @foreach ($centroPadres as $padre)
                    <option value="{{ $padre->id }}" {{ old('centro_padre_id', $centroCosto->centro_padre_id) == $padre->id ? 'selected' : '' }}>
                        {{ $padre->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Actualizar Centro de Costo
            </button>
            <a href="{{ route('centro_costos.index') }}"
                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
@endcan
