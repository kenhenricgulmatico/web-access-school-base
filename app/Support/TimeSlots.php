<?php

namespace App\Support;

use App\Models\RequestItem;
use Illuminate\Support\Carbon;

/**
 * One place for the reservation time pickers (Create / Edit Reservation):
 * 30-minute steps between OPEN and CLOSE, e.g. 9:30 AM – 10:30 AM.
 * Change STEP / OPEN / CLOSE here to change every form at once.
 */
class TimeSlots
{
    public const STEP  = 30;      // minutes
    public const OPEN  = '07:00'; // first start time (7:00 AM)
    public const CLOSE = '20:00'; // last end time (8:00 PM)

    /** Start choices: ['08:00' => '8:00 AM', '08:30' => '8:30 AM', … '17:30' => '5:30 PM']. */
    public static function starts(): array
    {
        $out  = [];
        $time = Carbon::createFromFormat('H:i', self::OPEN);
        $last = Carbon::createFromFormat('H:i', self::CLOSE)->subMinutes(self::STEP);

        while ($time->lte($last)) {
            $out[$time->format('H:i')] = $time->format('g:i A');
            $time->addMinutes(self::STEP);
        }

        return $out;
    }

    /** End choices after $start, with the length: ['10:30' => '10:30 AM · 1 hr', …]. */
    public static function ends(?string $start): array
    {
        $start = self::normalize($start) ?? self::OPEN;
        $out   = [];
        $from  = Carbon::createFromFormat('H:i', $start);
        $time  = $from->copy()->addMinutes(self::STEP);
        $close = Carbon::createFromFormat('H:i', self::CLOSE);

        while ($time->lte($close)) {
            $out[$time->format('H:i')] = $time->format('g:i A') . ' · ' . self::duration($from->diffInMinutes($time));
            $time->addMinutes(self::STEP);
        }

        return $out;
    }

    /** Every valid end time (for validation). */
    public static function allEnds(): array
    {
        return array_keys(self::ends(self::OPEN));
    }

    /** "30 min", "1 hr", "1 hr 30 min", "2 hrs". */
    public static function duration(int $minutes): string
    {
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        $hours = $h ? $h . ' ' . ($h === 1 ? 'hr' : 'hrs') : '';
        $mins  = $m ? $m . ' min' : '';

        return trim($hours . ' ' . $mins);
    }

    /** "09:00:00" / "9:00" → "09:00"; null when it isn't a time. */
    public static function normalize(?string $time): ?string
    {
        if (! $time) {
            return null;
        }

        return rescue(fn () => Carbon::parse($time)->format('H:i'), null, false);
    }

    /** A sensible end after $start: one hour later, or the next slot near closing time. */
    public static function defaultEnd(string $start): string
    {
        $ends = array_keys(self::ends($start));

        return $ends[1] ?? $ends[0] ?? self::CLOSE;
    }

    /** Validation rules for the two fields. */
    public static function rules(): array
    {
        return [
            'start_time' => ['required', 'in:' . implode(',', array_keys(self::starts()))],
            'end_time'   => ['required', 'in:' . implode(',', self::allEnds()), 'after:start_time'],
        ];
    }

    public static function messages(): array
    {
        return [
            'start_time.in' => 'Please pick a start time from the list (every 30 minutes).',
            'end_time.in'   => 'Please pick an end time from the list (every 30 minutes).',
        ];
    }

    /**
     * Approved bookings of $facility on $date (optionally leaving out one request,
     * e.g. the one being edited): [ ['start' => '09:00', 'end' => '10:30', 'label' => …, 'department' => …], … ]
     */
    public static function approvedBookings(?string $facility, ?string $date, ?int $exceptRequestId = null): array
    {
        if (! $facility || ! $date) {
            return [];
        }

        return RequestItem::whereNull('resource_id')
            ->where('item_name', $facility)
            ->whereDate('request_date', $date)
            ->whereHas('request', fn ($q) => static::withoutDuplicateCopies($q
                ->where('status', 'approved')
                // leave out the request being edited — and its endorsed copy
                ->when($exceptRequestId, fn ($e) => $e->where('id', '!=', $exceptRequestId)
                    ->where(fn ($c) => $c->whereNull('forwarded_from_request_id')
                        ->orWhere('forwarded_from_request_id', '!=', $exceptRequestId))), ['approved']))
            ->with('request.department')
            ->orderBy('start_time')
            ->get()
            ->filter(fn ($i) => $i->start_time && $i->end_time)
            ->map(fn ($i) => [
                'start'      => Carbon::parse($i->start_time)->format('H:i'),
                'end'        => Carbon::parse($i->end_time)->format('H:i'),
                'label'      => Carbon::parse($i->start_time)->format('g:i A') . ' – ' . Carbon::parse($i->end_time)->format('g:i A'),
                'department' => $i->request?->department?->department_name ?? '—',
            ])
            ->values()
            ->all();
    }

    /**
     * ✅ One booking, counted once.
     *    When a Program Head endorses a faculty request, the system keeps TWO requests for the
     *    same room and time: the faculty's own and the Program Head's copy sent to the Admin
     *    (requests.forwarded_from_request_id). Leave the copy out while its faculty request is
     *    in one of $sourceStatuses — the faculty request already stands for that booking.
     *    (If the faculty request was NOT approved, the copy still counts: it is the real booking.)
     */
    public static function withoutDuplicateCopies($requestQuery, array $sourceStatuses)
    {
        return $requestQuery->where(fn ($q) => $q
            ->whereNull('requests.forwarded_from_request_id')
            ->orWhereNotExists(fn ($src) => $src->selectRaw('1')
                ->from('requests as src')
                ->whereColumn('src.id', 'requests.forwarded_from_request_id')
                ->whereIn('src.status', $sourceStatuses)));
    }

    /** True when $start–$end overlaps any of $bookings. */
    public static function overlaps(string $start, string $end, array $bookings): bool
    {
        foreach ($bookings as $b) {
            if ($start < $b['end'] && $end > $b['start']) {
                return true;
            }
        }

        return false;
    }
}
