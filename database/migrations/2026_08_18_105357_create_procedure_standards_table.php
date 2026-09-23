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
            Schema::create('procedure_standards', function (Blueprint $table) {
                $table->id();
                $table->foreignId('standard_id')->constrained('standards')->cascadeOnUpdate()->restrictOnDelete();
                $table->foreignId('procedure_id')->constrained('procedures')->cascadeOnUpdate()->restrictOnDelete();
                $table->timestamps();

                $table->unique(['standard_id', 'procedure_id']);
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('procedure_standards');
        }
    };
