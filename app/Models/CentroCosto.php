<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentroCosto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'ubicacion',
        'descripcion',
        'centro_padre_id',
    ];

    protected $casts = [
        'centro_padre_id' => 'integer',
    ];

    public function centroPadre()
    {
        return $this->belongsTo(CentroCosto::class, 'centro_padre_id');
    }

    public function centroHijos()
    {
        return $this->hasMany(CentroCosto::class, 'centro_padre_id');
    }

    public function empleados()
    {
        return $this->hasMany(Personal::class, 'centro_costo_id');
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class, 'centro_costo_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'centro_costo_id');
    }

    public function bolsas()
    {
        return $this->hasMany(Bolsa::class, 'centro_costo_id');
    }

    public function recursos()
    {
        return $this->hasMany(Recurso::class, 'centro_costo_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_centro_costo');
    }

    public function tipoCombustibles()
    {
        return $this->hasMany(TipoCombustible::class, 'centro_costo_id');
    }

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'id_centro_costo');
    }
}
