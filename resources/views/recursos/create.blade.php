@extends('layouts.layout')
@can('puede_crear_recurso')
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

            <form action="{{ route('recursos.store') }}" method="POST"
                class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="numero_factura">Número de
                        Factura:</label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        type="text" id="numero_factura" name="numero_factura" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="litros">Litros:</label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        type="number" id="litros" name="litros" step="0.00001" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="monto">monto:</label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        type="number" id="monto" name="monto" step="0.00001" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="centro_costo_id">Centro de
                        Costo:</label>
                    <select
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="centro_costo_id" name="centro_costo_id">
                        <option selected value="">Seleccione un centro de costo</option>
                        @foreach ($centroCostos as $centroCosto)
                            <option value="{{ $centroCosto->id }}">{{ $centroCosto->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="tipo_combustible_id">Tipo de
                        Combustible:</label>
                    <select
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="tipo_combustible_id" name="tipo_combustible_id">
                        <option selected value="">Seleccione un tipo de combustible</option>
                        @foreach ($tipoCombustubles as $tipo)
                            <option value="{{ $tipo->id }}" data-centro="{{ $tipo->centro_costo_id }}">{{ $tipo->nombre }} ({{ $tipo->categoria }})</option>
                        @endforeach
                    </select>
                </div>

<script>
document.getElementById('centro_costo_id').addEventListener('change', function() {
    var centroId = this.value;
    document.querySelectorAll('#tipo_combustible_id option[data-centro]').forEach(function(opt) {
        opt.style.display = (!centroId || opt.dataset.centro === centroId) ? '' : 'none';
    });
    document.getElementById('tipo_combustible_id').value = '';
});
</script>

                <div class="flex items-center justify-between">
                    <button
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        type="submit">
                        Crear Recurso
                    </button>
                </div>
            </form>
        </div>
    @endsection
@endcan
