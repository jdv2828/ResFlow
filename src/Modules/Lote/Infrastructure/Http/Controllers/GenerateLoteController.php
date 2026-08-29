<?php

namespace Src\Modules\Lote\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Src\Modules\Lote\Application\Services\GenerateLoteTicketsService;
use Src\Modules\Recurso\Domain\Exceptions\CombustibleNotFoundInEstacion;

class GenerateLoteController extends Controller
{
    public function __construct(
        private GenerateLoteTicketsService $service
    ) {}

    public function __invoke(int $id)
    {
        try {
            $result = $this->service->execute($id);
            $ticketIds = $result['ticket_ids'] ?? [];
            $skippedCount = $result['skipped_count'] ?? 0;

            $message = 'Lote generado exitosamente. Se crearon ' . count($ticketIds) . ' tickets.';
            if ($skippedCount > 0) {
                $message .= ' ' . $skippedCount . ' empleado(s) manual(es) omitido(s) (sin registro en el sistema).';
            }

            return redirect()
                ->route('lotes.index')
                ->with('success', $message);
        } catch (CombustibleNotFoundInEstacion $e) {
            return redirect()
                ->route('lotes.index')
                ->with('error', 'El combustible no está disponible en el centro de costo del lote. Verificá que exista una bolsa activa para ese combustible.');
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('lotes.index')
                ->with('error', $e->getMessage());
        }
    }
}
