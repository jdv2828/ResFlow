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
        Schema::create('bolsa_combustibles', function (Blueprint $table) {
            $table->id();
            $table->decimal('litros_super',12,2);
            $table->decimal('litros_premiun',12,2);
            $table->decimal('litros_diesel',12,2);
            $table->decimal('litros_euro_diesel',12,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bolsa_combustibles');
    }
};
