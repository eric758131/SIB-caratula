<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use Illuminate\Http\Request;

class OwnerApiController extends Controller
{
    /**
     * Alta rápida de propietario desde el asistente de trámites.
     *
     * Si ya existe un propietario con ese nombre (sin importar mayúsculas ni espacios),
     * se reutiliza su registro en vez de rechazarlo: owners.name es único en la base.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'min:3', 'max:200', "regex:/^[\pL\s'\-\.\&]+$/u"],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[\d\+\-\s\(\)]+$/'],
        ], [
            'name.required' => 'El nombre del propietario es obligatorio.',
            'name.regex'    => 'El nombre solo puede contener letras, espacios, apóstrofes, guiones, puntos y &.',
            'phone.regex'   => 'El teléfono solo puede contener números, espacios, +, - y paréntesis.',
        ], [
            'name'  => 'nombre',
            'phone' => 'teléfono',
        ]);

        $name  = $this->normalizeName($validated['name']);
        $phone = !empty($validated['phone']) ? trim($validated['phone']) : null;

        $owner = Owner::whereRaw('LOWER(name) = ?', [mb_strtolower($name, 'UTF-8')])->first();
        $existing = (bool) $owner;

        if ($owner) {
            // Se toman sus datos; solo se completa el teléfono si no tenía, y se reactiva si estaba inactivo
            $owner->fill([
                'phone'  => $owner->phone ?: $phone,
                'status' => true,
            ]);
            if ($owner->isDirty()) {
                $owner->save();
            }
        } else {
            $owner = Owner::create([
                'name'   => $name,
                'phone'  => $phone,
                'status' => true,
            ]);
        }

        return response()->json([
            'id'       => $owner->id,
            'name'     => $owner->name,
            'phone'    => $owner->phone,
            'existing' => $existing,
        ], $existing ? 200 : 201);
    }

    private function normalizeName(string $name): string
    {
        $name = preg_replace('/\s+/', ' ', trim($name));

        return mb_convert_case(mb_strtolower($name, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }
}
