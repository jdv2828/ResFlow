@extends('layouts.layout')

@section('content')
    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            @auth
                <div>
                    <h2 class="text-center text-3xl font-extrabold text-gray-900">
                        Bienvenido
                    </h2>
                    <h2 class="text-center text-3xl font-extrabold text-gray-900">{{ auth()->user()->name }}</h2>
                    <p class="text-center text-gray-500">
                        Tu correo electrónico es {{ auth()->user()->email }}
                    </p>
                </div>
            @else
                <div>
                    <div class="text-center">
                        <img class="h-36 w-auto mx-auto" src="/img/resourceflow-icon.svg" alt="Logo">
                    </div>
                </div>
                <div>
                    <h2 class="text-center text-3xl font-extrabold text-gray-900">
                        ResFlow — Gestión de Recursos
                    </h2>
                </div>
                <div class="space-y-4">
                    <a href="{{ route('login') }}" class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-brand-blue-accent hover:bg-brand-blue focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-blue-accent">
                        Iniciar sesión
                    </a>
                </div>
            @endauth
        </div>
    </div>
@endsection
