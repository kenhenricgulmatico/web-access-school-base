<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Endorsed (forwarded) requests used to save their purpose as
 * "For <faculty name>: <purpose>" (or the older "For <name>'s request #12: <purpose>").
 * Now they keep only the faculty member's own purpose — this cleans the old rows.
 *
 * Only the exact prefix built from the original requester's real name is removed,
 * so a purpose that merely starts with "For …" is left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('requests as fwd')
            ->join('requests as src', 'src.id', '=', 'fwd.forwarded_from_request_id')
            ->leftJoin('users as u', 'u.id', '=', 'src.user_id')
            ->whereNotNull('fwd.forwarded_from_request_id')
            ->get(['fwd.id', 'fwd.purpose', 'src.id as src_id', 'u.name as src_name']);

        foreach ($rows as $row) {
            $name    = $row->src_name ?? 'a student';
            $purpose = (string) $row->purpose;

            foreach (["For {$name}'s request #{$row->src_id}: ", "For {$name}: "] as $prefix) {
                if (str_starts_with($purpose, $prefix)) {
                    DB::table('requests')->where('id', $row->id)->update([
                        'purpose' => substr($purpose, strlen($prefix)),
                    ]);
                    break;
                }
            }
        }
    }

    public function down(): void
    {
        // Nothing to undo: the faculty name is still on the original request.
    }
};
