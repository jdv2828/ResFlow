@extends('layouts.layout')
@can('puede_leer_recurso')

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

            <h1 class="text-2xl font-bold mb-4">Recursos</h1>

            <div class="flex justify-between items-center mb-4">
                @can('puede_crear_recurso')
                    <a href="{{ route('recursos.create') }}"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Crear Recurso
                    </a>
                @endcan
            </div>

            {{-- Bolsas de Combustible --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                @forelse ($bolsasPorEstacion as $estacionId => $bolsasGrupo)
                    @php $estacion = $bolsasGrupo->first()->centroCosto ?? null; @endphp
                    @php $totalDisponible = $bolsasGrupo->sum('cantidad_disponible'); @endphp
                    @php $totalEmitidos = $bolsasGrupo->sum(fn($b) => $b->recurso->litros_emitidos ?? 0); @endphp
                    @php $totalConsumidos = $bolsasGrupo->sum(fn($b) => $b->recurso->litros_consumidos ?? 0); @endphp
                    <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-brand-blue-accent">
                        <h3 class="font-bold text-gray-900 mb-1">{{ $estacion->nombre ?? 'N/A' }}</h3>
                        <div class="flex justify-between text-xs text-gray-500 mb-2">
                            <span>{{ $bolsasGrupo->count() }} combustible(s)</span>
                            <span class="font-semibold text-brand-blue-accent">{{ $formatNumber($totalDisponible) }} L total</span>
                        </div>
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-400">
                                    <th class="text-left py-1 font-medium">Combustible</th>
                                    <th class="text-left py-1 font-medium">Recurso</th>
                                    <th class="text-right py-1 font-medium">Disponible</th>
                                    <th class="text-right py-1 font-medium">Asignado</th>
                                    <th class="text-right py-1 font-medium">Consumido</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bolsasGrupo as $bolsa)
                                    <tr class="border-b border-gray-50">
                                        <td class="py-1 text-gray-700">{{ $bolsa->tipoCombustible->nombre ?? 'N/A' }} <span class="text-xs text-gray-500">({{ $bolsa->tipoCombustible->categoria ?? '' }})</span></td>
                                        <td class="py-1 text-gray-500 text-xs">{{ $bolsa->recurso->numero_factura ?? 'N/A' }}</td>
                                        <td class="py-1 text-right font-medium">{{ $formatNumber($bolsa->cantidad_disponible ?? 0) }} L</td>
                                        <td class="py-1 text-right text-gray-500">{{ $formatNumber($bolsa->recurso->litros_emitidos ?? 0) }} L</td>
                                        <td class="py-1 text-right text-gray-500">{{ $formatNumber($bolsa->recurso->litros_consumidos ?? 0) }} L</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="font-semibold border-t border-gray-200 text-gray-700">
                                    <td class="pt-1" colspan="2">Total</td>
                                    <td class="pt-1 text-right">{{ $formatNumber($totalDisponible) }} L</td>
                                    <td class="pt-1 text-right">{{ $formatNumber($totalEmitidos) }} L</td>
                                    <td class="pt-1 text-right">{{ $formatNumber($totalConsumidos) }} L</td>
                                </tr>
                            </tfoot>
                        </table>
                        <p class="text-[10px] text-gray-400 mt-2 border-t border-gray-100 pt-2">
                            Activos: {{ $formatNumber($totalEmitidos - $totalConsumidos) }} L
                        </p>
                    </div>
                @empty
                    <p class="text-gray-500 col-span-full text-center py-4">No hay bolsas de combustible activas.</p>
                @endforelse
            </div>

            {{-- Search --}}
            <form method="GET" class="mb-4">
                <div class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Buscar por número de factura..."
                        class="border rounded py-2 px-3 w-full md:w-1/3">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Buscar
                    </button>
                    @if (request('search'))
                        <a href="{{ route('recursos.index') }}"
                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="table-auto w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">Nro de Factura</th>
                            <th class="px-4 py-2">Estado</th>
                            <th class="px-4 py-2">Litros</th>
                            <th class="px-4 py-2">Disponibles</th>
                            <th class="px-4 py-2">Emit/Cons</th>
                            <th class="px-4 py-2">Monto</th>
                            <th class="px-4 py-2">Centro de Costo</th>
                            <th class="px-4 py-2">Tipo de combustible</th>
                            <th class="px-4 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recursos as $recurso)
                            <tr class="text-gray-700 text-center">
                                <td class="border px-4 py-2">{{ $recurso->numero_factura }}</td>
                                <td class="border px-4 py-2">
                                    <span class="inline-flex rounded px-3 py-1 text-sm font-semibold {{ $recurso->activo ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">
                                        {{ $recurso->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                    @if (!$recurso->activo)
                                        <p class="text-[10px] text-gray-400 mt-1">En espera: se activa al agotar el recurso anterior de este combustible.</p>
                                    @endif
                                </td>
                                <td class="border px-4 py-2">{{ $formatNumber($recurso->litros) }} L</td>
                                <td class="border px-4 py-2">{{ $formatNumber($recurso->litros_disponibles ?? 0) }} / {{ $formatNumber($recurso->litros) }} L</td>
                                <td class="border px-4 py-2">E: {{ $formatNumber($recurso->litros_emitidos ?? 0) }} / C: {{ $formatNumber($recurso->litros_consumidos ?? 0) }}</td>
                                <td class="border px-4 py-2">${{ $formatNumber($recurso->monto) }}</td>
                                <td class="border px-4 py-2">{{ $recurso->centroCosto->nombre ?? 'N/A' }}</td>
                                <td class="border px-4 py-2">{{ $recurso->tipoCombustible->nombre }} <span class="text-xs text-gray-500">({{ $recurso->tipoCombustible->categoria ?? '' }})</span></td>
                                <td class="border px-4 py-2">
                                    @can('puede_editar_recurso')
                                        <a href="{{ route('recursos.edit', $recurso->id) }}"
                                            class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded inline-block mb-2">
                                            Editar
                                        </a>
                                    @endcan
                                    @if (auth()->user()->hasRole('admin'))
                                        <a href="{{ route('auditoria.recursos.timeline', $recurso->id) }}"
                                            class="bg-slate-600 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded inline-block mb-2">
                                            Timeline
                                        </a>
                                    @endif
                                    @can('puede_borrar_recurso')
                                        <form action="{{ route('recursos.destroy', $recurso->id) }}" method="POST" class="inline">
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
                                <td colspan="9" class="text-center py-4 text-gray-500">
                                    No se encontraron recursos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $recursos->links() }}
            </div>
        </div>
    @endsection
@endcan
