<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * requests.forwarded_from_request_id
 *   → on a Program Head's Request to Admin: the student / faculty request it was
 *     forwarded from ("Forward to Admin"). When the Admin approves this request,
 *     the student's request is approved automatically (rejected → rejected too).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->foreignId('forwarded_from_request_id')
                ->nullable()
                ->after('status')
                ->constrained('requests')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('forwarded_from_request_id');
        });
    }
};
