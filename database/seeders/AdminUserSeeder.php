<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Asegurarse de que el rol 'admin' exista (esto es opcional pero muy seguro)
        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // 2. Crear o buscar al usuario administrador
        $user = User::updateOrCreate(
            ['email' => 'admin@sib.com'], // busca por email
            [
                'name'     => 'Administrador',
                'password' => Hash::make('Admin1234'),
            ]
        );

        // 3. Asignarle el rol al usuario usando Spatie
        $user->assignRole($role); 
        // O simplemente: $user->assignRole('admin');
    }
}