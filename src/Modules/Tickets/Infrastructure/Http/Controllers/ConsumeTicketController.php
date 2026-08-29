<?php
namespace Src\Modules\Tickets\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConsumeTicketRequest;
use Illuminate\Support\Facades\Log;
use Src\Modules\Tickets\Application\Dtos\ConsumeTicketDto;
use Src\Modules\Tickets\Application\Services\ConsumeTicketService;
use Src\Modules\Tickets\Domain\Exceptions\TicketNotFoundException;
use Src\Modules\Tickets\Domain\Exceptions\InsufficientLitrosException;
use InvalidArgumentException;

class ConsumeTicketController extends Controller
{
    public function __construct(
        private ConsumeTicketService $consumeTicketService
    ) {}

    public function __invoke(ConsumeTicketRequest $request, $id)
    {
        try {
            $consumeTicketDto = new ConsumeTicketDto(
                $id,
                $request->litros_consumidos
            );

            $this->consumeTicketService->execute($consumeTicketDto);

            return redirect()->back()->with('success', 'Litros consumidos exitosamente.');

        } catch (TicketNotFoundException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        } catch (InsufficientLitrosException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error inesperado.'])->withInput();
        }
    }
}
