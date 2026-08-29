<?php

namespace App\Models;

use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    use HasFactory, ActivityLoggable;

    protected $fillable = [
        'nombre',
        'centro_costo_id',
        'responsable_id',
        'tipo_combustible_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function centroCosto()
    {
        return $this->belongsTo(CentroCosto::class);
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function tipoCombustible()
    {
        return $this->belongsTo(TipoCombustible::class);
    }

    public function loteEmpleados()
    {
        return $this->hasMany(LoteEmpleado::class);
    }
}
