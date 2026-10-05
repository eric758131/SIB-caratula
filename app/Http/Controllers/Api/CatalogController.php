<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Engineer;
use App\Models\Owner;
use App\Models\PrimaryCategory;
use App\Models\RequiredDocument;
use App\Models\SecondaryCategory;
use App\Models\Specialty;
use App\Models\Standard;
use App\Models\TertiaryCategory;
use App\Models\Topic;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CatalogController extends Controller
{
    /**
     * Categorías primarias activas.
     */
    public function primaryCategories()
    {
        $categories = PrimaryCategory::where('status', true)
            ->orderBy('name')
            ->get();

        return response()->json($categories->map(fn($c) => $this->mapCategory($c, true)));
    }

    /**
     * Secundarias de una primaria (con su documento de definición, si tiene).
     */
    public function secondaryCategories(PrimaryCategory $primaryCategory)
    {
        $categories = $primaryCategory->secondaryCategories()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return response()->json($categories->map(fn($c) => [
            ...$this->mapCategory($c),
            'definition' => $this->imageUrl($c->definition),
        ]));
    }

    /**
     * Terciarias de una secundaria.
     */
    public function tertiaryCategories(SecondaryCategory $secondaryCategory)
    {
        $categories = $secondaryCategory->tertiaryCategories()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return response()->json($categories->map(fn($c) => $this->mapCategory($c)));
    }

    /**
     * Detalles completos de una categoría terciaria:
     * parámetros (con tipo de dato, tarifa y si son obligatorios), documentos y especialidades.
     */
    public function tertiaryCategoryDetail(TertiaryCategory $tertiaryCategory)
    {
        $tertiaryCategory->load([
            'parameters' => fn($q) => $q->where('parameters.status', true)
                ->where('tertiary_categories_parameters.status', true)
                ->orderBy('parameters.name'),
            'requiredDocuments' => fn($q) => $q->where('required_documents.status', true)->orderBy('required_documents.name'),
            'specialties',
        ]);

        return response()->json([
            ...$this->mapCategory($tertiaryCategory),

            'parameters' => $tertiaryCategory->parameters->map(fn($p) => [
                'id'              => $p->id,
                'name'            => $p->name,
                'description'     => $p->description,
                'unit_of_measure' => $p->unit_of_measure,
                'parameter_type'  => $p->parameter_type,
                'data_type'       => $p->data_type,
                'max_decimals'    => $p->max_decimals,
                'tariff_index'    => $p->pivot->tariff_index !== null ? (float) $p->pivot->tariff_index : null,
                'is_required'     => (bool) $p->pivot->is_required,
            ]),

            // Documentos que el usuario debe entregar: se descargan en el paso 1 y se suben llenos en el 3
            'required_documents' => $tertiaryCategory->requiredDocuments->map(fn($d) => $this->mapRequiredDocument($d)),
            // Todos los formatos en un .zip (null si ningún documento tiene archivo)
            'required_documents_zip_url' => $tertiaryCategory->requiredDocuments->contains(fn($d) => $d->file_path)
                ? route('api.tertiaries.required-documents.download', $tertiaryCategory)
                : null,

            'specialties' => $tertiaryCategory->specialties->map(fn($s) => [
                'id'   => $s->id,
                'name' => $s->name,
            ]),
        ]);
    }

    /**
     * Topics disponibles (para el caso INFORMES).
     */
    public function topics(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $topics = Topic::where('status', true)
            ->whereSearch($search, ['name'])
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name']);

        return response()->json($topics);
    }

    /**
     * Normas activas.
     */
    public function standards()
    {
        $standards = Standard::with('country:id,name')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return response()->json($standards->map(fn($s) => [
            'id'          => $s->id,
            'name'        => $s->name,
            'description' => $s->description,
            'country'     => $s->country?->name,
        ]));
    }

    /**
     * Propietarios activos (búsqueda opcional).
     */
    public function owners(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $owners = Owner::where('status', true)
            ->whereSearch($search, ['name', 'phone'])
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'phone']);

        return response()->json($owners);
    }

    /**
     * Ingeniero activo (proyectista) por RNI exacto: no se listan ingenieros sin su RNI completo.
     */
    public function engineers(Request $request)
    {
        $rni = trim((string) $request->input('search', ''));

        if ($rni === '') {
            return response()->json([]);
        }

        $engineers = Engineer::with('specialties')
            ->where('status', Engineer::STATUS_ACTIVO)
            ->where('rni', $rni)
            ->get();

        return response()->json($engineers->map(fn($e) => [
            'id'          => $e->id,
            'full_name'   => $e->full_name,
            'rni'         => $e->rni,
            'ci'          => $e->ci,
            'specialties' => $e->specialties->pluck('name'),
        ]));
    }

    /**
     * Opciones para el formulario de alta rápida de ingenieros.
     */
    public function engineerFormOptions()
    {
        return response()->json([
            'branches' => Branch::where('status', true)->orderBy('name')->get(['id', 'name']),
            'universities' => University::where('status', true)->orderBy('name')->get(['id', 'name']),
            'specialties' => Specialty::where('status', true)->orderBy('name')->get(['id', 'name']),
            'sib_departmentals' => collect(Engineer::SIB_DEPARTAMENTAL_LABELS)
                ->map(fn($label, $value) => ['value' => $value, 'label' => $label])
                ->values(),
        ]);
    }

    /**
     * Descarga la plantilla/archivo de un documento requerido.
     */
    public function downloadRequiredDocument(RequiredDocument $requiredDocument)
    {
        abort_unless(
            $requiredDocument->status
                && $requiredDocument->file_path
                && Storage::disk('public')->exists($requiredDocument->file_path),
            404,
            'El documento no tiene un archivo disponible.'
        );

        $extension = pathinfo($requiredDocument->file_path, PATHINFO_EXTENSION);

        return Storage::disk('public')->download(
            $requiredDocument->file_path,
            Str::slug($requiredDocument->name) . '.' . $extension
        );
    }

    /**
     * Descarga en un .zip todas las plantillas de los documentos requeridos de una terciaria.
     */
    public function downloadAllRequiredDocuments(TertiaryCategory $tertiaryCategory)
    {
        $disk = Storage::disk('public');

        $documents = $tertiaryCategory->requiredDocuments()
            ->where('required_documents.status', true)
            ->whereNotNull('required_documents.file_path')
            ->orderBy('required_documents.name')
            ->get()
            ->filter(fn($d) => $disk->exists($d->file_path));

        abort_if($documents->isEmpty(), 404, 'No hay formatos disponibles para descargar.');

        $zipPath = tempnam(sys_get_temp_dir(), 'formatos');
        $zip = new \ZipArchive();
        abort_unless($zip->open($zipPath, \ZipArchive::OVERWRITE) === true, 500, 'No se pudo crear el archivo comprimido.');

        $used = [];
        foreach ($documents as $index => $document) {
            $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);
            $base = Str::slug($document->name) ?: 'documento-' . ($index + 1);

            // Evita que dos documentos con el mismo nombre se pisen dentro del zip
            $name = $base . '.' . $extension;
            for ($n = 2; isset($used[$name]); $n++) {
                $name = "{$base}-{$n}.{$extension}";
            }
            $used[$name] = true;

            $zip->addFile($disk->path($document->file_path), $name);
        }
        $zip->close();

        return response()
            ->download($zipPath, 'formatos-' . Str::slug($tertiaryCategory->name) . '.zip', ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend();
    }


    /* ============================================================
     |  Helpers
     * ============================================================ */
    private function mapCategory($category, bool $isPrimary = false): array
    {
        $data = [
            ...$this->mapHelp($category),
            'code' => $category->code ?? null,
        ];

        if ($isPrimary) {
            $data['logo']       = $this->imageUrl($category->logo_image ?? null);
            $data['is_special'] = $category->isInformes();
        }

        return $data;
    }

    /**
     * Ayudas visuales comunes a categorías y parámetros.
     */
    private function mapHelp($model): array
    {
        return [
            'id'              => $model->id,
            'name'            => $model->name,
            'description'     => $model->description,
            'example'         => $model->example,
            'important_notes' => $model->important_notes,
            // Cada imagen va en su lugar: 1 tras la descripción, 2 tras el ejemplo, 3 tras lo importante
            'image_1'         => $this->imageUrl($model->image_1),
            'image_2'         => $this->imageUrl($model->image_2),
            'image_3'         => $this->imageUrl($model->image_3),
            'images' => array_values(array_filter([
                $this->imageUrl($model->image_1),
                $this->imageUrl($model->image_2),
                $this->imageUrl($model->image_3),
            ])),
        ];
    }

    private function mapRequiredDocument(RequiredDocument $document): array
    {
        return [
            'id'           => $document->id,
            'name'         => $document->name,
            'download_url' => $document->file_path
                ? route('api.required-documents.download', $document)
                : null,
        ];
    }

    private function imageUrl(?string $path): ?string
    {
        if (!$path) return null;

        return url('storage/' . $path);
    }
}