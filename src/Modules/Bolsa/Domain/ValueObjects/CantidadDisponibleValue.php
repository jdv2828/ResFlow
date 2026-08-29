<?php

namespace Src\Modules\Bolsa\Domain\ValueObjects;

class CantidadDisponibleValue
{
    private float $litros;

    public function __construct(float $litros)
    {
        if ($litros < 0) {
            throw new \InvalidArgumentException('La cantidad de litros no puede ser negativa');
        }
        $this->litros = $litros;
    }

    public function getLitros():float{
        return $this->litros;
    }
}
