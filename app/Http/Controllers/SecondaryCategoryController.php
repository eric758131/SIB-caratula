<?php

namespace App\Http\Controllers;

use App\Models\PrimaryCategory;
use App\Models\SecondaryCategory;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SecondaryCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $primaryId = $request->input('primary_category_id', '');
        $status = $request->input('status', '');

        $secondaryCategories = SecondaryCategory::query()
            ->with('primaryCategory')
            ->whereSearch($search, ['name', 'description', 'id', 'primaryCategory.name'])
            ->when($primaryId !== '' && $primaryId !== null, function ($query) use ($primaryId) {
                $query->where('primary_category_id', $primaryId);
            })
            ->when($status !== '' && $status !== null, function ($query) use ($status) {
                $query->where('status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
            })
            ->sortable([
                'id'      => 'id',
                'name'    => 'name',
                'primary' => fn ($q, $dir) => $q->orderBy(
                    PrimaryCategory::select('name')->whereColumn('primary_categories.id', 'secondary_categories.primary_category_id'),
                    $dir
                ),
                'status'  => 'status',
            ], 'name')
            ->paginate(10)
            ->withQueryString();

        $primaryCategories = PrimaryCategory::orderBy('name')->get();

        return view('secondary-categories.index', compact(
            'secondaryCategories',
            'primaryCategories',
            'search',
            'primaryId',
            'status'
        ));
    }

    public function create()
    {
        $primaryCategories = PrimaryCategory::where('status', true)->orderBy('name')->get();

        return view('secondary-categories.create', compact('primaryCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'primary_category_id' => [
                'required',
                'integer',
                Rule::exists('primary_categories', 'id')->where('status', true),
            ],
            'name' => [
                'required',
                'string',
                'min:3',
                'max:200',
                'regex:/^[\pL\pN\s\.\-\(\)\/]+$/u',
                Rule::unique('secondary_categories', 'name')->where(function ($query) use ($request) {
                    $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                }),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'example' => ['nullable', 'string', 'max:2000'],
            'important_notes' => ['nullable', 'string', 'max:2000'],
            'image_1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_3' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'definition' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'status' => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());

        $validated['name'] = $this->normalizeName($validated['name']);
        $validated['description'] = $this->normalizeText($validated['description'] ?? null);
        $validated['example'] = $this->normalizeText($validated['example'] ?? null);
        $validated['important_notes'] = $this->normalizeText($validated['important_notes'] ?? null);
        $validated['status'] = $request->boolean('status', true);

        foreach (['image_1', 'image_2', 'image_3'] as $field) {
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store('secondary-categories', 'public');
            }
        }

        if ($request->hasFile('definition')) {
            $validated['definition'] = $request->file('definition')->store('secondary-categories/definitions', 'public');
        }

        try {
            SecondaryCategory::create($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['name' => 'Ya existe una categoría secundaria con ese nombre.']);
            }
            throw $e;
        }

        return redirect()
            ->route('secondary-categories.index')
            ->with('success', 'Categoría secundaria creada correctamente.');
    }

    public function edit(SecondaryCategory $secondaryCategory)
    {
        $primaryCategories = PrimaryCategory::where(function ($q) use ($secondaryCategory) {
                $q->where('status', true)
                  ->orWhere('id', $secondaryCategory->primary_category_id);
            })
            ->orderBy('name')
            ->get();

        return view('secondary-categories.edit', compact('secondaryCategory', 'primaryCategories'));
    }

    public function update(Request $request, SecondaryCategory $secondaryCategory)
    {
        $validated = $request->validate([
            'primary_category_id' => [
                'required',
                'integer',
                Rule::exists('primary_categories', 'id')->where(function ($q) use ($secondaryCategory) {
                    $q->where('status', true)
                    ->orWhere('id', $secondaryCategory->primary_category_id);
                }),
            ],
            'name' => [
                'required',
                'string',
                'min:3',
                'max:200',
                'regex:/^[\pL\pN\s\.\-\(\)\/]+$/u',
                Rule::unique('secondary_categories', 'name')
                    ->where(function ($query) use ($request) {
                        $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                    })
                    ->ignore($secondaryCategory->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'example' => ['nullable', 'string', 'max:2000'],
            'important_notes' => ['nullable', 'string', 'max:2000'],
            'image_1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_3' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'definition' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'status' => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());

        $validated['name'] = $this->normalizeName($validated['name']);
        $validated['description'] = $this->normalizeText($validated['description'] ?? null);
        $validated['example'] = $this->normalizeText($validated['example'] ?? null);
        $validated['important_notes'] = $this->normalizeText($validated['important_notes'] ?? null);
        $validated['status'] = $request->boolean('status', true);

        // Documento de definición: eliminar (sin reemplazo) o reemplazar
        if ($request->boolean('remove_definition') && !$request->hasFile('definition')) {
            if ($secondaryCategory->definition) {
                Storage::disk('public')->delete($secondaryCategory->definition);
            }
            $validated['definition'] = null;
        }

        if ($request->hasFile('definition')) {
            if ($secondaryCategory->definition) {
                Storage::disk('public')->delete($secondaryCategory->definition);
            }
            $validated['definition'] = $request->file('definition')->store('secondary-categories/definitions', 'public');
        }

        $imageFields = ['image_1', 'image_2', 'image_3'];

        // Eliminar imágenes marcadas (y sin reemplazo)
        foreach ($imageFields as $field) {
            if ($request->boolean("remove_{$field}") && !$request->hasFile($field)) {
                if ($secondaryCategory->$field) {
                    Storage::disk('public')->delete($secondaryCategory->$field);
                }
                $validated[$field] = null;
            }
        }

        // Subir nuevas imágenes (reemplazan las anteriores)
        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                if ($secondaryCategory->$field) {
                    Storage::disk('public')->delete($secondaryCategory->$field);
                }
                $validated[$field] = $request->file($field)->store('secondary-categories', 'public');
            }
        }

        try {
            $secondaryCategory->update($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['name' => 'Ya existe una categoría secundaria con ese nombre.']);
            }
            throw $e;
        }

        return redirect()
            ->route('secondary-categories.index')
            ->with('success', 'Categoría secundaria actualizada correctamente.');
    }

    public function destroy(SecondaryCategory $secondaryCategory)
    {
        $secondaryCategory->update(['status' => ! $secondaryCategory->status]);

        $message = $secondaryCategory->status
            ? 'Categoría secundaria reactivada correctamente.'
            : 'Categoría secundaria desactivada correctamente.';

        return redirect()
            ->route('secondary-categories.index')
            ->with('success', $message);
    }

    private function normalizeName(string $name): string
    {
        $name = preg_replace('/\s+/', ' ', trim($name));

        return mb_convert_case(mb_strtolower($name, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    private function normalizeText(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text) ?: null;
    }

    private function validationMessages(): array
    {
        return [
            'primary_category_id.required' => 'Debes seleccionar una categoría primaria.',
            'primary_category_id.exists' => 'La categoría primaria seleccionada no es válida o está inactiva.',
            'name.required' => 'El nombre es obligatorio.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'name.max' => 'El nombre no puede superar los :max caracteres.',
            'name.regex' => 'El nombre solo puede contener letras, números, espacios, puntos, guiones, paréntesis y barras.',
            'name.unique' => 'Ya existe una categoría secundaria con ese nombre.',
            'description.max' => 'La descripción no puede superar los :max caracteres.',
            'example.max' => 'El ejemplo no puede superar los :max caracteres.',
            'important_notes.max' => 'Las notas importantes no pueden superar los :max caracteres.',
            'image_1.image' => 'La imagen 1 debe ser un archivo de imagen.',
            'image_1.mimes' => 'La imagen 1 debe ser jpg, jpeg, png o webp.',
            'image_1.max' => 'La imagen 1 no puede superar los 4 MB.',
            'image_2.image' => 'La imagen 2 debe ser un archivo de imagen.',
            'image_2.mimes' => 'La imagen 2 debe ser jpg, jpeg, png o webp.',
            'image_2.max' => 'La imagen 2 no puede superar los 4 MB.',
            'image_3.image' => 'La imagen 3 debe ser un archivo de imagen.',
            'image_3.mimes' => 'La imagen 3 debe ser jpg, jpeg, png o webp.',
            'image_3.max' => 'La imagen 3 no puede superar los 4 MB.',
            'definition.file' => 'El documento de definición no es válido.',
            'definition.mimes' => 'El documento de definición debe ser PDF, JPG, JPEG, PNG o WEBP.',
            'definition.max' => 'El documento de definición no puede superar los 10 MB.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'primary_category_id' => 'categoría primaria',
            'name' => 'nombre',
            'description' => 'descripción',
            'example' => 'ejemplo',
            'important_notes' => 'notas importantes',
            'image_1' => 'imagen 1',
            'image_2' => 'imagen 2',
            'image_3' => 'imagen 3',
            'definition' => 'documento de definición',
        ];
    }
}