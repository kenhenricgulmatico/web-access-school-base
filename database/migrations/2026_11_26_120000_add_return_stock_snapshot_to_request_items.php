<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Main-stock level just before / after a borrowed item was returned to the Admin
 * in good condition — shown as "Before → After" on the Stock History page.
 * Null for returns made before this was recorded (and for damaged / lost items,
 * which don't go back to stock).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            $table->unsignedInteger('stock_before_return')->nullable()->after('received_by');
            $table->unsignedInteger('stock_after_return')->nullable()->after('stock_before_return');
        });
    }

    public function down(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            $table->dropColumn(['stock_before_return', 'stock_after_return']);
        });
    }
};
