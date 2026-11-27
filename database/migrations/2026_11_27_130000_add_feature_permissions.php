<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * ✅ Permissions for features that had none, granted to the roles that already use them
 *    (so nobody loses access): Borrowed Items, Calendar, Stock History, Deactivate user.
 */
return new class extends Migration
{
    private const NEW = [
        'borrowed-items.view'   => ['admin', 'program head', 'faculty'],
        'borrowed-items.manage' => ['admin', 'program head', 'faculty'],
        'calendar.view'         => ['admin', 'program head', 'faculty'],
        'stock-history.view'    => ['admin'],
        'stock-history.export'  => ['admin'],
        'users.deactivate'      => ['admin'],
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::NEW as $name => $roles) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);

            foreach ($roles as $roleName) {
                Role::where('name', $roleName)->where('guard_name', 'web')->first()?->givePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::whereIn('name', array_keys(self::NEW))->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
