<?php

namespace Src\Modules\Bolsa\Infrastructure\Repositories;

use App\Models\Bolsa;
use App\Models\Recurso;
use SebastianBergmann\LinesOfCode\NegativeValueException;
use Src\Modules\Bolsa\Domain\Contracts\BolsaRepository;
use Src\Modules\Bolsa\Domain\Entities\BolsaEntity;

class BolsaRepositoryImpl implements BolsaRepository
{

    public function increaseLiters(BolsaEntity $bolsaEntity): void
    {
        if (!$bolsaEntity->recurso_id) {
            throw new \InvalidArgumentException('La bolsa requiere un recurso para operar.');
        }

        $eloquentBolsa = Bolsa::firstOrCreate(
            ['recurso_id' => $bolsaEntity->recurso_id],
            [
                'tipo_combustible_id' => $bolsaEntity->tipo_combustible_id,
                'centro_costo_id' => $bolsaEntity->centro_costo_id,
                'cantidad_disponible' => 0,
            ]
        );

        $eloquentBolsa->cantidad_disponible += $bolsaEntity->cantidad_disponible->getLitros();
        $eloquentBolsa->save();
    }

    public function decreaseLiters(BolsaEntity $bolsaEntity): void
    {
        if (!$bolsaEntity->recurso_id) {
            throw new \InvalidArgumentException('La bolsa requiere un recurso para operar.');
        }

        $eloquentBolsa = Bolsa::where('recurso_id', $bolsaEntity->recurso_id)
            ->first();

        if (!$eloquentBolsa) {
            throw new \InvalidArgumentException('No se encontro la bolsa del recurso indicado.');
        }

        if(($eloquentBolsa->cantidad_disponible -= $bolsaEntity->cantidad_disponible->getLitros())<0)
            throw new NegativeValueException('El valor final de la bolsa no puede ser menor que 0.');
        $eloquentBolsa->save();
    }

    public function findCurrentRecursoId(int $centroCostoId, int $tipoCombustibleId): ?int
    {
        return Recurso::query()
            ->where('activo', true)
            ->whereNull('finalizado_por')
            ->where('centro_costo_id', $centroCostoId)
            ->where('tipo_combustible_id', $tipoCombustibleId)
            ->orderBy('orden')
            ->orderBy('id')
            ->value('id');
    }
}
