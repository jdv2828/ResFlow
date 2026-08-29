<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('bolsa_combustibles');
    }

    public function down(): void
    {
        Schema::create('bolsa_combustibles', function ($table) {
            $table->id();
            $table->decimal('litros_super', 12, 2)->default(0);
            $table->decimal('litros_premiun', 12, 2)->default(0);
            $table->decimal('litros_diesel', 12, 2)->default(0);
            $table->decimal('litros_euro_diesel', 12, 2)->default(0);
            $table->timestamps();
        });
    }
};
