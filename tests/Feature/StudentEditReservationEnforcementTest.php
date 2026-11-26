<?php

namespace Tests\Feature;

use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StudentEditReservationEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    public function test_student_cannot_edit_into_conflicting_time(): void
    {
        $dept     = $this->createDepartment();
        $studentA = $this->createUser('faculty', $dept);
        $studentB = $this->createUser('faculty', $dept);

        $date = now()->addDays(3)->toDateString();

        // Already-approved 9:00–10:00
        $approved = ResourceRequest::create([
            'user_id'         => $studentA->id,
            'department_id'   => $dept->id,
            'request_type_id' => 1,
            'purpose'         => 'Approved',
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

        // Student B's own pending booking 14:00–15:00 (non-conflicting)
        $pending = ResourceRequest::create([
            'user_id'         => $studentB->id,
            'department_id'   => $dept->id,
            'request_type_id' => 1,
            'purpose'         => 'Pending edit',
            'status'          => 'pending',
        ]);

        RequestItem::create([
            'request_id'   => $pending->id,
            'resource_id'  => null,
            'item_name'    => 'Computer Laboratory 1',
            'quantity'     => 1,
            'request_date' => $date,
            'start_time'   => '14:00',
            'end_time'     => '15:00',
        ]);

        // Student B edits it into the conflicting 9:30–10:30 slot
        Livewire::actingAs($studentB)
            ->test('pages::student-faculty.reservation.edit-reservation', ['id' => $pending->id])
            ->set('start_time', '09:30')
            ->set('end_time', '10:30')
            ->call('update');

        // The facility item should NOT have been changed to 09:30–10:30
        $this->assertDatabaseMissing('request_items', [
            'request_id' => $pending->id,
            'start_time' => '09:30',
            'end_time'   => '10:30',
        ]);
    }
}
