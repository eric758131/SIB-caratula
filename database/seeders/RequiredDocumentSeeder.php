<?php

namespace Database\Seeders;

use App\Models\RequiredDocument;
use App\Models\TertiaryCategory;
use Illuminate\Database\Seeder;

/**
 * Un documento requerido de prueba por cada categoría terciaria (sin plantilla),
 * para probar la subida de archivos del paso 4 del asistente.
 */
class RequiredDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $tertiaryCategories = TertiaryCategory::orderBy('code')->get();

        foreach ($tertiaryCategories as $tertiaryCategory) {
            // El código va en el nombre para que sea único
            $document = RequiredDocument::updateOrCreate(
                ['name' => "Plano del proyecto ({$tertiaryCategory->code})"],
                ['file_path' => null, 'status' => true]
            );

            $document->tertiaryCategories()->syncWithoutDetaching([$tertiaryCategory->id]);
        }

        $this->command->info('Documentos requeridos creados: ' . $tertiaryCategories->count());
    }
}
