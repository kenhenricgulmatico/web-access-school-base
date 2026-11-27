<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ✅ Assigned items — furniture and equipment a teacher KEEPS (no return date), e.g. a table and
 *    chair for the faculty room. When the teacher stops teaching it is handed over to the next one.
 *
 *    request_items.assigned_at  — when it became "kept" instead of "borrowed"
 *    request_items.holder_id    — who has it now (only on the one record that stands for the items)
 *    item_handovers             — every change of hands: assigned → handed over → returned to stock
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            $table->timestamp('assigned_at')->nullable()->after('due_back_at');
            $table->foreignId('holder_id')->nullable()->after('assigned_at')->constrained('users')->nullOnDelete();
        });

        Schema::create('item_handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_item_id')->constrained('request_items')->cascadeOnDelete();
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete();   // null = back to stock
            $table->foreignId('by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kind', 20);   // assigned | handed_over | returned
            $table->string('note', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_handovers');
        Schema::table('request_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('holder_id');
            $table->dropColumn('assigned_at');
        });
    }
};
