<?php

namespace App\Support;

use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Return deadlines for borrowed items (chairs, projectors…).
 *
 * Only items whose stock type is "Material" (chairs, projectors…) are tracked;
 * "Supplies" such as bond paper and markers are used up and never get a return date.
 *
 * ✅ Return chain for an item the Program Head borrowed FOR a faculty member (endorsed request):
 *    Faculty "Return to Program Head" → Program Head confirms → Program Head "Return to Admin" → Admin confirms.
 */
class ReturnTracking
{
    /** Days a borrower has to give back items from a plain material request. */
    public const RETURN_DAYS = 3;

    /**
     * For a Program Head's borrowed item that was endorsed from a faculty request:
     * the faculty member's matching item (same resource). Null when the Program Head borrowed it for themselves.
     */
    public static function facultyItemFor(RequestItem $programHeadItem): ?RequestItem
    {
        $programHeadItem->loadMissing('request');

        $facultyRequestId = $programHeadItem->request?->forwarded_from_request_id;

        if (! $facultyRequestId || ! $programHeadItem->resource_id) {
            return null;
        }

        return RequestItem::with('request.user')
            ->where('request_id', $facultyRequestId)
            ->where('resource_id', $programHeadItem->resource_id)
            ->first();
    }

    /**
     * The other way round: for a faculty member's item, the Program Head's matching item borrowed
     * FROM THE ADMIN for it (endorsed request, approved). Null when the department gave it from its own stock.
     */
    public static function adminItemFor(RequestItem $facultyItem): ?RequestItem
    {
        if (! $facultyItem->resource_id) {
            return null;
        }

        return RequestItem::query()
            ->where('resource_id', $facultyItem->resource_id)
            ->whereHas('request', fn ($q) => $q
                ->where('forwarded_from_request_id', $facultyItem->request_id)
                ->where('status', 'approved'))
            ->latest('id')
            ->first();
    }

    /** True while the faculty member still has the item, so the Program Head cannot return it to the Admin yet. */
    public static function stillWithFaculty(RequestItem $programHeadItem): bool
    {
        $facultyItem = static::facultyItemFor($programHeadItem);

        return $facultyItem !== null
            && $facultyItem->request?->status === 'approved'
            && $facultyItem->returned_at === null;
    }

    /**
     * Give every borrowed item of an approved request its "return by" date.
     * ✅ Only "Material" items (chairs, projectors…) must be returned —
     *    "Supplies" like bond paper or markers are used up, not given back.
     *  - With a facility reservation: return on the reservation day.
     *  - Material request only:       request date + RETURN_DAYS.
     */
    public static function assignDueDates(ResourceRequest $request): void
    {
        $request->loadMissing('items.resource');

        $facility = $request->items->firstWhere('resource_id', null);

        foreach ($request->items as $item) {
            if (! $item->resource_id || ! $item->resource?->is_returnable || $item->due_back_at || $item->assigned_at) {
                continue;
            }

            $due = $facility
                ? Carbon::parse($facility->request_date)
                : Carbon::parse($item->request_date)->addDays(self::RETURN_DAYS);

            $item->update(['due_back_at' => $due->toDateString()]);
        }
    }

    /**
     * Mark one borrowed item as returned.
     * Items returned in GOOD condition go back to the department's stock;
     * damaged or lost items do not.
     */
    public static function markReturned(RequestItem $item, string $condition, ?string $notes, User $receiver): void
    {
        DB::transaction(function () use ($item, $condition, $notes, $receiver) {
            $item = RequestItem::with('request')->lockForUpdate()->findOrFail($item->id);

            if ($item->returned_at) {
                throw new \RuntimeException('This item was already marked as returned.');
            }

            $item->update([
                'returned_at'      => now(),
                'return_condition' => $condition,
                'return_notes'     => $notes ?: null,
                'received_by'      => $receiver->id,
            ]);

            if ($condition === 'good') {
                DB::table('resource_all_locations')
                    ->where('resource_id', $item->resource_id)
                    ->where('department_id', $item->request->department_id)
                    ->increment('allocated_quantity', $item->quantity);
            }
        });
    }

    /** "Due today", "Due in 2 days", "Overdue by 3 days", "Returned (Good)". */
    public static function label(RequestItem $item): string
    {
        if ($item->returned_at) {
            return 'Returned (' . (RequestItem::CONDITIONS[$item->return_condition] ?? ucfirst((string) $item->return_condition)) . ')';
        }

        $days = (int) today()->diffInDays($item->due_back_at, false);

        return match (true) {
            $days < 0   => 'Overdue by ' . abs($days) . ' ' . str('day')->plural(abs($days)),
            $days === 0 => 'Due today',
            default     => 'Due in ' . $days . ' ' . str('day')->plural($days),
        };
    }
}
