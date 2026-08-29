<?php
namespace Src\Modules\ServicesProviders;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Src\Modules\ActivityLog\Infrastructure\Listeners\RecordActivityListener;
use Src\Modules\Bolsa\Infrastructure\Listeners\DecreaseLitersListener;
use Src\Modules\Bolsa\Infrastructure\Listeners\IncreaseLitersListener;
use Src\Modules\Bolsa\Infrastructure\Listeners\IncreaseLitersOnTicketDeletedListener;
use Src\Modules\Recurso\Domain\Events\RecursoActivated;
use Src\Modules\Recurso\Domain\Events\RecursoDeactivated;
use Src\Modules\Recurso\Domain\Events\RecursoWasCreated;
use Src\Modules\Recurso\Domain\Events\RecursoWasDeleted;
use Src\Modules\Lote\Domain\Events\LoteWasGenerated;
use Src\Modules\Tickets\Domain\Events\TicketWasConsumed;
use Src\Modules\Tickets\Domain\Events\TicketStatusWasChanged;
use Src\Modules\Tickets\Domain\Events\TicketWasCreated;
use Src\Modules\Tickets\Domain\Events\TicketWasUpdated;
use Src\Modules\Tickets\Domain\Events\TicketWasDeleted;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        RecursoWasCreated::class =>[
            IncreaseLitersListener::class,
            RecordActivityListener::class,
        ],
        RecursoWasDeleted::class=>[
            DecreaseLitersListener::class,
            RecordActivityListener::class,
        ],
        RecursoActivated::class => [
            RecordActivityListener::class,
        ],
        RecursoDeactivated::class => [
            RecordActivityListener::class,
        ],
        TicketWasCreated::class => [
            RecordActivityListener::class,
        ],
        TicketWasDeleted::class => [
            IncreaseLitersOnTicketDeletedListener::class,
            RecordActivityListener::class,
        ],
        TicketWasConsumed::class => [
            RecordActivityListener::class,
        ],
        TicketStatusWasChanged::class => [
            RecordActivityListener::class,
        ],
        TicketWasUpdated::class => [
            RecordActivityListener::class,
        ],
        LoteWasGenerated::class => [
            RecordActivityListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
