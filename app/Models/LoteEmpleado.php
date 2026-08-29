<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoteEmpleado extends Model
{
    use HasFactory;

    protected $fillable = [
        'lote_id',
        'personal_id',
        'nombre_completo',
        'dni',
        'patente',
        'litros',
        'cantidad_vales',
        'fecha_caducidad',
    ];

    protected $casts = [
        'litros' => 'float',
        'cantidad_vales' => 'integer',
        'fecha_caducidad' => 'datetime',
    ];

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function personal()
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }
}
