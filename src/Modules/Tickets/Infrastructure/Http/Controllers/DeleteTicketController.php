<?php
namespace Src\Modules\Tickets\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Src\Modules\Tickets\Application\Services\DeleteTicketService;
use Src\Modules\Tickets\Domain\Exceptions\TicketNotFoundException;
use InvalidArgumentException;

class DeleteTicketController extends Controller
{
    public function __construct(
        private DeleteTicketService $deleteTicketService
    ) {}

    public function __invoke(int $id)
    {
        try {
            $this->deleteTicketService->execute($id);

            return redirect()->route('tickets.index')->with('success', 'Ticket eliminado exitosamente.');

        } catch (TicketNotFoundException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error inesperado.'])->withInput();
        }
    }
}
