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
        Schema::create('expedientes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_expediente')->unique();
            $table->unsignedInteger('orden')->default(0);
            $table->decimal('litros', 12, 5);
            $table->decimal('litros_disponibles', 12, 5)->default(0);
            $table->decimal('litros_inicial', 12, 5)->default(0);
            $table->decimal('litros_emitidos', 12, 5)->default(0);
            $table->decimal('litros_consumidos', 12, 5)->default(0);
            $table->decimal('monto', 12, 5);
            $table->foreignId('tipo_combustible_id')->nullable()->constrained('tipo_combustibles')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('estacion_servicio_id')->nullable()->constrained('estacion_servicios')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('emitido_por')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreignId('finalizado_por')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('set null');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expedientes');
    }
};
