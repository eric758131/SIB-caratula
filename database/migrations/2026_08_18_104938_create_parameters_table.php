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

            $table->string('name', 200)->unique();
            $table->text('description')->nullable();
            $table->string('unit_of_measure', 20)->nullable();

            // Clasificación funcional
            $table->enum('parameter_type', [
                'parametrico',
                'caratula',
                'judicial',
                'direccional',
            ])->default('parametrico');

            // Tipo de dato que debe ingresarse
            $table->enum('data_type', [
                'numero',
                'texto',
                'fecha',
                'booleano',
            ])->default('texto');

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
