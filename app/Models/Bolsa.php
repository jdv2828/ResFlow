<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bolsa extends Model
{
    use HasFactory;

    protected $fillable = [
        'centro_costo_id',
        'tipo_combustible_id',
        'recurso_id',
        'cantidad_disponible',
    ];

    protected $casts = [
        'centro_costo_id' => 'integer',
        'tipo_combustible_id' => 'integer',
        'recurso_id' => 'integer',
        'cantidad_disponible' => 'float',
    ];


    public function centroCosto()
    {
        return $this->belongsTo(CentroCosto::class);
    }

    public function tipoCombustible()
    {
        return $this->belongsTo(TipoCombustible::class);
    }

    public function recurso()
    {
        return $this->belongsTo(Recurso::class);
    }
}
