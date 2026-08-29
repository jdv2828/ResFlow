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
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->string('numero_identificacion', 50)->nullable()->after('patente');
            $table->foreignId('id_chofer')->nullable()->constrained('empleados')->nullOnDelete()->cascadeOnUpdate()->after('numero_identificacion');
            $table->foreignId('id_centro_costo')->nullable()->constrained('centro_costos')->nullOnDelete()->cascadeOnUpdate()->after('id_chofer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                $table->dropForeign(['id_centro_costo']);
                $table->dropForeign(['id_chofer']);
            }

            $table->dropColumn(['numero_identificacion', 'id_chofer', 'id_centro_costo']);
        });
    }
};
