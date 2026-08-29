@extends('layouts.layout')
@can('puede_leer_vehiculo')
    @section('content')
        <div class="container mx-auto px-4">
            <h1 class="text-2xl font-bold mb-4">Lista de Vehículos</h1>

            <div class="flex justify-between items-center mb-4">
                @can('puede_crear_vehiculo')
                    <a href="{{ route('vehiculos.create') }}"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Crear Nuevo Vehículo
                    </a>
                @endcan
            </div>

            <form method="GET" class="mb-4">
                <div class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Buscar por marca, modelo o patente..."
                        class="border rounded py-2 px-3 w-full md:w-1/3">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Buscar
                    </button>
                    @if (request('search'))
                        <a href="{{ route('vehiculos.index') }}"
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
                            <th class="px-4 py-2">Marca</th>
                            <th class="px-4 py-2">Modelo</th>
                            <th class="px-4 py-2">Patente</th>
                            <th class="px-4 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vehiculos as $vehiculo)
                            <tr class="text-gray-700 text-center">
                                <td class="border px-4 py-2">{{ $vehiculo->marca }}</td>
                                <td class="border px-4 py-2">{{ $vehiculo->modelo }}</td>
                                <td class="border px-4 py-2">{{ $vehiculo->patente }}</td>
                                <td class="border px-4 py-2">
                                    @can('puede_editar_vehiculo')
                                        <a href="{{ route('vehiculos.edit', $vehiculo->id) }}"
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                            Modificar
                                        </a>
                                    @endcan
                                    @can('puede_borrar_vehiculo')
                                        <form action="{{ route('vehiculos.destroy', $vehiculo->id) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                                Eliminar
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-gray-500">
                                    No se encontraron vehículos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $vehiculos->links() }}
            </div>
        </div>
    @endsection
@endcan
