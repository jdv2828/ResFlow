<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ResourceFlow — Gestión de Recursos')</title>
    <link rel="icon" href="/img/resourceflow-icon.svg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-100 min-h-screen">

@php
    if (!function_exists('formatNumber')) {
        function formatNumber($number)
        {
            return number_format($number, 0, ',', '.');
        }
    }
@endphp

<div x-data="{ open: false }" class="flex min-h-screen">

    @auth
    {{-- Overlay móvil --}}
    <div x-cloak x-show="open" @click="open = false" class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside x-cloak class="fixed lg:static inset-y-0 left-0 z-40 w-60 bg-brand-blue flex flex-col lg:translate-x-0 transition-transform duration-200 -translate-x-full"
        :class="open && 'translate-x-0'">
        <div class="h-16 flex items-center px-4 border-b border-white/20 flex-shrink-0">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img class="h-10 w-auto" src="/img/resourceflow-icon.svg" alt="Logo">
                <span class="text-white font-bold text-lg">ResourceFlow</span>
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto px-2 py-4 space-y-1">
            {{-- OPERACIONES --}}
            <div class="text-white/50 text-xs uppercase tracking-wider px-3 py-2 font-semibold">Operaciones</div>

            @can('puede_ver_ticket')
            <a href="{{ route('tickets.index') }}" @click="open = false"
                class="flex items-center gap-3 text-white/90 hover:text-white px-3 py-2 rounded-md hover:bg-white/10 text-sm font-medium transition-colors">
                <x-icon name="ticket" class="w-5 h-5 flex-shrink-0" />
                Tickets
            </a>
            @endcan

            @can('puede_ver_gestion_ticket')
            <a href="{{ route('tickets.showManageTicket') }}" @click="open = false"
                class="flex items-center gap-3 text-white/90 hover:text-white px-3 py-2 rounded-md hover:bg-white/10 text-sm font-medium transition-colors">
                <x-icon name="clipboard-list" class="w-5 h-5 flex-shrink-0" />
                Gestión de Tickets
            </a>
            @endcan

            @can('puede_ver_recurso')
            <a href="{{ route('recursos.index') }}" @click="open = false"
                class="flex items-center gap-3 text-white/90 hover:text-white px-3 py-2 rounded-md hover:bg-white/10 text-sm font-medium transition-colors">
                <x-icon name="folder-open" class="w-5 h-5 flex-shrink-0" />
                Recursos
            </a>
            @endcan

            @can('puede_ver_lote')
            <a href="{{ route('lotes.index') }}" @click="open = false"
                class="flex items-center gap-3 text-white/90 hover:text-white px-3 py-2 rounded-md hover:bg-white/10 text-sm font-medium transition-colors">
                <x-icon name="cube" class="w-5 h-5 flex-shrink-0" />
                Lotes
            </a>
            @endcan

            @can('puede_ver_informes')
            <a href="{{ route('reportes.index') }}" @click="open = false"
                class="flex items-center gap-3 text-white/90 hover:text-white px-3 py-2 rounded-md hover:bg-white/10 text-sm font-medium transition-colors">
                <x-icon name="chart-bar" class="w-5 h-5 flex-shrink-0" />
                Informes
            </a>
            @endcan

            {{-- ADMINISTRACIÓN --}}
            <div class="text-white/50 text-xs uppercase tracking-wider px-3 py-2 pt-4 font-semibold">Administración</div>

            @can('puede_ver_personal')
            <a href="{{ route('personal.index') }}" @click="open = false"
                class="flex items-center gap-3 text-white/90 hover:text-white px-3 py-2 rounded-md hover:bg-white/10 text-sm font-medium transition-colors">
                <x-icon name="users" class="w-5 h-5 flex-shrink-0" />
                Personal
            </a>
            @endcan

            @can('puede_ver_vehiculo')
            <a href="{{ route('vehiculos.index') }}" @click="open = false"
                class="flex items-center gap-3 text-white/90 hover:text-white px-3 py-2 rounded-md hover:bg-white/10 text-sm font-medium transition-colors">
                <x-icon name="truck" class="w-5 h-5 flex-shrink-0" />
                Vehículos
            </a>
            @endcan

            @can('puede_ver_centro_costo')
            <a href="{{ route('centro_costos.index') }}" @click="open = false"
                class="flex items-center gap-3 text-white/90 hover:text-white px-3 py-2 rounded-md hover:bg-white/10 text-sm font-medium transition-colors">
                <x-icon name="office-building" class="w-5 h-5 flex-shrink-0" />
                Centros de Costo
            </a>
            @endcan

            {{-- SISTEMA --}}
            <div class="text-white/50 text-xs uppercase tracking-wider px-3 py-2 pt-4 font-semibold">Sistema</div>

            @can('puede_ver_roles_y_permisos')
            <a href="{{ route('usuarios.gestion') }}" @click="open = false"
                class="flex items-center gap-3 text-white/90 hover:text-white px-3 py-2 rounded-md hover:bg-white/10 text-sm font-medium transition-colors">
                <x-icon name="shield-check" class="w-5 h-5 flex-shrink-0" />
                Roles y Permisos
            </a>
            @endcan

            @role('admin')
            <a href="{{ route('auditoria.index') }}" @click="open = false"
                class="flex items-center gap-3 text-white/90 hover:text-white px-3 py-2 rounded-md hover:bg-white/10 text-sm font-medium transition-colors">
                <x-icon name="clock" class="w-5 h-5 flex-shrink-0" />
                Auditoría
            </a>
            @endrole

            @can('puede_ver_roles_y_permisos')
            <a href="{{ route('roles.index') }}" @click="open = false"
                class="flex items-center gap-3 text-white/90 hover:text-white px-3 py-2 rounded-md hover:bg-white/10 text-sm font-medium transition-colors">
                <x-icon name="shield-check" class="w-5 h-5 flex-shrink-0" />
                Roles
            </a>
            @endcan
        </nav>

        <div class="border-t border-white/20 p-3">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="flex items-center gap-3 text-white/70 hover:text-white px-3 py-2 rounded-md hover:bg-white/10 text-sm font-medium w-full transition-colors">
                    <x-icon name="logout" class="w-5 h-5 flex-shrink-0" />
                    Cerrar sesión
                </button>
            </form>
        </div>
    </aside>
    @endauth

    {{-- Contenido principal --}}
    <div class="flex-1 flex flex-col min-w-0">
        @auth
        {{-- Header --}}
        <header class="bg-white shadow-sm h-16 flex items-center justify-between px-4 lg:px-6 flex-shrink-0">
            <button @click="open = !open" class="lg:hidden p-2 rounded-md text-gray-600 hover:bg-gray-100 transition-colors"
                aria-label="Abrir menú">
                <x-icon name="menu" class="w-6 h-6" />
            </button>

            <div class="flex items-center gap-3 ml-auto">
                <span class="text-sm text-gray-600 hidden sm:block">{{ auth()->user()->name }}</span>
                <span class="text-xs text-gray-400 hidden sm:block">{{ auth()->user()->email }}</span>
<form action="{{ route('logout') }}" method="POST" class="sm:inline-block">
                    @csrf
                    <button type="submit"
                        class="ml-2 bg-white hover:bg-gray-100 text-gray-700 font-bold py-1.5 px-3 rounded text-sm border border-gray-300 transition-colors">
                        Salir
                    </button>
                </form>
            </div>
        </header>
        @endauth

        {{-- Contenido de página --}}
        <main class="flex-1 overflow-y-auto p-4 lg:p-6">
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
