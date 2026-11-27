<?php

namespace App\Support;

use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use App\Models\User;

/**
 * ✅ User accounts in one place: the shared default password, what a person still has open
 *    (pending requests, borrowed items), and where they land after signing in.
 */
class Accounts
{
    /** The password the Admin gives new accounts / "Reset to default". Must be changed at first sign-in. */
    public const DEFAULT_PASSWORD = 'csav.csav';

    /** Roles the Admin may give in Add / Edit User (never "admin"). */
    public const ASSIGNABLE_ROLES = ['program head', 'faculty'];

    /**
     * userId => ['pending' => n, 'borrowed' => n]
     *   pending  = their requests still waiting for a decision
     *   borrowed = Material items (chairs, projectors…) from their approved requests not returned yet
     */
    public static function openItems(array $userIds): array
    {
        if (! $userIds) {
            return [];
        }

        $pending = ResourceRequest::whereIn('user_id', $userIds)
            ->where('status', 'pending')
            ->selectRaw('user_id, COUNT(*) as n')
            ->groupBy('user_id')
            ->pluck('n', 'user_id');

        $borrowed = RequestItem::query()
            ->join('requests as rq', 'rq.id', '=', 'request_items.request_id')
            ->join('resources as rs', 'rs.id', '=', 'request_items.resource_id')
            ->whereIn('rq.user_id', $userIds)
            ->where('rq.status', 'approved')
            ->where('rs.is_returnable', true)
            ->whereNull('request_items.returned_at')
            ->whereNull('request_items.assigned_at')   // kept items are counted separately
            ->selectRaw('rq.user_id, SUM(request_items.quantity) as n')
            ->groupBy('rq.user_id')
            ->pluck('n', 'user_id');

        // ✅ assigned items they keep (no return date) — must be handed over before they leave
        $kept = RequestItem::assignedNow()
            ->whereIn('holder_id', $userIds)
            ->selectRaw('holder_id, SUM(quantity) as n')
            ->groupBy('holder_id')
            ->pluck('n', 'holder_id');

        $out = [];
        foreach ($userIds as $id) {
            $out[$id] = [
                'pending'  => (int) ($pending[$id] ?? 0),
                'borrowed' => (int) ($borrowed[$id] ?? 0),
                'assigned' => (int) ($kept[$id] ?? 0),
            ];
        }

        return $out;
    }

    /** "2 pending requests and 3 borrowed items" / "" when nothing is open. */
    public static function openItemsText(array $open): string
    {
        $parts = [];
        if ($open['pending'] ?? 0) {
            $parts[] = $open['pending'] . ' pending ' . \Illuminate\Support\Str::plural('request', $open['pending']);
        }
        if ($open['borrowed'] ?? 0) {
            $parts[] = $open['borrowed'] . ' borrowed ' . \Illuminate\Support\Str::plural('item', $open['borrowed']) . ' not returned';
        }
        if ($open['assigned'] ?? 0) {
            $parts[] = $open['assigned'] . ' assigned ' . \Illuminate\Support\Str::plural('item', $open['assigned']) . ' they keep';
        }

        return implode(' and ', $parts);
    }

    /** Where a signed-in user belongs. */
    public static function homeRoute(User $user): string
    {
        return match (true) {
            $user->hasRole('admin')        => route('admin.dashboard'),
            $user->hasRole('program head') => route('coordinator.dashboard'),
            default                        => route('portal.dashboard'),
        };
    }
}
