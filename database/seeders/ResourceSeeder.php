<?php

namespace Database\Seeders;

use App\Models\Resource;
use App\Models\ResourceType;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    public function run(): void
    {
        $materials = [
            // Paper Supplies (Ream)
            ['name' => 'Bond Paper A4',    'type' => 'Paper Supplies',  'unit' => 'Ream'],
            ['name' => 'Bond Paper Short', 'type' => 'Paper Supplies',  'unit' => 'Ream'],
            ['name' => 'Bond Paper Legal', 'type' => 'Paper Supplies',  'unit' => 'Ream'],

            // Writing Materials (Set)
            ['name' => 'Marker',           'type' => 'Writing Materials', 'unit' => 'Set'],

            // Furniture (Pcs)
            ['name' => 'Chair',            'type' => 'Furniture',       'unit' => 'Pcs', 'returnable' => true],
        ];

        foreach ($materials as $material) {
            $type = ResourceType::firstOrCreate([
                'type_name' => $material['type'],
            ]);

            Resource::firstOrCreate(
                [
                    'resource_name'    => $material['name'],
                    'resource_type_id' => $type->id,
                ],
                [
                    'description'        => $material['name'],
                    'quantity_available' => 0,
                    'unit'               => $material['unit'],
                    'status'             => 'available',
                    'is_returnable'      => $material['returnable'] ?? false,
                ]
            );
        }
    }
}
