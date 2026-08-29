<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->decimal('litros', 12, 5);
            $table->decimal('litros_consumidos', 12, 5)->default(0);
            $table->decimal('litros_asignados', 12, 5)->default(0);
            $table->foreignId('empleado_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('vehiculo_id')->nullable()->constrained('vehiculos')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('emitido_por')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('finalizado_por')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('tipo_combustible_id')->nullable()->constrained('tipo_combustibles')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('estacion_servicio_id')->nullable()->constrained('estacion_servicios')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('ticket_status_id')->nullable()->constrained('ticket_statuses')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('expediente_id')->nullable()->constrained('expedientes')->nullOnDelete()->cascadeOnUpdate();
            $table->boolean('activo')->default(true);
            $table->dateTime('fecha_caducidad')->nullable();
            $table->string('hash', 500)->nullable();
            $table->string('numero_automatico', 500)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
