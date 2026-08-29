<?php

namespace Src\Modules\Recurso\Domain\Contracts;

use Src\Modules\Recurso\Domain\Entities\RecursoEntity;

interface RecursoRepository
{
    public function findByNumeroRecurso(string $nroFactura): ?RecursoEntity;

    public function save(RecursoEntity $recurso): void;

    public function findById(string $id) :?RecursoEntity;

    public function delete(int $id): void;

    public function update(RecursoEntity $recurso): void;

    public function getNextOrderForStationAndFuel(int $centroCostoId, int $tipoCombustibleId): int;

    /** @return \App\Models\Recurso[] */
    public function findLockForUpdate(int $id): ?\App\Models\Recurso;

    /** @return \Illuminate\Support\Collection|\App\Models\Recurso[] */
    public function findActiveByCentroAndFuelForUpdate(int $centroCostoId, int $tipoCombustibleId);
}
