<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recurso extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_factura',
        'orden',
        'litros',
        'litros_disponibles',
        'litros_inicial',
        'litros_emitidos',
        'litros_consumidos',
        'monto',
        'tipo_combustible_id',
        'centro_costo_id',
        'emitido_por',
        'finalizado_por',
        'activo',
    ];

    protected $casts = [
        'orden' => 'integer',
        'litros' => 'float',
        'litros_disponibles' => 'float',
        'litros_inicial' => 'float',
        'litros_emitidos' => 'float',
        'litros_consumidos' => 'float',
        'monto' => 'float',
        'tipo_combustible_id' => 'integer',
        'centro_costo_id' => 'integer',
        'emitido_por' => 'integer',
        'finalizado_por' => 'integer',
        'activo' => 'boolean',
    ];

    public function tipoCombustible()
    {
        return $this->belongsTo(TipoCombustible::class);
    }

    public function centroCosto()
    {
        return $this->belongsTo(CentroCosto::class);
    }

    public function emitidoPor()
    {
        return $this->belongsTo(User::class, 'emitido_por');
    }

    public function finalizadoPor()
    {
        return $this->belongsTo(User::class, 'finalizado_por');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function bolsa()
    {
        return $this->hasOne(Bolsa::class);
    }
}
