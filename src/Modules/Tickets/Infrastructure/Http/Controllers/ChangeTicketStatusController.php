<?php
namespace Src\Modules\Tickets\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeTicketStatusRequest;
use Illuminate\Support\Facades\Log;
use Src\Modules\Tickets\Application\Services\ChangeTicketStatusService;
use Src\Modules\Tickets\Domain\Exceptions\TicketNotFoundException;
use InvalidArgumentException;

class ChangeTicketStatusController extends Controller
{
    public function __construct(
        private ChangeTicketStatusService $changeTicketStatusService
    ) {}

    public function markAsInProcess(ChangeTicketStatusRequest $request)
    {
        try {
            $this->changeTicketStatusService->markAsInProcess($request->hash);

            return redirect()->route('tickets.index')->with('success', 'Ticket marcado como en proceso.');

        } catch (TicketNotFoundException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error inesperado.'])->withInput();
        }
    }

    public function markAsPending(ChangeTicketStatusRequest $request)
    {
        try {
            $this->changeTicketStatusService->markAsPending($request->hash);

            return redirect()->route('tickets.index')->with('success', 'Ticket marcado como pendiente.');

        } catch (TicketNotFoundException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error inesperado.'])->withInput();
        }
    }
}
