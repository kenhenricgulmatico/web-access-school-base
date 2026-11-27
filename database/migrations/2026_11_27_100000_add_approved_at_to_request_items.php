<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ✅ request_items.approved_at — the moment the Admin approved the item and it left the main stock.
 *    Stock History used the request's "last changed" time, which moves if the request is touched
 *    again after approval. Past approvals are filled in from the audit log (the first time the
 *    request was saved as "approved"), falling back to the request's last-changed time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('stock_after_approval');
        });

        $requestIds = DB::table('request_items')
            ->join('requests', 'requests.id', '=', 'request_items.request_id')
            ->whereNotNull('request_items.resource_id')
            ->where('requests.status', 'approved')
            ->distinct()
            ->pluck('requests.id');

        $hasAudit = Schema::hasTable('audit_logs') && DB::getDriverName() === 'mysql';

        foreach ($requestIds as $id) {
            $at = null;

            if ($hasAudit) {
                $at = DB::table('audit_logs')
                    ->where('table_name', 'requests')
                    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(record, '$.status')) = 'approved'")
                    ->whereRaw("JSON_EXTRACT(record, '$.id') = ?", [$id])
                    ->min('created_at');
            }

            $at ??= DB::table('requests')->where('id', $id)->value('updated_at');

            DB::table('request_items')
                ->where('request_id', $id)
                ->whereNotNull('resource_id')
                ->whereNull('approved_at')
                ->update(['approved_at' => $at]);
        }
    }

    public function down(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            $table->dropColumn('approved_at');
        });
    }
};
