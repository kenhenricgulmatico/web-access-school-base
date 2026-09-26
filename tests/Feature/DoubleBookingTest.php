<?php

namespace Tests\Feature;

use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoubleBookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    public function test_facility_can_be_detected_as_conflicting_for_same_time(): void
    {
        $dept    = $this->createDepartment();
        $student = $this->createUser('student', $dept);

        $date = now()->addDays(3)->toDateString();

        $existing = ResourceRequest::create([
            'user_id'         => $student->id,
            'department_id'   => $dept->id,
            'request_type_id' => 1,
            'purpose'         => 'Existing booking',
            'status'          => 'approved',
        ]);

        RequestItem::create([
            'request_id'   => $existing->id,
            'resource_id'  => null,
            'item_name'    => 'Computer Laboratory 1',
            'quantity'     => 1,
            'request_date' => $date,
            'start_time'   => '09:00',
            'end_time'     => '10:00',
        ]);

        $conflict = RequestItem::whereNull('resource_id')
            ->where('item_name', 'Computer Laboratory 1')
            ->where('request_date', $date)
            ->whereHas('request', fn ($q) => $q->where('status', 'approved'))
            ->where('start_time', '<', '10:30')
            ->where('end_time', '>', '09:30')
            ->exists();

        $this->assertTrue($conflict);
    }

    public function test_non_overlapping_time_has_no_conflict(): void
    {
        $dept    = $this->createDepartment();
        $student = $this->createUser('student', $dept);

        $date = now()->addDays(3)->toDateString();

        $existing = ResourceRequest::create([
            'user_id'         => $student->id,
            'department_id'   => $dept->id,
            'request_type_id' => 1,
            'purpose'         => 'Existing booking',
            'status'          => 'approved',
        ]);

        RequestItem::create([
            'request_id'   => $existing->id,
            'resource_id'  => null,
            'item_name'    => 'Computer Laboratory 1',
            'quantity'     => 1,
            'request_date' => $date,
            'start_time'   => '09:00',
            'end_time'     => '10:00',
        ]);

        $conflict = RequestItem::whereNull('resource_id')
            ->where('item_name', 'Computer Laboratory 1')
            ->where('request_date', $date)
            ->whereHas('request', fn ($q) => $q->where('status', 'approved'))
            ->where('start_time', '<', '11:00')
            ->where('end_time', '>', '10:30')
            ->exists();

        $this->assertFalse($conflict);
    }
}
