<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Engineer;
use App\Models\Specialty;
use App\Models\University;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class EngineerController extends Controller
{
    public function index(Request $request)
    {
        $search         = trim((string) $request->input('search', ''));
        $branchId       = $request->input('branch_id', '');
        $universityId   = $request->input('university_id', '');
        $departmental   = $request->input('sib_departmental', '');
        $status         = $request->input('status', '');

        $engineers = Engineer::query()
            ->with(['branch', 'university'])
            // "juan perez" encuentra a Juan (nombre) Pérez (apellido): cada palabra en cualquier columna
            ->whereSearch($search, [
                'name', 'father_last_name', 'mother_last_name', 'rni', 'ci', 'email', 'id',
                'branch.name', 'university.name',
            ])
            ->when($branchId !== '' && $branchId !== null, fn($q) => $q->where('branch_id', $branchId))
            ->when($universityId !== '' && $universityId !== null, fn($q) => $q->where('university_id', $universityId))
            ->when($departmental !== '' && $departmental !== null, fn($q) => $q->where('sib_departmental', $departmental))
            ->when($status !== '' && $status !== null, fn($q) => $q->where('status', $status))
            ->sortable([
                'id'           => 'id',
                // Ingeniero: apellidos y luego nombre
                'engineer'     => ['father_last_name', 'mother_last_name', 'name'],
                'rni'          => fn ($q, $dir) => $q->orderByRaw('LENGTH(rni) ' . $dir)->orderBy('rni', $dir),
                'ci'           => 'ci',
                'branch'       => fn ($q, $dir) => $q->orderBy(
                    Branch::select('name')->whereColumn('branches.id', 'engineers.branch_id'),
                    $dir
                ),
                'departmental' => 'sib_departmental',
                'status'       => 'status',
            ], 'engineer')
            ->paginate(15)
            ->withQueryString();

        $branches     = Branch::orderBy('name')->get();
        $universities = University::orderBy('name')->get();

        return view('engineers.index', compact(
            'engineers',
            'branches',
            'universities',
            'search',
            'branchId',
            'universityId',
            'departmental',
            'status'
        ));
    }

    public function create()
    {
        $branches     = Branch::where('status', true)->orderBy('name')->get();
        $universities = University::where('status', true)->orderBy('name')->get();
        $specialties  = Specialty::where('status', true)->orderBy('name')->get();

        return view('engineers.create', compact('branches', 'universities', 'specialties'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateEngineer($request);

        $validated = $this->normalizeFields($validated);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('engineers', 'public');
        }

        try {
            $engineer = Engineer::create($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['rni' => 'Ya existe un ingeniero con ese RNI o CI.']);
            }
            throw $e;
        }

        if ($request->filled('specialties')) {
            $engineer->specialties()->sync($request->input('specialties', []));
        }

        return redirect()
            ->route('engineers.index')
            ->with('success', 'Ingeniero creado correctamente.');
    }

    public function edit(Engineer $engineer)
    {
        $engineer->load('specialties');

        $branches = Branch::where(function ($q) use ($engineer) {
                $q->where('status', true)->orWhere('id', $engineer->branch_id);
            })->orderBy('name')->get();

        $universities = University::where(function ($q) use ($engineer) {
                $q->where('status', true)->orWhere('id', $engineer->university_id);
            })->orderBy('name')->get();

        $specialties = Specialty::where(function ($q) use ($engineer) {
                $q->where('status', true);
                $currentIds = $engineer->specialties->pluck('id')->toArray();
                if (!empty($currentIds)) {
                    $q->orWhereIn('id', $currentIds);
                }
            })->orderBy('name')->get();

        return view('engineers.edit', compact('engineer', 'branches', 'universities', 'specialties'));
    }

    public function update(Request $request, Engineer $engineer)
    {
        $validated = $this->validateEngineer($request, $engineer);

        $validated = $this->normalizeFields($validated);

        // Eliminar imagen actual si el usuario lo marcó y NO sube una nueva
        if ($request->boolean('remove_image') && !$request->hasFile('image')) {
            if ($engineer->image) {
                Storage::disk('public')->delete($engineer->image);
            }
            $validated['image'] = null;
        }

        // Subir nueva imagen (reemplaza la anterior)
        if ($request->hasFile('image')) {
            if ($engineer->image) {
                Storage::disk('public')->delete($engineer->image);
            }
            $validated['image'] = $request->file('image')->store('engineers', 'public');
        }

        try {
            $engineer->update($validated);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                return back()->withInput()
                    ->withErrors(['rni' => 'Ya existe un ingeniero con ese RNI o CI.']);
            }
            throw $e;
        }

        $engineer->specialties()->sync($request->input('specialties', []));

        return redirect()
            ->route('engineers.index')
            ->with('success', 'Ingeniero actualizado correctamente.');
    }

    public function destroy(Engineer $engineer)
    {
        $deactivatable = [
            Engineer::STATUS_ACTIVO,
            Engineer::STATUS_EMERITO,
        ];

        if (in_array($engineer->status, $deactivatable, true)) {
            // Guardamos el estado actual para poder restaurarlo después
            $engineer->update([
                'status' => Engineer::STATUS_INACTIVO,
            ]);

            return redirect()
                ->route('engineers.index')
                ->with('success', 'Ingeniero desactivado correctamente.');
        }

        // Reactivar → siempre a activo
        $engineer->update(['status' => Engineer::STATUS_ACTIVO]);

        return redirect()
            ->route('engineers.index')
            ->with('success', 'Ingeniero activado correctamente.');
    }

    /* ============================================================
     |  Validación central
     * ============================================================ */
    private function validateEngineer(Request $request, ?Engineer $engineer = null): array
    {
        $id = $engineer?->id;

        $rules = [
            'branch_id' => [
                'required',
                'integer',
                Rule::exists('branches', 'id')->where(function ($q) use ($engineer) {
                    $q->where('status', true);
                    if ($engineer) {
                        $q->orWhere('id', $engineer->branch_id);
                    }
                }),
            ],
            'university_id' => [
                'required',
                'integer',
                Rule::exists('universities', 'id')->where(function ($q) use ($engineer) {
                    $q->where('status', true);
                    if ($engineer) {
                        $q->orWhere('id', $engineer->university_id);
                    }
                }),
            ],
            'rni' => [
                'required',
                'string',
                'digits_between:4,20',
                Rule::unique('engineers', 'rni')->ignore($id),
            ],
            'name' => [
                'required',
                'string',
                'min:2',
                'max:200',
                "regex:/^[\pL\s'\-\.]+$/u",
            ],
            'father_last_name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                "regex:/^[\pL\s'\-\.]+$/u",
            ],
            'mother_last_name' => [
                'nullable',
                'string',
                'min:2',
                'max:100',
                "regex:/^[\pL\s'\-\.]+$/u",
            ],
            'ci' => [
                'required',
                'string',
                'max:20',
                'regex:/^\d{5,15}(-[A-Z0-9]{1,3})?$/',
                Rule::unique('engineers', 'ci')->ignore($id),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[\d\+\-\s\(\)]+$/',
            ],
            'email' => [
                'nullable',
                'string',
                'email:rfc',
                'max:100',
            ],
            'address' => ['nullable', 'string', 'max:500'],
            'sib_departmental' => [
                'required',
                Rule::in(Engineer::SIB_DEPARTAMENTALS),
            ],
            'suspension_start_date' => ['nullable', 'date'],
            'suspension_end_date'   => ['nullable', 'date', 'after_or_equal:suspension_start_date'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'status' => [
                'required',
                Rule::in(Engineer::STATUSES),
            ],
            'specialties'   => ['nullable', 'array'],
            'specialties.*' => ['integer', Rule::exists('specialties', 'id')->where('status', true)],
        ];

        $validated = $request->validate($rules, $this->validationMessages(), $this->validationAttributes());

        // Reglas cruzadas según status
        $status = $validated['status'];
        $start  = $validated['suspension_start_date'] ?? null;
        $end    = $validated['suspension_end_date']   ?? null;

        $errors = [];

        if ($status === Engineer::STATUS_SUSPENSION_DEFINIDA) {
            if (!$start) {
                $errors['suspension_start_date'] = 'La fecha de inicio de suspensión es obligatoria para una suspensión definida.';
            }
            if (!$end) {
                $errors['suspension_end_date'] = 'La fecha de fin de suspensión es obligatoria para una suspensión definida.';
            }
        } elseif ($status === Engineer::STATUS_SUSPENSION_INDEFINIDA) {
            if (!$start) {
                $errors['suspension_start_date'] = 'La fecha de inicio de suspensión es obligatoria para una suspensión indefinida.';
            }
            if ($end) {
                $errors['suspension_end_date'] = 'Una suspensión indefinida no debe tener fecha de fin.';
            }
        } else {
            // Estado normal: limpiar fechas de suspensión
            $validated['suspension_start_date'] = null;
            $validated['suspension_end_date']   = null;
        }

        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }

        return $validated;
    }

    /* ============================================================
     |  Normalización
     * ============================================================ */
    private function normalizeFields(array $data): array
    {
        $data['rni'] = preg_replace('/\D/', '', $data['rni']);
        $data['ci']  = strtoupper(trim($data['ci']));

        $data['name']              = $this->normalizeName($data['name']);
        $data['father_last_name']  = $this->normalizeName($data['father_last_name']);
        $data['mother_last_name']  = !empty($data['mother_last_name'])
            ? $this->normalizeName($data['mother_last_name'])
            : null;

        $data['email']   = !empty($data['email']) ? mb_strtolower(trim($data['email'])) : null;
        $data['phone']   = !empty($data['phone']) ? trim($data['phone']) : null;
        $data['address'] = !empty($data['address']) ? $this->normalizeText($data['address']) : null;

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

    /* ============================================================
     |  Mensajes y atributos
     * ============================================================ */
    private function validationMessages(): array
    {
        return [
            'branch_id.required' => 'Debes seleccionar una rama de ingeniería.',
            'branch_id.exists'   => 'La rama seleccionada no es válida o está inactiva.',
            'university_id.required' => 'Debes seleccionar una universidad.',
            'university_id.exists'   => 'La universidad seleccionada no es válida o está inactiva.',

            'rni.required' => 'El RNI es obligatorio.',
            'rni.digits_between' => 'El RNI debe contener solo números (entre 4 y 20 dígitos).',
            'rni.unique' => 'Ya existe un ingeniero con ese RNI.',

            'name.required' => 'El nombre es obligatorio.',
            'name.regex' => 'El nombre solo puede contener letras, espacios, apóstrofes, guiones y puntos.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',

            'father_last_name.required' => 'El apellido paterno es obligatorio.',
            'father_last_name.regex' => 'El apellido paterno solo puede contener letras, espacios, apóstrofes, guiones y puntos.',

            'mother_last_name.regex' => 'El apellido materno solo puede contener letras, espacios, apóstrofes, guiones y puntos.',

            'ci.required' => 'El CI es obligatorio.',
            'ci.regex' => 'El CI debe tener entre 5 y 15 dígitos, con extensión opcional (ej: 1234567-1A).',
            'ci.unique' => 'Ya existe un ingeniero con ese CI.',

            'phone.regex' => 'El teléfono solo puede contener números, +, -, espacios y paréntesis.',

            'email.email' => 'El correo electrónico no tiene un formato válido.',

            'sib_departmental.required' => 'Debes seleccionar una departamental.',
            'sib_departmental.in' => 'La departamental seleccionada no es válida.',

            'suspension_start_date.date' => 'La fecha de inicio de suspensión no es válida.',
            'suspension_end_date.date' => 'La fecha de fin de suspensión no es válida.',
            'suspension_end_date.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',

            'image.image' => 'El archivo debe ser una imagen.',
            'image.mimes' => 'La imagen debe ser jpg, jpeg, png o webp.',
            'image.max' => 'La imagen no puede superar los 4 MB.',

            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado seleccionado no es válido.',

            'specialties.*.exists' => 'Una de las especialidades seleccionadas no es válida o está inactiva.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'branch_id' => 'rama',
            'university_id' => 'universidad',
            'rni' => 'RNI',
            'name' => 'nombre',
            'father_last_name' => 'apellido paterno',
            'mother_last_name' => 'apellido materno',
            'ci' => 'CI',
            'phone' => 'teléfono',
            'email' => 'correo',
            'address' => 'dirección',
            'sib_departmental' => 'departamental',
            'suspension_start_date' => 'fecha de inicio de suspensión',
            'suspension_end_date' => 'fecha de fin de suspensión',
            'image' => 'imagen',
            'status' => 'estado',
            'specialties' => 'especialidades',
        ];
    }
}