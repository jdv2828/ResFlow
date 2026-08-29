<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Separate calls per column — SQLite can't handle multiple dropColumn in one modification.
        foreach (['estacion_servicio', 'estacion_servicio_id', 'empleado'] as $column) {
            if (Schema::hasColumn('movement_histories', $column)) {
                Schema::table('movement_histories', fn ($table) => $table->dropColumn($column));
            }
        }
    }

    public function down(): void
    {
        foreach (['estacion_servicio', 'estacion_servicio_id', 'empleado'] as $column) {
            if (!Schema::hasColumn('movement_histories', $column)) {
                Schema::table('movement_histories', fn ($table) => $table->string($column)->nullable());
            }
        }
    }
};
