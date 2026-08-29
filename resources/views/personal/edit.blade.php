@extends('layouts.layout')
@can('puede_editar_personal')
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

            <h1 class="text-2xl font-bold mb-4">Editar Personal</h1>

            <form action="{{ route('personal.update', $personal->id) }}" method="POST" enctype="multipart/form-data"
                class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="apellido">apellido:</label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        type="text" id="apellido" name="apellido" value="{{ $personal->apellido }}" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="nombre">nombre:</label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        type="text" id="nombre" name="nombre" value="{{ $personal->nombre }}" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="legajo">dni:</label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        type="text" id="legajo" name="legajo" value="{{ $personal->legajo }}" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">email:</label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        type="text" id="email" name="email" value="{{ $personal->email }}" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="telefono">telefono:</label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        type="text" id="telefono" name="telefono" value="{{ $personal->telefono }}" required>
                </div>

                <div x-data="{ preview: '{{ $personal->foto ? asset('images/' . $personal->foto) : '' }}' }" class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="foto">Foto:</label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        type="file" id="foto" name="foto" accept="image/*" @change="preview = URL.createObjectURL($event.target.files[0])">
                    <img id="preview" x-show="preview" :src="preview" class="mt-2 max-w-xs rounded" x-bind:alt="'Vista previa'">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="centro_costo_id">Centro de Costo:</label>
                    <select
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="centro_costo_id" name="centro_costo_id">
                        <option selected value="">Sin centro de costo padre</option>
                        @foreach ($centroCostos as $centro)
                            <option value="{{ $centro->id }}" {{ $personal->centro_costo_id == $centro->id ? 'selected' : '' }}>
                                {{ $centro->nombre }}</option>
                        @endforeach
                    </select>
                </div>





                <div class="flex items-center justify-between">
                    <button
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        type="submit">
                        Actualizar Personal
                    </button>
                </div>
            </form>
        </div>
    @endsection
@endcan
