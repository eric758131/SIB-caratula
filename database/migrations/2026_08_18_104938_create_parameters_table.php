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
        Schema::create('parameters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('image_1')->nullable();        // imagen 1
            $table->text('example')->nullable();          // ejemplo
            $table->string('image_2')->nullable();        // imagen 2
            $table->text('important_notes')->nullable();  // notas importantes
            $table->string('image_3')->nullable();        // imagen 3
            $table->string('unit_of_measure')->nullable();
            $table->enum('parameter_type', [
                'caratula',
                'parametrico'
            ])->default('caratula');
            $table->boolean('status')->default(true);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parameters');
    }
};
