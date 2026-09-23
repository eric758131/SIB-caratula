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
        Schema::create('engineers_specialties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engineer_id')->constrained('engineers')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('specialty_id')->constrained('specialties')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engineers_specialties');
    }
};
