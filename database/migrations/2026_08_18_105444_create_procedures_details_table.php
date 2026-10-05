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
        Schema::create('procedures_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->constrained('procedures')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('tertiary_category_parameter_id')->nullable()->constrained('tertiary_categories_parameters')->cascadeOnUpdate()->nullOnDelete();
            // Solo una de estas columnas debería tener valor
            $table->decimal('numeric_value', 16, 6)->nullable();
            $table->text('text_value')->nullable();
            $table->date('date_value')->nullable();
            $table->boolean('boolean_value')->nullable();

            // Datos históricos utilizados en el cálculo
            $table->string('unit_of_measure', 20)->nullable();
            $table->decimal('tariff_index', 16, 6)->nullable();
            $table->decimal('subtotal', 12, 2)->nullable();

            $table->timestamps();

            $table->unique([
                'procedure_id',
                'tertiary_category_parameter_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedures_details');
    }
};
