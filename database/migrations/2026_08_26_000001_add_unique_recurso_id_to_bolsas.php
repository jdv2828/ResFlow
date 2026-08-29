<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Deduplicate: one recurso = one bolsa business rule.
        // Before removing duplicates, merge the cantidad_disponible of the extra rows
        // into the kept (MIN id) row so no liters are lost.
        // MySQL can't self-reference in UPDATE/DELETE subqueries, so use derived tables.
        DB::statement('
            UPDATE bolsas AS keep
            JOIN (
                SELECT MIN(id) AS min_id, recurso_id, SUM(cantidad_disponible) AS total
                FROM bolsas
                GROUP BY recurso_id
                HAVING COUNT(*) > 1
            ) AS merged ON keep.id = merged.min_id
            SET keep.cantidad_disponible = merged.total
        ');

        DB::statement('
            DELETE FROM bolsas WHERE id IN (
                SELECT id FROM (
                    SELECT id FROM bolsas
                    WHERE recurso_id IN (
                        SELECT recurso_id FROM bolsas GROUP BY recurso_id HAVING COUNT(*) > 1
                    ) AND id NOT IN (
                        SELECT MIN(id) FROM bolsas GROUP BY recurso_id
                    )
                ) AS tmp
            )
        ');

        Schema::table('bolsas', function (Blueprint $table) {
            $table->unique('recurso_id');
        });
    }

    public function down(): void
    {
        Schema::table('bolsas', function (Blueprint $table) {
            $table->dropUnique('bolsas_recurso_id_unique');
        });
    }
};