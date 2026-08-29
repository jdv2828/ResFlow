<?php

namespace Src\Modules\Lote\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Src\Modules\Lote\Application\Dtos\StoreLoteDto;
use Src\Modules\Lote\Application\Services\UpdateLoteService;

class UpdateLoteController extends Controller
{
    public function __construct(
        private UpdateLoteService $service
    ) {}

    public function __invoke(Request $request, int $id)
    {
        $validated = $request->validate([
            'nombre' => 'nullable|string|max:255',
            'centro_costo_id' => 'required|integer|exists:centro_costos,id',
            'responsable_id' => 'nullable|integer|exists:users,id',
            'tipo_combustible_id' => 'required|integer|exists:tipo_combustibles,id',
            'empleados' => 'required|array|min:1',
            'empleados.*.personal_id' => 'nullable|integer|exists:personal,id',
            'empleados.*.nombre_completo' => 'nullable|string|max:255',
            'empleados.*.dni' => 'nullable|string|max:20',
            'empleados.*.patente' => 'nullable|string|max:20',
            'empleados.*.litros' => 'required|numeric|min:0',
            'empleados.*.cantidad_vales' => 'required|integer|min:1',
            'empleados.*.fecha_caducidad' => 'nullable|date',
        ]);

        $dto = new StoreLoteDto(
            nombre: $validated['nombre'] ?? null,
            responsable_id: $validated['responsable_id'] ?? null,
            centro_costo_id: $validated['centro_costo_id'],
            tipo_combustible_id: $validated['tipo_combustible_id'],
            empleados: $validated['empleados'],
        );

        $this->service->execute($id, $dto);

        return redirect()->route('lotes.index')->with('success', 'Lote actualizado exitosamente.');
    }
}
