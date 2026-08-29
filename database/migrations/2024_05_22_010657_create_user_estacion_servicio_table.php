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
        Schema::create('user_estacion_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estacion_servicio_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('set null');
            $table->unique(['user_id', 'estacion_servicio_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_estacion_servicio', function (Blueprint $table) {
            $table->dropForeign(['estacion_servicio_id']); // Asegura que el nombre es correcto
            $table->dropForeign(['user_id']); // Asegura que el nombre es correcto
        });

        Schema::dropIfExists('user_estacion_servicio');
    }
};
