<?php

namespace App\Support;

use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use App\Models\RequestType;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * ✅ "What needs the Admin today" — the numbers on the Admin dashboard's welcome strip.
 *    Each count uses the same rules as the page it links to, so the numbers match.
 */
class AdminTodo
{
    public static function counts(): array
    {
        return [
            'requests' => self::waitingRequests(),
            'low'      => self::lowStock(),
            'out'      => self::outOfStock(),
            'overdue'  => self::overdueItems(),
            'accounts' => User::where('status', 'pending')->count(),
        ];
    }

    /** Program Head requests waiting for the Admin (Incoming Requests → Pending). */
    public static function waitingRequests(): int
    {
        return ResourceRequest::where('status', 'pending')
            ->whereHas('user', fn ($q) => $q->role('program head'))
            ->count();
    }

    /** Materials (not rooms) — same list as Stock Materials. */
    protected static function materials()
    {
        return Resource::where(fn ($q) => $q
            ->whereHas('resourceType', fn ($t) => $t->whereNotIn('type_name', [ResourceType::FACILITY]))
            ->orWhereDoesntHave('resourceType'));
    }

    /** At or below the material's own "warn me" level, but not empty (Stock Materials → Low stock). */
    public static function lowStock(): int
    {
        return self::materials()
            ->where('quantity_available', '>', 0)
            ->whereColumn('quantity_available', '<=', 'low_stock_at')
            ->count();
    }

    /** Stock Materials → Out of stock. */
    public static function outOfStock(): int
    {
        return self::materials()->where('quantity_available', '<=', 0)->count();
    }

    /** Borrowed Items → Overdue (Pcs items Program Heads haven't brought back in time). */
    public static function overdueItems(): int
    {
        $facility = (int) RequestType::FACILITY;
        $days     = (int) ReturnTracking::RETURN_DAYS;
        $plusDays = DB::getDriverName() === 'sqlite'
            ? "date(request_items.request_date, '+{$days} days')"
            : "DATE_ADD(request_items.request_date, INTERVAL {$days} DAY)";
        $due = "COALESCE(request_items.due_back_at, CASE
                    WHEN (SELECT r.request_type_id FROM requests r WHERE r.id = request_items.request_id) = {$facility}
                    THEN request_items.request_date
                    ELSE {$plusDays}
                END)";

        return RequestItem::query()
            ->whereNotNull('request_items.resource_id')
            ->whereNull('request_items.assigned_at')
            ->whereNull('returned_at')
            ->whereHas('resource', fn ($r) => $r->where('is_returnable', true))
            ->whereHas('request', fn ($q) => $q
                ->where('status', 'approved')
                ->whereHas('user', fn ($u) => $u->role('program head')))
            ->whereRaw("{$due} < ?", [today()->toDateString()])
            ->count();
    }
}
