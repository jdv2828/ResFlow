<?php

namespace Src\Modules\Tickets\Domain\ValueObjects;

class TicketStatusValue
{
    private int $value;

    public const GENERADO = 1;
    public const FINALIZADO = 2;
    public const VENCIDO = 3;
    public const UTILIZADO = 4;
    public const ANULADO = 5;

    public function __construct(int $value)
    {
        if (!in_array($value, [self::GENERADO, self::FINALIZADO, self::VENCIDO, self::UTILIZADO, self::ANULADO])) {
            throw new \InvalidArgumentException('Estado de ticket inválido');
        }
        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(TicketStatusValue $other): bool
    {
        return $this->value === $other->getValue();
    }

    public function isActive(): bool
    {
        return in_array($this->value, [self::GENERADO]);
    }
}
