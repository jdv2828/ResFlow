<?php

namespace Src\Modules\Tickets\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTicketRequest;
use Illuminate\Support\Facades\Log;
use Src\Modules\Tickets\Application\Dtos\UpdateTicketDto;
use Src\Modules\Tickets\Application\Services\UpdateTicketService;
use InvalidArgumentException;
use Src\Modules\Tickets\Domain\Exceptions\TicketNotFoundException;

class UpdateTicketController extends Controller
{
    public function __construct(
        private UpdateTicketService $updateTicketService
    ) {}

    public function __invoke(UpdateTicketRequest $request, int $id)
    {
        try {
            $dto = new UpdateTicketDto(
                id: $id,
                litros: $request->litros,
                personal_id: $request->personal_id,
                tipo_combustible_id: $request->tipo_combustible_id,
                centro_costo_id: $request->centro_costo_id,
                fecha_caducidad: $request->fecha_caducidad,
            );

            $this->updateTicketService->execute($dto);

            return redirect()->route('tickets.index')->with('success', 'Ticket actualizado exitosamente.');

        } catch (TicketNotFoundException $e) {
            return redirect()->route('tickets.index')->withErrors(['error' => 'Ticket no encontrado.']);
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        } catch (\Exception $e) {
            Log::error('Error al actualizar ticket', [$e]);
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error inesperado.'])->withInput();
        }
    }
}
