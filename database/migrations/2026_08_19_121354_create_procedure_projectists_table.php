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
        Schema::create('procedure_projectists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->constrained('procedures')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('engineer_id')->constrained('engineers')->cascadeOnUpdate()->restrictOnDelete();
            $table->boolean('is_principal')->default(false);
            $table->timestamps();
            
            // Índice único para evitar duplicados
            $table->unique(['procedure_id', 'engineer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedure_projectists');
    }
};
