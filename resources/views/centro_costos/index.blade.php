@extends('layouts.layout')
@can('puede_leer_centro_costo')
@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-4">Centros de Costo</h1>

    <div class="flex justify-between items-center mb-4">
        @can('puede_crear_centro_costo')
            <a href="{{ route('centro_costos.create') }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Crear Centro de Costo
            </a>
        @endcan
    </div>

    <form method="GET" class="mb-4">
        <div class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Buscar centro de costo..."
                class="border rounded py-2 px-3 w-full md:w-1/3">
            <button type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Buscar
            </button>
            @if (request('search'))
                <a href="{{ route('centro_costos.index') }}"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Limpiar
                </a>
            @endif
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="table-auto w-full">
            <thead>
                <tr>
                    <th class="px-4 py-2">Nombre</th>
                    <th class="px-4 py-2">Ubicación</th>
                    <th class="px-4 py-2">Centro Padre</th>
                    <th class="px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($centroCostos as $centroCosto)
                    <tr class="text-gray-700 text-center">
                        <td class="border px-4 py-2">{{ $centroCosto->nombre }}</td>
                        <td class="border px-4 py-2">{{ $centroCosto->ubicacion ?? 'N/A' }}</td>
                        <td class="border px-4 py-2">{{ $centroCosto->centroPadre?->nombre ?? '-' }}</td>
                        <td class="border px-4 py-2">
                            @can('puede_editar_centro_costo')
                                <a href="{{ route('centro_costos.edit', $centroCosto->id) }}"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Editar
                                </a>
                            @endcan
                            @can('puede_borrar_centro_costo')
                                <form action="{{ route('centro_costos.destroy', $centroCosto->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                                        onclick="return confirm('¿Está seguro?')">
                                        Eliminar
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-gray-500">
                            No se encontraron centros de costo.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $centroCostos->links() }}
    </div>
</div>
@endsection
@endcan
