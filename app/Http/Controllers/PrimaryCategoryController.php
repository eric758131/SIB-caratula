<?php

namespace App\Http\Controllers;

use App\Models\PrimaryCategory;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PrimaryCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', '');

        $primaryCategories = PrimaryCategory::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                      ->orWhere('description', 'ILIKE', "%{$search}%")
                      ->orWhere('id', 'ILIKE', "%{$search}%");
                });
            })
            ->when($status !== '' && $status !== null, function ($query) use ($status) {
                $query->where('status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('primary-categories.index', compact('primaryCategories', 'search', 'status'));
    }

    public function create()
    {
        return view('primary-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:200',
                'regex:/^[\pL\pN\s\.\-\(\)\/]+$/u',
                Rule::unique('primary_categories', 'name')->where(function ($query) use ($request) {
                    $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                }),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'example' => ['nullable', 'string', 'max:2000'],
            'important_notes' => ['nullable', 'string', 'max:2000'],
            'image_1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_3' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'logo_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'status' => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());

        $validated['name'] = $this->normalizeName($validated['name']);
        $validated['description'] = $this->normalizeText($validated['description'] ?? null);
        $validated['example'] = $this->normalizeText($validated['example'] ?? null);
        $validated['important_notes'] = $this->normalizeText($validated['important_notes'] ?? null);
        $validated['status'] = $request->boolean('status', true);

        foreach (['image_1', 'image_2', 'image_3', 'logo_image'] as $field) {
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store('primary-categories', 'public');
            }
        }

        try {
            PrimaryCategory::create($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['name' => 'Ya existe una categoría primaria con ese nombre.']);
            }
            throw $e;
        }

        return redirect()
            ->route('primary-categories.index')
            ->with('success', 'Categoría primaria creada correctamente.');
    }

    public function edit(PrimaryCategory $primaryCategory)
    {
        return view('primary-categories.edit', compact('primaryCategory'));
    }

    public function update(Request $request, PrimaryCategory $primaryCategory)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:200',
                'regex:/^[\pL\pN\s\.\-\(\)\/]+$/u',
                Rule::unique('primary_categories', 'name')
                    ->where(function ($query) use ($request) {
                        $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                    })
                    ->ignore($primaryCategory->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'example' => ['nullable', 'string', 'max:2000'],
            'important_notes' => ['nullable', 'string', 'max:2000'],
            'image_1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_3' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'logo_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'status' => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());

        $validated['name'] = $this->normalizeName($validated['name']);
        $validated['description'] = $this->normalizeText($validated['description'] ?? null);
        $validated['example'] = $this->normalizeText($validated['example'] ?? null);
        $validated['important_notes'] = $this->normalizeText($validated['important_notes'] ?? null);
        $validated['status'] = $request->boolean('status', true);

        foreach (['image_1', 'image_2', 'image_3', 'logo_image'] as $field) {
            if ($request->hasFile($field)) {
                if ($primaryCategory->$field) {
                    Storage::disk('public')->delete($primaryCategory->$field);
                }
                $validated[$field] = $request->file($field)->store('primary-categories', 'public');
            }
        }

        try {
            $primaryCategory->update($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['name' => 'Ya existe una categoría primaria con ese nombre.']);
            }
            throw $e;
        }

        return redirect()
            ->route('primary-categories.index')
            ->with('success', 'Categoría primaria actualizada correctamente.');
    }

    public function destroy(PrimaryCategory $primaryCategory)
    {
        $primaryCategory->update(['status' => ! $primaryCategory->status]);

        $message = $primaryCategory->status
            ? 'Categoría primaria reactivada correctamente.'
            : 'Categoría primaria desactivada correctamente.';

        return redirect()
            ->route('primary-categories.index')
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
            'name.required' => 'El nombre es obligatorio.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'name.max' => 'El nombre no puede superar los :max caracteres.',
            'name.regex' => 'El nombre solo puede contener letras, números, espacios, puntos, guiones, paréntesis y barras.',
            'name.unique' => 'Ya existe una categoría primaria con ese nombre.',
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
            'logo_image.image' => 'El logo debe ser un archivo de imagen.',
            'logo_image.mimes' => 'El logo debe ser jpg, jpeg, png o webp.',
            'logo_image.max' => 'El logo no puede superar los 4 MB.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'example' => 'ejemplo',
            'important_notes' => 'notas importantes',
            'image_1' => 'imagen 1',
            'image_2' => 'imagen 2',
            'image_3' => 'imagen 3',
            'logo_image' => 'logo',
        ];
    }
}