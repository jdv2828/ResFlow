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
        Schema::create('movement_histories', function (Blueprint $table) {
            $table->id();
            $table->decimal('litros',12,5)->nullable();
            $table->string('topic')->nullable();
            $table->string('empleado_id')->nullable();
            $table->string('empleado')->nullable();
            $table->string('usuario_gestor_id')->nullable();
            $table->string('estacion_servicio')->nullable();
            $table->string('tipo_combustible')->nullable();
            $table->string('estacion_servicio_id')->nullable();
            $table->string('tipo_combustible_id')->nullable();
            $table->string('ticket_id')->nullable();
            $table->string('estado_id')->nullable();
            $table->string('estado_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movement_histories');
    }
};
