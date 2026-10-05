<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nro. de decimales máximos que acepta un parámetro de tipo Número.
     * null = sin límite.
     */
    public function up(): void
    {
        Schema::table('parameters', function (Blueprint $table) {
            $table->unsignedInteger('max_decimals')->nullable()->after('data_type');
        });
    }

    public function down(): void
    {
        Schema::table('parameters', function (Blueprint $table) {
            $table->dropColumn('max_decimals');
        });
    }
};
