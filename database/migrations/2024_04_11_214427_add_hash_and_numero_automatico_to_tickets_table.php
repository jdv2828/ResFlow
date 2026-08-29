<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // ponytail: emptied during mejora-migraciones consolidation. All operations baked into parent CREATE.

    public function up(): void
    {
        // No-op: consolidated into parent CREATE migration.
    }

    public function down(): void
    {
        // No-op: parent CREATE's down() drops the table.
    }
};
