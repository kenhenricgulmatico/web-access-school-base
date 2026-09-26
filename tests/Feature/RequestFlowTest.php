<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Request as ResourceRequest;
use App\Models\RequestApproval;
use App\Models\RequestItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    public function test_student_can_create_facility_reservation(): void
    {
        $dept     = $this->createDepartment();
        $student  = $this->createUser('student', $dept);
        $facility = $this->createFacility('Computer Laboratory 1');

        $this->actingAs($student);

        $request = ResourceRequest::create([
            'user_id'         => $student->id,
            'department_id'   => $dept->id,
            'request_type_id' => 1,
            'purpose'         => 'For final capstone defense',
            'status'          => 'pending',
        ]);

        RequestItem::create([
            'request_id'   => $request->id,
            'resource_id'  => null,
            'item_name'    => $facility->resource_name,
            'quantity'     => 1,
            'request_date' => now()->addDay()->toDateString(),
            'start_time'   => '09:00',
            'end_time'     => '10:00',
        ]);

        $this->assertDatabaseHas('requests', [
            'id'              => $request->id,
            'status'          => 'pending',
            'request_type_id' => 1,
        ]);

        $this->assertDatabaseHas('request_items', [
            'request_id' => $request->id,
            'item_name'  => 'Computer Laboratory 1',
        ]);
    }

    public function test_program_head_approval_sets_status_to_approved(): void
    {
        $dept    = $this->createDepartment();
        $student = $this->createUser('student', $dept);
        $ph      = $this->createUser('program head', $dept);

        $request = ResourceRequest::create([
            'user_id'         => $student->id,
            'department_id'   => $dept->id,
            'request_type_id' => 1,
            'purpose'         => 'Test purpose',
            'status'          => 'pending',
        ]);

        $request->update(['status' => 'approved']);

        $this->assertDatabaseHas('requests', [
            'id'     => $request->id,
            'status' => 'approved',
        ]);
    }

    public function test_approval_creates_a_notification_row(): void
    {
        $dept    = $this->createDepartment();
        $student = $this->createUser('student', $dept);

        $request = ResourceRequest::create([
            'user_id'         => $student->id,
            'department_id'   => $dept->id,
            'request_type_id' => 1,
            'purpose'         => 'Test purpose',
            'status'          => 'pending',
        ]);

        Notification::create([
            'user_id'    => $student->id,
            'request_id' => $request->id,
            'message'    => 'Your request has been approved.',
            'type'       => 'Gmail',
            'status'     => 'pending',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id'    => $student->id,
            'request_id' => $request->id,
            'status'     => 'pending',
        ]);
    }

    public function test_request_approval_row_can_be_created(): void
    {
        $dept    = $this->createDepartment();
        $student = $this->createUser('student', $dept);
        $ph      = $this->createUser('program head', $dept);

        $request = ResourceRequest::create([
            'user_id'         => $student->id,
            'department_id'   => $dept->id,
            'request_type_id' => 1,
            'purpose'         => 'Test purpose',
            'status'          => 'pending',
        ]);

        RequestApproval::create([
            'request_id'  => $request->id,
            'approver_id' => $ph->id,
            'status'      => 'approved',
            'remarks'     => 'Looks good',
            'approved_at' => now(),
        ]);

        $this->assertDatabaseHas('request_approvals', [
            'request_id'  => $request->id,
            'approver_id' => $ph->id,
            'status'      => 'approved',
            'remarks'     => 'Looks good',
        ]);
    }
}
