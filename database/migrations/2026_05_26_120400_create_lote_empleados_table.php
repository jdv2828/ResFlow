<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lote_empleados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('empleado_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('nombre_completo', 255)->nullable();
            $table->string('dni', 20)->nullable();
            $table->string('patente', 20)->nullable();
            $table->decimal('litros', 12, 5)->default(0);
            $table->integer('cantidad_vales')->default(1);
            $table->dateTime('fecha_caducidad')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_empleados');
    }
};
