<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Vale de Combustible N° {{ $ticket->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #333;
            padding: 25px 30px;
        }

        .watermark {
            position: fixed;
            top: 45%;
            left: 50%;
            margin-left: -140px;
            margin-top: -140px;
            opacity: 0.04;
            z-index: -1;
            pointer-events: none;
        }
        .watermark img { width: 280px; }

        .header {
            border-bottom: 2px solid #C1272D;
            padding-bottom: 8px;
            margin-bottom: 14px;
            overflow: hidden;
            position: relative;
        }
        .header .logo { float: left; width: 90px; }
        .header h1 {
            text-align: center;
            font-size: 17px;
            color: #C1272D;
            letter-spacing: 1px;
            padding-top: 5px;
        }
        .header .nro {
            float: right;
            font-size: 15px;
            font-weight: bold;
            color: #555;
            padding-top: 5px;
        }

        .info { line-height: 2.2; margin-bottom: 18px; }
        .info .row { overflow: hidden; }
        .info .lbl {
            display: inline-block;
            width: 135px;
            font-weight: bold;
            color: #444;
        }
        .info .val {
            display: inline-block;
        }
        .info .litros {
            font-size: 13px;
            font-weight: bold;
            color: #C1272D;
        }

        .codes-section {
            margin-top: 18px;
            padding-top: 8px;
            border-top: 1px dashed #bbb;
            text-align: center;
        }
        .code-box {
            display: inline-block;
            width: 46%;
            text-align: center;
            vertical-align: middle;
            padding: 4px;
        }
        .code-box .label {
            display: block;
            font-size: 9px;
            color: #777;
            margin-top: 4px;
        }

        .hash-box {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8px;
            color: #aaa;
            word-break: break-all;
        }

        .footer {
            margin-top: 20px;
            padding-top: 6px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8px;
            color: #aaa;
        }
    </style>
</head>
<body>

{{-- Marca de agua de fondo --}}
<div class="watermark">
    <img src="./img/resourceflow-icon.svg" alt="ResFlow">
</div>

{{-- Encabezado --}}
<div class="header">
    <img class="logo" src="./img/resourceflow-icon.svg" alt="ResourceFlow">
    <span class="nro">N° {{ $ticket->id }}</span>
    <h1>VALE DE RECURSO</h1>
</div>

{{-- Datos del vale --}}
<div class="info">
    <div class="row">
        <span class="lbl">Área:</span>
        <span class="val">{{ $ticket->personal->centroCosto->nombre }}</span>
    </div>
    <div class="row">
        <span class="lbl">Personal:</span>
        <span class="val">{{ $ticket->personal->nombre }} {{ $ticket->personal->apellido }}</span>
    </div>
    @php $vehiculo = $ticket->personal->vehiculosAsChofer->first(); @endphp
    <div class="row">
        <span class="lbl">Vehículo:</span>
        <span class="val">
            @if($vehiculo)
                {{ $vehiculo->marca }} {{ $vehiculo->modelo }} ({{ $vehiculo->patente }})
            @else
                Sin vehículo asignado
            @endif
        </span>
    </div>
    <div class="row">
        <span class="lbl">Combustible:</span>
        <span class="val">{{ $ticket->tipoCombustible->nombre }} ({{ $ticket->tipoCombustible->categoria ?? '' }})</span>
    </div>
    <div class="row">
        <span class="lbl">Litros asignados:</span>
        <span class="val litros">{{ number_format($ticket->litros, 2, ',', '.') }} L</span>
    </div>
    <div class="row">
        <span class="lbl">Centro de Costo:</span>
        <span class="val">{{ $ticket->centroCosto->nombre }}</span>
    </div>
    <div class="row">
        <span class="lbl">Factura:</span>
        <span class="val">{{ $ticket->recurso?->numero_factura ?? 'Sin factura' }}</span>
    </div>
    <div class="row">
        <span class="lbl">Emitido por:</span>
        <span class="val">{{ $ticket->emitidoPor->name }}</span>
    </div>
    <div class="row">
        <span class="lbl">Vencimiento:</span>
        <span class="val">{{ \Carbon\Carbon::parse($ticket->fecha_caducidad)->format('d/m/Y') }}</span>
    </div>
</div>

{{-- Códigos QR y Barras --}}
<div class="codes-section">
    <div class="code-box">
        {!! $qr !!}
        <span class="label">Código QR</span>
    </div>
    <div class="code-box">
        {!! $barcode !!}
        <span class="label">Código de Barras</span>
    </div>
</div>

{{-- Hash --}}
@if($ticket->hash)
<div class="hash-box">
    Hash: {{ $ticket->hash }}
</div>
@endif

{{-- Footer --}}
<div class="footer">
    ResFlow &mdash; Generado el {{ now()->format('d/m/Y H:i') }}
</div>

</body>
</html>
