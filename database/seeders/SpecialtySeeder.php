<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $specialties = [
            // Eléctricas
            'Alta Tensión',
            'Media Tensión',
            'Baja Tensión',
            'Instalaciones Eléctricas Domiciliarias',
            'Instalaciones Eléctricas Industriales',
            'Sistemas de Puesta a Tierra',
            'Generación Distribuida',
            'Energías Renovables',
            'Iluminación',
            'Subestaciones Eléctricas',

            // Civiles / Estructurales
            'Diseño Estructural',
            'Cálculo Estructural',
            'Hormigón Armado',
            'Estructuras Metálicas',
            'Geotecnia',
            'Hidráulica',
            'Vías y Carreteras',
            'Puentes',
            'Topografía',

            // Sanitarias
            'Agua Potable',
            'Alcantarillado Sanitario',
            'Tratamiento de Aguas Residuales',
            'Instalaciones Sanitarias Domiciliarias',
            'Instalaciones Sanitarias Industriales',

            // Gas
            'Instalaciones de Gas Domiciliario',
            'Instalaciones de Gas Comercial',
            'Instalaciones de Gas Industrial',
            'Redes de Gas Natural',
            'Gases Licuados de Petróleo',

            // Mecánicas
            'Diseño de Máquinas',
            'Mantenimiento Industrial',
            'Refrigeración y Aire Acondicionado',
            'Ventilación',
            'Sistemas Hidráulicos',
            'Sistemas Neumáticos',

            // Otras
            'Telecomunicaciones',
            'Redes de Datos',
            'Automatización y Control',
            'Instrumentación Industrial',
            'Gestión Ambiental',
            'Seguridad Industrial',
            'Prevención de Incendios',
            'Gestión de Proyectos',
            'Supervisión de Obras',
        ];

        foreach ($specialties as $name) {
            Specialty::updateOrCreate(
                ['name' => $name],
                ['status' => true]
            );
        }

        $this->command->info('Especialidades creadas: ' . Specialty::count());
    }
}