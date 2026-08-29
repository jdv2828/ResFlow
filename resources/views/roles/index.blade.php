@extends('layouts.layout')
@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Gestión de Roles</h1>

    {{-- Crear rol --}}
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-6">
        <h2 class="text-lg font-bold mb-4">Crear Nuevo Rol</h2>
        <form action="{{ route('roles.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Rol</label>
                <input type="text" name="name" required
                    class="block w-full md:w-1/3 py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    placeholder="ej: supervisor">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Crear Rol</button>
        </form>
    </div>

    {{-- Lista de roles --}}
    <div class="space-y-4">
        @foreach ($roles as $role)
            <div class="bg-white shadow-md rounded px-8 pt-6 pb-8">
                <form action="{{ route('roles.update', $role->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-4">
                            <input type="text" name="name" value="{{ $role->name }}"
                                class="text-lg font-bold border rounded py-1 px-2"
                                {{ $role->name === 'admin' ? 'readonly' : '' }}>
                            <span class="text-sm text-gray-500">{{ $role->users_count ?? 0 }} usuarios</span>
                        </div>
                        @if ($role->name !== 'admin')
                            <button type="submit" class="px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600">Guardar</button>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-1">
                        @foreach ($groupedPerms as $group => $perms)
                            <div class="mb-2">
                                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-1">{{ $group }}</h4>
                                @foreach ($perms as $perm)
                                    <label class="flex items-center gap-2 text-sm py-0.5">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                            {{ $role->hasPermissionTo($perm->name) ? 'checked' : '' }}
                                            {{ $role->name === 'admin' ? 'disabled' : '' }}
                                            class="h-3.5 w-3.5 text-blue-600 border-gray-300 rounded">
                                        <span class="text-xs">{{ $perm->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </form>

                @if ($role->name !== 'admin')
                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="mt-4">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 text-sm hover:underline"
                            onclick="return confirm('¿Eliminar rol {{ $role->name }}?')">
                            Eliminar rol
                        </button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
