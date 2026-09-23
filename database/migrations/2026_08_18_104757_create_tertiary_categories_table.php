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
        Schema::create('tertiary_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('secondary_category_id')->constrained('secondary_categories')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_1')->nullable();        // imagen 1
            $table->text('example')->nullable();          // ejemplo
            $table->string('image_2')->nullable();        // imagen 2
            $table->text('important_notes')->nullable();  // notas importantes
            $table->string('image_3')->nullable();        // imagen 3
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tertiary_categories');
    }
};
