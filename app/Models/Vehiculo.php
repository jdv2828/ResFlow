<?php

namespace App\Models;

use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory, ActivityLoggable;

    protected $fillable = [
        'marca',
        'modelo',
        'anio',
        'color',
        'patente',
        'foto',
        'numero_identificacion',
        'id_chofer',
        'id_centro_costo',
    ];

    protected $casts = [
        'id_chofer' => 'integer',
        'id_centro_costo' => 'integer',
    ];

    public function chofer()
    {
        return $this->belongsTo(Personal::class, 'id_chofer');
    }

    public function centroCosto()
    {
        return $this->belongsTo(CentroCosto::class, 'id_centro_costo');
    }
}
