<?php
namespace App\Http\Services;

interface BolsaService
{
    public function addLitersBolsa(int $tipo_combustible_id, int $centro_costo_id, float $litros);
}
