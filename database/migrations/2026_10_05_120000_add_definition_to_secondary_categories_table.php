<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Documento de definición de la categoría secundaria (ruta en el disco public).
     * El usuario lo puede ver desde el asistente.
     */
    public function up(): void
    {
        Schema::table('secondary_categories', function (Blueprint $table) {
            $table->string('definition')->nullable()->after('image_3');
        });
    }

    public function down(): void
    {
        Schema::table('secondary_categories', function (Blueprint $table) {
            $table->dropColumn('definition');
        });
    }
};
