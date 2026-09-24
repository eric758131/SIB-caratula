<?php

namespace App\Http\Controllers;

use App\Models\Parameter;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ParameterController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $type   = $request->input('parameter_type', '');
        $status = $request->input('status', '');

        $parameters = Parameter::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                      ->orWhere('description', 'ILIKE', "%{$search}%")
                      ->orWhere('unit_of_measure', 'ILIKE', "%{$search}%")
                      ->orWhere('id', 'ILIKE', "%{$search}%");
                });
            })
            ->when($type !== '' && $type !== null, fn($q) => $q->where('parameter_type', $type))
            ->when($status !== '' && $status !== null, function ($query) use ($status) {
                $query->where('status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('parameters.index', compact('parameters', 'search', 'type', 'status'));
    }

    public function create()
    {
        return view('parameters.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateParameter($request);

        $validated = $this->normalizeFields($validated);

        foreach (['image_1', 'image_2', 'image_3'] as $field) {
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store('parameters', 'public');
            }
        }

        try {
            Parameter::create($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['name' => 'Ya existe un parámetro con ese nombre.']);
            }
            throw $e;
        }

        return redirect()
            ->route('parameters.index')
            ->with('success', 'Parámetro creado correctamente.');
    }

    public function edit(Parameter $parameter)
    {
        return view('parameters.edit', compact('parameter'));
    }

    public function update(Request $request, Parameter $parameter)
    {
        $validated = $this->validateParameter($request, $parameter);

        $validated = $this->normalizeFields($validated);

        $imageFields = ['image_1', 'image_2', 'image_3'];

        // Eliminar imágenes marcadas (y sin reemplazo)
        foreach ($imageFields as $field) {
            if ($request->boolean("remove_{$field}") && !$request->hasFile($field)) {
                if ($parameter->$field) {
                    Storage::disk('public')->delete($parameter->$field);
                }
                $validated[$field] = null;
            }
        }

        // Subir nuevas imágenes (reemplazan las anteriores)
        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                if ($parameter->$field) {
                    Storage::disk('public')->delete($parameter->$field);
                }
                $validated[$field] = $request->file($field)->store('parameters', 'public');
            }
        }

        try {
            $parameter->update($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['name' => 'Ya existe un parámetro con ese nombre.']);
            }
            throw $e;
        }

        return redirect()
            ->route('parameters.index')
            ->with('success', 'Parámetro actualizado correctamente.');
    }

    public function destroy(Parameter $parameter)
    {
        $parameter->update(['status' => ! $parameter->status]);

        $message = $parameter->status
            ? 'Parámetro reactivado correctamente.'
            : 'Parámetro desactivado correctamente.';

        return redirect()
            ->route('parameters.index')
            ->with('success', $message);
    }

    /* ============================================================
     |  Validación central
     * ============================================================ */
    private function validateParameter(Request $request, ?Parameter $parameter = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:200',
                'regex:/^[\pL\pN\s\.\-\(\)\/]+$/u',
                Rule::unique('parameters', 'name')
                    ->where(function ($query) use ($request) {
                        $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                    })
                    ->ignore($parameter?->id),
            ],
            'description'     => ['nullable', 'string', 'max:2000'],
            'example'         => ['nullable', 'string', 'max:2000'],
            'important_notes' => ['nullable', 'string', 'max:2000'],
            'unit_of_measure' => ['nullable', 'string', 'max:50'],
            'parameter_type'  => ['required', Rule::in(Parameter::TYPES)],
            'image_1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_3' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'status'  => ['nullable', 'boolean'],
        ], $this->validationMessages(), $this->validationAttributes());
    }

    private function normalizeFields(array $data): array
    {
        $data['name']            = $this->normalizeName($data['name']);
        $data['description']     = $this->normalizeText($data['description'] ?? null);
        $data['example']         = $this->normalizeText($data['example'] ?? null);
        $data['important_notes'] = $this->normalizeText($data['important_notes'] ?? null);
        $data['unit_of_measure'] = !empty($data['unit_of_measure']) ? trim($data['unit_of_measure']) : null;
        $data['status']          = request()->boolean('status', true);

        return $data;
    }

    private function normalizeName(string $name): string
    {
        $name = preg_replace('/\s+/', ' ', trim($name));

        return mb_convert_case(mb_strtolower($name, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    private function normalizeText(?string $text): ?string
    {
        if ($text === null) return null;
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text) ?: null;
    }

    private function validationMessages(): array
    {
        return [
            'name.required' => 'El nombre del parámetro es obligatorio.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'name.max' => 'El nombre no puede superar los :max caracteres.',
            'name.regex' => 'El nombre solo puede contener letras, números, espacios, puntos, guiones, paréntesis y barras.',
            'name.unique' => 'Ya existe un parámetro con ese nombre.',
            'parameter_type.required' => 'Debes seleccionar un tipo de parámetro.',
            'parameter_type.in' => 'El tipo de parámetro seleccionado no es válido.',
            'unit_of_measure.max' => 'La unidad de medida no puede superar los :max caracteres.',
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
            'name' => 'nombre',
            'description' => 'descripción',
            'example' => 'ejemplo',
            'important_notes' => 'notas importantes',
            'unit_of_measure' => 'unidad de medida',
            'parameter_type' => 'tipo de parámetro',
            'image_1' => 'imagen 1',
            'image_2' => 'imagen 2',
            'image_3' => 'imagen 3',
        ];
    }
}