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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->char('hash_code', 64)->unique();
            $table->enum('document_type', ['memoria_calculo', 'carta_autorizacion', 'plano_estructural', 'plano_sanitario', 'plano_arquiteconico', 'plano_electrico', 'plano_tecnico', 'otro'])->default('otro');
            $table->string('other_field', 100)->nullable();
            $table->enum('status', ['activo', 'inactivo', 'observado', 'anulado'])->default('activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
