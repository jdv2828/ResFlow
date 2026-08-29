<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                $table->dropForeign(['area_id']);
            }

            $table->dropColumn('area_id');
        });

        Schema::table('empleados', function (Blueprint $table) {
            $table->foreignId('centro_costo_id')->nullable()->constrained('centro_costos')->nullOnDelete()->cascadeOnUpdate();
        });

        Schema::table('lotes', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                $table->dropForeign(['area_id']);
                $table->dropForeign(['estacion_servicio_id']);
            }

            $table->dropColumn(['area_id', 'estacion_servicio_id']);
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->foreignId('centro_costo_id')->nullable()->constrained('centro_costos')->nullOnDelete()->cascadeOnUpdate();
        });

        Schema::table('tipo_combustibles', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                $table->dropForeign(['estacion_servicio_id']);
            }

            $table->dropColumn('estacion_servicio_id');
        });

        Schema::table('tipo_combustibles', function (Blueprint $table) {
            $table->foreignId('centro_costo_id')->nullable()->constrained('centro_costos')->nullOnDelete()->cascadeOnUpdate();
        });

        Schema::table('tickets', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                $table->dropForeign(['estacion_servicio_id']);
                $table->dropForeign(['expediente_id']);
            }

            $table->dropColumn(['estacion_servicio_id', 'expediente_id']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('centro_costo_id')->nullable()->constrained('centro_costos')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('recurso_id')->nullable()->constrained('recursos')->nullOnDelete()->cascadeOnUpdate();
        });

        Schema::table('bolsas', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                $table->dropForeign(['estacion_servicio_id']);
                $table->dropForeign(['expediente_id']);
            }

            $table->dropColumn(['estacion_servicio_id', 'expediente_id']);
        });

        Schema::table('bolsas', function (Blueprint $table) {
            $table->foreignId('centro_costo_id')->nullable()->constrained('centro_costos')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('recurso_id')->nullable()->constrained('recursos')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bolsas', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                $table->dropForeign(['recurso_id']);
                $table->dropForeign(['centro_costo_id']);
            }

            $table->dropColumn(['recurso_id', 'centro_costo_id']);
        });

        Schema::table('bolsas', function (Blueprint $table) {
            $table->foreignId('expediente_id')->nullable()->constrained('expedientes')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('estacion_servicio_id')->nullable()->constrained('estacion_servicios')->nullOnDelete()->cascadeOnUpdate();
        });

        Schema::table('tickets', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                $table->dropForeign(['recurso_id']);
                $table->dropForeign(['centro_costo_id']);
            }

            $table->dropColumn(['recurso_id', 'centro_costo_id']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('expediente_id')->nullable()->constrained('expedientes')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('estacion_servicio_id')->nullable()->constrained('estacion_servicios')->nullOnDelete()->cascadeOnUpdate();
        });

        Schema::table('tipo_combustibles', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                $table->dropForeign(['centro_costo_id']);
            }

            $table->dropColumn('centro_costo_id');
        });

        Schema::table('tipo_combustibles', function (Blueprint $table) {
            $table->foreignId('estacion_servicio_id')->nullable()->constrained('estacion_servicios')->nullOnDelete()->cascadeOnUpdate();
        });

        Schema::table('lotes', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                $table->dropForeign(['centro_costo_id']);
            }

            $table->dropColumn('centro_costo_id');
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('estacion_servicio_id')->nullable()->constrained('estacion_servicios')->nullOnDelete()->cascadeOnUpdate();
        });

        Schema::table('empleados', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                $table->dropForeign(['centro_costo_id']);
            }

            $table->dropColumn('centro_costo_id');
        });

        Schema::table('empleados', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete()->cascadeOnUpdate();
        });
    }
};
