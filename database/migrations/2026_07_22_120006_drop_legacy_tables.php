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
        Schema::dropIfExists('empleado_vehiculo');
        Schema::dropIfExists('expedientes');
        Schema::dropIfExists('estacion_servicios');
        Schema::dropIfExists('areas');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->foreignId('area_padre_id')->nullable()->constrained('areas')->nullOnDelete()->cascadeOnUpdate();
            $table->string('responsable')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });

        Schema::create('estacion_servicios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
        });

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

        Schema::create('empleado_vehiculo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('set null');
            $table->foreignId('vehiculo_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('set null');
            $table->unique(['empleado_id', 'vehiculo_id']);
        });
    }
};
