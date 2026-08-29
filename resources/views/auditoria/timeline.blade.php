@extends('layouts.layout')

@section('content')
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold">{{ $title }}</h1>
                <p class="text-gray-600 mt-1">{{ $subtitle }}</p>
            </div>

            <div class="flex gap-3">
                @isset($pdfRoute)
                    <a href="{{ $pdfRoute }}" target="_blank" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Exportar PDF
                    </a>
                @endisset
                <a href="{{ $backRoute }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Volver a auditoría
                </a>
            </div>
        </div>

        @if (!empty($summary['items']))
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                @foreach ($summary['items'] as $item)
                    <div class="bg-white shadow-md rounded px-5 py-4 border-l-4 {{ $summary['type'] === 'ticket' ? 'border-blue-500' : 'border-green-500' }}">
                        <p class="text-sm text-gray-500">{{ $item['label'] }}</p>
                        <p class="text-xl font-bold break-words">{{ $item['value'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="space-y-4">
            @forelse ($activityLogs as $activityLog)
                <div class="bg-white shadow-md rounded p-5 border-l-4 {{ str_contains($activityLog->action, 'ticket') ? 'border-blue-500' : (str_contains($activityLog->action, 'recurso') ? 'border-green-500' : 'border-gray-400') }}">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
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

                        @if ($activityLog->subject_link)
                            <a href="{{ $activityLog->subject_link }}" class="text-blue-600 hover:text-blue-800 underline text-sm">
                                {{ $activityLog->subject_label }}
                            </a>
                        @else
                            <div class="text-sm text-gray-600">{{ $activityLog->subject_label }}</div>
                        @endif
                    </div>

                    @if ($activityLog->formatted_data !== [])
                        <div class="mt-4 bg-gray-50 rounded p-3 text-sm space-y-2">
                            @foreach ($activityLog->formatted_data as $label => $value)
                                <div class="grid grid-cols-1 md:grid-cols-[180px_1fr] gap-2">
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
                    @endif
                </div>
            @empty
                <div class="bg-white shadow-md rounded p-6 text-center text-gray-500">
                    No hay actividad registrada para esta línea de tiempo.
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $activityLogs->links() }}
        </div>
    </div>
@endsection
