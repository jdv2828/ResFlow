@extends('layouts.layout')
@section('content')
@can('puede_ver_roles_y_permisos')
<div class="container mx-auto p-4">
    <h1 class="text-xl font-semibold mb-4">Gestión de Roles y Permisos</h1>

    <div x-data="{
        selectedUser: '',
        userRoleIds: [],
        userPermissionIds: [],
        fetchUserState() {
            if (!this.selectedUser) { this.userRoleIds = []; this.userPermissionIds = []; return; }
            fetch('/usuarios/' + this.selectedUser + '/roles-permisos')
                .then(r => r.json())
                .then(data => {
                    this.userRoleIds = data.role_ids.map(String);
                    this.userPermissionIds = data.permission_ids.map(String);
                });
        },
        isRoleSelected(id) { return this.userRoleIds.includes(String(id)); },
        isPermSelected(id) { return this.userPermissionIds.includes(String(id)); }
    }">
        {{-- Sincronizar --}}
        <form action="{{ route('usuarios.sincronizar') }}" method="POST"
            class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            @csrf
            <h2 class="text-lg font-bold mb-4">Sincronizar Roles y Permisos</h2>
            <p class="text-sm text-gray-500 mb-4">Seleccioná un usuario para ver sus asignaciones actuales. Marcá los roles y permisos deseados y sincronizá.</p>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                <select name="usuario_id" x-model="selectedUser" @change="fetchUserState()"
                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Seleccionar usuario</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Roles</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach ($roles as $role)
                        <label class="flex items-center gap-2 p-2 border rounded cursor-pointer hover:bg-gray-50"
                            :class="{ 'bg-blue-50 border-blue-300': isRoleSelected({{ $role->id }}) }">
                            <input type="checkbox" name="rol_id[]" value="{{ $role->id }}"
                                :checked="isRoleSelected({{ $role->id }})"
                                class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <span class="text-sm">{{ $role->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Permisos</label>
                @foreach ($permissions as $group => $groupPerms)
                    <div class="mb-3">
                        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-1">{{ $group }}</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-1">
                            @foreach ($groupPerms as $perm)
                                <label class="flex items-center gap-2 p-1 rounded cursor-pointer hover:bg-gray-50 text-sm"
                                    :class="{ 'bg-green-50': isPermSelected({{ $perm->id }}) }">
                                    <input type="checkbox" name="permiso_id[]" value="{{ $perm->id }}"
                                        :checked="isPermSelected({{ $perm->id }})"
                                        class="h-3.5 w-3.5 text-blue-600 border-gray-300 rounded">
                                    <span class="text-xs">{{ $perm->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="submit"
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                Sincronizar
            </button>
        </form>

        {{-- Asignar (append) --}}
        <form action="{{ route('usuarios.asignacion') }}" method="POST"
            class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            @csrf
            <h2 class="text-lg font-bold mb-4">Asignar (Agregar)</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                    <select name="usuario_id"
                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Roles</label>
                    <select name="rol_id[]" multiple
                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Permisos</label>
                    <select name="permiso_id[]" multiple
                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @foreach ($permissions as $group => $groupPerms)
                            <optgroup label="{{ $group }}">
                                @foreach ($groupPerms as $perm)
                                    <option value="{{ $perm->id }}">{{ $perm->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Asignar</button>
        </form>

        {{-- Remover --}}
        <form action="{{ route('usuarios.remover') }}" method="POST"
            class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            @csrf
            <h2 class="text-lg font-bold mb-4 text-red-600">Remover</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                    <select name="usuario_id"
                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Roles a Remover</label>
                    <select name="rol_id[]" multiple
                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Permisos a Remover</label>
                    <select name="permiso_id[]" multiple
                        class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                        @foreach ($permissions as $group => $groupPerms)
                            <optgroup label="{{ $group }}">
                                @foreach ($groupPerms as $perm)
                                    <option value="{{ $perm->id }}">{{ $perm->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="mt-4 px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Remover</button>
        </form>
    </div>
</div>
@else
<div class="container mx-auto p-4">
    <div class="bg-red-500 text-white px-6 py-4 border-0 rounded relative mb-4">
        <span><b>Error!</b> No tenés permisos para acceder a esta sección.</span>
    </div>
</div>
@endcan
@endsection
