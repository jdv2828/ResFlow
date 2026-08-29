<?php

namespace Src\Modules\Recurso\Domain\ValueObjects;

class MontoValue
{
    private float $monto;

    public function __construct(float $monto)
    {
        if ($monto < 0) {
            throw new \InvalidArgumentException('El monto no puede ser negativo');
        }
        $this->monto = $monto;
    }

    public function getMonto(): float
    {
        return $this->monto;
    }
}
