<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ✅ Stock Materials:
 *    - resources.low_stock_at → "warn me when stock is at or below this" (per material)
 *    - stocks.reason          → why stock was REMOVED (used_up, damaged, lost, correction).
 *                               A removal is a `stocks` row with a negative quantity_added.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->unsignedInteger('low_stock_at')->default(5)->after('quantity_available');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->string('reason', 30)->nullable()->after('remarks');
        });
    }

    public function down(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->dropColumn('low_stock_at');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn('reason');
        });
    }
};
