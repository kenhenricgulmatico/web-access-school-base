<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Remembers the main-stock level before / after an approval, per request item,
 * so the Stock History page can show the real "Before → After" from that moment
 * (instead of working it out from today's stock).
 */
class StockSnapshot
{
    /**
     * Call inside the approval transaction, with the stock level read
     * (under lock) just BEFORE the decrement.
     *
     * If one request has several lines of the same resource, each line gets
     * its own step: 50 → 45 for the first line, 45 → 40 for the next, …
     */
    public static function recordApproval(int $requestId, int $resourceId, int $stockBefore): void
    {
        $running = $stockBefore;

        $lines = DB::table('request_items')
            ->where('request_id', $requestId)
            ->where('resource_id', $resourceId)
            ->orderBy('id')
            ->get(['id', 'quantity']);

        foreach ($lines as $line) {
            $after = max($running - (int) $line->quantity, 0);

            // Query builder on purpose: this bookkeeping shouldn't add audit-log entries
            DB::table('request_items')->where('id', $line->id)->update([
                'stock_before_approval' => $running,
                'stock_after_approval'  => $after,
            ]);

            $running = $after;
        }
    }
}
