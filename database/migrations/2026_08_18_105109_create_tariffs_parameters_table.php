<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tariffs_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tertiary_category_id')->constrained('tertiary_categories')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('parameter_id')->constrained('parameters')->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('tariff_index', 10, 6)->default(0); // o decimal dependiendo del tipo
            $table->timestamps();
            
            // Opcional: evitar duplicados
            $table->unique(['tertiary_category_id', 'parameter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tertiary_categories_parameters');
    }
};