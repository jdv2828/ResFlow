<?php

namespace App\Providers;

use App\Http\Services\BolsaService;
use App\Http\Services\Impl\BolsaServiceImpl;
use Src\Modules\Bolsa\Domain\Contracts\BolsaRepository;
use Src\Modules\Bolsa\Infrastructure\Repositories\BolsaRepositoryImpl;
use Src\Modules\Recurso\Domain\Contracts\RecursoRepository;
use Src\Modules\Recurso\Infrastructure\Repositories\RecursoRepositoryImpl;
use Src\Modules\Lote\Domain\Contracts\LoteRepository;
use Src\Modules\Lote\Infrastructure\Repositories\LoteRepositoryImpl;
use Src\Modules\Tickets\Domain\Contracts\TicketRepository;
use Src\Modules\Tickets\Infrastructure\Repositories\TicketRepositoryImpl;
use Src\Shared\Domain\Contracts\CheckCombustibleInterface;
use Src\Shared\Infrastructure\Services\CheckCombustibleEstacion;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BolsaRepository::class,BolsaRepositoryImpl::class);
        $this->app->bind(TicketRepository::class,TicketRepositoryImpl::class);
        $this->app->bind(LoteRepository::class,LoteRepositoryImpl::class);
        $this->app->bind(RecursoRepository::class,RecursoRepositoryImpl::class);
        $this->app->bind(CheckCombustibleInterface::class,CheckCombustibleEstacion::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
