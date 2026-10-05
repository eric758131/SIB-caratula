<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Engineer;
use App\Models\Parameter;
use App\Models\PrimaryCategory;
use App\Models\Procedure;
use App\Models\ProcedureDocument;
use App\Models\RequiredDocument;
use App\Models\TertiaryCategoryParameter;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Asistente público de trámites (Angular).
 *
 * Flujo:
 *  1-3. POST /procedures              → crea el trámite como BORRADOR (pendiente)
 *       PUT  /procedures/{hash}       → el usuario vuelve atrás y corrige
 *  4.   POST /procedures/{hash}/documents   → sube UN archivo por petición (tabla documents)
 *  5.   POST /procedures/{hash}/submit      → valida documentos y pasa a REGISTRADO
 *  6.   GET  /procedures/{hash}             → incluye las descargas de la categoría terciaria
 *
 * Las rutas usan hash_code (no el id) para que no se puedan recorrer trámites ajenos.
 */
class ProcedureApiController extends Controller
{
    /** Colección de Media Library del modelo Document */
    private const MEDIA_COLLECTION = 'file';

    private const ALLOWED_EXTENSIONS = 'pdf,doc,docx,xls,xlsx,dwg,dxf,jpg,jpeg,png,zip,rar';

    /* ============================================================
     |  Pasos 1-3: datos del trámite (borrador)
     * ============================================================ */
    public function store(Request $request)
    {
        $data = $this->validateProcedure($request);

        $procedure = DB::transaction(function () use ($data) {
            $procedure = new Procedure([
                'hash_code'      => hash('sha256', Str::uuid()->toString()),
                'number'         => $this->generateNumber($data['primary']),
                'entry_date'     => today(),
                'procedure_type' => Procedure::PROCEDURE_TYPE_REGISTRO_INICIAL,
                'status'         => Procedure::STATUS_PENDIENTE,
            ]);

            $this->fillAndSync($procedure, $data);

            return $procedure;
        });

        return response()->json($this->present($procedure), 201);
    }

    public function update(Request $request, Procedure $procedure)
    {
        $this->ensureDraft($procedure);

        $data = $this->validateProcedure($request);

        DB::transaction(fn () => $this->fillAndSync($procedure, $data));

        return response()->json($this->present($procedure->fresh()));
    }

    public function show(Procedure $procedure)
    {
        return response()->json($this->present($procedure));
    }

    /* ============================================================
     |  Paso 4: archivos del usuario (tabla documents + Media Library)
     * ============================================================ */

    /**
     * Sube UN archivo por petición: así el frontend muestra el progreso
     * de cada archivo y no choca con post_max_size al mandar varios planos juntos.
     *
     * Cada archivo es un Document (su archivo en la colección "file") vinculado al trámite
     * en procedures_documents. required_document_id indica a qué documento requerido
     * responde; si no viene, es un documento adicional que el usuario envía por su cuenta.
     */
    public function uploadDocument(Request $request, Procedure $procedure)
    {
        $this->ensureDraft($procedure);

        $maxKb = (int) (config('media-library.max_file_size') / 1024);

        $validated = $request->validate([
            'required_document_id' => [
                'nullable',
                'integer',
                Rule::in($this->requiredDocuments($procedure)->pluck('id')->all()),
            ],
            'file' => ['required', 'file', 'max:' . $maxKb, 'extensions:' . self::ALLOWED_EXTENSIONS],
        ], [
            'required_document_id.in' => 'Ese documento no es requerido para la categoría del trámite.',
            'file.required'   => 'Debes seleccionar un archivo.',
            'file.uploaded'   => 'El archivo no se pudo subir; puede que supere el límite del servidor.',
            'file.max'        => 'El archivo no puede superar los ' . intdiv($maxKb, 1024) . ' MB.',
            'file.extensions' => 'Formato no permitido. Usa: ' . str_replace(',', ', ', self::ALLOWED_EXTENSIONS) . '.',
        ], [
            'required_document_id' => 'documento requerido',
            'file' => 'archivo',
        ]);

        $requiredId = isset($validated['required_document_id']) ? (int) $validated['required_document_id'] : null;
        $required   = $requiredId ? RequiredDocument::find($requiredId) : null;
        $fileName   = pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME);

        $procedureDocument = DB::transaction(function () use ($procedure, $requiredId, $required, $fileName) {
            $document = Document::create([
                'name'          => mb_substr($fileName !== '' ? $fileName : 'Documento', 0, 200),
                'description'   => $required
                    ? "{$required->name} — trámite {$procedure->number}"
                    : "Documento adicional — trámite {$procedure->number}",
                'hash_code'     => hash('sha256', Str::uuid()->toString()),
                'document_type' => Document::DOCUMENT_TYPE_OTRO,
                'status'        => Document::STATUS_ACTIVO,
            ]);

            $document->addMediaFromRequest('file')->toMediaCollection(self::MEDIA_COLLECTION);

            return $procedure->documents()->create([
                'document_id'          => $document->id,
                'required_document_id' => $requiredId,
            ]);
        });

        return response()->json($this->mapFile($procedure, $procedureDocument->load('document.media')), 201);
    }

    public function deleteDocument(Procedure $procedure, ProcedureDocument $procedureDocument)
    {
        $this->ensureDraft($procedure);
        $this->ensureFileBelongsTo($procedure, $procedureDocument);

        // Borra el Document: su archivo (Media Library) y el vínculo con el trámite se van con él
        $procedureDocument->document->delete();

        return response()->noContent();
    }

    public function downloadDocument(Procedure $procedure, ProcedureDocument $procedureDocument)
    {
        $this->ensureFileBelongsTo($procedure, $procedureDocument);

        $media = $procedureDocument->document?->getFirstMedia(self::MEDIA_COLLECTION);
        abort_unless($media, 404, 'El archivo ya no está disponible.');

        // Media es Responsable: responde con el archivo como descarga
        return $media;
    }

    /* ============================================================
     |  Paso 5: envío
     * ============================================================ */
    public function submit(Procedure $procedure)
    {
        $this->ensureDraft($procedure);

        // Los documentos adicionales no cuentan: solo se exigen los requeridos
        $uploadedIds = $procedure->documents()
            ->whereNotNull('required_document_id')
            ->pluck('required_document_id')
            ->unique();

        $missing = $this->requiredDocuments($procedure)
            ->reject(fn ($doc) => $uploadedIds->contains($doc->id));

        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'documents' => 'Faltan documentos por subir: ' . $missing->pluck('name')->implode(', ') . '.',
            ]);
        }

        $procedure->update([
            'status'     => Procedure::STATUS_REGISTRADO,
            'entry_date' => today(),
        ]);

        return response()->json($this->present($procedure->fresh()));
    }

    /* ============================================================
     |  Validación
     * ============================================================ */

    /**
     * Valida el payload y resuelve lo que se necesita para guardar:
     * la categoría primaria y los parámetros asignados a la terciaria (tertiary_categories_parameters).
     */
    private function validateProcedure(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:500'],

            'primary_category_id' => [
                'required', 'integer',
                Rule::exists('primary_categories', 'id')->where('status', true),
            ],
            'secondary_category_id' => [
                'required', 'integer',
                Rule::exists('secondary_categories', 'id')
                    ->where('primary_category_id', $request->integer('primary_category_id'))
                    ->where('status', true),
            ],
            'tertiary_category_id' => [
                'nullable', 'integer',
                Rule::exists('tertiary_categories', 'id')
                    ->where('secondary_category_id', $request->integer('secondary_category_id'))
                    ->where('status', true),
            ],
            'topic_name' => ['nullable', 'string', 'min:3', 'max:200'],

            'address'      => ['nullable', 'string', 'max:500'],
            'zone'         => ['nullable', 'string', 'max:255'],
            'municipality' => ['nullable', 'string', 'max:255'],
            'latitude'     => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'    => ['nullable', 'numeric', 'between:-180,180'],
            'observations' => ['nullable', 'string', 'max:2000'],

            'standards'   => ['nullable', 'array'],
            'standards.*' => ['integer', 'distinct', Rule::exists('standards', 'id')->where('status', true)],

            'owners'                => ['required', 'array', 'min:1'],
            'owners.*.owner_id'     => ['required', 'integer', 'distinct', Rule::exists('owners', 'id')->where('status', true)],
            'owners.*.is_principal' => ['nullable', 'boolean'],

            'projectists'                => ['required', 'array', 'min:1'],
            'projectists.*.engineer_id'  => [
                'required', 'integer', 'distinct',
                Rule::exists('engineers', 'id')->where('status', Engineer::STATUS_ACTIVO),
            ],
            'projectists.*.is_principal' => ['nullable', 'boolean'],

            'parameters'                => ['nullable', 'array'],
            'parameters.*.parameter_id' => ['required', 'integer', 'distinct'],
            // El tipo del valor depende del data_type del parámetro: se valida más abajo
            'parameters.*.value'        => ['nullable'],
        ], $this->validationMessages(), $this->validationAttributes());

        $primary = PrimaryCategory::findOrFail($validated['primary_category_id']);
        $errors  = [];

        // Caso INFORMES: sin terciaria ni parámetros, el usuario escribe el topic
        if ($primary->isInformes()) {
            if (blank($validated['topic_name'] ?? null)) {
                $errors['topic_name'] = 'Para INFORMES debes escribir el tema del informe.';
            }
            $validated['tertiary_category_id'] = null;
            $validated['parameters'] = [];
        } else {
            if (empty($validated['tertiary_category_id'])) {
                $errors['tertiary_category_id'] = 'Debes seleccionar una categoría terciaria.';
            }
            $validated['topic_name'] = null;
        }

        // Parámetros activos asignados a la terciaria elegida
        $links = collect();
        if (!empty($validated['tertiary_category_id'])) {
            $links = TertiaryCategoryParameter::with('parameter')
                ->where('tertiary_category_id', $validated['tertiary_category_id'])
                ->where('status', true)
                ->whereHas('parameter', fn ($q) => $q->where('status', true))
                ->get()
                ->keyBy('parameter_id');
        }

        // Cada valor debe corresponder a la terciaria y respetar el tipo de dato del parámetro
        $values = [];
        foreach ($validated['parameters'] ?? [] as $i => $param) {
            $link = $links->get($param['parameter_id']);
            if (!$link) {
                $errors["parameters.$i.parameter_id"] = 'El parámetro no corresponde a la categoría terciaria seleccionada.';
                continue;
            }

            $value = $param['value'] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            $error = $this->parameterValueError($link->parameter, $value);
            if ($error) {
                $errors["parameters.$i.value"] = $error;
                continue;
            }

            $values[$link->parameter_id] = $value;
        }

        // Los obligatorios deben venir con valor
        foreach ($links as $link) {
            if ($link->is_required && !array_key_exists($link->parameter_id, $values)
                && !$this->hasErrorFor($errors, $validated['parameters'] ?? [], $link->parameter_id)) {
                $errors["parameters.{$link->parameter_id}"] = "Completa el parámetro \"{$link->parameter->name}\".";
            }
        }

        foreach (['owners' => 'propietario', 'projectists' => 'proyectista'] as $key => $label) {
            $principals = collect($validated[$key])->filter(fn ($row) => !empty($row['is_principal']))->count();
            if ($principals > 1) {
                $errors[$key] = "Solo puede haber un {$label} principal.";
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $validated['primary'] = $primary;
        $validated['links']   = $links;
        $validated['values']  = $values;

        return $validated;
    }

    /**
     * Mensaje de error si el valor no corresponde al tipo de dato del parámetro.
     */
    private function parameterValueError(Parameter $parameter, mixed $value): ?string
    {
        $name = $parameter->name;

        return match ($parameter->data_type) {
            Parameter::DATA_NUMERO => match (true) {
                !is_numeric($value) || $value < 0 || $value > 9999999999.999999
                    => "\"{$name}\" debe ser un número positivo.",
                $parameter->max_decimals !== null && $this->decimalCount($value) > $parameter->max_decimals
                    => $parameter->max_decimals === 0
                        ? "\"{$name}\" debe ser un número entero (sin decimales)."
                        : "\"{$name}\" admite como máximo {$parameter->max_decimals} decimal(es).",
                default => null,
            },
            Parameter::DATA_FECHA => !is_string($value) || !\DateTime::createFromFormat('!Y-m-d', $value)
                ? "\"{$name}\" debe ser una fecha válida."
                : null,
            Parameter::DATA_BOOLEANO => !is_bool($value) && !in_array($value, [0, 1, '0', '1'], true)
                ? "\"{$name}\" debe ser Sí o No."
                : null,
            default => !is_string($value) && !is_numeric($value)
                ? "\"{$name}\" debe ser un texto."
                : (mb_strlen((string) $value) > 1000 ? "\"{$name}\" no puede superar los 1000 caracteres." : null),
        };
    }

    /** Cuántos decimales tiene un número (120.50 → 1, 3 → 0) */
    private function decimalCount(mixed $value): int
    {
        $text = (string) (0 + $value);
        if (stripos($text, 'e') !== false) {
            $text = rtrim(rtrim(sprintf('%.20F', (float) $value), '0'), '.');
        }
        $dot = strpos($text, '.');

        return $dot === false ? 0 : strlen($text) - $dot - 1;
    }

    /** true si ya hay un error de valor para ese parámetro (para no repetir el aviso de obligatorio) */
    private function hasErrorFor(array $errors, array $params, int $parameterId): bool
    {
        foreach ($params as $i => $param) {
            if ((int) ($param['parameter_id'] ?? 0) === $parameterId && isset($errors["parameters.$i.value"])) {
                return true;
            }
        }

        return false;
    }

    /* ============================================================
     |  Persistencia
     * ============================================================ */
    private function fillAndSync(Procedure $procedure, array $data): void
    {
        $topic = !empty($data['topic_name']) ? $this->findOrCreateTopic($data['topic_name']) : null;

        $procedure->fill([
            'topic_id'              => $topic?->id,
            'primary_category_id'   => $data['primary_category_id'],
            'secondary_category_id' => $data['secondary_category_id'],
            'tertiary_category_id'  => $data['tertiary_category_id'],
            'title'                 => $this->normalizeUpper($data['title']),
            'address'               => $data['address'] ?? null,
            'latitude'              => $data['latitude'] ?? null,
            'longitude'             => $data['longitude'] ?? null,
            'zone'                  => $data['zone'] ?? null,
            'municipality'          => $data['municipality'] ?? null,
            'observations'          => $data['observations'] ?? null,
        ])->save();

        // Normas
        $procedure->standards()->sync($data['standards'] ?? []);

        // Propietarios y proyectistas: si nadie viene marcado, el primero es el principal
        $procedure->procedureOwners()->delete();
        $procedure->procedureOwners()->createMany(
            $this->withPrincipal($data['owners'], 'owner_id')
        );

        $procedure->projectists()->delete();
        $procedure->projectists()->createMany(
            $this->withPrincipal($data['projectists'], 'engineer_id')
        );

        // Parámetros → detalles (el valor va en la columna de su tipo de dato)
        $procedure->details()->delete();
        foreach ($data['values'] as $parameterId => $value) {
            $link      = $data['links']->get($parameterId);
            $parameter = $link->parameter;
            $index     = $link->tariff_index !== null ? (float) $link->tariff_index : null;
            $isNumber  = $parameter->data_type === Parameter::DATA_NUMERO;

            $procedure->details()->create([
                'tertiary_category_parameter_id' => $link->id,
                'numeric_value'   => $isNumber ? (float) $value : null,
                'text_value'      => $parameter->data_type === Parameter::DATA_TEXTO ? trim((string) $value) : null,
                'date_value'      => $parameter->data_type === Parameter::DATA_FECHA ? $value : null,
                'boolean_value'   => $parameter->data_type === Parameter::DATA_BOOLEANO ? (bool) $value : null,
                'unit_of_measure' => $parameter->unit_of_measure,
                'tariff_index'    => $index,
                // Solo los números con tarifa suman a la cotización
                'subtotal'        => $isNumber && $index !== null ? round((float) $value * $index, 2) : null,
            ]);
        }

        $procedure->update([
            'total_quote_amount' => $procedure->details()->sum('subtotal'),
        ]);

        // Si cambió la terciaria, se descartan los archivos de documentos que ya no se piden
        // (los documentos adicionales del usuario se conservan)
        $requiredIds = $this->requiredDocuments($procedure)->pluck('id');
        $procedure->documents()
            ->with('document')
            ->whereNotNull('required_document_id')
            ->whereNotIn('required_document_id', $requiredIds)
            ->get()
            ->each(fn (ProcedureDocument $pd) => $pd->document?->delete());
    }

    private function withPrincipal(array $rows, string $foreignKey): array
    {
        $hasPrincipal = collect($rows)->contains(fn ($row) => !empty($row['is_principal']));

        return collect($rows)->values()->map(fn ($row, $i) => [
            $foreignKey    => $row[$foreignKey],
            'is_principal' => $hasPrincipal ? !empty($row['is_principal']) : $i === 0,
        ])->all();
    }

    private function findOrCreateTopic(string $name): Topic
    {
        $name = $this->normalizeUpper($name);

        return Topic::whereRaw('LOWER(name) = ?', [mb_strtolower($name, 'UTF-8')])->first()
            ?? Topic::create(['name' => $name, 'status' => true]);
    }

    /**
     * Correlativo anual global con prefijo de la primaria: EDI-2026-00001.
     * Se bloquea la última fila del año para que dos envíos simultáneos no repitan número.
     */
    private function generateNumber(PrimaryCategory $primary): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^A-Z]/i', '', Str::ascii($primary->name)), 0, 3)) ?: 'TRM';
        $year   = now()->format('Y');

        $last = Procedure::where('number', 'like', "%-{$year}-%")
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('number');

        $next = $last ? ((int) substr($last, -5)) + 1 : 1;

        return sprintf('%s-%s-%05d', $prefix, $year, $next);
    }

    /* ============================================================
     |  Helpers
     * ============================================================ */
    private function ensureDraft(Procedure $procedure): void
    {
        abort_unless($procedure->isDraft(), 409, 'El trámite ya fue enviado y no se puede modificar.');
    }

    private function ensureFileBelongsTo(Procedure $procedure, ProcedureDocument $procedureDocument): void
    {
        abort_unless((int) $procedureDocument->procedure_id === $procedure->id, 404);
    }

    private function requiredDocuments(Procedure $procedure): Collection
    {
        if (!$procedure->tertiary_category_id) {
            return collect();
        }

        return $procedure->tertiaryCategory
            ->requiredDocuments()
            ->where('required_documents.status', true)
            ->orderBy('required_documents.name')
            ->get();
    }

    private function normalizeUpper(string $text): string
    {
        return mb_strtoupper(preg_replace('/\s+/', ' ', trim($text)), 'UTF-8');
    }

    private function imageUrl(?string $path): ?string
    {
        return $path ? url('storage/' . $path) : null;
    }

    /**
     * Archivo subido por el usuario. id = fila de procedures_documents.
     */
    private function mapFile(Procedure $procedure, ProcedureDocument $procedureDocument): ?array
    {
        $media = $procedureDocument->document?->getFirstMedia(self::MEDIA_COLLECTION);
        if (!$media) {
            return null;
        }

        return [
            'id'                   => $procedureDocument->id,
            'required_document_id' => $procedureDocument->required_document_id,
            'file_name'            => $media->file_name,
            'mime_type'            => $media->mime_type,
            'size'                 => $media->size,
            'human_size'           => $media->human_readable_size,
            'download_url'         => route('api.procedures.documents.download', [
                'procedure'         => $procedure->hash_code,
                'procedureDocument' => $procedureDocument->id,
            ]),
        ];
    }

    /**
     * Respuesta completa: sirve para la vista previa de la carátula,
     * el checklist de documentos y las descargas finales.
     */
    private function present(Procedure $procedure): array
    {
        $procedure->loadMissing([
            'primaryCategory',
            'secondaryCategory',
            'tertiaryCategory',
            'topic',
            'standards.country',
            'procedureOwners.owner',
            'projectists.engineer',
            'details.tertiaryCategoryParameter.parameter',
            'documents.document.media',
        ]);

        $files = $procedure->documents
            ->map(fn (ProcedureDocument $pd) => $this->mapFile($procedure, $pd))
            ->filter()
            ->values();

        return [
            'hash_code'          => $procedure->hash_code,
            'number'             => $procedure->number,
            'title'              => $procedure->title,
            'entry_date'         => $procedure->entry_date?->format('Y-m-d'),
            'status'             => $procedure->status,
            'procedure_type'     => $procedure->procedure_type,
            'is_draft'           => $procedure->isDraft(),
            'address'            => $procedure->address,
            'zone'               => $procedure->zone,
            'municipality'       => $procedure->municipality,
            'latitude'           => $procedure->latitude,
            'longitude'          => $procedure->longitude,
            'observations'       => $procedure->observations,
            'total_quote_amount' => (float) $procedure->total_quote_amount,

            'primary_category' => [
                'id'         => $procedure->primaryCategory->id,
                'name'       => $procedure->primaryCategory->name,
                'logo'       => $this->imageUrl($procedure->primaryCategory->logo_image),
                'is_special' => $procedure->primaryCategory->isInformes(),
            ],
            'secondary_category' => [
                'id'   => $procedure->secondaryCategory->id,
                'name' => $procedure->secondaryCategory->name,
            ],
            'tertiary_category' => $procedure->tertiaryCategory ? [
                'id'   => $procedure->tertiaryCategory->id,
                'code' => $procedure->tertiaryCategory->code,
                'name' => $procedure->tertiaryCategory->name,
            ] : null,
            'topic' => $procedure->topic ? [
                'id'   => $procedure->topic->id,
                'name' => $procedure->topic->name,
            ] : null,

            'standards' => $procedure->standards->map(fn ($s) => [
                'id'      => $s->id,
                'name'    => $s->name,
                'country' => $s->country?->name,
            ])->values(),

            'owners' => $procedure->procedureOwners
                ->sortByDesc('is_principal')
                ->map(fn ($po) => [
                    'id'           => $po->owner->id,
                    'name'         => $po->owner->name,
                    'phone'        => $po->owner->phone,
                    'is_principal' => (bool) $po->is_principal,
                ])->values(),

            'projectists' => $procedure->projectists
                ->sortByDesc('is_principal')
                ->map(fn ($pp) => [
                    'id'           => $pp->engineer->id,
                    'full_name'    => $pp->engineer->full_name,
                    'rni'          => $pp->engineer->rni,
                    'ci'           => $pp->engineer->ci,
                    'is_principal' => (bool) $pp->is_principal,
                ])->values(),

            // Todos los parámetros de la terciaria (los que no se llenaron van con value null)
            'parameters' => $this->presentParameters($procedure),

            // Documentos requeridos (se descargan en el paso 1 y se suben llenos en el 4)
            'required_documents' => $this->requiredDocuments($procedure)->map(fn ($doc) => [
                'id'           => $doc->id,
                'name'         => $doc->name,
                'download_url' => $doc->file_path
                    ? route('api.required-documents.download', $doc)
                    : null,
                'files'        => $files->where('required_document_id', $doc->id)->values(),
            ])->values(),

            // Documentos adicionales que el usuario envía por su cuenta
            'extra_documents' => $files->whereNull('required_document_id')->values(),
        ];
    }

    /**
     * Parámetros para la carátula: todos los activos de la terciaria, en el mismo orden que el
     * asistente (por nombre), con su valor si se llenó. Así la carátula impresa muestra también
     * los opcionales vacíos. Los detalles cuyo parámetro ya se quitó de la terciaria (FK en null)
     * no se muestran.
     */
    private function presentParameters(Procedure $procedure): Collection
    {
        $details = $procedure->details
            ->filter(fn ($d) => $d->tertiaryCategoryParameter?->parameter)
            ->keyBy(fn ($d) => $d->tertiaryCategoryParameter->parameter_id);

        $links = $procedure->tertiary_category_id
            ? TertiaryCategoryParameter::with('parameter')
                ->where('tertiary_category_id', $procedure->tertiary_category_id)
                ->where('status', true)
                ->whereHas('parameter', fn ($q) => $q->where('status', true))
                ->get()
                ->keyBy('parameter_id')
            : collect();

        // Si un parámetro se desactivó después de guardar, se conserva el valor que ya tenía
        foreach ($details as $parameterId => $detail) {
            if (!$links->has($parameterId)) {
                $links->put($parameterId, $detail->tertiaryCategoryParameter);
            }
        }

        return $links
            ->sortBy(fn ($link) => mb_strtolower($link->parameter->name, 'UTF-8'))
            ->map(function ($link) use ($details) {
                $detail = $details->get($link->parameter_id);

                return [
                    'parameter_id'    => $link->parameter_id,
                    'name'            => $link->parameter->name,
                    'parameter_type'  => $link->parameter->parameter_type,
                    'data_type'       => $link->parameter->data_type,
                    'unit_of_measure' => $detail?->unit_of_measure ?? $link->parameter->unit_of_measure,
                    'value'           => $detail?->value,
                    'tariff_index'    => $detail?->tariff_index !== null ? (float) $detail->tariff_index : null,
                    'subtotal'        => $detail?->subtotal !== null ? (float) $detail->subtotal : null,
                ];
            })
            ->values();
    }

    private function validationMessages(): array
    {
        return [
            'title.required'                => 'Debes escribir el título del proyecto.',
            'title.min'                     => 'El título debe tener al menos :min caracteres.',
            'primary_category_id.required'  => 'Debes seleccionar una categoría primaria.',
            'secondary_category_id.required' => 'Debes seleccionar una categoría secundaria.',
            'secondary_category_id.exists'  => 'La categoría secundaria no pertenece a la primaria seleccionada.',
            'tertiary_category_id.exists'   => 'La categoría terciaria no pertenece a la secundaria seleccionada.',
            'owners.required'               => 'Debes agregar al menos un propietario.',
            'owners.min'                    => 'Debes agregar al menos un propietario.',
            'owners.*.owner_id.distinct'    => 'Un propietario está repetido.',
            'projectists.required'          => 'Debes agregar al menos un proyectista.',
            'projectists.min'               => 'Debes agregar al menos un proyectista.',
            'projectists.*.engineer_id.exists'   => 'Uno de los proyectistas no existe o no está activo (puede estar suspendido).',
            'projectists.*.engineer_id.distinct' => 'Un proyectista está repetido.',
            'parameters.*.parameter_id.distinct' => 'Un parámetro está repetido.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'title'                 => 'título',
            'primary_category_id'   => 'categoría primaria',
            'secondary_category_id' => 'categoría secundaria',
            'tertiary_category_id'  => 'categoría terciaria',
            'topic_name'            => 'tema del informe',
            'address'               => 'dirección',
            'zone'                  => 'zona',
            'municipality'          => 'municipio',
            'owners'                => 'propietarios',
            'projectists'           => 'proyectistas',
            'standards'             => 'normas',
            'parameters'            => 'parámetros',
            'parameters.*.value'    => 'valor',
        ];
    }
}
