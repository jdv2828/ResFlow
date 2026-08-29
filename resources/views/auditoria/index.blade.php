@extends('layouts.layout')

@section('content')
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-4">Auditoría de actividad</h1>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white shadow-md rounded px-5 py-4 border-l-4 border-blue-500">
                <p class="text-sm text-gray-500">Hoy</p>
                <p class="text-2xl font-bold">{{ $metrics['today_count'] }}</p>
            </div>
            <div class="bg-white shadow-md rounded px-5 py-4 border-l-4 border-green-500">
                <p class="text-sm text-gray-500">Últimos 7 días</p>
                <p class="text-2xl font-bold">{{ $metrics['last_7_days_count'] }}</p>
            </div>
            <div class="bg-white shadow-md rounded px-5 py-4 border-l-4 border-purple-500">
                <p class="text-sm text-gray-500">Últimos 30 días</p>
                <p class="text-2xl font-bold">{{ $metrics['last_30_days_count'] }}</p>
            </div>
            <div class="bg-white shadow-md rounded px-5 py-4 border-l-4 border-yellow-500">
                <p class="text-sm text-gray-500">Usuarios únicos</p>
                <p class="text-2xl font-bold">{{ $metrics['unique_users_count'] }}</p>
            </div>
        </div>

        <form method="GET" action="{{ route('auditoria.index') }}" class="bg-white shadow-md rounded px-6 py-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-bold mb-2" for="search">Buscar</label>
                    <input id="search" name="search" type="text" value="{{ request('search') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
                        placeholder="acción, usuario, subject...">
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2" for="action">Acción</label>
                    <select id="action" name="action" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                        <option value="">Todas</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                                {{ $action }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2" for="user_id">Usuario</label>
                    <select id="user_id" name="user_id" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                        <option value="">Todos</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ (string) request('user_id') === (string) $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2" for="subject_type">Tipo</label>
                    <select id="subject_type" name="subject_type" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                        <option value="">Todos</option>
                        @foreach ($subjectTypes as $subjectType)
                            <option value="{{ $subjectType }}" {{ request('subject_type') === $subjectType ? 'selected' : '' }}>
                                {{ class_basename($subjectType) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2" for="subject_id">ID sujeto</label>
                    <input id="subject_id" name="subject_id" type="number" value="{{ request('subject_id') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
                        placeholder="Ej: 15">
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2" for="recurso_id">ID recurso</label>
                    <input id="recurso_id" name="recurso_id" type="number" value="{{ request('recurso_id') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
                        placeholder="Ej: 2">
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2" for="numero_factura">Nro factura</label>
                    <input id="numero_factura" name="numero_factura" type="text" value="{{ request('numero_factura') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
                        placeholder="Ej: FAC-SEED-001">
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2" for="ticket_hash">Hash ticket</label>
                    <input id="ticket_hash" name="ticket_hash" type="text" value="{{ request('ticket_hash') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
                        placeholder="hash del ticket">
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2" for="ticket_id">ID ticket</label>
                    <input id="ticket_id" name="ticket_id" type="number" value="{{ request('ticket_id') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
                        placeholder="Ej: 15">
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2" for="date_from">Desde</label>
                    <input id="date_from" name="date_from" type="date" value="{{ request('date_from') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2" for="date_to">Hasta</label>
                    <input id="date_to" name="date_to" type="date" value="{{ request('date_to') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                </div>
            </div>

            <div class="flex gap-3 mt-4">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Filtrar
                </button>
                <a href="{{ route('auditoria.exportCsv', request()->query()) }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Exportar CSV
                </a>
                <a href="{{ route('auditoria.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Limpiar
                </a>
            </div>
        </form>

        <div class="bg-white shadow-md rounded overflow-x-auto">
            <table class="table-auto w-full">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left">Fecha</th>
                        <th class="px-4 py-2 text-left">Acción</th>
                        <th class="px-4 py-2 text-left">Usuario</th>
                        <th class="px-4 py-2 text-left">Sujeto</th>
                        <th class="px-4 py-2 text-left">Datos</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activityLogs as $activityLog)
                        <tr class="border-t align-top">
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ $activityLog->created_at?->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded px-2 py-1 text-xs font-semibold {{ $activityLog->action_badge_classes }}">
                                    {{ $activityLog->action_label }}
                                </span>
                                <div class="text-xs text-gray-500 mt-1">{{ $activityLog->action }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if ($activityLog->user)
                                    {{ $activityLog->user->name }}<br>
                                    <span class="text-xs text-gray-500">{{ $activityLog->user->email }}</span>
                                @else
                                    <span class="text-gray-500">Sistema</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($activityLog->subject_link)
                                    <a href="{{ $activityLog->subject_link }}" class="text-blue-600 hover:text-blue-800 underline">
                                        {{ $activityLog->subject_label }}
                                    </a>
                                @else
                                    <span class="text-gray-700">{{ $activityLog->subject_label }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($activityLog->formatted_data !== [])
                                    <div class="bg-gray-50 rounded p-2 text-xs space-y-1">
                                        @foreach ($activityLog->formatted_data as $label => $value)
                                            <div class="grid grid-cols-[140px_1fr] gap-2">
                                                <span class="font-semibold text-gray-600">{{ $label }}</span>
                                                <div class="break-all text-gray-800">
                                                    @if (is_array($value))
                                                        <div class="space-y-1">
                                                            @foreach ($value as $item)
                                                                <div class="rounded border border-gray-200 bg-white px-2 py-1">
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
                                @else
                                    <span class="text-gray-500 text-xs">Sin datos adicionales</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">No hay registros de auditoría para los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $activityLogs->links() }}
        </div>
    </div>
@endsection
