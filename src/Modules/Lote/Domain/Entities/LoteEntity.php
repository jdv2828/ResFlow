<?php

namespace Src\Modules\Lote\Domain\Entities;

class LoteEntity
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $nombre = null,
        public readonly ?int $responsable_id = null,
        public readonly ?int $centro_costo_id = null,
        public readonly ?int $tipo_combustible_id = null,
        public readonly bool $activo = true,
        public array $empleados = []
    ) {}
}
