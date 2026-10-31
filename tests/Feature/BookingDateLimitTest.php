<?php

namespace Tests\Feature;

use App\Support\BookingDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookingDateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    protected function reservationForm(string $date)
    {
        $dept    = $this->createDepartment();
        $student = $this->createUser('student', $dept);
        $this->createFacility('Computer Laboratory 1');

        return Livewire::actingAs($student)
            ->test('pages::student-faculty.reservation.create-reservation')
            ->set('facility_name', 'Computer Laboratory 1')
            ->set('used_date', $date)
            ->set('start_time', '08:00')
            ->set('end_time', '10:00')
            ->set('purpose', 'Laboratory class for testing')
            ->call('submit');
    }

    public function test_far_future_year_is_rejected(): void
    {
        $this->reservationForm('2089-05-01')->assertHasErrors(['used_date' => 'before_or_equal']);
        $this->assertDatabaseMissing('request_items', ['request_date' => '2089-05-01']);
    }

    public function test_five_digit_year_is_rejected(): void
    {
        $this->reservationForm('20899-05-01')->assertHasErrors('used_date');
    }

    public function test_past_date_is_rejected(): void
    {
        $this->reservationForm(now()->subDay()->toDateString())->assertHasErrors(['used_date' => 'after_or_equal']);
    }

    public function test_date_inside_window_is_accepted(): void
    {
        $this->reservationForm(now()->addDays(30)->toDateString())->assertHasNoErrors('used_date');
    }

    public function test_last_allowed_day_is_accepted_and_next_day_rejected(): void
    {
        $this->reservationForm(BookingDate::max())->assertHasNoErrors('used_date');
        $this->reservationForm(now()->addDays(BookingDate::MAX_DAYS_AHEAD + 1)->toDateString())
            ->assertHasErrors('used_date');
    }

    public function test_date_picker_has_min_and_max(): void
    {
        $dept    = $this->createDepartment();
        $student = $this->createUser('student', $dept);

        Livewire::actingAs($student)
            ->test('pages::student-faculty.reservation.create-reservation')
            ->assertSeeHtml('min="' . BookingDate::min() . '"')
            ->assertSeeHtml('max="' . BookingDate::max() . '"');
    }

    public function test_calendar_cannot_go_past_booking_window(): void
    {
        $dept    = $this->createDepartment();
        $student = $this->createUser('student', $dept);

        $c = Livewire::actingAs($student)->test('pages::student-faculty.calendar');
        for ($i = 0; $i < 40; $i++) {
            $c->call('nextMonth');
        }

        $last = \Carbon\Carbon::parse(BookingDate::max());
        $c->assertSet('year', $last->year)->assertSet('month', $last->month);

        $c->call('selectDate', '2089-05-01')->assertSet('selectedDate', now()->toDateString());
    }
}
