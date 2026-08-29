@extends('layouts.layout')
@can('puede_crear_vehiculo')
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

        <form action="{{ route('vehiculos.store') }}" method="POST" enctype="multipart/form-data"
            class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="marca">Marca:</label>
                <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    type="text" id="marca" name="marca" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="modelo">Modelo:</label>
                <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    type="text" id="modelo" name="modelo" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="patente">Patente:</label>
                <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    type="text" id="patente" name="patente" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="numero_identificacion">Número de Identificación:</label>
                <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    type="text" id="numero_identificacion" name="numero_identificacion">
            </div>

            <div x-data="{ choferInput: '' }" class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="id_chofer">Chofer:</label>
                <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    type="text" id="id_chofer" list="ChoferList"
                    placeholder="Escriba nombre, apellido o DNI del personal" autocomplete="off" required
                    @input="choferInput = $event.target.value; document.getElementById('chofer_id').value = [...$el.list.options].find(o => o.value === $event.target.value)?.dataset?.value || ''">
                <datalist id="ChoferList">
                    @foreach ($personal as $p)
                        <option data-value="{{ $p->id }}"
                            value="{{ $p->nombre }} {{ $p->apellido }} - DNI {{ $p->dni ?? $p->legajo }}">
                        </option>
                    @endforeach
                </datalist>
                <input type="hidden" id="chofer_id" name="id_chofer">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="id_centro_costo">Centro de Costo:</label>
                <select
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="id_centro_costo" name="id_centro_costo">
                    <option value="">Seleccionar centro de costo</option>
                    @foreach ($centroCostos as $centro)
                        <option value="{{ $centro->id }}">{{ $centro->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div x-data="{ preview: '' }" class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="foto">Foto:</label>
                <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    type="file" id="foto" name="foto" accept="image/*" @change="preview = URL.createObjectURL($event.target.files[0])">
                <img id="preview" x-show="preview" :src="preview" class="mt-2 max-w-xs rounded" x-bind:alt="'Vista previa'">
            </div>

            <div class="flex items-center justify-between">
                <button
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                    type="submit">
                    Crear Vehículo
                </button>
            </div>
        </form>
    </div>
@endsection

@endcan
