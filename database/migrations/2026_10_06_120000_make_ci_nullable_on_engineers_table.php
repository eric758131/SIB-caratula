<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El CI del ingeniero pasa a ser opcional (sigue siendo único cuando se registra).
     */
    public function up(): void
    {
        Schema::table('engineers', function (Blueprint $table) {
            $table->string('ci', 20)->nullable()->comment('Carnet de identidad')->change();
        });
    }

    public function down(): void
    {
        Schema::table('engineers', function (Blueprint $table) {
            $table->string('ci', 20)->nullable(false)->comment('Carnet de identidad')->change();
        });
    }
};
