<?php
namespace Src\Shared\Infrastructure\Services;

use Illuminate\Support\Facades\DB;
use Src\Shared\Domain\Contracts\CheckCombustibleInterface;

final class CheckCombustibleEstacion implements CheckCombustibleInterface
{
    public function getCombustibles(string $centroCosto): array
    {
        return DB::table('tipo_combustibles')
            ->join('centro_costos', 'centro_costos.id', '=', 'tipo_combustibles.centro_costo_id')
            ->where('centro_costos.nombre', $centroCosto)
            ->pluck('tipo_combustibles.nombre')
            ->all();
    }

    public function tieneCombustibleByIds(int $centroCostoId, int $combustibleId): bool
    {
        return DB::table('tipo_combustibles')
            ->where('id', $combustibleId)
            ->where('centro_costo_id', $centroCostoId)
            ->exists();
    }
}
