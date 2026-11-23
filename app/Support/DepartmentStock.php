<?php

namespace App\Support;

use App\Models\Request as ResourceRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * What a student / faculty request needs that the department does NOT have yet.
 * Used by the Program Head's "Forward to Admin" button: the missing items are
 * pre-filled on Request to Admin, so the Program Head can borrow them from the Admin first.
 */
class DepartmentStock
{
    /**
     * Items still missing from the department stock for this request.
     * Each row: resource_id, item_name, unit, requested, in_department, missing.
     */
    public static function shortage(ResourceRequest $request): Collection
    {
        $request->loadMissing('items.resource');

        $materials = $request->items->whereNotNull('resource_id');

        if ($materials->isEmpty()) {
            return collect();
        }

        $inDepartment = DB::table('resource_all_locations')
            ->where('department_id', $request->department_id)
            ->whereIn('resource_id', $materials->pluck('resource_id'))
            ->pluck('allocated_quantity', 'resource_id');

        return $materials
            ->groupBy('resource_id')
            ->map(function ($rows, $resourceId) use ($inDepartment) {
                $requested = (int) $rows->sum('quantity');
                $have      = (int) ($inDepartment[$resourceId] ?? 0);
                $first     = $rows->first();

                return (object) [
                    'resource_id'   => (int) $resourceId,
                    'item_name'     => $first->item_name,
                    'unit'          => $first->resource?->unit ?: 'Ream',
                    'requested'     => $requested,
                    'in_department' => $have,
                    'missing'       => max($requested - $have, 0),
                ];
            })
            ->filter(fn ($row) => $row->missing > 0)
            ->values();
    }

    /** Short text for a tooltip, e.g. "Chair: need 10 more Pcs". */
    public static function summary(ResourceRequest $request): string
    {
        return static::shortage($request)
            ->map(fn ($r) => "{$r->item_name}: need {$r->missing} more {$r->unit}")
            ->implode(', ');
    }
}
