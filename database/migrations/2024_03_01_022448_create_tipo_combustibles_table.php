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
        Schema::create('tipo_combustibles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->foreignId('estacion_servicio_id')->nullable()->constrained('estacion_servicios')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**s
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_combustibles');
    }
};
