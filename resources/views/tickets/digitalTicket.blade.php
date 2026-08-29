<!DOCTYPE html>
<html>

<head>
    <title>Vale de Combustible</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl m-5">
        <div class="p-8">
            <div class="uppercase tracking-wide text-b text-indigo-500 font-semibold text-center">
            <img class="h-16 w-auto" src="/img/logo.png" alt="Logo" style="margin: 0 auto;">
                 <br>
                 Vale de Combustible
            </div>
            <br>
            <p class="block mt-1 text-lg leading-tight font-medium text-black">Cantidad: {{ number_format($ticket->litros, 2, ',', '.') }} Litros</p>
            <br>
            <p class="block mt-1 text-lg leading-tight font-medium text-black">Asignado a: {{ $ticket->personal->nombre }} {{ $ticket->personal->apellido }}</p>
            <br>
            <p class="block mt-1 text-lg leading-tight font-medium text-black">Centro de Costo: {{ $ticket->centroCosto->nombre }}</p>
            <br>
            <p class="block mt-1 text-lg leading-tight font-medium text-black">Tipo Combustible: {{ $ticket->tipoCombustible->nombre }} ({{ $ticket->tipoCombustible->categoria ?? '' }})</p>
            <br>
            <p class="block mt-1 text-lg leading-tight font-medium text-black">Emitido por: {{ $ticket->emitidoPor->name }}</p>
            <br>
            <div class="mt-2">
                <p class="block mt-1 text-lg leading-tight font-medium text-black">Vehiculo:</p>
                @foreach ($ticket->personal->vehiculosAsChofer as $vehiculo)
                    <p class="block mt-1 text-lg leading-tight font-medium text-black">{{ $vehiculo->marca }} {{ $vehiculo->modelo }} {{ $vehiculo->patente }}</p>
                @endforeach
            </div>
            <br>
            {{-- <form action="{{ route('tickets.finish', $ticket->id) }}" method="POST" class="mt-4">
                @csrf
                @method('DELETE')
                <h2 class="text-red-500">Al finalizar el ticket el mismo se anulara y no podra ser reutilizado</h2>
                <br>
                <div class="text-center">
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Finalizar Ticket
                    </button>
                </div>
            </form> --}}
        </div>
    </div>
</body>

</html>
