<?php

namespace App\Http\Controllers;

use App\Models\SecondaryCategory;
use App\Models\TertiaryCategory;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TertiaryCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search      = trim((string) $request->input('search', ''));
        $secondaryId = $request->input('secondary_category_id', '');
        $status      = $request->input('status', '');

        $tertiaryCategories = TertiaryCategory::query()
            ->with('secondaryCategory.primaryCategory')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                      ->orWhere('code', 'ILIKE', "%{$search}%")
                      ->orWhere('description', 'ILIKE', "%{$search}%")
                      ->orWhere('id', 'ILIKE', "%{$search}%");
                });
            })
            ->when($secondaryId !== '' && $secondaryId !== null, function ($query) use ($secondaryId) {
                $query->where('secondary_category_id', $secondaryId);
            })
            ->when($status !== '' && $status !== null, function ($query) use ($status) {
                $query->where('status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        $secondaryCategories = SecondaryCategory::with('primaryCategory')->orderBy('name')->get();

        return view('tertiary-categories.index', compact(
            'tertiaryCategories',
            'secondaryCategories',
            'search',
            'secondaryId',
            'status'
        ));
    }

    public function create()
    {
        $secondaryCategories = SecondaryCategory::with('primaryCategory')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('tertiary-categories.create', compact('secondaryCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'secondary_category_id' => [
                'required',
                'integer',
                Rule::exists('secondary_categories', 'id')->where('status', true),
            ],
            'code' => [
                'required',
                'string',
                'min:1',
                'max:20',
                'regex:/^[A-Z0-9\-\.\/]+$/',
                Rule::unique('tertiary_categories', 'code'),
            ],
            'name' => [
                'required',
                'string',
                'min:3',
                'max:200',
                'regex:/^[\pL\pN\s\.\-\(\)\/]+$/u',
                Rule::unique('tertiary_categories', 'name')->where(function ($query) use ($request) {
                    $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                }),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'example' => ['nullable', 'string', 'max:2000'],
            'important_notes' => ['nullable', 'string', 'max:2000'],
            'image_1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_3' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'status' => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());

        $validated['code'] = $this->normalizeCode($validated['code']);
        $validated['name'] = $this->normalizeName($validated['name']);
        $validated['description'] = $this->normalizeText($validated['description'] ?? null);
        $validated['example'] = $this->normalizeText($validated['example'] ?? null);
        $validated['important_notes'] = $this->normalizeText($validated['important_notes'] ?? null);
        $validated['status'] = $request->boolean('status', true);

        foreach (['image_1', 'image_2', 'image_3'] as $field) {
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store('tertiary-categories', 'public');
            }
        }

        try {
            TertiaryCategory::create($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['code' => 'Ya existe una categoría terciaria con ese código o nombre.']);
            }
            throw $e;
        }

        return redirect()
            ->route('tertiary-categories.index')
            ->with('success', 'Categoría terciaria creada correctamente.');
    }

    public function edit(TertiaryCategory $tertiaryCategory)
    {
        $secondaryCategories = SecondaryCategory::with('primaryCategory')
            ->where(function ($q) use ($tertiaryCategory) {
                $q->where('status', true)
                  ->orWhere('id', $tertiaryCategory->secondary_category_id);
            })
            ->orderBy('name')
            ->get();

        return view('tertiary-categories.edit', compact('tertiaryCategory', 'secondaryCategories'));
    }

    public function update(Request $request, TertiaryCategory $tertiaryCategory)
    {
        $validated = $request->validate([
            'secondary_category_id' => [
                'required',
                'integer',
                Rule::exists('secondary_categories', 'id')->where(function ($q) use ($tertiaryCategory) {
                    $q->where('status', true)
                    ->orWhere('id', $tertiaryCategory->secondary_category_id);
                }),
            ],
            'code' => [
                'required',
                'string',
                'min:1',
                'max:20',
                'regex:/^[A-Z0-9\-\.\/]+$/',
                Rule::unique('tertiary_categories', 'code')->ignore($tertiaryCategory->id),
            ],
            'name' => [
                'required',
                'string',
                'min:3',
                'max:200',
                'regex:/^[\pL\pN\s\.\-\(\)\/]+$/u',
                Rule::unique('tertiary_categories', 'name')
                    ->where(function ($query) use ($request) {
                        $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                    })
                    ->ignore($tertiaryCategory->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'example' => ['nullable', 'string', 'max:2000'],
            'important_notes' => ['nullable', 'string', 'max:2000'],
            'image_1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_3' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'status' => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());

        $validated['code'] = $this->normalizeCode($validated['code']);
        $validated['name'] = $this->normalizeName($validated['name']);
        $validated['description'] = $this->normalizeText($validated['description'] ?? null);
        $validated['example'] = $this->normalizeText($validated['example'] ?? null);
        $validated['important_notes'] = $this->normalizeText($validated['important_notes'] ?? null);
        $validated['status'] = $request->boolean('status', true);

        $imageFields = ['image_1', 'image_2', 'image_3'];

        // Eliminar imágenes marcadas (y sin reemplazo)
        foreach ($imageFields as $field) {
            if ($request->boolean("remove_{$field}") && !$request->hasFile($field)) {
                if ($tertiaryCategory->$field) {
                    Storage::disk('public')->delete($tertiaryCategory->$field);
                }
                $validated[$field] = null;
            }
        }

        // Subir nuevas imágenes (reemplazan las anteriores)
        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                if ($tertiaryCategory->$field) {
                    Storage::disk('public')->delete($tertiaryCategory->$field);
                }
                $validated[$field] = $request->file($field)->store('tertiary-categories', 'public');
            }
        }

        try {
            $tertiaryCategory->update($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['code' => 'Ya existe una categoría terciaria con ese código o nombre.']);
            }
            throw $e;
        }

        return redirect()
            ->route('tertiary-categories.index')
            ->with('success', 'Categoría terciaria actualizada correctamente.');
    }

    public function destroy(TertiaryCategory $tertiaryCategory)
    {
        $tertiaryCategory->update(['status' => ! $tertiaryCategory->status]);

        $message = $tertiaryCategory->status
            ? 'Categoría terciaria reactivada correctamente.'
            : 'Categoría terciaria desactivada correctamente.';

        return redirect()
            ->route('tertiary-categories.index')
            ->with('success', $message);
    }

    private function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
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
            'secondary_category_id.required' => 'Debes seleccionar una categoría secundaria.',
            'secondary_category_id.exists' => 'La categoría secundaria seleccionada no es válida o está inactiva.',
            'code.required' => 'El código es obligatorio.',
            'code.min' => 'El código debe tener al menos :min caracteres.',
            'code.max' => 'El código no puede superar los :max caracteres.',
            'code.regex' => 'El código solo puede contener letras mayúsculas, números, guiones, puntos y barras.',
            'code.unique' => 'Ya existe una categoría terciaria con ese código.',
            'name.required' => 'El nombre es obligatorio.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'name.max' => 'El nombre no puede superar los :max caracteres.',
            'name.regex' => 'El nombre solo puede contener letras, números, espacios, puntos, guiones, paréntesis y barras.',
            'name.unique' => 'Ya existe una categoría terciaria con ese nombre.',
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
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'secondary_category_id' => 'categoría secundaria',
            'code' => 'código',
            'name' => 'nombre',
            'description' => 'descripción',
            'example' => 'ejemplo',
            'important_notes' => 'notas importantes',
            'image_1' => 'imagen 1',
            'image_2' => 'imagen 2',
            'image_3' => 'imagen 3',
        ];
    }
}