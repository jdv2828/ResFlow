@extends('layouts.layout')
@section('content')
<div class="min-h-screen flex items-center justify-center p-6 bg-gray-100">
    <div class="flex w-full max-w-4xl rounded-xl shadow-2xl overflow-hidden">
    {{-- Left: Brand panel --}}
    <div class="hidden lg:flex lg:w-1/2 bg-brand-blue flex-col items-center justify-center px-12 py-16 text-white">
        <div class="max-w-md text-center">
            <img src="/img/resourceflow-icon.svg" alt="ResourceFlow Logo" class="h-24 w-auto mx-auto mb-6">
            <h1 class="text-4xl font-bold mb-4">ResourceFlow</h1>
            <p class="text-xl text-white/80">Gestión de Recursos</p>
        </div>
    </div>

    {{-- Right: Form panel --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center px-8 py-16 bg-white">
        <div class="w-full max-w-md">
            <div class="text-center lg:hidden mb-8">
                <img src="/img/resourceflow-icon.svg" alt="ResourceFlow Logo" class="h-16 w-auto mx-auto mb-2">
                <h1 class="text-2xl font-bold text-gray-900">ResourceFlow</h1>
                <p class="text-sm text-gray-500">Gestión de Recursos</p>
            </div>

            <h2 class="text-3xl font-extrabold text-gray-900 mb-8">Iniciar Sesión</h2>

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                        class="appearance-none block w-full px-3 py-2.5 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-blue-accent focus:border-brand-blue-accent sm:text-sm"
                        placeholder="correo@ejemplo.com">
                    @error('email')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                        class="appearance-none block w-full px-3 py-2.5 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-blue-accent focus:border-brand-blue-accent sm:text-sm"
                        placeholder="••••••••">
                    @error('password')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 text-brand-blue-accent focus:ring-brand-blue-accent border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-600">Recuérdame</span>
                    </label>
                </div>

                <button type="submit"
                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-brand-blue-accent hover:bg-brand-blue focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-blue-accent">
                    Iniciar sesión
                </button>
            </form>
        </div>
    </div>
</div>
</div>
@endsection
