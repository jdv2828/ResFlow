<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;
use App\Models\Recurso;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Src\Modules\Recurso\Application\Services\RebalanceRecursosService;
use Src\Modules\Bolsa\Application\Services\IncreaseLitersService;
use Src\Modules\Bolsa\Application\Dtos\FuelLitersDto;

class CheckTicketExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:check-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if any ticket has expired and update its status';

    /**
     * Create a new command instance.
     */
    public function __construct(
        private IncreaseLitersService $increaseLitersService,
        private RebalanceRecursosService $rebalanceRecursosService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $tickets = Ticket::where('fecha_caducidad', '<=', $now)
            ->where('activo', 1)
            ->get();

        foreach ($tickets as $ticket) {
            DB::transaction(function () use ($ticket) {
                $ticket->update([
                    'activo' => 0,
                    'ticket_status_id' => 3,
                    'finalizado_por' => null
                ]);

                if (!$ticket->recurso_id) {
                    return;
                }

                $recurso = Recurso::query()
                    ->whereKey($ticket->recurso_id)
                    ->lockForUpdate()
                    ->first();

                if ($recurso && is_null($recurso->finalizado_por)) {
                    $recurso->litros_disponibles += $ticket->litros;
                    $recurso->litros_emitidos = max(0, $recurso->litros_emitidos - $ticket->litros);
                    $recurso->save();
                }

                $dto = new FuelLitersDto(
                    $ticket->litros,
                    $ticket->tipo_combustible_id,
                    $ticket->centro_costo_id,
                    $ticket->recurso_id
                );

                $this->increaseLitersService->execute($dto);

                ActivityLog::create([
                    'user_id' => null,
                    'action' => 'ticket.expired',
                    'subject_type' => $ticket->getMorphClass(),
                    'subject_id' => $ticket->id,
                    'data' => [
                        'litros_devueltos' => $ticket->litros,
                        'tipo_combustible_id' => $ticket->tipo_combustible_id,
                        'centro_costo_id' => $ticket->centro_costo_id,
                        'recurso_id' => $ticket->recurso_id,
                    ],
                ]);

                if ($recurso) {
                    $this->rebalanceRecursosService->executeByStationAndFuel(
                        $recurso->centro_costo_id,
                        $recurso->tipo_combustible_id,
                    );
                }
            });
        }

        $this->info("{$tickets->count()} tickets expired have been updated and liters returned to bolsa.");
    }
}
