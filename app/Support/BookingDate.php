<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * One place that decides which dates a reservation / request may use.
 *
 * Stops dates like "2089-05-01" (or a typed 5-digit year) from being saved:
 * a booking must be from today up to MAX_DAYS_AHEAD days in the future.
 * Change MAX_DAYS_AHEAD to make the window longer or shorter.
 */
class BookingDate
{
    /** How far ahead users can book (180 days ≈ 6 months / one semester). */
    public const MAX_DAYS_AHEAD = 180;

    /** Earliest allowed date, for the <input type="date" min="..."> attribute. */
    public static function min(): string
    {
        return Carbon::today()->toDateString();
    }

    /** Latest allowed date, for the <input type="date" max="..."> attribute. */
    public static function max(): string
    {
        return Carbon::today()->addDays(self::MAX_DAYS_AHEAD)->toDateString();
    }

    /** Validation rules for a booking date field. */
    public static function rules(): array
    {
        return [
            'required',
            'date_format:Y-m-d',
            'after_or_equal:today',
            'before_or_equal:' . self::max(),
        ];
    }

    /** Friendly error messages for a booking date field, e.g. messages('used_date'). */
    public static function messages(string $field): array
    {
        $latest = Carbon::parse(self::max())->format('M d, Y');

        return [
            "{$field}.required"        => 'Please select a date',
            "{$field}.date_format"     => 'Please enter a valid date',
            "{$field}.after_or_equal"  => 'Date must be today or later',
            "{$field}.before_or_equal" => "Date can only be up to {$latest} (about 6 months from today)",
        ];
    }
}
