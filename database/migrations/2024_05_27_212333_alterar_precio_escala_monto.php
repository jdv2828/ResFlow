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
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE expedientes MODIFY monto DECIMAL(18,5);');

            return;
        }

        Schema::table('expedientes', function (Blueprint $table) {
            $table->decimal('monto', 18, 5)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE expedientes MODIFY monto DECIMAL(12,5);');

            return;
        }

        Schema::table('expedientes', function (Blueprint $table) {
            $table->decimal('monto', 12, 5)->change();
        });
    }
};
