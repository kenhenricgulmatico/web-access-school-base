<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Main-stock level just before / after the Admin approved this item
 * (stock goes down) — shown as "Before → After" on the Stock History page.
 * Null for approvals made before this was recorded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            $table->unsignedInteger('stock_before_approval')->nullable()->after('received_by');
            $table->unsignedInteger('stock_after_approval')->nullable()->after('stock_before_approval');
        });
    }

    public function down(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            $table->dropColumn(['stock_before_approval', 'stock_after_approval']);
        });
    }
};
