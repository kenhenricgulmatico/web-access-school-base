<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ─────────────────────────────────────────────────────────────
        // 1. ROLES
        // ─────────────────────────────────────────────────────────────
        $adminRole       = Role::firstOrCreate(['name' => 'admin',        'guard_name' => 'web']);
        $programHeadRole = Role::firstOrCreate(['name' => 'program head', 'guard_name' => 'web']);
        $facultyRole     = Role::firstOrCreate(['name' => 'faculty',      'guard_name' => 'web']);
        // ✅ No "student" role anymore — only admin, program head and faculty

        // ─────────────────────────────────────────────────────────────
        // 2. PERMISSIONS
        // ─────────────────────────────────────────────────────────────
        $permissions = [
            // ── User management ──
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.approve',
            'users.reset-password',

            // ── Roles ──
            'roles.view',
            'roles.manage',

            // ── Departments ──
            'departments.view',
            'departments.manage',

            // ── Student & faculty management (Program Head scope) ──
            'members.view',
            'members.create',
            'members.update',
            'members.delete',

            // ── Generic requests (used by PH/Admin approval flows) ──
            'requests.view-department',
            'requests.view-all',
            'requests.approve',
            'requests.reject',

            // ── ✅ Facility Requests (student/faculty portal) ──
            'facility-requests.view',
            'facility-requests.view-all',
            'facility-requests.create',
            'facility-requests.update',
            'facility-requests.cancel',

            // ── ✅ Material Requests (student/faculty portal) ──
            'material-requests.view',
            'material-requests.view-all',
            'material-requests.create',
            'material-requests.update',
            'material-requests.cancel',

            // ── Program Head Requests (PH → Admin) ──
            'program-head-requests.view',
            'program-head-requests.view-all',
            'program-head-requests.create',
            'program-head-requests.update',
            'program-head-requests.delete',
            'program-head-requests.approve',
            'program-head-requests.reject',

            // ── Stock Materials ──
            'stock-materials.view',
            'stock-materials.view-all',
            'stock-materials.create',
            'stock-materials.update',
            'stock-materials.delete',

            // ── Facility Schedule ──
            'facility-schedule.view',
            'facility-schedule.view-all',
            'facility-schedule.create',
            'facility-schedule.update',
            'facility-schedule.delete',

            // ── Resource allocation ──
            'allocations.view',
            'allocations.manage',

            // ── Audit & logs ──
            'audit.view-own-department',
            'audit.view-all',

            // ── Reporting & dashboards ──
            'reports.view-own',
            'reports.view-department',
            'reports.view-all',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        // ─────────────────────────────────────────────────────────────
        // 3. ASSIGN PERMISSIONS PER ROLE
        // ─────────────────────────────────────────────────────────────

        // ── ADMIN — unchanged ──
        $adminRole->syncPermissions([
            'users.view', 'users.create', 'users.update', 'users.delete',
            'users.approve', 'users.reset-password',
            'roles.view', 'roles.manage',
            'departments.view', 'departments.manage',
            'requests.view-all', 'requests.approve', 'requests.reject',
            'program-head-requests.view', 'program-head-requests.view-all',
            'program-head-requests.approve', 'program-head-requests.reject',
            'stock-materials.view', 'stock-materials.view-all',
            'stock-materials.create', 'stock-materials.update', 'stock-materials.delete',
            'facility-schedule.view', 'facility-schedule.view-all',
            'facility-schedule.create', 'facility-schedule.update', 'facility-schedule.delete',
            'allocations.view', 'allocations.manage',
            'audit.view-all',
            'reports.view-all',
        ]);

        // ── PROGRAM HEAD — unchanged ──
        $programHeadRole->syncPermissions([
            'members.view', 'members.create', 'members.update', 'members.delete',
            'requests.view-department', 'requests.approve', 'requests.reject',
            'program-head-requests.view', 'program-head-requests.create',
            'program-head-requests.update', 'program-head-requests.delete',
            'stock-materials.view',
            'facility-schedule.view',
            'allocations.view',
            'audit.view-own-department', 'reports.view-department',
        ]);

        // ── FACULTY & STUDENT — self-service portal ──
        //    ✅ Uses facility-requests.* and material-requests.* for granular control
        $portalPermissions = [
            // Facility requests (facility portal)
            'facility-requests.view',
            'facility-requests.view-all',
            'facility-requests.create',
            'facility-requests.update',
            'facility-requests.cancel',

            // Material requests (material portal)
            'material-requests.view',
            'material-requests.view-all',
            'material-requests.create',
            'material-requests.update',
            'material-requests.cancel',

            // Read-only for supporting data
            'facility-schedule.view',
            'stock-materials.view',

            // Own reports
            'reports.view-own',
        ];

        $facultyRole->syncPermissions($portalPermissions);

        // ─────────────────────────────────────────────────────────────
        // 4. ORPHAN CHECK
        // ─────────────────────────────────────────────────────────────
        $orphans = Permission::doesntHave('roles')->pluck('name');

        if ($orphans->isNotEmpty()) {
            $this->command->warn(
                '⚠ Unassigned permissions (not granted to any role): '
                . $orphans->implode(', ')
            );
        }

        // ─────────────────────────────────────────────────────────────
        // 5. SEED ADMIN USER
        // ─────────────────────────────────────────────────────────────
        $admin = User::updateOrCreate(
            ['email' => 'admin@csav.edu.ph'],
            [
                'name'     => 'Admin',
                'password' => bcrypt('123123'),
                'status'   => 'approved',
            ]
        );

        $admin->syncRoles([$adminRole]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
