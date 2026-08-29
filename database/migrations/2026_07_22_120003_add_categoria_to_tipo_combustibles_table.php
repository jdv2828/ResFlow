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
        Schema::table('tipo_combustibles', function (Blueprint $table) {
            $table->enum('categoria', ['combustible', 'aceites', 'lubricantes', 'refrigerantes'])->default('combustible')->after('descripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_combustibles', function (Blueprint $table) {
            $table->dropColumn('categoria');
        });
    }
};
