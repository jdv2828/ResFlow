@extends('layouts.layout')
@can('puede_editar_ticket')
    @section('content')
        {{-- {{ ($ticket->litros)}}
        {{$tieneDecimales($ticket->litros)?'si' : 'no'}} --}}
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

            <form action="{{ route('tickets.update', $ticket->id) }}" method="POST"
                class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="litros">Litros:</label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        type="number" id="litros" name="litros" step="0.00001"
                        value="{{ $tieneDecimales($ticket->litros) ? $ticket->litros : intval($ticket->litros) }}" required>
                </div>

                <div x-data="{ empleadoInput: '' }" class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="personal_id">Personal / Vehículo:</label>

                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        type="text" id="IdEmpleadosInput" list="IdEmpleadoList"
                        placeholder="Escribe el nombre del personal o la patente del vehiculo asignado" autocomplete="off"
                        value="{{ $ticket->personal->nombre }} {{ $ticket->personal->apellido }} - DNI {{ $ticket->personal->legajo }} | VEHICULO: {{ $ticket->personal->vehiculosAsChofer->first()->modelo ?? 'Sin vehículo asignado' }} - PATENTE: {{ $ticket->personal->vehiculosAsChofer->first()->patente ?? 'N/A' }}"
                        @input="empleadoInput = $event.target.value; personal_id.value = [...$el.list.options].find(o => o.value === $event.target.value)?.dataset?.value || ''">

                    <datalist id="IdEmpleadoList">
                        @foreach ($empleados as $empleado)
                            @php
                                $vehiculos = $empleado->vehiculosAsChofer;
                                $baseLabel = $empleado->nombre . ' ' . $empleado->apellido . ' - DNI ' . $empleado->legajo;
                            @endphp

                            @if ($vehiculos->isEmpty())
                                <option data-value="{{ $empleado->id }}"
                                    value="{{ $baseLabel }} | VEHICULO: Sin vehículo asignado - PATENTE: N/A">
                                    {{ $baseLabel }} | SIN VEHICULO ASIGNADO
                                </option>
                            @else
                                @foreach ($vehiculos as $vehiculo)
                                    <option data-value="{{ $empleado->id }}"
                                        value="{{ $baseLabel }} | VEHICULO: {{ $vehiculo->modelo ?? 'N/A' }} - PATENTE: {{ $vehiculo->patente ?? 'N/A' }}">
                                        {{ $baseLabel }} | PATENTE VEHICULO ASIGNADO: {{ $vehiculo->patente ?? 'N/A' }}
                                    </option>
                                @endforeach
                            @endif
                        @endforeach
                    </datalist>

                    <input type="hidden" id="personal_id" name="personal_id" value="{{ $ticket->personal_id }}">
                </div>

                <div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="centro_costo_id">Centro de
                        Costo:</label>
                    <select
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="centro_costo_id" name="centro_costo_id">
                        <option value="">Seleccione un centro de costo</option>
                        @foreach ($centroCostos as $centro)
                            <option value="{{ $centro->id }}" {{ $centro->id == $ticket->centro_costo_id ? 'selected' : '' }}>{{ $centro->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="tipo_combustible_id">Tipo de
                        Combustible:</label>
                    <select
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="tipo_combustible_id" name="tipo_combustible_id" required>
                        <option value="">Seleccione un tipo de combustible</option>
                        @foreach ($tipoCombustubles as $tipo)
                            <option value="{{ $tipo->id }}" data-centro="{{ $tipo->centro_costo_id }}" {{ $ticket->tipo_combustible_id == $tipo->id ? 'selected' : '' }}>{{ $tipo->nombre }} ({{ $tipo->categoria }})</option>
                        @endforeach
                    </select>
                </div>
                </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var centroSelect = document.getElementById('centro_costo_id');
    var combustibleSelect = document.getElementById('tipo_combustible_id');
    if (centroSelect && combustibleSelect) {
        var currentCombustibleValue = combustibleSelect.value;
        centroSelect.addEventListener('change', function() {
            var centroId = this.value;
            combustibleSelect.querySelectorAll('option[data-centro]').forEach(function(opt) {
                opt.style.display = (!centroId || opt.dataset.centro === centroId) ? '' : 'none';
            });
            combustibleSelect.value = '';
        });
        if (centroSelect.value) {
            var centroId = centroSelect.value;
            combustibleSelect.querySelectorAll('option[data-centro]').forEach(function(opt) {
                opt.style.display = (!centroId || opt.dataset.centro === centroId) ? '' : 'none';
            });
        }
        combustibleSelect.value = currentCombustibleValue;
    }
});
</script>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="fecha_caducidad">Fecha de caducidad:</label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        type="datetime-local" id="fecha_caducidad" name="fecha_caducidad"
                        value="{{ $ticket->fecha_caducidad }}">
                </div>

                <div class="flex items-center justify-between">
                    <button
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        type="submit">
                        Actualizar ticket
                    </button>
                </div>
            </form>

            @if (auth()->user()->hasRole('admin'))
                <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-xl font-bold">Historial reciente del ticket</h2>
                            <p class="text-gray-600 text-sm mt-1">Últimos movimientos relacionados a este ticket.</p>
                        </div>
                        <a href="{{ route('auditoria.tickets.timeline', $ticket->id) }}"
                            class="bg-slate-600 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded">
                            Ver timeline completo
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($recentActivityLogs as $activityLog)
                            <div class="border rounded p-4 bg-gray-50">
                                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                                    <div>
                                        <div class="flex items-center gap-3 flex-wrap">
                                            <span class="inline-flex rounded px-2 py-1 text-xs font-semibold {{ $activityLog->action_badge_classes }}">
                                                {{ $activityLog->action_label }}
                                            </span>
                                            <span class="text-sm text-gray-500">{{ $activityLog->created_at?->format('d/m/Y H:i:s') }}</span>
                                        </div>
                                        <div class="mt-2 text-sm text-gray-700">
                                            @if ($activityLog->user)
                                                Usuario: <strong>{{ $activityLog->user->name }}</strong> ({{ $activityLog->user->email }})
                                            @else
                                                Usuario: <strong>Sistema</strong>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if ($activityLog->formatted_data !== [])
                                    <div class="mt-3 bg-white rounded p-3 text-sm space-y-2">
                                        @foreach ($activityLog->formatted_data as $label => $value)
                                            <div class="grid grid-cols-1 md:grid-cols-[180px_1fr] gap-2">
                                                <span class="font-semibold text-gray-600">{{ $label }}</span>
                                                <div class="break-all text-gray-800">
                                                    @if (is_array($value))
                                                        <div class="space-y-1">
                                                            @foreach ($value as $item)
                                                                <div class="rounded border border-gray-200 bg-gray-50 px-2 py-1">
                                                                    <span class="font-semibold">{{ $item['numero_factura'] ?? 'N/A' }}</span>
                                                                    <span class="text-gray-500">(#{{ $item['recurso_id'] ?? 'N/A' }})</span>
                                                                    <span class="ml-2">{{ $item['litros'] ?? '0,00 L' }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-gray-500">No hay actividad reciente para este ticket.</div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    @endsection

@endcan
