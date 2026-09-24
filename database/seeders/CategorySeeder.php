<?php

namespace Database\Seeders;

use App\Models\PrimaryCategory;
use App\Models\SecondaryCategory;
use App\Models\TertiaryCategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            [
                'name' => 'Instalaciones Eléctricas',
                'description' => 'Categoría que agrupa todos los trámites relacionados con instalaciones eléctricas.',
                'example' => 'Viviendas, locales comerciales, industrias.',
                'important_notes' => 'Verificar que el proyectista esté colegiado y habilitado.',
                'secondaries' => [
                    [
                        'name' => 'Instalaciones Domiciliarias',
                        'description' => 'Trámites de instalaciones eléctricas en viviendas unifamiliares.',
                        'example' => 'Casa de dos pisos con acometida monofásica.',
                        'important_notes' => 'Máximo 10 kW de potencia contratada.',
                        'tertiaries' => [
                            [
                                'code' => 'TER-EL-001',
                                'name' => 'Instalación Monofásica',
                                'description' => 'Instalación eléctrica con suministro monofásico.',
                                'example' => 'Vivienda con medidor monofásico de 220V.',
                                'important_notes' => 'Debe cumplir con la norma NB 777.',
                            ],
                            [
                                'code' => 'TER-EL-002',
                                'name' => 'Instalación Trifásica',
                                'description' => 'Instalación eléctrica con suministro trifásico.',
                                'example' => 'Vivienda con equipos de alta demanda energética.',
                                'important_notes' => 'Requiere estudio de carga detallado.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Instalaciones Comerciales',
                        'description' => 'Trámites para locales comerciales y oficinas.',
                        'example' => 'Tienda, restaurante, farmacia.',
                        'important_notes' => 'Requiere memoria de cálculo firmada por ingeniero.',
                        'tertiaries' => [
                            [
                                'code' => 'TER-EL-003',
                                'name' => 'Instalación Comercial Básica',
                                'description' => 'Instalación eléctrica para locales con carga moderada.',
                                'example' => 'Local comercial de 50 m².',
                                'important_notes' => 'Incluye tablero de distribución.',
                            ],
                            [
                                'code' => 'TER-EL-004',
                                'name' => 'Instalación Comercial de Alta Demanda',
                                'description' => 'Instalación eléctrica para locales con carga elevada.',
                                'example' => 'Supermercado, centro comercial.',
                                'important_notes' => 'Requiere transformador propio.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Instalaciones Sanitarias',
                'description' => 'Categoría que agrupa trámites de agua potable y desagüe.',
                'example' => 'Viviendas, edificios, industrias.',
                'important_notes' => 'Coordinar con EPSAS según municipio.',
                'secondaries' => [
                    [
                        'name' => 'Agua Potable',
                        'description' => 'Trámites relacionados con conexiones de agua potable.',
                        'example' => 'Conexión domiciliaria, medidor.',
                        'important_notes' => 'Verificar disponibilidad de red.',
                        'tertiaries' => [
                            [
                                'code' => 'TER-SA-001',
                                'name' => 'Conexión Domiciliaria',
                                'description' => 'Conexión de agua potable para vivienda.',
                                'example' => 'Conexión de 1/2 pulgada.',
                                'important_notes' => 'Requiere plano de ubicación del medidor.',
                            ],
                            [
                                'code' => 'TER-SA-002',
                                'name' => 'Conexión Comercial',
                                'description' => 'Conexión de agua potable para local comercial.',
                                'example' => 'Conexión de 3/4 pulgada.',
                                'important_notes' => 'Requiere carta de solicitud del propietario.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Alcantarillado',
                        'description' => 'Trámites relacionados con desagüe y alcantarillado.',
                        'example' => 'Conexión a red pública.',
                        'important_notes' => 'Verificar pendiente mínima.',
                        'tertiaries' => [
                            [
                                'code' => 'TER-SA-003',
                                'name' => 'Conexión a Red Pública',
                                'description' => 'Conexión de desagüe a la red pública.',
                                'example' => 'Vivienda conectada al colector principal.',
                                'important_notes' => 'Requiere cámara de inspección.',
                            ],
                            [
                                'code' => 'TER-SA-004',
                                'name' => 'Pozo Séptico',
                                'description' => 'Instalación de pozo séptico para zonas sin red.',
                                'example' => 'Vivienda rural sin alcantarillado.',
                                'important_notes' => 'Debe cumplir distancia mínima de la vivienda.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Instalaciones de Gas',
                'description' => 'Categoría que agrupa trámites de instalaciones de gas domiciliario y comercial.',
                'example' => 'Viviendas, restaurantes, panaderías.',
                'important_notes' => 'Requiere certificación de hermeticidad.',
                'secondaries' => [
                    [
                        'name' => 'Gas Domiciliario',
                        'description' => 'Instalaciones de gas para viviendas.',
                        'example' => 'Conexión a red de gas natural.',
                        'important_notes' => 'Verificar ventilación del ambiente.',
                        'tertiaries' => [
                            [
                                'code' => 'TER-GA-001',
                                'name' => 'Instalación Interior',
                                'description' => 'Instalación interna de gas en vivienda.',
                                'example' => 'Conexión de cocina y calefón.',
                                'important_notes' => 'Requiere prueba de hermeticidad.',
                            ],
                            [
                                'code' => 'TER-GA-002',
                                'name' => 'Conexión Exterior',
                                'description' => 'Conexión de gas desde la red pública al medidor.',
                                'example' => 'Acometida desde la red hasta el medidor.',
                                'important_notes' => 'Requiere plano aprobado por la empresa distribuidora.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Gas Comercial',
                        'description' => 'Instalaciones de gas para locales comerciales.',
                        'example' => 'Restaurantes, panaderías, lavanderías.',
                        'important_notes' => 'Requiere mayor capacidad de suministro.',
                        'tertiaries' => [
                            [
                                'code' => 'TER-GA-003',
                                'name' => 'Instalación Comercial Básica',
                                'description' => 'Instalación de gas para local con demanda moderada.',
                                'example' => 'Cafetería con dos hornillas.',
                                'important_notes' => 'Debe tener detector de gas.',
                            ],
                            [
                                'code' => 'TER-GA-004',
                                'name' => 'Instalación Comercial de Alta Demanda',
                                'description' => 'Instalación de gas para local con consumo elevado.',
                                'example' => 'Panadería industrial.',
                                'important_notes' => 'Requiere medidor de alta capacidad.',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($tree as $primaryData) {
            $primary = PrimaryCategory::updateOrCreate(
                ['name' => $primaryData['name']],
                [
                    'description' => $primaryData['description'],
                    'example' => $primaryData['example'],
                    'important_notes' => $primaryData['important_notes'],
                    'status' => true,
                ]
            );

            foreach ($primaryData['secondaries'] as $secondaryData) {
                $secondary = SecondaryCategory::updateOrCreate(
                    [
                        'primary_category_id' => $primary->id,
                        'name' => $secondaryData['name'],
                    ],
                    [
                        'description' => $secondaryData['description'],
                        'example' => $secondaryData['example'],
                        'important_notes' => $secondaryData['important_notes'],
                        'status' => true,
                    ]
                );

                foreach ($secondaryData['tertiaries'] as $tertiaryData) {
                    TertiaryCategory::updateOrCreate(
                        ['code' => $tertiaryData['code']],
                        [
                            'secondary_category_id' => $secondary->id,
                            'name' => $tertiaryData['name'],
                            'description' => $tertiaryData['description'],
                            'example' => $tertiaryData['example'],
                            'important_notes' => $tertiaryData['important_notes'],
                            'status' => true,
                        ]
                    );
                }
            }
        }

        $this->command->info('Categorías creadas:');
        $this->command->info('  Primarias: ' . PrimaryCategory::count());
        $this->command->info('  Secundarias: ' . SecondaryCategory::count());
        $this->command->info('  Terciarias: ' . TertiaryCategory::count());
    }
}