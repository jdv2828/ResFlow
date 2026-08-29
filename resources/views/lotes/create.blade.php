@extends('layouts.layout')
@can('puede_crear_lote')


@section('content')
@php
$centroMap = $centroCostos->mapWithKeys(fn($c) => [
    (string) $c->id => [
        'nombre' => $c->nombre,
        'centroPadre' => $c->centroPadre?->nombre ?? '-',
    ]
]);
@endphp
<script>window._centroMap = @json($centroMap);</script>
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-4">Crear Lote de Combustible</h1>

    <form action="{{ route('lotes.store') }}" method="POST"
        x-data="{
            empleados: [],
            loading: false,
            centroSeleccionado: '',
            centroNombre: '',
            centroPadre: '',
            loadByCentroCosto(centroCostoId) {
                if (!centroCostoId) {
                    this.empleados = [];
                    this.centroSeleccionado = '';
                    return;
                }
                this.loading = true;
                this.centroSeleccionado = centroCostoId;
                fetch(`/api/lotes/empleados-por-centro/${centroCostoId}`)
                    .then(r => r.json())
                    .then(data => {
                        this.empleados = data.map(emp => ({
                            ...emp,
                            personal_id: emp.id,
                            litros: 0,
                            cantidad_vales: 1,
                            fecha_caducidad: ''
                        }));
                        this.loading = false;
                    })
                    .catch(() => {
                        this.empleados = [];
                        this.loading = false;
                    });
                this.centroNombre = window._centroMap[centroCostoId]?.nombre ?? '';
                this.centroPadre = window._centroMap[centroCostoId]?.centroPadre ?? '';
            },
            addManual() {
                this.empleados.push({
                    id: '',
                    personal_id: '',
                    nombre_completo: '',
                    dni: '',

                    secretaria: this.centroNombre,
                    direccion: this.centroPadre,
                    litros: 0,
                    cantidad_vales: 1,
                    fecha_caducidad: ''
                });
            },
            remove(idx) {
                this.empleados.splice(idx, 1);
            }
        }"
        @submit="if (empleados.length === 0) { $event.preventDefault(); alert('Debe agregar al menos un personal al lote.'); }">
        @csrf

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-bold mb-1">Nombre del Lote (opcional)</label>
                <input type="text" name="nombre" class="border rounded w-full py-2 px-3"
                    placeholder="Ej: Lote Semanal Obras Públicas">
            </div>
            <div>
                <label class="block text-sm font-bold mb-1">Responsable</label>
                <select name="responsable_id" class="border rounded w-full py-2 px-3">
                    <option value="">Seleccionar responsable</option>
                    @foreach ($responsables as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold mb-1">Dirección (Centro de Costo)</label>
                <select name="centro_costo_id" id="centroCostoSelect" class="border rounded w-full py-2 px-3" required @change="loadByCentroCosto($event.target.value)">
                    <option value="">Seleccionar dirección</option>
@foreach ($centroCostos as $centroCosto)
    <option value="{{ $centroCosto->id }}"
        data-centro-padre="{{ $centroCosto->centroPadre?->nombre ?? '-' }}">
        {{ $centroCosto->nombre }}
    </option>
@endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold mb-1">Tipo de Combustible</label>
                <select name="tipo_combustible_id" id="tipoCombustibleSelect" class="border rounded w-full py-2 px-3" required>
                    <option value="">Seleccionar combustible</option>
                    @foreach ($combustibles as $comb)
                        <option value="{{ $comb->id }}" data-centro="{{ $comb->centro_costo_id }}">{{ $comb->nombre }} ({{ $comb->categoria }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <h2 class="text-xl font-bold mb-4">Personal del Lote</h2>

        <div class="overflow-x-auto mb-4">
            <table class="table-auto w-full border">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-2 py-2 border text-sm">Nombre y Apellido</th>
                        <th class="px-2 py-2 border text-sm">DNI</th>
                        <th class="px-2 py-2 border text-sm">Centro de Costo</th>
                        <th class="px-2 py-2 border text-sm">Centro Padre</th>
                        <th class="px-2 py-2 border text-sm">Litros</th>
                        <th class="px-2 py-2 border text-sm">Fec. Vencimiento</th>
                        <th class="px-2 py-2 border text-sm">Cant. Vales</th>
                        <th class="px-2 py-2 border text-sm">Quitar</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="8" class="text-center py-4">Cargando personal...</td>
                        </tr>
                    </template>
                    <template x-if="!loading && empleados.length === 0">
                        <tr>
                            <td colspan="8" class="text-center py-4 text-gray-500" x-text="centroSeleccionado ? 'No se encontró personal en esta dirección.' : 'Seleccione una Dirección para cargar el personal.'"></td>
                        </tr>
                    </template>
                    <template x-for="(emp, idx) in empleados" :key="idx">
                        <tr>
                            <td class="border px-2 py-1">
                                <input type="text" :name="'empleados['+idx+'][nombre_completo]'" x-model="emp.nombre_completo"
                                    class="border rounded w-full py-1 px-1 text-sm" placeholder="Nombre y Apellido">
                            </td>
                            <td class="border px-2 py-1">
                                <input type="text" :name="'empleados['+idx+'][dni]'" x-model="emp.dni"
                                    class="border rounded w-full py-1 px-1 text-sm" placeholder="DNI">
                            </td>
                            <td class="border px-2 py-1 text-sm" x-text="emp.secretaria"></td>
                            <td class="border px-2 py-1 text-sm" x-text="emp.direccion"></td>
                            <td class="border px-2 py-1">
                                <input type="hidden" :name="'empleados['+idx+'][personal_id]'" :value="emp.personal_id || ''">
                                <input type="number" :name="'empleados['+idx+'][litros]'" step="0.01" min="0"
                                    class="border rounded w-24 py-1 px-1 text-sm" x-model="emp.litros" placeholder="Litros">
                            </td>
                            <td class="border px-2 py-1">
                                <input type="date" :name="'empleados['+idx+'][fecha_caducidad]'"
                                    class="border rounded py-1 px-1 text-sm" x-model="emp.fecha_caducidad">
                            </td>
                            <td class="border px-2 py-1">
                                <input type="number" :name="'empleados['+idx+'][cantidad_vales]'" min="1"
                                    class="border rounded w-20 py-1 px-1 text-sm" x-model="emp.cantidad_vales" placeholder="Vales">
                            </td>
                            <td class="border px-2 py-1 text-center">
                                <button type="button" @click="remove(idx)"
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs">
                                    X
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="flex gap-4 mb-4">
            <button type="button" @click="addManual()"
                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                + Agregar Personal Manual
            </button>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                Guardar Lote
            </button>
            <a href="{{ route('lotes.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded">
                Cancelar
            </a>
        </div>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var centroSelect = document.getElementById('centroCostoSelect');
    var combustibleSelect = document.getElementById('tipoCombustibleSelect');
    if (centroSelect && combustibleSelect) {
        centroSelect.addEventListener('change', function() {
            var centroId = this.value;
            combustibleSelect.querySelectorAll('option[data-centro]').forEach(function(opt) {
                opt.style.display = (!centroId || opt.dataset.centro === centroId) ? '' : 'none';
            });
            combustibleSelect.value = '';
        });
    }
});
</script>
@endsection
@endcan
