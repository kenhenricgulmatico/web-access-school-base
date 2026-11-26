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
            // Supplies — used up, never returned
            ['name' => 'Bond Paper A4',    'type' => ResourceType::SUPPLIES, 'unit' => 'Ream'],
            ['name' => 'Bond Paper Short', 'type' => ResourceType::SUPPLIES, 'unit' => 'Ream'],
            ['name' => 'Bond Paper Legal', 'type' => ResourceType::SUPPLIES, 'unit' => 'Ream'],
            ['name' => 'Marker',           'type' => ResourceType::SUPPLIES, 'unit' => 'Set'],

            // Material — borrowed and returned
            ['name' => 'Chair',            'type' => ResourceType::MATERIAL, 'unit' => 'Pcs'],
        ];

        foreach ($materials as $material) {
            $type = ResourceType::firstOrCreate([
                'type_name' => $material['type'],
            ]);

            // "is_returnable" is set automatically from the type (see Resource::booted)
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
                ]
            );
        }
    }
}
