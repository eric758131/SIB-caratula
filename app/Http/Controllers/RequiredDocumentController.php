<?php

namespace App\Http\Controllers;

use App\Models\RequiredDocument;
use App\Models\TertiaryCategory;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RequiredDocumentController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', '');

        $requiredDocuments = RequiredDocument::query()
            ->withCount('tertiaryCategories')
            ->whereSearch($search, ['name', 'id', 'tertiaryCategories.name', 'tertiaryCategories.code'])
            ->when($status !== '' && $status !== null, function ($query) use ($status) {
                $query->where('status', filter_var($status, FILTER_VALIDATE_BOOLEAN));
            })
            ->sortable([
                'id'         => 'id',
                'name'       => 'name',
                // Primero los que tienen archivo (o al revés)
                'file'       => fn ($q, $dir) => $q->orderByRaw('file_path IS NULL ' . ($dir === 'asc' ? 'ASC' : 'DESC')),
                'tertiaries' => fn ($q, $dir) => $q->orderBy('tertiary_categories_count', $dir),
                'status'     => 'status',
            ], 'name')
            ->paginate(15)
            ->withQueryString();

        return view('required-documents.index', compact('requiredDocuments', 'search', 'status'));
    }

    public function create()
    {
        $tertiaryCategories = TertiaryCategory::with('secondaryCategory.primaryCategory')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('required-documents.create', compact('tertiaryCategories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateDocument($request);

        $validated['name'] = $this->normalizeName($validated['name']);
        $validated['status'] = $request->boolean('status', true);

        if ($request->hasFile('file_path')) {
            $validated['file_path'] = $request->file('file_path')->store('required-documents', 'public');
        }

        try {
            $document = RequiredDocument::create($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['name' => 'Ya existe un documento requerido con ese nombre.']);
            }
            throw $e;
        }

        $document->tertiaryCategories()->sync($request->input('tertiary_categories', []));

        return redirect()
            ->route('required-documents.index')
            ->with('success', 'Documento requerido creado correctamente.');
    }

    public function edit(RequiredDocument $requiredDocument)
    {
        $requiredDocument->load('tertiaryCategories');

        $assignedIds = $requiredDocument->tertiaryCategories->pluck('id')->toArray();

        $tertiaryCategories = TertiaryCategory::with('secondaryCategory.primaryCategory')
            ->where(function ($q) use ($assignedIds) {
                $q->where('status', true);
                if (!empty($assignedIds)) {
                    $q->orWhereIn('id', $assignedIds);
                }
            })
            ->orderBy('name')
            ->get();

        return view('required-documents.edit', compact('requiredDocument', 'tertiaryCategories', 'assignedIds'));
    }

    public function update(Request $request, RequiredDocument $requiredDocument)
    {
        $validated = $this->validateDocument($request, $requiredDocument);

        $validated['name'] = $this->normalizeName($validated['name']);
        $validated['status'] = $request->boolean('status', true);

        // Eliminar archivo actual
        if ($request->boolean('remove_file') && !$request->hasFile('file_path')) {
            if ($requiredDocument->file_path) {
                Storage::disk('public')->delete($requiredDocument->file_path);
            }
            $validated['file_path'] = null;
        }

        // Subir nuevo archivo
        if ($request->hasFile('file_path')) {
            if ($requiredDocument->file_path) {
                Storage::disk('public')->delete($requiredDocument->file_path);
            }
            $validated['file_path'] = $request->file('file_path')->store('required-documents', 'public');
        }

        try {
            $requiredDocument->update($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['name' => 'Ya existe un documento requerido con ese nombre.']);
            }
            throw $e;
        }

        $requiredDocument->tertiaryCategories()->sync($request->input('tertiary_categories', []));

        return redirect()
            ->route('required-documents.index')
            ->with('success', 'Documento requerido actualizado correctamente.');
    }

    public function destroy(RequiredDocument $requiredDocument)
    {
        if ($requiredDocument->status && $requiredDocument->tertiaryCategories()->exists()) {
            return redirect()
                ->route('required-documents.index')
                ->with('error', 'No se puede desactivar: el documento está asignado a una o más categorías terciarias.');
        }

        $requiredDocument->update(['status' => ! $requiredDocument->status]);

        $message = $requiredDocument->status
            ? 'Documento requerido reactivado correctamente.'
            : 'Documento requerido desactivado correctamente.';

        return redirect()
            ->route('required-documents.index')
            ->with('success', $message);
    }

    private function validateDocument(Request $request, ?RequiredDocument $document = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:200',
                'regex:/^[\pL\pN\s\.\-\(\)\/]+$/u',
                Rule::unique('required_documents', 'name')
                    ->where(function ($query) use ($request) {
                        $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->input('name')), 'UTF-8')]);
                    })
                    ->ignore($document?->id),
            ],
            'file_path' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
                'max:8192',
            ],
            'status' => ['nullable', 'boolean'],
            'tertiary_categories' => ['nullable', 'array'],
            'tertiary_categories.*' => ['integer', Rule::exists('tertiary_categories', 'id')->where('status', true)],
        ], $this->validationMessages(), $this->validationAttributes());
    }

    private function normalizeName(string $name): string
    {
        $name = preg_replace('/\s+/', ' ', trim($name));

        return mb_convert_case(mb_strtolower($name, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    private function validationMessages(): array
    {
        return [
            'name.required' => 'El nombre del documento es obligatorio.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'name.max' => 'El nombre no puede superar los :max caracteres.',
            'name.regex' => 'El nombre solo puede contener letras, números, espacios, puntos, guiones, paréntesis y barras.',
            'name.unique' => 'Ya existe un documento requerido con ese nombre.',
            'file_path.file' => 'El archivo no es válido.',
            'file_path.mimes' => 'El archivo debe ser PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG o PNG.',
            'file_path.max' => 'El archivo no puede superar los 8 MB.',
            'tertiary_categories.array' => 'Las categorías terciarias deben ser un arreglo.',
            'tertiary_categories.*.exists' => 'Una de las categorías terciarias seleccionadas no es válida o está inactiva.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'name' => 'nombre',
            'file_path' => 'archivo',
            'status' => 'estado',
            'tertiary_categories' => 'categorías terciarias',
        ];
    }
}