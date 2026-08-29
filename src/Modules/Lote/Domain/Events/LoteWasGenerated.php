<?php

namespace Src\Modules\Lote\Domain\Events;

use Src\Modules\Lote\Domain\Entities\LoteEntity;

class LoteWasGenerated
{
    public function __construct(
        public readonly LoteEntity $lote,
        public readonly array $ticketIds = []
    ) {}
}
