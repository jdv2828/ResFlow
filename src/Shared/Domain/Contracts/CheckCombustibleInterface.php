<?php
namespace Src\Shared\Domain\Contracts;

interface CheckCombustibleInterface{
    public function getCombustibles(string $centroCosto): array;
    public function tieneCombustibleByIds(int $centroCostoId, int $combustibleId): bool;
}
