<?php

namespace Src\Modules\Recurso\Infrastructure\Repositories;

use App\Models\Recurso as EloquentRecurso;
use Src\Modules\Recurso\Domain\Contracts\RecursoRepository;
use Src\Modules\Recurso\Domain\Entities\RecursoEntity;
use Src\Modules\Recurso\Domain\Exceptions\RecursoNotFoundException;
use Src\Modules\Recurso\Domain\ValueObjects\LitrosValue;
use Src\Modules\Recurso\Domain\ValueObjects\MontoValue;
use Illuminate\Support\Facades\DB;

class RecursoRepositoryImpl implements RecursoRepository
{
    public function findByNumeroRecurso(string $nroFactura): ?RecursoEntity
    {
        $recurso = DB::table('recursos')->where('numero_factura', $nroFactura)->first();

        if (!$recurso) {
            return null;
        }

        return new RecursoEntity(
            $recurso->id,
            new LitrosValue($recurso->litros),
            new LitrosValue($recurso->litros_disponibles ?? $recurso->litros),
            new MontoValue($recurso->monto),
            $recurso->tipo_combustible_id,
            $recurso->centro_costo_id,
            $recurso->numero_factura,
            $recurso->orden ?? 0,
            $recurso->emitido_por,
            $recurso->finalizado_por,
            $recurso->activo,
        );
    }

    public function save(RecursoEntity $recurso): void
    {
        $eloquentRecurso = EloquentRecurso::query()
            ->where('numero_factura', $recurso->numero_factura)
            ->first() ?? new EloquentRecurso();

        $isNew = !$eloquentRecurso->exists;

        $eloquentRecurso->litros = $recurso->litros->getLitros();
        $eloquentRecurso->litros_disponibles = $recurso->litros_disponibles->getLitros();
        $eloquentRecurso->monto = $recurso->monto->getMonto();
        $eloquentRecurso->tipo_combustible_id = $recurso->tipo_combustible_id;
        $eloquentRecurso->centro_costo_id = $recurso->centro_costo_id;
        $eloquentRecurso->numero_factura = $recurso->numero_factura;
        $eloquentRecurso->orden = $recurso->orden;
        $eloquentRecurso->emitido_por = $recurso->emitido_por;
        $eloquentRecurso->finalizado_por = $recurso->finalizado_por;
        $eloquentRecurso->activo = $recurso->activo;

        if ($isNew) {
            $eloquentRecurso->litros_inicial = $recurso->litros->getLitros();
            $eloquentRecurso->litros_emitidos = 0;
            $eloquentRecurso->litros_consumidos = 0;
        }

        $eloquentRecurso->save();

        $recurso->id = $eloquentRecurso->id;
    }

    public function delete(int $id): void
    {
        $eloquentRecurso = EloquentRecurso::find($id);

        if (!$eloquentRecurso) {
            throw new RecursoNotFoundException();
        }

        $eloquentRecurso->finalizado_por = auth()->user()->id;
        $eloquentRecurso->emitido_por = null;
        $eloquentRecurso->activo = false;

        $eloquentRecurso->delete();
    }

    public function findById(string $id): ?RecursoEntity
    {
        $recurso = DB::table('recursos')->where('id', $id)->first();

        if (!$recurso) {
            return null;
        }

        return new RecursoEntity(
            $recurso->id,
            new LitrosValue($recurso->litros),
            new LitrosValue($recurso->litros_disponibles ?? $recurso->litros),
            new MontoValue($recurso->monto),
            $recurso->tipo_combustible_id,
            $recurso->centro_costo_id,
            $recurso->numero_factura,
            $recurso->orden ?? 0,
            $recurso->emitido_por,
            $recurso->finalizado_por,
            $recurso->activo,
        );

    }

    public function update(RecursoEntity $recurso): void
    {
        $eloquentRecurso = EloquentRecurso::find($recurso->id);

        if (!$eloquentRecurso) {
            throw new RecursoNotFoundException();
        }

        $eloquentRecurso->litros = $recurso->litros->getLitros();
        $eloquentRecurso->litros_disponibles = $recurso->litros_disponibles->getLitros();
        $eloquentRecurso->monto = $recurso->monto->getMonto();
        $eloquentRecurso->tipo_combustible_id = $recurso->tipo_combustible_id;
        $eloquentRecurso->centro_costo_id = $recurso->centro_costo_id;
        $eloquentRecurso->numero_factura = $recurso->numero_factura;
        $eloquentRecurso->orden = $recurso->orden;
        $eloquentRecurso->emitido_por = $recurso->emitido_por;
        $eloquentRecurso->finalizado_por = $recurso->finalizado_por;
        $eloquentRecurso->activo = $recurso->activo;

        $eloquentRecurso->save();
    }

    public function getNextOrderForStationAndFuel(int $centroCostoId, int $tipoCombustibleId): int
    {
        $maxOrder = EloquentRecurso::query()
            ->where('centro_costo_id', $centroCostoId)
            ->where('tipo_combustible_id', $tipoCombustibleId)
            ->max('orden');

        return ((int) $maxOrder) + 1;
    }

    public function findLockForUpdate(int $id): ?\App\Models\Recurso
    {
        return EloquentRecurso::query()
            ->whereKey($id)
            ->lockForUpdate()
            ->first();
    }

    public function findActiveByCentroAndFuelForUpdate(int $centroCostoId, int $tipoCombustibleId)
    {
        return EloquentRecurso::query()
            ->where('centro_costo_id', $centroCostoId)
            ->where('tipo_combustible_id', $tipoCombustibleId)
            ->whereNull('finalizado_por')
            ->orderBy('orden')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }
}
