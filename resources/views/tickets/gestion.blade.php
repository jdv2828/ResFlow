@extends('layouts.layout')
@can('puede_ver_gestion_ticket')
@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-4">Lista de tickets</h1>

    <div class="flex justify-between items-center mb-4">

        <form action="{{ route('tickets.showManageTicket') }}" method="GET" class="flex items-center space-x-2">
            <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded" type="submit">
                Buscar
            </button>
            <input class="p-2 border rounded" type="text" name="id" placeholder="Ingrese número de ticket" value="{{ request('id') }}">
        </form>
        @can('puede_ver_informes')
            <a href="{{ route('reportes.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Informes
            </a>
        @endcan

    </div>

    <div class="overflow-x-auto">
        <table class="table-auto w-full">
            <thead>
                <tr>
                    <th class="px-4 py-2">Nro ticket</th>
                    <th class="px-4 py-2">Personal</th>
                    <th class="px-4 py-2">Litros Actuales</th>
                    <th class="px-4 py-2">Litros consumidos</th>
                    <th class="px-4 py-2">Litros a Consumir</th>
                     <th class="px-4 py-2">Tipo Combustible</th>
                     <th class="px-4 py-2">Centro de Costo</th>
                     <th class="px-4 py-2">Factura</th>
                     <th class="px-4 py-2">Fecha de Vencimiento</th>
                     <th class="px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @if ($tickets->count() > 0)
                @foreach ($tickets as $ticket)
                <tr class="text-white-700 text-center" data-ticket-row="{{ $ticket->id }}">

                        <td class="border px-4 py-2">{{ $ticket->id }}</td>
                        <td class="border px-4 py-2">
                            {{ $ticket->personal->nombre . ' ' . $ticket->personal->apellido }}
                        </td>
                         <td class="border px-4 py-2">{{ $formatNumber($ticket->litros) }}</td>
                        <td class="border px-4 py-2">{{ $formatNumber($ticket->litros_consumidos ?? 0) }}</td>
                         <td class="border px-4 py-2">
                             <input type="number" step="0.01" name="litros_consumidos" value="0" form="consume-form-{{ $ticket->id }}" class="border rounded px-1 py-0.5 w-24">
                         </td>
                         <td class="border px-4 py-2">{{ $ticket->tipoCombustible->nombre }} <span class="text-xs text-gray-500">({{ $ticket->tipoCombustible->categoria ?? '' }})</span></td>
                         <td class="border px-4 py-2">{{ $ticket->centroCosto->nombre }}</td>
                        <td class="border px-4 py-2">{{ $ticket->recurso?->numero_factura ?? 'Sin factura' }}</td>
                        <td class="border px-4 py-2">{{ $ticket->fecha_caducidad }}</td>
                        <td class="border px-4 py-2">
                            @if (auth()->user()->hasRole('admin'))
                                <a href="{{ route('auditoria.tickets.timeline', $ticket->id) }}"
                                    class="bg-slate-600 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded inline-block mb-2">
                                    Timeline
                                </a>
                            @endif
                            <form action="{{ route('tickets.manageTicketConsumption', $ticket->id) }}" method="POST" class="inline" id="consume-form-{{ $ticket->id }}">
                                @csrf
                                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Consumir
                                </button>
                            </form>
                        </td>

                    </tr>
                @endforeach
                @endif
            </tbody>
        </table>

        <hr class="bg-black p-[1px] my-4 mb-4">
        <h1 class="text-2xl font-bold mb-4">Tickets consumidos</h1>

        <div class="flex mb-4">
            <form action="{{ route('tickets.showManageTicket') }}" method="GET" class="flex w-full">
                <input type="text" name="search" placeholder="Buscar por Nro ticket o Nombre de personal" value="{{ request('search') }}"
                       class="p-2 border rounded w-full">
                <button type="submit" class="ml-2 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Buscar
                </button>
            </form>
        </div>

        <table class="table-auto w-full">
            <thead>
                <tr>
                    <th class="px-4 py-2">Nro de ticket</th>
                    <th class="px-4 py-2">Personal</th>
                    <th class="px-4 py-2">Litros Actuales</th>
                    <th class="px-4 py-2">Litros consumidos</th>
                     <th class="px-4 py-2">Tipo Combustible</th>
                     <th class="px-4 py-2">Centro de Costo</th>
                     <th class="px-4 py-2">Factura</th>
                     <th class="px-4 py-2">Último consumo</th>
                     <th class="px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @if ($ticketsNoActivos->count() > 0)
                @foreach ($ticketsNoActivos as $ticket)
                <tr class="text-white-700 text-center">

                        <td class="border px-4 py-2">{{ $ticket->id }}</td>
                        <td class="border px-4 py-2">
                            @if($ticket->personal)
                            {{ $ticket->personal->nombre. ' '. $ticket->personal->apellido }}
                            @else
                            sin personal asignado
                            @endif
                            </td>
                        <td class="border px-4 py-2">{{ $formatNumber($ticket->litros) }}</td>
                        <td class="border px-4 py-2">{{ $formatNumber($ticket->litros_consumidos ?? 0) }}</td>
                        <td class="border px-4 py-2">{{ $ticket->tipoCombustible->nombre }} <span class="text-xs text-gray-500">({{ $ticket->tipoCombustible->categoria ?? '' }})</span></td>
                        <td class="border px-4 py-2">{{ $ticket->centroCosto->nombre }}</td>
                        <td class="border px-4 py-2">{{ $ticket->recurso?->numero_factura ?? 'Sin factura' }}</td>
                        <td class="border px-4 py-2">{{ $ticket->updated_at }}</td>
                        <td class="border px-4 py-2">
                            @if (auth()->user()->hasRole('admin'))
                                <a href="{{ route('auditoria.tickets.timeline', $ticket->id) }}"
                                    class="bg-slate-600 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded inline-block">
                                    Timeline
                                </a>
                            @endif
                        </td>

                    </tr>
                @endforeach
                @endif
            </tbody>
        </table>
        @if ($ticketsNoActivos->hasPages())
            <div class="flex justify-between items-center mt-4">
                <div>
                    <span class="text-sm text-gray-700">
                        Mostrando {{ $ticketsNoActivos->firstItem() }} a {{ $ticketsNoActivos->lastItem() }} de {{ $ticketsNoActivos->total() }} tickets consumidos
                    </span>
                </div>
                <nav class="flex items-center gap-1">
                    @if ($ticketsNoActivos->onFirstPage())
                        <span class="px-3 py-1 text-sm text-gray-400 bg-gray-100 border border-gray-300 rounded cursor-not-allowed">Anterior</span>
                    @else
                        <a href="{{ $ticketsNoActivos->previousPageUrl() }}" class="px-3 py-1 text-sm text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">Anterior</a>
                    @endif

                    @php
                        $start = max(1, $ticketsNoActivos->currentPage() - 2);
                        $end = min($ticketsNoActivos->lastPage(), $ticketsNoActivos->currentPage() + 2);
                    @endphp

                    @if ($start > 1)
                        <a href="{{ $ticketsNoActivos->url(1) }}" class="px-3 py-1 text-sm text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">1</a>
                        @if ($start > 2)
                            <span class="px-3 py-1 text-sm text-gray-400">...</span>
                        @endif
                    @endif

                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $ticketsNoActivos->currentPage())
                            <span class="px-3 py-1 text-sm text-white bg-blue-500 border border-blue-500 rounded">{{ $page }}</span>
                        @else
                            <a href="{{ $ticketsNoActivos->url($page) }}" class="px-3 py-1 text-sm text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">{{ $page }}</a>
                        @endif
                    @endfor

                    @if ($end < $ticketsNoActivos->lastPage())
                        @if ($end < $ticketsNoActivos->lastPage() - 1)
                            <span class="px-3 py-1 text-sm text-gray-400">...</span>
                        @endif
                        <a href="{{ $ticketsNoActivos->url($ticketsNoActivos->lastPage()) }}" class="px-3 py-1 text-sm text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">{{ $ticketsNoActivos->lastPage() }}</a>
                    @endif

                    @if ($ticketsNoActivos->hasMorePages())
                        <a href="{{ $ticketsNoActivos->nextPageUrl() }}" class="px-3 py-1 text-sm text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">Siguiente</a>
                    @else
                        <span class="px-3 py-1 text-sm text-gray-400 bg-gray-100 border border-gray-300 rounded cursor-not-allowed">Siguiente</span>
                    @endif
                </nav>
            </div>
        @endif
    </div>
</div>
@endsection

@endcan
