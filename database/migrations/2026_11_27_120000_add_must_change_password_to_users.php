<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ✅ users.must_change_password — set when the Admin creates an account or resets a password
 *    (the Admin knows it), and when someone signs in with the shared default password.
 *    Until they choose their own, every page sends them to "Choose your own password".
 *
 *    users.status gains the value "deactivated" (a string column, no change needed):
 *    the account can't sign in, but its requests and history stay.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('must_change_password');
        });
    }
};
