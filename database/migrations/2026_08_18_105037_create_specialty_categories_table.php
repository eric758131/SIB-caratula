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
        Schema::create('specialty_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialty_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('tertiary_category_id')->constrained('tertiary_categories')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            
            // Índice único para evitar duplicados
            $table->unique(['specialty_id', 'tertiary_category_id'], 'specialty_categories_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches_specialties');
    }
};
