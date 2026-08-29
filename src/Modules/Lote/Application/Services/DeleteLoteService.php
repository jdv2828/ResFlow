<?php

namespace Src\Modules\Lote\Application\Services;

use Illuminate\Support\Facades\DB;
use Src\Modules\Lote\Domain\Contracts\LoteRepository;

class DeleteLoteService
{
    public function __construct(
        private LoteRepository $repository
    ) {}

    public function execute(int $id): void
    {
        DB::transaction(function () use ($id) {
            $this->repository->delete($id);
        });
    }
}
