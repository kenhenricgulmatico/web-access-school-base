<?php

namespace App\Support;

use App\Models\ItemHandover;
use App\Models\Notification;
use App\Models\RequestItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * ✅ Assigned items: furniture and equipment a teacher KEEPS (no return date) — a table and chair
 *    for the faculty room — and that is HANDED OVER to the next teacher instead of being returned.
 *
 *    One set of chairs can have two records when a Program Head forwarded a faculty request to the
 *    Admin (the faculty's item + the Program Head's copy). Exactly ONE of them carries holder_id —
 *    the item in the holder's own request — so nothing is listed or counted twice:
 *      • Program Head's own request      → that item, holder = Program Head
 *      • faculty request (any route)     → the faculty item, holder = faculty; the Program Head's copy
 *                                          (if forwarded) is marked assigned too, with no holder
 */
class Assignments
{
    /** Turn a borrowed Material item (not returned yet) into an assigned one. Returns the holder record. */
    public static function assign(RequestItem $item, User $by, ?string $note = null): RequestItem
    {
        return DB::transaction(function () use ($item, $by, $note) {
            $item = RequestItem::with(['request.user', 'resource'])->lockForUpdate()->findOrFail($item->id);

            if ($item->returned_at) {
                throw new \RuntimeException('This item was already returned.');
            }
            if (! $item->resource?->is_returnable) {
                throw new \RuntimeException('Only Material items (chairs, tables, projectors…) can be assigned.');
            }
            if ($item->assigned_at) {
                throw new \RuntimeException('This item is already assigned.');
            }

            [$holderRecord, $mirror] = static::pair($item);

            $holderRecord->update([
                'assigned_at'         => now(),
                'holder_id'           => $holderRecord->request->user_id,
                'due_back_at'         => null,
                'return_requested_at' => null,
            ]);

            $mirror?->update([
                'assigned_at'         => now(),
                'holder_id'           => null,
                'due_back_at'         => null,
                'return_requested_at' => null,
            ]);

            ItemHandover::create([
                'request_item_id' => $holderRecord->id,
                'from_user_id'    => null,
                'to_user_id'      => $holderRecord->holder_id,
                'by_user_id'      => $by->id,
                'kind'            => 'assigned',
                'note'            => $note,
            ]);

            static::tell($holderRecord->holder_id, $holderRecord,
                "{$holderRecord->item_name} ×{$holderRecord->quantity} is now assigned to you — you keep it, there is no return date. "
                . 'If you stop teaching it will be handed over to the next teacher.');

            return $holderRecord->fresh();
        });
    }

    /** Give an assigned item to someone else (same department). */
    public static function handOver(RequestItem $holderRecord, User $to, User $by, ?string $note = null): void
    {
        DB::transaction(function () use ($holderRecord, $to, $by, $note) {
            $item = RequestItem::with('holder')->lockForUpdate()->findOrFail($holderRecord->id);

            if (! $item->assigned_at || ! $item->holder_id || $item->returned_at) {
                throw new \RuntimeException('This item is not assigned to anyone right now.');
            }
            if ($item->holder_id === $to->id) {
                throw new \RuntimeException("{$to->name} already has it.");
            }
            if ($to->status !== 'approved' || ! $to->hasAnyRole(['faculty', 'program head'])) {
                throw new \RuntimeException("{$to->name} cannot receive items (account not active).");
            }
            if ($item->holder && (int) $to->department_id !== (int) $item->holder->department_id) {
                throw new \RuntimeException("{$to->name} is in another department.");
            }

            $from = $item->holder_id;
            $item->update(['holder_id' => $to->id]);

            ItemHandover::create([
                'request_item_id' => $item->id,
                'from_user_id'    => $from,
                'to_user_id'      => $to->id,
                'by_user_id'      => $by->id,
                'kind'            => 'handed_over',
                'note'            => $note,
            ]);

            static::tell($to->id, $item, "{$item->item_name} ×{$item->quantity} was handed over to you"
                . ($item->holder ? " from {$item->holder->name}" : '') . '. You keep it (no return date).');
            static::tell($from, $item, "{$item->item_name} ×{$item->quantity} was handed over to {$to->name}.");
        });
    }

    /**
     * Where the items go back to when nobody needs them anymore:
     *   'main'       → the Admin's main stock (they came from the Admin)
     *   'department' → the department's own stock (the Program Head gave them from it)
     */
    public static function returnsTo(RequestItem $holderRecord): string
    {
        return static::mainStockRecord($holderRecord) ? 'main' : 'department';
    }

    /** Take an assigned item back into stock (Good goes back; damaged / lost does not). */
    public static function returnToStock(RequestItem $holderRecord, string $condition, User $by, ?string $notes = null): string
    {
        return DB::transaction(function () use ($holderRecord, $condition, $by, $notes) {
            $item = RequestItem::with(['request.user', 'holder'])->lockForUpdate()->findOrFail($holderRecord->id);

            if (! $item->assigned_at || ! $item->holder_id || $item->returned_at) {
                throw new \RuntimeException('This item is not assigned to anyone right now.');
            }

            $main   = static::mainStockRecord($item);
            $fields = [
                'returned_at'      => now(),
                'return_condition' => $condition,
                'return_notes'     => $notes ?: null,
                'received_by'      => $by->id,
            ];

            if ($main) {
                // Back to the Admin: off the department's count, Good → main stock (Before → After kept)
                $main = RequestItem::with('request')->lockForUpdate()->findOrFail($main->id);
                $main->update($fields);

                $allocation = DB::table('resource_all_locations')
                    ->where('resource_id', $main->resource_id)
                    ->where('department_id', $main->request->department_id)
                    ->lockForUpdate()
                    ->first();
                if ($allocation) {
                    DB::table('resource_all_locations')->where('id', $allocation->id)->update([
                        'allocated_quantity' => max((int) $allocation->allocated_quantity - (int) $main->quantity, 0),
                        'updated_at'         => now(),
                    ]);
                }

                if ($condition === 'good') {
                    $before = (int) DB::table('resources')->where('id', $main->resource_id)->lockForUpdate()->value('quantity_available');
                    DB::table('resources')->where('id', $main->resource_id)->increment('quantity_available', $main->quantity);
                    $main->update(['stock_before_return' => $before, 'stock_after_return' => $before + (int) $main->quantity]);
                }

                if ($main->id !== $item->id) {
                    $item->update($fields);
                }
                $where = 'the main stock';
            } else {
                // Back to the department's own stock
                $item->update($fields);
                if ($condition === 'good') {
                    DB::table('resource_all_locations')
                        ->where('resource_id', $item->resource_id)
                        ->where('department_id', $item->request->department_id)
                        ->increment('allocated_quantity', $item->quantity);
                }
                $where = 'the department stock';
            }

            ItemHandover::create([
                'request_item_id' => $item->id,
                'from_user_id'    => $item->holder_id,
                'to_user_id'      => null,
                'by_user_id'      => $by->id,
                'kind'            => 'returned',
                'note'            => trim(($condition !== 'good' ? ucfirst($condition) . '. ' : '') . ($notes ?? '')) ?: null,
            ]);

            static::tell($item->holder_id, $item, "{$item->item_name} ×{$item->quantity} was taken back into {$where}.");

            return $condition === 'good' ? "Back in {$where}." : "Marked {$condition}; not added back to stock.";
        });
    }

    /** People in the same department who can receive it (active faculty and the Program Head), except the holder. */
    public static function recipients(RequestItem $holderRecord)
    {
        $deptId = $holderRecord->holder?->department_id ?? $holderRecord->request?->department_id;

        return User::where('department_id', $deptId)
            ->where('status', 'approved')
            ->whereKeyNot($holderRecord->holder_id)
            ->whereHas('roles', fn ($r) => $r->whereIn('name', ['faculty', 'program head']))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /** What a person keeps now: [ ['name' => 'Table', 'qty' => 1], … ]. */
    public static function heldBy(int $userId)
    {
        return RequestItem::assignedNow()->where('holder_id', $userId)->get(['id', 'item_name', 'quantity']);
    }

    /* ------------------------------------------------------------------ */

    /** [record that carries the holder, its twin or null]. */
    protected static function pair(RequestItem $item): array
    {
        $item->loadMissing('request.user');

        // The Program Head's copy of a forwarded faculty request → the faculty item holds it
        if ($item->request?->forwarded_from_request_id) {
            $faculty = ReturnTracking::facultyItemFor($item);
            if ($faculty && $faculty->request?->status === 'approved' && ! $faculty->returned_at) {
                return [$faculty, $item];
            }
            return [$item, null];
        }

        // A faculty item that the Program Head borrowed from the Admin for → its copy is the twin
        $adminCopy = ReturnTracking::adminItemFor($item);

        return [$item, $adminCopy && ! $adminCopy->returned_at ? $adminCopy : null];
    }

    /** The record whose stock came from the Admin's main stock (Program Head request), or null. */
    protected static function mainStockRecord(RequestItem $holderRecord): ?RequestItem
    {
        $holderRecord->loadMissing('request.user');

        if ($holderRecord->request?->user?->hasRole('program head')) {
            return $holderRecord;
        }

        $copy = ReturnTracking::adminItemFor($holderRecord);

        return $copy && ! $copy->returned_at ? $copy : null;
    }

    protected static function tell(?int $userId, RequestItem $item, string $message): void
    {
        if (! $userId) {
            return;
        }

        Notification::create([
            'user_id'    => $userId,
            'request_id' => $item->request_id,
            'message'    => $message,
            'type'       => 'Return',
            'status'     => 'pending',
        ]);
    }
}
