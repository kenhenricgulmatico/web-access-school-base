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
 * Only items whose resource is marked "returnable" by the admin are tracked;
 * supplies such as bond paper are used up and never get a return date.
 */
class ReturnTracking
{
    /** Days a borrower has to give back items from a plain material request. */
    public const RETURN_DAYS = 3;

    /**
     * Give every returnable item of an approved request its "return by" date.
     *  - With a facility reservation: return on the reservation day.
     *  - Material request only:       request date + RETURN_DAYS.
     */
    public static function assignDueDates(ResourceRequest $request): void
    {
        $request->loadMissing('items.resource');

        $facility = $request->items->firstWhere('resource_id', null);

        foreach ($request->items as $item) {
            if (! $item->resource_id || ! $item->resource?->is_returnable || $item->due_back_at) {
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
