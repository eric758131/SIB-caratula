<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Engineer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EngineerApiController extends Controller
{
    /**
     * Alta rápida de ingeniero (proyectista) desde el asistente de trámites.
     * Se crea siempre como ACTIVO; la suspensión y la foto se gestionan en el panel web.
     */
    public function store(Request $request)
    {
        $namePattern = "regex:/^[\pL\s'\-\.]+$/u";

        $validated = $request->validate([
            'branch_id'        => ['required', 'integer', Rule::exists('branches', 'id')->where('status', true)],
            'university_id'    => ['required', 'integer', Rule::exists('universities', 'id')->where('status', true)],
            'rni'              => ['required', 'string', 'digits_between:4,20', Rule::unique('engineers', 'rni')],
            'name'             => ['required', 'string', 'min:2', 'max:200', $namePattern],
            'father_last_name' => ['required', 'string', 'min:2', 'max:100', $namePattern],
            'mother_last_name' => ['nullable', 'string', 'min:2', 'max:100', $namePattern],
            'ci'               => ['required', 'string', 'max:20', 'regex:/^\d{5,15}(-[A-Z0-9]{1,3})?$/', Rule::unique('engineers', 'ci')],
            'phone'            => ['nullable', 'string', 'max:30', 'regex:/^[\d\+\-\s\(\)]+$/'],
            'email'            => ['nullable', 'string', 'email:rfc', 'max:100'],
            'sib_departmental' => ['required', Rule::in(Engineer::SIB_DEPARTAMENTALS)],
            'specialties'      => ['nullable', 'array'],
            'specialties.*'    => ['integer', Rule::exists('specialties', 'id')->where('status', true)],
        ], [
            'rni.unique'           => 'Ya existe un ingeniero con ese RNI.',
            'rni.digits_between'   => 'El RNI debe tener solo números (entre :min y :max dígitos).',
            'ci.unique'            => 'Ya existe un ingeniero con ese CI.',
            'ci.regex'             => 'El CI debe tener de 5 a 15 dígitos y opcionalmente un complemento (ej. 1234567-1A).',
            'branch_id.exists'     => 'La rama seleccionada no es válida o está inactiva.',
            'university_id.exists' => 'La universidad seleccionada no es válida o está inactiva.',
        ], [
            'branch_id'        => 'rama',
            'university_id'    => 'universidad',
            'rni'              => 'RNI',
            'name'             => 'nombre',
            'father_last_name' => 'apellido paterno',
            'mother_last_name' => 'apellido materno',
            'ci'               => 'CI',
            'phone'            => 'teléfono',
            'email'            => 'correo',
            'sib_departmental' => 'departamental SIB',
        ]);

        $engineer = Engineer::create([
            'branch_id'        => $validated['branch_id'],
            'university_id'    => $validated['university_id'],
            'rni'              => preg_replace('/\D/', '', $validated['rni']),
            'name'             => $this->normalizeName($validated['name']),
            'father_last_name' => $this->normalizeName($validated['father_last_name']),
            'mother_last_name' => !empty($validated['mother_last_name'])
                ? $this->normalizeName($validated['mother_last_name'])
                : null,
            'ci'               => strtoupper(trim($validated['ci'])),
            'phone'            => !empty($validated['phone']) ? trim($validated['phone']) : null,
            'email'            => !empty($validated['email']) ? mb_strtolower(trim($validated['email'])) : null,
            'sib_departmental' => $validated['sib_departmental'],
            'status'           => Engineer::STATUS_ACTIVO,
        ]);

        if (!empty($validated['specialties'])) {
            $engineer->specialties()->sync($validated['specialties']);
        }

        return response()->json([
            'id'          => $engineer->id,
            'full_name'   => $engineer->full_name,
            'rni'         => $engineer->rni,
            'ci'          => $engineer->ci,
            'specialties' => $engineer->specialties()->pluck('name'),
        ], 201);
    }

    private function normalizeName(string $name): string
    {
        $name = preg_replace('/\s+/', ' ', trim($name));

        return mb_convert_case(mb_strtolower($name, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }
}
