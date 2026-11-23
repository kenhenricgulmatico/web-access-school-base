<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * request_items.return_requested_at
 *   → when a Program Head clicked "Return to Admin" for items they borrowed
 *     from the Admin. The Admin then confirms it with "Mark returned".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            $table->timestamp('return_requested_at')->nullable()->after('returned_at');
        });
    }

    public function down(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            $table->dropColumn('return_requested_at');
        });
    }
};
