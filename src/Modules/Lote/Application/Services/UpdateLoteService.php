<?php

namespace Src\Modules\Lote\Application\Services;

use Illuminate\Support\Facades\DB;
use Src\Modules\Lote\Application\Dtos\StoreLoteDto;
use Src\Modules\Lote\Domain\Contracts\LoteRepository;
use Src\Modules\Lote\Domain\Entities\LoteEntity;

class UpdateLoteService
{
    public function __construct(
        private LoteRepository $repository
    ) {}

    public function execute(int $id, StoreLoteDto $dto): LoteEntity
    {
        return DB::transaction(function () use ($id, $dto) {
            $loteEntity = new LoteEntity(
                id: $id,
                nombre: $dto->nombre,
                responsable_id: $dto->responsable_id,
                centro_costo_id: $dto->centro_costo_id,
                tipo_combustible_id: $dto->tipo_combustible_id,
                activo: true,
                empleados: $dto->empleados,
            );

            return $this->repository->save($loteEntity);
        });
    }
}
