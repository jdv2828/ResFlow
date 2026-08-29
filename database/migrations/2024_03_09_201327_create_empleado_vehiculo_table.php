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
        Schema::create('empleado_vehiculo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('set null');
            $table->foreignId('vehiculo_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('set null');
            $table->unique(['empleado_id', 'vehiculo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleado_vehiculo');
    }
};
