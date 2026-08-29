<?php
namespace Src\Modules\Recurso\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Src\Modules\Recurso\Application\Services\DeleteRecursoService;
use Src\Modules\Recurso\Domain\Exceptions\RecursoNotFoundException;

class DeleteRecursoController extends Controller
{
    public function __construct(
        private DeleteRecursoService $deleteRecursoService
    ) {}

    public function __invoke(int $id)
    {
        try {

            $this->deleteRecursoService->exectute($id);

            return redirect()->route('recursos.index')->with('success', 'Recurso borrado exitosamente.');

        } catch (RecursoNotFoundException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error inesperado.'])->withInput();
        }
    }
}

