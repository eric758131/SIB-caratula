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
        Schema::create('procedures_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('procedure_id')->constrained('procedures')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();

            // Índice único para evitar duplicados
            $table->unique(['document_id', 'procedure_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedures_documents');
    }
};
