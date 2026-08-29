<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Ticket extends Model
{
    use HasFactory;

    protected $table = 'tickets';

    protected $fillable = [
        'litros',
        'litros_consumidos',
        'litros_asignados',
        'personal_id',
        'vehiculo_id',
        'tipo_combustible_id',
        'centro_costo_id',
        'recurso_id',
        'emitido_por',
        'finalizado_por',
        'fecha_caducidad',
        'ticket_status_id',
        'activo',
        'hash',
        'numero_automatico'
    ];

    protected $casts = [
        'litros' => 'float',
        'litros_consumidos' => 'float',
        'litros_asignados' => 'float',
        'personal_id' => 'integer',
        'vehiculo_id' => 'integer',
        'tipo_combustible_id' => 'integer',
        'centro_costo_id' => 'integer',
        'recurso_id' => 'integer',
        'emitido_por' => 'integer',
        'finalizado_por' => 'integer',
        'fecha_caducidad' => 'datetime',
        'ticket_status_id' => 'integer',
        'activo' => 'boolean'
    ];

    public function personal()
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

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

    public function emitidoPor()
    {
        return $this->belongsTo(User::class, 'emitido_por');
    }

    public function finalizadoPor()
    {
        return $this->belongsTo(User::class, 'finalizado_por');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {

            $id = rand(1,10000);
            Log::info($id);
            $numeroAutomaticoTicket = str_pad(rand(0, pow(10, 10) - 10), 10, '0', STR_PAD_LEFT);
            $seed = $id . $numeroAutomaticoTicket;
            $ticket->hash = hash('sha256', $seed);
            $ticket->numero_automatico = $numeroAutomaticoTicket;
        });
    }
}
