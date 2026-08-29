<?php

namespace App\Models;

use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    use HasFactory, ActivityLoggable;

    protected $table = 'personal';

    protected $fillable = ['nombre', 'apellido', 'legajo', 'dni', 'foto', 'email', 'telefono', 'centro_costo_id', 'cargo_id'];

    protected $appends = ['nombre_completo'];

    public function getNombreCompletoAttribute()
    {
        return trim($this->nombre . ' ' . $this->apellido);
    }

    public function centroCosto()
    {
        return $this->belongsTo(CentroCosto::class);
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function vehiculosAsChofer()
    {
        return $this->hasMany(Vehiculo::class, 'id_chofer');
    }
}
