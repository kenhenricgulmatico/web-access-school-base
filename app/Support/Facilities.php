<?php

namespace App\Support;

use App\Models\Notification;
use App\Models\RequestItem;
use App\Models\Resource;
use App\Models\ResourceType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * ✅ Facility rooms in one place: clean names, no duplicates, renaming that keeps bookings,
 *    and the maintenance notice.
 *
 *    Bookings remember a room by its NAME (request_items.item_name), so a rename must move
 *    them too — otherwise they'd point at a name that no longer exists and the double-booking
 *    check would stop seeing them.
 */
class Facilities
{
    public const KINDS = [
        'Classroom',
        'Computer laboratory',
        'Science laboratory',
        'Library',
        'Court / Gym',
        'Hall / Auditorium',
        'Other',
    ];

    /** "  Room   101 " → "Room 101". */
    public static function cleanName(?string $name): string
    {
        return preg_replace('/\s+/u', ' ', trim((string) $name));
    }

    /** All facility rooms. */
    public static function query()
    {
        return Resource::whereHas('resourceType', fn ($q) => $q->where('type_name', ResourceType::FACILITY));
    }

    /** True when another room already has this name (upper/lower case and spaces ignored). */
    public static function nameTaken(string $name, ?int $exceptId = null): bool
    {
        return static::query()
            ->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))
            ->get(['id', 'resource_name'])
            ->contains(fn ($r) => mb_strtolower(static::cleanName($r->resource_name)) === mb_strtolower(static::cleanName($name)));
    }

    /** Validation for the room form fields shared by Add and Edit. */
    public static function rules(string $prefix = ''): array
    {
        return [
            $prefix . 'facility_name'      => 'required|string|max:100',
            $prefix . 'room_kind'          => 'nullable|in:' . implode(',', self::KINDS),
            $prefix . 'capacity'           => 'nullable|integer|min:1|max:5000',
            $prefix . 'location'           => 'nullable|string|max:100',
            $prefix . 'status'             => 'required|in:available,maintenance',
            $prefix . 'maintenance_reason' => 'nullable|string|max:255',
            $prefix . 'maintenance_until'  => 'nullable|date|after_or_equal:today',
        ];
    }

    /**
     * Rename a room and move every booking that uses the old name.
     * Returns how many bookings were moved.
     */
    public static function rename(Resource $room, string $newName): int
    {
        $old = $room->resource_name;
        $new = static::cleanName($newName);

        if ($old === $new) {
            return 0;
        }

        return DB::transaction(function () use ($room, $old, $new) {
            $room->update(['resource_name' => $new]);

            return RequestItem::whereNull('resource_id')
                ->where('item_name', $old)
                ->update(['item_name' => $new]);
        });
    }

    /** Bookings of this room from today on (approved or still waiting), each booking counted once. */
    public static function upcomingBookings(Resource $room)
    {
        return RequestItem::whereNull('resource_id')
            ->where('item_name', $room->resource_name)
            ->whereDate('request_date', '>=', today())
            ->whereHas('request', fn ($q) => TimeSlots::withoutDuplicateCopies($q->whereIn('status', ['pending', 'approved']), ['pending', 'approved']))
            ->with('request.user')
            ->orderBy('request_date')
            ->orderBy('start_time')
            ->get();
    }

    /** "Aircon repair, until Dec 05" / "until Dec 05" / "Aircon repair" / "". */
    public static function maintenanceNote(Resource $room): string
    {
        $parts = array_filter([
            $room->maintenance_reason,
            $room->maintenance_until ? 'until ' . Carbon::parse($room->maintenance_until)->format('M d') : null,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Tell everyone with an upcoming booking that the room went under maintenance.
     * Returns the affected bookings.
     */
    public static function notifyMaintenance(Resource $room)
    {
        $upcoming = static::upcomingBookings($room);
        $note     = static::maintenanceNote($room);

        foreach ($upcoming->groupBy(fn ($i) => $i->request?->user_id) as $userId => $items) {
            if (! $userId) {
                continue;
            }

            $dates = $items->map(fn ($i) => Carbon::parse($i->request_date)->format('M d'))->unique()->implode(', ');

            Notification::create([
                'user_id'    => $userId,
                'request_id' => $items->first()->request_id,
                'message'    => "{$room->resource_name} is now under maintenance" . ($note ? " ({$note})" : '')
                              . ". Your booking on {$dates} may be affected — please contact the Admin or book another room.",
                'type'       => 'Gmail',
                'status'     => 'pending',
            ]);
        }

        return $upcoming;
    }
}
