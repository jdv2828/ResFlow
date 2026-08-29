<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin-top: 24px; margin-bottom: 8px; }
        .subtitle { color: #4b5563; margin-bottom: 16px; }
        .summary-grid { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .summary-grid td { border: 1px solid #d1d5db; padding: 8px; width: 25%; vertical-align: top; }
        .summary-label { font-size: 10px; color: #6b7280; text-transform: uppercase; }
        .summary-value { font-size: 14px; font-weight: bold; margin-top: 4px; }
        .entry { border: 1px solid #d1d5db; margin-bottom: 10px; padding: 10px; }
        .entry-header { margin-bottom: 8px; }
        .action { font-weight: bold; }
        .meta { color: #6b7280; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .data-table td { border: 1px solid #e5e7eb; padding: 6px; vertical-align: top; }
        .data-label { width: 180px; font-weight: bold; color: #374151; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="subtitle">{{ $subtitle }}</div>

    @if (!empty($summary['items']))
        <table class="summary-grid">
            <tr>
                @foreach ($summary['items'] as $index => $item)
                    <td>
                        <div class="summary-label">{{ $item['label'] }}</div>
                        <div class="summary-value">{{ $item['value'] }}</div>
                    </td>
                    @if (($index + 1) % 4 === 0 && !$loop->last)
                        </tr><tr>
                    @endif
                @endforeach
            </tr>
        </table>
    @endif

    <h2>Eventos</h2>

    @forelse ($activityLogs as $activityLog)
        <div class="entry">
            <div class="entry-header">
                <div class="action">{{ $activityLog->action_label }} ({{ $activityLog->action }})</div>
                <div class="meta">
                    {{ $activityLog->created_at?->format('d/m/Y H:i:s') }}
                    | Usuario:
                    @if ($activityLog->user)
                        {{ $activityLog->user->name }} ({{ $activityLog->user->email }})
                    @else
                        Sistema
                    @endif
                    | Sujeto: {{ $activityLog->subject_label }}
                </div>
            </div>

            @if ($activityLog->formatted_data !== [])
                <table class="data-table">
                    @foreach ($activityLog->formatted_data as $label => $value)
                        <tr>
                            <td class="data-label">{{ $label }}</td>
                            <td>
                                @if (is_array($value))
                                    @foreach ($value as $item)
                                        <div>
                                            <strong>{{ $item['numero_factura'] ?? 'N/A' }}</strong>
                                            (#{{ $item['recurso_id'] ?? 'N/A' }})
                                            - {{ $item['litros'] ?? '0,00 L' }}
                                        </div>
                                    @endforeach
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </div>
    @empty
        <p>No hay actividad registrada para esta línea de tiempo.</p>
    @endforelse
</body>
</html>
