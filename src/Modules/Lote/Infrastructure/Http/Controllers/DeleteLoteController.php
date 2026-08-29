<?php

namespace Src\Modules\Lote\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Src\Modules\Lote\Application\Services\DeleteLoteService;

class DeleteLoteController extends Controller
{
    public function __construct(
        private DeleteLoteService $service
    ) {}

    public function __invoke(int $id)
    {
        $this->service->execute($id);

        return redirect()->route('lotes.index')->with('success', 'Lote eliminado exitosamente.');
    }
}
