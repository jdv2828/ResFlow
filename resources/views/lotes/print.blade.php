<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lote {{ $lote->nombre ?? '#' . $lote->id }}</title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { text-align: center; font-size: 16px; margin-bottom: 5px; }
        .header-info { text-align: center; margin-bottom: 15px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background-color: #C1272D; color: white; padding: 6px 4px; font-size: 11px; text-align: left; }
        td { border: 1px solid #ccc; padding: 5px 4px; font-size: 11px; }
        .footer { text-align: center; font-size: 10px; margin-top: 20px; color: #666; }
        .ticket-divider { border-top: 1px dashed #999; margin: 20px 0; }
        .ticket-box { border: 2px solid #C1272D; padding: 10px; margin-bottom: 10px; border-radius: 5px; }
        .ticket-header { background-color: #C1272D; color: white; padding: 5px 10px; margin: -10px -10px 10px -10px; font-weight: bold; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; background: #C1272D; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Imprimir Lote
        </button>
        <a href="{{ route('lotes.index') }}" style="padding: 10px 20px; font-size: 14px; background: #666; color: white; border: none; border-radius: 5px; text-decoration: none; margin-left: 10px;">
            Volver
        </a>
    </div>

    <h1>LOTE DE COMBUSTIBLE</h1>
    <div class="header-info">
        <strong>{{ $lote->nombre ?? 'Lote #' . $lote->id }}</strong><br>
        Dirección: {{ $lote->centroCosto->nombre ?? 'N/A' }} |
        Responsable: {{ $lote->responsable->name ?? 'N/A' }}<br>
        Combustible: {{ $lote->tipoCombustible->nombre ?? 'N/A' }} <span class="text-xs text-gray-500">({{ $lote->tipoCombustible->categoria ?? '' }})</span><br>
        Fecha: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Nombre y Apellido</th>
                <th>DNI</th>
                <th>Patente</th>
                <th>Secretaría</th>
                <th>Dirección</th>
                <th>Litros/Vale</th>
                <th>Vencimiento</th>
                <th>Cant. Vales</th>
                <th>Total Litros</th>
            </tr>
        </thead>
        <tbody>
            @php $totalLitros = 0; $totalVales = 0; @endphp
            @foreach ($lote->loteEmpleados as $detalle)
                @php
                    $empleado = $detalle->personal;
                    $litrosSubtotal = $detalle->litros * $detalle->cantidad_vales;
                    $totalLitros += $litrosSubtotal;
                    $totalVales += $detalle->cantidad_vales;
                @endphp
                <tr>
                    <td>{{ $empleado->nombre_completo ?? 'N/A' }}</td>
                    <td>{{ $empleado->dni ?? '-' }}</td>
                    <td>{{ $empleado->vehiculosAsChofer->first()?->patente ?? '-' }}</td>
                    <td>{{ $empleado->centroCosto->nombre ?? '-' }}</td>
                    <td>{{ $empleado->centroCosto?->centroPadre?->nombre ?? '-' }}</td>
                    <td style="text-align: right">{{ number_format($detalle->litros, 2) }} L</td>
                    <td>{{ $detalle->fecha_caducidad ? $detalle->fecha_caducidad->format('d/m/Y') : '-' }}</td>
                    <td style="text-align: center">{{ $detalle->cantidad_vales }}</td>
                    <td style="text-align: right">{{ number_format($litrosSubtotal, 2) }} L</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f0f0f0;">
                <td colspan="5" style="text-align: right;">Totales:</td>
                <td style="text-align: right;"></td>
                <td></td>
                <td style="text-align: center;">{{ $totalVales }}</td>
                <td style="text-align: right;">{{ number_format($totalLitros, 2) }} L</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generado por {{ auth()->user()->name }} el {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
