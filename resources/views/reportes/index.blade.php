@extends('layouts.layout')
@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-6">Informes</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Vales Consumidos --}}
        <div class="border rounded p-6 bg-white shadow">
            <h2 class="text-xl font-bold mb-4">Vales Consumidos</h2>
            <p class="text-sm text-gray-600 mb-4">Descargar reporte de vales que ya fueron consumidos.</p>
            <form action="{{ route('reportes.download') }}" method="GET">
                <div class="mb-4">
                    <label class="block text-sm font-bold mb-1">Desde (fecha y hora)</label>
                    <input type="datetime-local" name="fecha_desde" value="{{ request('fecha_desde', now()->format('Y-m-d\T00:00')) }}"
                        class="border rounded w-full py-2 px-3">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-bold mb-1">Hasta (fecha y hora)</label>
                    <input type="datetime-local" name="fecha_hasta" value="{{ request('fecha_hasta', now()->format('Y-m-d\T23:59')) }}"
                        class="border rounded w-full py-2 px-3">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-bold mb-1">Estado</label>
                    <select name="estado" class="border rounded w-full py-2 px-3">
                        <option value="">Todos</option>
                        <option value="4" {{ request('estado') == '4' ? 'selected' : '' }}>Utilizado</option>
                        <option value="3" {{ request('estado') == '3' ? 'selected' : '' }}>Vencido</option>
                        <option value="5" {{ request('estado') == '5' ? 'selected' : '' }}>Eliminado</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-bold mb-1">Formato</label>
                    <div class="flex gap-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="formato" value="csv" checked
                                class="text-brand-blue-accent focus:ring-brand-blue-accent">
                            <span class="ml-2 text-sm">CSV</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="formato" value="xlsx"
                                {{ request('formato') == 'xlsx' ? 'checked' : '' }}
                                class="text-brand-blue-accent focus:ring-brand-blue-accent">
                            <span class="ml-2 text-sm">XLSX (Excel)</span>
                        </label>
                    </div>
                </div>
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Descargar
                </button>
            </form>
        </div>

        {{-- Vales Emitidos --}}
        <div class="border rounded p-6 bg-white shadow">
            <h2 class="text-xl font-bold mb-4">Vales Emitidos</h2>
            <p class="text-sm text-gray-600 mb-4">Descargar reporte de vales activos pendientes de consumo.</p>
            <form action="{{ route('reportes.emitidos') }}" method="GET">
                <div class="mb-4">
                    <label class="block text-sm font-bold mb-1">Desde (fecha y hora)</label>
                    <input type="datetime-local" name="fecha_desde" value="{{ request('fecha_desde', now()->format('Y-m-d\T00:00')) }}"
                        class="border rounded w-full py-2 px-3">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-bold mb-1">Hasta (fecha y hora)</label>
                    <input type="datetime-local" name="fecha_hasta" value="{{ request('fecha_hasta', now()->format('Y-m-d\T23:59')) }}"
                        class="border rounded w-full py-2 px-3">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-bold mb-1">Estado</label>
                    <select name="estado" class="border rounded w-full py-2 px-3">
                        <option value="">Todos</option>
                        <option value="1" {{ request('estado') == '1' ? 'selected' : '' }}>Generado</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-bold mb-1">Formato</label>
                    <div class="flex gap-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="formato" value="csv" checked
                                class="text-brand-blue-accent focus:ring-brand-blue-accent">
                            <span class="ml-2 text-sm">CSV</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="formato" value="xlsx"
                                {{ request('formato') == 'xlsx' ? 'checked' : '' }}
                                class="text-brand-blue-accent focus:ring-brand-blue-accent">
                            <span class="ml-2 text-sm">XLSX (Excel)</span>
                        </label>
                    </div>
                </div>
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Descargar
                </button>
            </form>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ url()->previous() }}"
            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Volver
        </a>
    </div>
</div>
@endsection
