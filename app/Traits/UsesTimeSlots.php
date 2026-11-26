<?php

namespace App\Traits;

use App\Support\TimeSlots;
use Illuminate\Support\Carbon;

/**
 * For Livewire forms with $facility_name, $start_time, $end_time and a date field:
 * powers the 30-minute pickers (partials.reservation-time-pickers) and the
 * "already booked" list (partials.reservation-day-bookings).
 *
 * Override timeSlotDate() / timeSlotExceptRequestId() when the form names
 * its date differently or is editing an existing request.
 */
trait UsesTimeSlots
{
    /** The date the booking is for. */
    protected function timeSlotDate(): ?string
    {
        return $this->request_date ?: null;
    }

    /** A request to leave out of the booked list (the one being edited). */
    protected function timeSlotExceptRequestId(): ?int
    {
        return null;
    }

    /** Approved bookings of the picked facility on the picked day. */
    public function getDayBookingsProperty(): array
    {
        return TimeSlots::approvedBookings($this->facility_name ?: null, $this->timeSlotDate(), $this->timeSlotExceptRequestId());
    }

    /** True when the 30-minute slot starting at $start is already booked. */
    public function startIsTaken(string $start): bool
    {
        $end = Carbon::createFromFormat('H:i', $start)->addMinutes(TimeSlots::STEP)->format('H:i');

        return TimeSlots::overlaps($start, $end, $this->dayBookings);
    }

    /** True when ending at $end (from the picked start) would run into a booking. */
    public function endIsTaken(string $end): bool
    {
        return $this->start_time && TimeSlots::overlaps($this->start_time, $end, $this->dayBookings);
    }

    /** After the start changes: keep the end after it (default 1 hour later). */
    protected function keepEndAfterStart(): void
    {
        if ($this->start_time && (! $this->end_time || $this->end_time <= $this->start_time)) {
            $this->end_time = TimeSlots::defaultEnd($this->start_time);
        }
    }
}
