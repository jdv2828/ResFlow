<?php

namespace Src\Modules\Lote\Domain\Contracts;

use Src\Modules\Lote\Domain\Entities\LoteEntity;

interface LoteRepository
{
    public function save(LoteEntity $lote): LoteEntity;
    public function findById(int $id): ?LoteEntity;
    public function delete(int $id): void;
    public function findWithLoteEmpleados(int $id): ?\App\Models\Lote;
}
