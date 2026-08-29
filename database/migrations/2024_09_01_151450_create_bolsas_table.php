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
        Schema::create('bolsas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estacion_servicio_id')->nullable()->constrained('estacion_servicios')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('tipo_combustible_id')->nullable()->constrained('tipo_combustibles')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('expediente_id')->nullable()->constrained('expedientes')->nullOnDelete()->cascadeOnUpdate();
            $table->unique('expediente_id');
            $table->decimal('cantidad_disponible', 12, 5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bolsas');
    }
};
