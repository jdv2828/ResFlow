<?php

namespace Src\Modules\Tickets\Domain\ValueObjects;

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

    public function getLitros(): float
    {
        return $this->litros;
    }

    public function equals(LitrosValue $other): bool
    {
        return $this->litros === $other->getLitros();
    }
}
