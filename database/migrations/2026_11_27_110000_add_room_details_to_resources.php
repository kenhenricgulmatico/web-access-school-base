<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ✅ Facility rooms:
 *    - capacity, location (building / floor), room_kind (Classroom, Computer laboratory…)
 *    - maintenance_reason + maintenance_until (shown on the list and the calendar, sent in notices)
 *    - cleans room names: extra spaces removed ("Library " → "Library"), and the bookings that
 *      use the old name follow, so nothing is lost.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->unsignedSmallInteger('capacity')->nullable()->after('quantity_available');
            $table->string('location', 100)->nullable()->after('capacity');
            $table->string('room_kind', 40)->nullable()->after('location');
            $table->string('maintenance_reason', 255)->nullable()->after('status');
            $table->date('maintenance_until')->nullable()->after('maintenance_reason');
        });

        $typeId = DB::table('resource_types')->where('type_name', 'Facilities')->value('id');
        if (! $typeId) {
            return;
        }

        $rooms = DB::table('resources')->where('resource_type_id', $typeId)->get(['id', 'resource_name']);
        $taken = [];

        foreach ($rooms as $room) {
            $clean = preg_replace('/\s+/u', ' ', trim($room->resource_name));
            $key   = mb_strtolower($clean);

            // two rooms that would end up with the same name: leave the second one as it is
            if (isset($taken[$key])) {
                continue;
            }
            $taken[$key] = true;

            if ($clean === $room->resource_name) {
                continue;
            }

            DB::table('resources')->where('id', $room->id)->update(['resource_name' => $clean]);
            DB::table('request_items')
                ->whereNull('resource_id')
                ->when(DB::getDriverName() === 'mysql',
                    fn ($q) => $q->whereRaw('BINARY item_name = ?', [$room->resource_name]),
                    fn ($q) => $q->where('item_name', $room->resource_name))
                ->update(['item_name' => $clean]);
        }
    }

    public function down(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->dropColumn(['capacity', 'location', 'room_kind', 'maintenance_reason', 'maintenance_until']);
        });
    }
};
