@extends('layouts.layout')
@can('puede_leer_personal')
    @section('content')
        <div class="container mx-auto px-4">
            <h1 class="text-2xl font-bold mb-4">Lista de personal</h1>

            <div class="flex justify-between items-center mb-4">
                @can('puede_crear_personal')
                    <a href="{{ route('personal.create') }}"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Crear nuevo personal
                    </a>
                @endcan
            </div>

            <form method="GET" class="mb-4">
                <div class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Buscar por nombre, apellido, DNI o legajo..."
                        class="border rounded py-2 px-3 w-full md:w-1/3">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Buscar
                    </button>
                    @if (request('search'))
                        <a href="{{ route('personal.index') }}"
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
                            <th class="px-4 py-2">Apellido</th>
                            <th class="px-4 py-2">DNI</th>
                            <th class="px-4 py-2">Email</th>
                            <th class="px-4 py-2">Telefono</th>
                            <th class="px-4 py-2">Area</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($personas as $personal)
                            <tr class="text-gray-700">
                                <td class="border px-4 py-2">{{ $personal->nombre }}</td>
                                <td class="border px-4 py-2">{{ $personal->apellido }}</td>
                                <td class="border px-4 py-2">{{ $personal->dni ?? $personal->legajo }}</td>
                                <td class="border px-4 py-2">{{ $personal->email }}</td>
                                <td class="border px-4 py-2">{{ $personal->telefono }}</td>
                                <td class="border px-4 py-2">{{ $personal->centroCosto->nombre ?? 'sin centro de costo' }}</td>
                                <td class="border px-4 py-2">
@can('puede_editar_personal')
                        <a href="{{ route('personal.edit', $personal->id) }}"
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                            Modificar
                                        </a>
                                    @endcan
                                    @can('puede_borrar_personal')
                                        <form action="{{ route('personal.destroy', $personal->id) }}" method="POST" class="inline">
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
                                <td colspan="7" class="text-center py-4 text-gray-500">
                                    No se encontró personal.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $personas->links() }}
            </div>
        </div>
    @endsection
@endcan
