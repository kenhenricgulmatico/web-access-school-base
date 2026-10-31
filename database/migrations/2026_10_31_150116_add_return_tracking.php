<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Return tracking for borrowed items.
 *
 *  resources.is_returnable    → the admin marks items that must be given back
 *                               (chairs, projectors…). Supplies like bond paper stay false.
 *
 *  request_items.due_back_at  → "return by" date, set when the request is approved
 *  request_items.returned_at  → when the program head marked it returned
 *  request_items.return_condition / return_notes / received_by
 *  request_items.reminder_sent_at → last reminder e-mail (max one per day)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->boolean('is_returnable')->default(false)->after('status');
        });

        Schema::table('request_items', function (Blueprint $table) {
            $table->date('due_back_at')->nullable()->after('end_time');
            $table->timestamp('returned_at')->nullable()->after('due_back_at');
            $table->string('return_condition', 20)->nullable()->after('returned_at'); // good | damaged | lost
            $table->string('return_notes', 500)->nullable()->after('return_condition');
            $table->foreignId('received_by')->nullable()->after('return_notes')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('reminder_sent_at')->nullable()->after('received_by');

            $table->index(['due_back_at', 'returned_at']);
        });

        // Sensible default: furniture is borrowed and given back.
        $furniture = DB::table('resource_types')->where('type_name', 'Furniture')->value('id');
        if ($furniture) {
            DB::table('resources')->where('resource_type_id', $furniture)->update(['is_returnable' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            $table->dropIndex(['due_back_at', 'returned_at']);
            $table->dropConstrainedForeignId('received_by');
            $table->dropColumn(['due_back_at', 'returned_at', 'return_condition', 'return_notes', 'reminder_sent_at']);
        });

        Schema::table('resources', function (Blueprint $table) {
            $table->dropColumn('is_returnable');
        });
    }
};
