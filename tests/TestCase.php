<?php

namespace Tests;

use App\Models\Department;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    protected function seedBaseData(): void
    {
        foreach (['admin', 'faculty', 'student', 'program head'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        \DB::table('request_types')->insertOrIgnore([
            ['id' => 1, 'type_name' => 'Facility Reservation', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'type_name' => 'Material Request', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    protected function createDepartment(string $name = 'Computer Studies'): Department
    {
        return Department::create(['department_name' => $name]);
    }

    protected function createUser(
        string $role = 'student',
        ?Department $department = null,
        string $status = 'approved',
        ?string $email = null,
    ): User {
        $user = User::create([
            'name'          => ucfirst($role) . ' User',
            'email'         => $email ?? $role . '-' . uniqid() . '@csav.edu.ph',
            'password'      => Hash::make('password'),
            'status'        => $status,
            'department_id' => $department?->id,
        ]);

        $user->assignRole($role);

        return $user;
    }

    protected function createFacility(string $name = 'Computer Laboratory 1'): Resource
    {
        $type = ResourceType::firstOrCreate(['type_name' => 'Facility']);

        return Resource::create([
            'resource_type_id'   => $type->id,
            'resource_name'      => $name,
            'description'        => $name,
            'quantity_available' => 1,
            'unit'               => 'Pcs',
            'status'             => 'available',
        ]);
    }

    protected function createMaterial(string $name = 'Bond Paper A4', int $qty = 100): Resource
    {
        $type = ResourceType::firstOrCreate(['type_name' => 'Paper Supplies']);

        return Resource::create([
            'resource_type_id'   => $type->id,
            'resource_name'      => $name,
            'description'        => $name,
            'quantity_available' => $qty,
            'unit'               => 'Ream',
            'status'             => 'available',
        ]);
    }
}
