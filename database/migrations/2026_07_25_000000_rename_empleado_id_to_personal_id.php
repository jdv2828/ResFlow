<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE tickets RENAME COLUMN empleado_id TO personal_id');
        DB::statement('ALTER TABLE lote_empleados RENAME COLUMN empleado_id TO personal_id');

        if (Schema::hasTable('empleado_vehiculo')) {
            DB::statement('ALTER TABLE empleado_vehiculo RENAME COLUMN empleado_id TO personal_id');
        }

        DB::statement('ALTER TABLE movement_histories RENAME COLUMN empleado_id TO personal_id');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE tickets RENAME COLUMN personal_id TO empleado_id');
        DB::statement('ALTER TABLE lote_empleados RENAME COLUMN personal_id TO empleado_id');

        if (Schema::hasTable('empleado_vehiculo')) {
            DB::statement('ALTER TABLE empleado_vehiculo RENAME COLUMN personal_id TO empleado_id');
        }

        DB::statement('ALTER TABLE movement_histories RENAME COLUMN personal_id TO empleado_id');
    }
};
