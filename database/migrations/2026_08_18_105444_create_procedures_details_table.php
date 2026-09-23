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
            $table->foreignId('procedure_id')->constrained('procedures')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('tariff_parameter_id')->constrained('tariffs_parameters')->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('parameter_procedure_quantity', 10, 2)->default(0);
            $table->string('unit_of_measure')->nullable();
            $table->decimal('tariff_index', 10, 6)->default(0);
            $table->decimal('parameter_procedure_subtotal', 10, 2)->default(0);
            $table->timestamps();
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
