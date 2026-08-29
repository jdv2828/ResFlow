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
        Schema::dropIfExists('user_estacion_servicio');

        Schema::create('user_centro_costo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('centro_costo_id')->nullable()->constrained('centro_costos')->cascadeOnUpdate()->nullOnDelete();
            $table->unique(['user_id', 'centro_costo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_centro_costo');

        Schema::create('user_estacion_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estacion_servicio_id')->nullable()->constrained('estacion_servicios')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->unique(['user_id', 'estacion_servicio_id']);
        });
    }
};
