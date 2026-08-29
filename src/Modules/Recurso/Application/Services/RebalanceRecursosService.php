<?php

namespace Src\Modules\Recurso\Application\Services;

use App\Models\Recurso;
use Illuminate\Support\Facades\DB;
use Src\Modules\Recurso\Domain\Contracts\RecursoRepository;
use Src\Modules\Recurso\Domain\Events\RecursoActivated;
use Src\Modules\Recurso\Domain\Events\RecursoDeactivated;

class RebalanceRecursosService
{
    public function __construct(
        private RecursoRepository $recursoRepository,
    ) {}

    public function executeByStationAndFuel(int $centroCostoId, int $tipoCombustibleId): void
    {
        DB::transaction(function () use ($centroCostoId, $tipoCombustibleId) {
            $recursos = $this->recursoRepository->findActiveByCentroAndFuelForUpdate($centroCostoId, $tipoCombustibleId);

            $activeRecursoId = $recursos
                ->first(fn (Recurso $recurso) => $recurso->litros_disponibles > 0)
                ?->id;

            foreach ($recursos as $recurso) {
                $shouldBeActive = $recurso->id === $activeRecursoId;

                if ($recurso->activo !== $shouldBeActive) {
                    $recurso->activo = $shouldBeActive;
                    $recurso->save();

                    event($shouldBeActive
                        ? new RecursoActivated(
                            $recurso->id,
                            $recurso->numero_factura,
                            (float) $recurso->litros_disponibles,
                            $recurso->tipo_combustible_id,
                            $recurso->centro_costo_id,
                        )
                        : new RecursoDeactivated(
                            $recurso->id,
                            $recurso->numero_factura,
                            (float) $recurso->litros_disponibles,
                            $recurso->tipo_combustible_id,
                            $recurso->centro_costo_id,
                        ));
                }
            }
        });
    }

    public function executeByRecursoId(int $recursoId): void
    {
        $recurso = $this->recursoRepository->findLockForUpdate($recursoId);

        if (!$recurso) {
            return;
        }

        $this->executeByStationAndFuel(
            $recurso->centro_costo_id,
            $recurso->tipo_combustible_id,
        );
    }
}
