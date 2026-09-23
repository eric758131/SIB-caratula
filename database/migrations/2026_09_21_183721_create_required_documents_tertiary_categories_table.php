<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('required_documents_tertiary_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('required_document_id')->constrained('required_documents')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('tertiary_category_id')->constrained('tertiary_categories')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['required_document_id', 'tertiary_category_id'], 'req_docs_tert_cats_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('required_documents_tertiary_categories');
    }
};