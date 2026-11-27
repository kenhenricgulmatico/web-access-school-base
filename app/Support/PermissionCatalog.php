<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * ✅ Every permission the system checks, grouped by feature, with plain names —
 *    one list used by Create Role, Edit Role and the role page.
 *
 *    Which groups each role can be given follows what that role's pages actually do
 *    (an Admin has no "Facility Requests" page, Faculty has no "Users" page…).
 */
class PermissionCatalog
{
    /** group => [title, what it controls] — in the order shown */
    public const GROUPS = [
        // Admin
        'program-head-requests' => ['Program Head Requests', 'Requests Program Heads send to the Admin (approve / reject), and Program Heads sending them.'],
        'borrowed-items'        => ['Borrowed Items',        'Chairs, projectors… lent out: accept borrowing, confirm returns, send reminders, send back.'],
        'stock-materials'       => ['Stock Materials',       'Materials and supplies in the main stock: add, remove, edit, delete.'],
        'stock-history'         => ['Stock History',         'Every change to the main stock, and downloading it as CSV.'],
        'calendar'              => ['Calendar',              'The calendar of bookings, material requests and return dates.'],
        'facility-schedule'     => ['Facilities',            'Rooms that can be booked: add, edit, maintenance, delete.'],
        'users'                 => ['Users',                 'Accounts: add, edit, approve sign-ups, reset passwords, deactivate.'],
        'departments'           => ['Departments',           'The list of departments.'],
        'audit'                 => ['Audit Logs',            'The record of who did what.'],
        // Program Head
        'members'               => ['Faculty',               'Faculty members of the department.'],
        'requests'              => ['Faculty Requests',      'Facility and material requests from faculty: view, approve, reject.'],
        'allocations'           => ['Resource Allocation',   'Materials given to the department.'],
        // Faculty
        'facility-requests'     => ['Facility Requests',     'Booking rooms.'],
        'material-requests'     => ['Material Requests',     'Requesting materials and supplies.'],
    ];

    /** permission => label shown next to its checkbox */
    public const LABELS = [
        'program-head-requests.view'     => 'View',
        'program-head-requests.view-all' => 'View all',
        'program-head-requests.create'   => 'Create',
        'program-head-requests.update'   => 'Edit',
        'program-head-requests.delete'   => 'Delete',
        'program-head-requests.approve'  => 'Approve',
        'program-head-requests.reject'   => 'Reject',

        'borrowed-items.view'   => 'View',
        'borrowed-items.manage' => 'Manage (accept, confirm returns, reminders)',

        'stock-materials.view'     => 'View',
        'stock-materials.view-all' => 'View all',
        'stock-materials.create'   => 'Add materials / stock',
        'stock-materials.update'   => 'Edit / remove stock',
        'stock-materials.delete'   => 'Delete',

        'stock-history.view'   => 'View',
        'stock-history.export' => 'Download CSV',

        'calendar.view' => 'View',

        'facility-schedule.view'     => 'View',
        'facility-schedule.view-all' => 'View all',
        'facility-schedule.create'   => 'Add',
        'facility-schedule.update'   => 'Edit / maintenance',
        'facility-schedule.delete'   => 'Delete',

        'users.view'           => 'View',
        'users.create'         => 'Add',
        'users.update'         => 'Edit',
        'users.delete'         => 'Delete',
        'users.approve'        => 'Approve sign-ups',
        'users.reset-password' => 'Reset password',
        'users.deactivate'     => 'Deactivate / reactivate',

        'departments.view'   => 'View',
        'departments.manage' => 'Add / edit / delete',

        'audit.view-all'             => 'View all',
        'audit.view-own-department'  => 'View own department',

        'members.view'   => 'View',
        'members.create' => 'Add',
        'members.update' => 'Edit',
        'members.delete' => 'Delete',

        'requests.view-department' => 'View',
        'requests.view-all'        => 'View all',
        'requests.approve'         => 'Approve',
        'requests.reject'          => 'Reject',

        'allocations.view'   => 'View',
        'allocations.manage' => 'Manage',

        'facility-requests.view'     => 'View',
        'facility-requests.view-all' => 'View all',
        'facility-requests.create'   => 'Create',
        'facility-requests.update'   => 'Edit',
        'facility-requests.cancel'   => 'Cancel',

        'material-requests.view'     => 'View',
        'material-requests.view-all' => 'View all',
        'material-requests.create'   => 'Create',
        'material-requests.update'   => 'Edit',
        'material-requests.cancel'   => 'Cancel',
    ];

    /** Groups each role's pages use. Other (custom) roles get the Admin list. */
    public const ROLE_GROUPS = [
        'admin'        => ['program-head-requests', 'borrowed-items', 'stock-materials', 'stock-history', 'calendar', 'facility-schedule', 'users', 'departments', 'audit'],
        'program head' => ['members', 'requests', 'program-head-requests', 'borrowed-items', 'calendar', 'allocations', 'audit'],
        'faculty'      => ['facility-requests', 'material-requests', 'borrowed-items', 'calendar'],
    ];

    /** Permissions a role's pages never use (kept off its checklist). */
    public const ROLE_HIDDEN = [
        'admin' => [
            'program-head-requests.create', 'program-head-requests.update', 'program-head-requests.delete',
            'audit.view-own-department',
        ],
        'program head' => [
            'program-head-requests.approve', 'program-head-requests.reject', 'program-head-requests.view-all',
            'requests.view-all',
            'allocations.manage',
            'audit.view-all',
        ],
        'faculty' => [],
    ];

    /**
     * ✅ Lockout protection: the Admin role always keeps these
     *    (otherwise one wrong click could lock everyone out of Users / Roles).
     *    Roles & Permissions itself (roles.*) is never on the checklist, so it can't be removed.
     */
    public const ROLE_LOCKED = [
        'admin' => ['users.view', 'users.update'],
    ];

    public static function groupsFor(?string $role): array
    {
        return self::ROLE_GROUPS[$role ?? ''] ?? self::ROLE_GROUPS['admin'];
    }

    public static function lockedFor(?string $role): array
    {
        return self::ROLE_LOCKED[$role ?? ''] ?? [];
    }

    /** Can this permission be ticked / unticked for this role? (locked ones are shown but fixed) */
    public static function manageable(?string $role, string $permission): bool
    {
        $hidden = self::ROLE_HIDDEN[$role ?? ''] ?? self::ROLE_HIDDEN['admin'];

        if (in_array($permission, $hidden, true)) {
            return false;
        }

        return in_array(self::group($permission), self::groupsFor($role), true);
    }

    public static function group(string $permission): string
    {
        return explode('.', $permission, 2)[0] ?? 'other';
    }

    /** What a group is called for a role (the Program Head knows it as "Request to Admin"). */
    public const ROLE_TITLES = [
        'program head' => ['program-head-requests' => ['Request to Admin', 'Requests the Program Head sends to the Admin: create, edit, delete.']],
    ];

    public static function title(string $group, ?string $role = null): string
    {
        return self::ROLE_TITLES[$role ?? ''][$group][0] ?? self::GROUPS[$group][0] ?? \Illuminate\Support\Str::headline($group);
    }

    public static function description(string $group, ?string $role = null): string
    {
        return self::ROLE_TITLES[$role ?? ''][$group][1] ?? self::GROUPS[$group][1] ?? '';
    }

    /** Groups in the order the role's own menu uses (falls back to the catalog order). */
    public static function orderFor(array $grouped, ?string $role): array
    {
        $order = self::groupsFor($role);
        uksort($grouped, function ($a, $b) use ($order) {
            $ia = array_search($a, $order, true);
            $ib = array_search($b, $order, true);
            return ($ia === false ? 999 : $ia) <=> ($ib === false ? 999 : $ib);
        });

        return $grouped;
    }

    public static function label(string $permission): string
    {
        return self::LABELS[$permission] ?? \Illuminate\Support\Str::headline(str_replace('.', ' ', $permission));
    }

    /**
     * Group permissions (models or names) in the catalog's order:
     *   ['users' => [perm, perm…], 'departments' => […], …]
     */
    public static function grouped(iterable $permissions): array
    {
        $byGroup = [];
        foreach ($permissions as $p) {
            $name = is_string($p) ? $p : $p->name;
            $byGroup[self::group($name)][] = $p;
        }

        $order = array_keys(self::GROUPS);
        uksort($byGroup, function ($a, $b) use ($order) {
            $ia = array_search($a, $order, true);
            $ib = array_search($b, $order, true);
            return ($ia === false ? 999 : $ia) <=> ($ib === false ? 999 : $ib) ?: strcmp($a, $b);
        });

        // inside a group: catalog label order
        $labelOrder = array_keys(self::LABELS);
        foreach ($byGroup as &$items) {
            usort($items, function ($a, $b) use ($labelOrder) {
                $na = is_string($a) ? $a : $a->name;
                $nb = is_string($b) ? $b : $b->name;
                $ia = array_search($na, $labelOrder, true);
                $ib = array_search($nb, $labelOrder, true);
                return ($ia === false ? 999 : $ia) <=> ($ib === false ? 999 : $ib);
            });
        }
        unset($items);

        return $byGroup;
    }

    /** Every permission name in the catalog. */
    public static function all(): Collection
    {
        return collect(array_keys(self::LABELS));
    }
}
