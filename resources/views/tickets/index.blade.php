@extends('layouts.layout')

@can('puede_leer_ticket')
    @section('content')
        <div class="container mx-auto px-4">

            @if ($errors->any())
                <div class="bg-red-500 text-white px-6 py-4 border-0 rounded relative mb-4">
                    <span class="inline-block align-middle mr-8">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </span>
                    <span class="inline-block align-middle mr-8">
                        <b class="capitalize">Error!</b>
                    </span>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <h1 class="text-2xl font-bold mb-4">Lista de vales</h1>

            <div class="flex justify-between items-center mb-4">
                @can('puede_crear_ticket')
                    <a href="{{ route('tickets.create') }}"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Crear Vale
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
                                    <th class="text-left py-1 font-medium">Factura</th>
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

            <form method="GET" class="mb-4">
                <div class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Buscar por Nro ticket o nombre de personal..."
                        class="border rounded py-2 px-3 w-full md:w-1/3">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Buscar
                    </button>
                    @if (request('search'))
                        <a href="{{ route('tickets.index') }}"
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
                            <th class="px-4 py-2">Id</th>
                            <th class="px-4 py-2">Personal</th>
                            <th class="px-4 py-2">Centro de Costo</th>
                            <th class="px-4 py-2">Litros</th>
                            <th class="px-4 py-2">Tipo Combustible</th>
                            <th class="px-4 py-2">Centro de Costo</th>
                            <th class="px-4 py-2">Factura</th>
                            <th class="px-4 py-2">Fecha de vencimiento</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($tickets->count() > 0)
                            @foreach ($tickets as $ticket)
                                <tr class="text-white-700 text-center">
                                    <td class="border px-4 py-2 ">{{ $ticket->id }}</td>
                                    <td class="border px-4 py-2">
                                        @if ($ticket->personal)
                                            {{ $ticket->personal->nombre . ' ' . $ticket->personal->apellido }}
                                        @else
                                            Sin personal asignado
                                        @endif
                                    </td>
                                    <td class="border px-4 py-2 ">{{ $ticket->personal->centroCosto->nombre ?? 'N/A' }}</td>
                                    <td class="border px-4 py-2 ">{{ $formatNumber($ticket->litros) }} </td>
                                    <td class="border px-4 py-2 ">{{ $ticket->tipoCombustible->nombre }} <span class="text-xs text-gray-500">({{ $ticket->tipoCombustible->categoria ?? '' }})</span></td>
                                    <td class="border px-4 py-2 ">{{ $ticket->centroCosto->nombre }}</td>
                                    <td class="border px-4 py-2 ">{{ $ticket->recurso?->numero_factura ?? 'Sin factura' }}</td>
                                    <td class="border px-4 py-2 ">{{ $ticket->fecha_caducidad }}</td>

                                    <td class="border px-4 py-2 ">
                                        @if (auth()->user()->hasRole('admin'))
                                            <a href="{{ route('auditoria.tickets.timeline', $ticket->id) }}"
                                                class="bg-slate-600 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded inline-block mb-2">
                                                Timeline
                                            </a>
                                        @endif
                                        @can('puede_editar_ticket')
                                            <a href="{{ route('tickets.edit', $ticket->id) }}"
                                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                                Modificar
                                            </a>
                                        @endcan
                                        @can('puede_borrar_ticket')
                                            <form action="{{ route('tickets.destroy', $ticket->id) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endcan

                                        @can('puede_imprimir_ticket')
                                            <a href="{{ route('pdf.generate', $ticket->id) }}"
                                                class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded"
                                                target="_blank">
                                                Imprimir
                                            </a>
                                        @endcan

                                    </td>
                                </tr>
                            @endforeach
                        @endif

                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $tickets->links() }}
            </div>
        </div>
    @endsection
@endcan
