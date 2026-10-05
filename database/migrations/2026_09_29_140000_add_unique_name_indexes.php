<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tablas cuyo nombre debe ser único pero no tenían índice en la base.
     * La validación "unique" de los controladores no alcanza sola: si se presiona
     * "Guardar" dos veces muy rápido, las dos peticiones pasan la validación antes
     * de que cualquiera inserte. El índice hace que la segunda falle (23505) y el
     * controlador ya responde "Ya existe…" en ese caso.
     *
     * LOWER(name) para que coincida con la regla de los controladores (sin importar mayúsculas).
     */
    private const TABLES = [
        'secondary_categories',
        'standards',
        'required_documents',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            DB::statement("CREATE UNIQUE INDEX {$table}_name_lower_unique ON {$table} (LOWER(name))");
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            DB::statement("DROP INDEX IF EXISTS {$table}_name_lower_unique");
        }
    }
};
