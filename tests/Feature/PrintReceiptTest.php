<?php

namespace Tests\Feature;

use App\Models\Request as ResourceRequest;
use App\Models\RequestApproval;
use App\Models\RequestItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PrintReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    public function test_approved_request_has_linked_approval_with_approved_at(): void
    {
        $dept    = $this->createDepartment();
        $student = $this->createUser('student', $dept);
        $ph      = $this->createUser('program head', $dept);

        $request = ResourceRequest::create([
            'user_id'         => $student->id,
            'department_id'   => $dept->id,
            'request_type_id' => 1,
            'purpose'         => 'For defense',
            'status'          => 'approved',
        ]);

        RequestItem::create([
            'request_id'   => $request->id,
            'resource_id'  => null,
            'item_name'    => 'Computer Laboratory 1',
            'quantity'     => 1,
            'request_date' => now()->toDateString(),
            'start_time'   => '09:00',
            'end_time'     => '10:00',
        ]);

        RequestApproval::create([
            'request_id'  => $request->id,
            'approver_id' => $ph->id,
            'status'      => 'approved',
            'remarks'     => 'Approved by program head',
            'approved_at' => now(),
        ]);

        $approval = DB::table('request_approvals')
            ->join('users', 'users.id', '=', 'request_approvals.approver_id')
            ->where('request_approvals.request_id', $request->id)
            ->where('request_approvals.status', 'approved')
            ->select('users.name as approver_name', 'request_approvals.approved_at')
            ->first();

        $this->assertNotNull($approval);
        $this->assertEquals('Program head User', $approval->approver_name);
        $this->assertNotNull($approval->approved_at);
    }
}
