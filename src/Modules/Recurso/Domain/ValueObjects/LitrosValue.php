<?php

namespace Src\Modules\Recurso\Domain\ValueObjects;

class LitrosValue
{
    private float $litros;

    public function __construct(float $litros)
    {
        if ($litros < 0) {
            throw new \InvalidArgumentException('Los litros no pueden ser negativos');
        }
        $this->litros = $litros;
    }

    public function getLitros():float{
        return $this->litros;
    }
}
