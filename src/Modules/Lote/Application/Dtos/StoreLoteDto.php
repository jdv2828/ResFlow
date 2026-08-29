<?php

namespace Src\Modules\Lote\Application\Dtos;

class StoreLoteDto
{
    public function __construct(
        public readonly ?string $nombre = null,
        public readonly ?int $responsable_id = null,
        public readonly ?int $centro_costo_id = null,
        public readonly ?int $tipo_combustible_id = null,
        public readonly array $empleados = []
    ) {}
}
