@extends('layouts.layout')
@can('puede_editar_recurso')
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

            <form action="{{ route('recursos.update', $recurso->id) }}" method="POST"
                class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                @csrf
                @method('PUT') <!-- Asegúrate de usar el método correcto aquí -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="numero_factura">Número de
                        Factura:</label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        type="text" id="numero_factura" name="numero_factura"
                        value="{{ $recurso->numero_factura }}" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="litros">Litros:</label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        type="number" id="litros" name="litros" step="0.00001"
                        value="{{ $tieneDecimales($recurso->litros) ? $recurso->litros : intval($recurso->litros) }}"
                        required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="monto">monto:</label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        type="number" id="monto" name="monto" step="0.00001" value="{{ $recurso->monto }}" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="centro_costo_id">Centro de
                        Costo:</label>
                    <select
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="centro_costo_id" name="centro_costo_id">
                        <option value="">Seleccione un centro de costo</option>
                        @foreach ($centroCostos as $centroCosto)
                            <option value="{{ $centroCosto->id }}"
                                {{ $centroCosto->id == $recurso->centro_costo_id ? 'selected' : '' }}>
                                {{ $centroCosto->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="tipo_combustible_id">Tipo de
                        Combustible:</label>
                    <select
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="tipo_combustible_id" name="tipo_combustible_id">
                        <option value="">Seleccione un tipo de combustible</option>
                        @foreach ($tipoCombustubles as $tipo)
                            <option value="{{ $tipo->id }}" data-centro="{{ $tipo->centro_costo_id }}"
                                {{ $tipo->id == $recurso->tipo_combustible_id ? 'selected' : '' }}>{{ $tipo->nombre }} ({{ $tipo->categoria }})</option>
                        @endforeach
                    </select>
                </div>

<script>
document.getElementById('centro_costo_id').addEventListener('change', function() {
    var centroId = this.value;
    var current = document.getElementById('tipo_combustible_id').value;
    document.querySelectorAll('#tipo_combustible_id option[data-centro]').forEach(function(opt) {
        opt.style.display = (!centroId || opt.dataset.centro === centroId) ? '' : 'none';
    });
    if (!centroId) document.getElementById('tipo_combustible_id').value = '';
});
</script>

                <div class="flex items-center justify-between">
                    <button
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        type="submit">
                        Actualizar Recurso
                    </button>
                </div>
            </form>

            @if (auth()->user()->hasRole('admin'))
                <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-xl font-bold">Historial reciente del recurso</h2>
                            <p class="text-gray-600 text-sm mt-1">Últimos movimientos relacionados a este recurso.</p>
                        </div>
                        <a href="{{ route('auditoria.recursos.timeline', $recurso->id) }}"
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
                                                <span class="break-all text-gray-800">{{ $value }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-gray-500">No hay actividad reciente para este recurso.</div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    @endsection
@endcan
