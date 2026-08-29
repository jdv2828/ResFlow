<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCombustible extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'centro_costo_id',
        'categoria',
    ];

    protected $casts = [
        'categoria' => 'string',
    ];

    public function recursos()
    {
        return $this->hasMany(Recurso::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function centroCosto()
    {
        return $this->belongsTo(CentroCosto::class);
    }
}
