<?php

namespace Tests\Feature;

use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FacilityApprovalEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    public function test_coordinator_cannot_approve_overlapping_facility_reservation(): void
    {
        // 1. Set up users
        $dept        = $this->createDepartment();
        $studentA    = $this->createUser('faculty', $dept);
        $studentB    = $this->createUser('faculty', $dept);
        $programHead = $this->createUser('program head', $dept);

        $date = now()->addDays(3)->toDateString();

        // 2. Student A already has an APPROVED booking (9:00–10:00)
        $approved = ResourceRequest::create([
            'user_id'         => $studentA->id,
            'department_id'   => $dept->id,
            'request_type_id' => 1,
            'purpose'         => 'Already-approved booking',
            'status'          => 'approved',
        ]);

        RequestItem::create([
            'request_id'   => $approved->id,
            'resource_id'  => null,
            'item_name'    => 'Computer Laboratory 1',
            'quantity'     => 1,
            'request_date' => $date,
            'start_time'   => '09:00',
            'end_time'     => '10:00',
        ]);

        // 3. Student B submits a PENDING overlapping booking (9:30–10:30)
        $pending = ResourceRequest::create([
            'user_id'         => $studentB->id,
            'department_id'   => $dept->id,
            'request_type_id' => 1,
            'purpose'         => 'Overlapping booking',
            'status'          => 'pending',
        ]);

        RequestItem::create([
            'request_id'   => $pending->id,
            'resource_id'  => null,
            'item_name'    => 'Computer Laboratory 1',
            'quantity'     => 1,
            'request_date' => $date,
            'start_time'   => '09:30',
            'end_time'     => '10:30',
        ]);

        // 4. Program Head tries to approve via /coordinator/facility
        Livewire::actingAs($programHead)
            ->test('pages::coordinator.reservation-facility')  // ← string, not class
            ->call('accept', $pending->id);

        // 5. Expected: the request should still be pending
        $this->assertDatabaseHas('requests', [
            'id'     => $pending->id,
            'status' => 'pending',
        ]);
    }
}
