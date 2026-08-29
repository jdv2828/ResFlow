<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        view()->composer('*', function ($view) {
            $view->with('formatNumber', function ($number) {
                return number_format($number, 2, ',', '.');
            });

            $view->with('tieneDecimales', function ($numero) {
                // Convertir el número a un float para asegurarnos de que es tratado como un número
                $numero = (float) $numero;

                // Comparar el número original con su versión redondeada hacia abajo
                if ($numero != floor($numero)) {
                    return true; // Tiene decimales
                } else {
                    return false; // No tiene decimales
                }
            });
        });
    }
}
