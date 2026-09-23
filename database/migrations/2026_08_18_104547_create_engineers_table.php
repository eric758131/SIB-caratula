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
        Schema::create('engineers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnUpdate()->restrictOnDelete();
            
            $table->string('rni', 20)->unique()->comment('Registro Nacional de Ingenieros');
            $table->string('name', 200);
            $table->string('father_last_name', 100);
            $table->string('mother_last_name', 100)->nullable();
            $table->string('ci', 20)->unique()->comment('Carnet de identidad');
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->enum('sib_departmental', ['la_paz', 'oruro', 'potosi', 'cochabamba', 'tarija', 'chuquisaca', 'santa_cruz', 'beni', 'pando'])->default('la_paz')->comment('Departamental de la SIB');
            $table->date('suspension_start_date')->nullable();
            $table->date('suspension_end_date')->nullable();
            $table->text('image')->nullable();
            $table->enum('status', [
                'activo',
                'inactivo',
                'suspension_indefinida',
                'suspension_definida',
                'emerito',
                'fallecido'
            ])->default('activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engineers');
    }
};
