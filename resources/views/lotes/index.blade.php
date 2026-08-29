@extends('layouts.layout')
@can('puede_ver_lote')
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

    @if (session('success'))
        <div class="bg-green-500 text-white px-6 py-4 border-0 rounded relative mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-500 text-white px-6 py-4 border-0 rounded relative mb-4">
            {{ session('error') }}
        </div>
    @endif

    <h1 class="text-2xl font-bold mb-4">Lotes de Combustible</h1>

    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('lotes.create') }}"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Crear Lote
        </a>
    </div>

    <form method="GET" class="mb-4">
        <div class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Buscar por nombre del lote..."
                class="border rounded py-2 px-3 w-full md:w-1/3">
            <button type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Buscar
            </button>
            @if (request('search'))
                <a href="{{ route('lotes.index') }}"
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
                    <th class="px-4 py-2">Centro de Costo</th>
                    <th class="px-4 py-2">Responsable</th>
                    <th class="px-4 py-2">Combustible</th>
                    <th class="px-4 py-2">Personal</th>
                    <th class="px-4 py-2">Estado</th>
                    <th class="px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lotes as $lote)
                    <tr class="text-center">
                        <td class="border px-4 py-2">{{ $lote->nombre ?? 'Lote #' . $lote->id }}</td>
                        <td class="border px-4 py-2">{{ $lote->centroCosto->nombre ?? 'N/A' }}</td>
                        <td class="border px-4 py-2">{{ $lote->responsable->name ?? 'N/A' }}</td>
                        <td class="border px-4 py-2">{{ $lote->tipoCombustible->nombre ?? 'N/A' }} <span class="text-xs text-gray-500">({{ $lote->tipoCombustible->categoria ?? '' }})</span></td>
                        <td class="border px-4 py-2">{{ $lote->loteEmpleados->count() }}</td>
                        <td class="border px-4 py-2">
                            <span class="inline-flex rounded px-3 py-1 text-sm font-semibold {{ $lote->activo ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">
                                {{ $lote->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="border px-4 py-2">
                            <div class="flex flex-col gap-2">
                                @can('puede_editar_lote')
                                    <a href="{{ route('lotes.edit', $lote->id) }}"
                                        class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-3 rounded text-sm">
                                        Editar
                                    </a>
                                @endcan
                                @can('puede_generar_lote')
                                    <form action="{{ route('lotes.generar', $lote->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-sm w-full">
                                            Generar
                                        </button>
                                    </form>
                                @endcan
                                @can('puede_imprimir_lote')
                                    <a href="{{ route('lotes.pdf', $lote->id) }}"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm">
                                        Descargar PDF
                                    </a>
                                    <a href="{{ route('lotes.imprimir', $lote->id) }}"
                                        class="bg-slate-500 hover:bg-slate-700 text-white font-bold py-1 px-3 rounded text-sm"
                                        target="_blank">
                                        Imprimir
                                    </a>
                                @endcan
                                @can('puede_borrar_lote')
                                    <form action="{{ route('lotes.destroy', $lote->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm w-full">
                                            Eliminar
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-gray-500">
                            No se encontraron lotes.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $lotes->links() }}
    </div>
</div>
@endsection
@endcan
