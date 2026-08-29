<?php
namespace Src\Modules\Tickets\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use Illuminate\Support\Facades\Log;
use Src\Modules\Tickets\Application\Dtos\StoreTicketDto;
use Src\Modules\Tickets\Application\Services\StoreTicketService;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Src\Modules\Recurso\Domain\Exceptions\CombustibleNotFoundInEstacion;

class StoreTicketController extends Controller
{
    public function __construct(
        private StoreTicketService $storeTicketService
    ) {}

    public function __invoke(StoreTicketRequest $request)
    {
        try {
            $storeTicketDto = new StoreTicketDto(
                litros: $request->litros,
                personal_id: $request->personal_id,
                tipo_combustible_id: $request->tipo_combustible_id,
                centro_costo_id: $request->centro_costo_id,
                emitido_por: auth()->user()->id,
                fecha_caducidad: $request->fecha_caducidad
            );

            $this->storeTicketService->execute($storeTicketDto);

            return redirect()->route('tickets.index')->with('success', 'Ticket creado exitosamente.');

        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        } catch (CombustibleNotFoundInEstacion $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        } catch (\Exception $e) {
            Log::error('Error inesperado',[$e]);
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error inesperado.'])->withInput();
        }
    }
}
