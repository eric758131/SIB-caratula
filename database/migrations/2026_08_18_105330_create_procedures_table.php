<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedures', function (Blueprint $table) {
            $table->id();
            //$table->foreignId('requester_id')->nullable()->constrained('requesters')->cascadeOnUpdate()->restrictOnDelete();
            //$table->foreignId('validator_id')->nullable()->constrained('engineers')->cascadeOnUpdate()->restrictOnDelete(); // proyectista
            $table->foreignId('primary_category_id')->constrained('primary_categories')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('secondary_category_id')->constrained('secondary_categories')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('tertiary_category_id')->constrained('tertiary_categories')->cascadeOnUpdate()->restrictOnDelete();

            $table->char('hash_code', 64)->unique();
            $table->string('number', 20)->unique();
            $table->date('entry_date');
            $table->text('title');
            $table->text('address')->nullable();
            $table->decimal('latitude', 12, 8)->nullable();
            $table->decimal('longitude', 12, 8)->nullable();
            $table->string('zone')->nullable();
            $table->string('municipality')->nullable();
            $table->decimal('bill_amount', 10, 2)->default(0);
            $table->string('bill_amount_literal')->nullable();
            $table->decimal('total_quote_amount', 10, 2)->default(0);
            $table->text('observations')->nullable();

            $table->unsignedBigInteger('memories_quantity')->default(0);
            $table->boolean('has_plans')->default(false);
            $table->unsignedBigInteger('plans_quantity')->default(0);
            $table->unsignedBigInteger('plans_copies_quantity')->default(0);
            $table->unsignedBigInteger('plans_total_quantity')->default(0);
            $table->unsignedBigInteger('copies_total_quantity')->default(0);

            $table->unsignedBigInteger('sticker_quantity')->default(0);
            $table->enum('procedure_type', [
                'registro_inicial',
                'copia_legalizada',
                'actualizacion_datos',
                'actualizacion_parametros',
                'actualizacion_tecnica'
            ])->default('registro_inicial');
            $table->date('validity_start')->nullable();
            $table->date('validity_end')->nullable();
            $table->enum('status', [
                'pendiente',
                'registrado',
                'cotizado',
                'designado',
                'en_verificacion',
                'observado',
                'en_correccion',
                'corregido',
                'anulado',
                'aprobado',
                'por_pagar',
                'pagado',
                'por_visar',
                'visado',
                'notificado',
                'en_recepcion',
                'entregado'
            ])->default('pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedures');
    }
};