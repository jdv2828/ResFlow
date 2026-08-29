<?php

namespace Src\Modules\Recurso\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Src\Modules\Recurso\Domain\Entities\RecursoEntity;

class RecursoWasCreated
{
    use Dispatchable, SerializesModels;

    public $recursoEntity;

    public function __construct(RecursoEntity $recursoEntity)
    {
        $this->recursoEntity = $recursoEntity;
    }
}
