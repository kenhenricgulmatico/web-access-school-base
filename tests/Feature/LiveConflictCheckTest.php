<?php

namespace Tests\Feature;

use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LiveConflictCheckTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    public function test_program_head_create_form_shows_live_conflict_error(): void
    {
        $dept        = $this->createDepartment();
        $programHead = $this->createUser('program head', $dept);
        $student     = $this->createUser('faculty', $dept);

        // Existing approved booking
        $existing = ResourceRequest::create([
            'user_id'         => $student->id,
            'department_id'   => $dept->id,
            'request_type_id' => 1,
            'purpose'         => 'Existing',
            'status'          => 'approved',
        ]);

        RequestItem::create([
            'request_id'   => $existing->id,
            'resource_id'  => null,
            'item_name'    => 'Computer Laboratory 1',
            'quantity'     => 1,
            'request_date' => '2026-12-30',
            'start_time'   => '09:00',
            'end_time'     => '10:00',
        ]);

        // Program Head tries to set overlapping values on the create-request form
        Livewire::actingAs($programHead)
            ->test('pages::coordinator.request-to-admin.create-request')
            ->set('request_type_id', 1)
            ->set('facility_name', 'Computer Laboratory 1')
            ->set('request_date', '2026-12-30')
            ->set('start_time', '09:30')
            ->set('end_time', '10:30')
            ->assertHasErrors('facility_name');  // ← program head form catches it
    }

    public function test_student_create_form_shows_live_conflict_error(): void
    {
        $dept    = $this->createDepartment();
        $studentA = $this->createUser('faculty', $dept);
        $studentB = $this->createUser('faculty', $dept);

        // Existing approved booking
        $existing = ResourceRequest::create([
            'user_id'         => $studentA->id,
            'department_id'   => $dept->id,
            'request_type_id' => 1,
            'purpose'         => 'Existing',
            'status'          => 'approved',
        ]);

        RequestItem::create([
            'request_id'   => $existing->id,
            'resource_id'  => null,
            'item_name'    => 'Computer Laboratory 1',
            'quantity'     => 1,
            'request_date' => '2026-12-30',
            'start_time'   => '09:00',
            'end_time'     => '10:00',
        ]);

        // Student tries to reserve an overlapping slot
        Livewire::actingAs($studentB)
            ->test('pages::student-faculty.reservation.create-reservation')
            ->set('facility_name', 'Computer Laboratory 1')
            ->set('used_date', '2026-12-30')
            ->set('start_time', '09:30')
            ->set('end_time', '10:30')
            ->assertHasErrors('facility_name');  // ← student form MUST also catch it
    }
}
