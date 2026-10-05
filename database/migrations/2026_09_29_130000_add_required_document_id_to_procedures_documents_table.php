<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A qué documento requerido responde cada archivo que sube el usuario.
     * null = documento adicional que el usuario envía por su cuenta.
     */
    public function up(): void
    {
        Schema::table('procedures_documents', function (Blueprint $table) {
            $table->foreignId('required_document_id')
                ->nullable()
                ->after('procedure_id')
                ->constrained('required_documents')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('procedures_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('required_document_id');
        });
    }
};
