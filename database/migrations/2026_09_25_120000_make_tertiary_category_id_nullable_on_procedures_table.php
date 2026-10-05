<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Caso INFORMES: el trámite no tiene categoría terciaria, usa un topic.
     */
    public function up(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            $table->foreignId('tertiary_category_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            $table->foreignId('tertiary_category_id')->nullable(false)->change();
        });
    }
};
