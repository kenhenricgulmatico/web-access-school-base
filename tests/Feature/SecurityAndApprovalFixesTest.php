<?php

namespace Tests\Feature;

use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use App\Notifications\RequestStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityAndApprovalFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    protected function facilityRequest($dept, $user, string $status = 'pending', string $room = 'Computer Lab 1'): ResourceRequest
    {
        $req = ResourceRequest::create([
            'user_id' => $user->id, 'department_id' => $dept->id,
            'request_type_id' => 1, 'purpose' => 'Facility test', 'status' => $status,
        ]);
        RequestItem::create([
            'request_id' => $req->id, 'resource_id' => null, 'item_name' => $room, 'quantity' => 1,
            'request_date' => now()->addDay()->toDateString(), 'start_time' => '08:00:00', 'end_time' => '10:00:00',
        ]);
        return $req;
    }

    protected function materialRequest($dept, $user, int $allocated, int $qty): ResourceRequest
    {
        $material = $this->createMaterial('Bond Paper A4', 100);
        DB::table('resource_all_locations')->insert([
            'resource_id' => $material->id, 'department_id' => $dept->id,
            'allocated_quantity' => $allocated, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $req = ResourceRequest::create([
            'user_id' => $user->id, 'department_id' => $dept->id,
            'request_type_id' => 2, 'purpose' => 'Material test', 'status' => 'pending',
        ]);
        RequestItem::create([
            'request_id' => $req->id, 'resource_id' => $material->id, 'item_name' => 'Bond Paper A4',
            'quantity' => $qty, 'request_date' => now()->toDateString(),
        ]);
        return $req;
    }

    // ---------- #1 View request page ----------

    public function test_single_view_approve_blocks_double_booking(): void
    {
        $dept = $this->createDepartment();
        $ph = $this->createUser('program head', $dept);
        $a = $this->createUser('student', $dept);
        $b = $this->createUser('student', $dept);

        $this->facilityRequest($dept, $a, 'approved');
        $clash = $this->facilityRequest($dept, $b);

        Livewire::actingAs($ph)
            ->test('pages::coordinator.view-request-reserve', ['id' => $clash->id])
            ->call('accept');

        $this->assertSame('pending', $clash->fresh()->status);
    }

    public function test_single_view_approve_deducts_stock_and_records_approval(): void
    {
        Notification::fake();
        $dept = $this->createDepartment();
        $ph = $this->createUser('program head', $dept);
        $student = $this->createUser('student', $dept);
        $req = $this->materialRequest($dept, $student, 10, 4);

        Livewire::actingAs($ph)
            ->test('pages::coordinator.view-request-reserve', ['id' => $req->id])
            ->call('accept');

        $this->assertSame('approved', $req->fresh()->status);
        $this->assertSame(6, (int) DB::table('resource_all_locations')->value('allocated_quantity'));
        $this->assertDatabaseHas('request_approvals', ['request_id' => $req->id, 'approver_id' => $ph->id, 'status' => 'approved']);
        Notification::assertSentTo($student, RequestStatusNotification::class);
    }

    public function test_single_view_approve_refuses_when_stock_is_short(): void
    {
        $dept = $this->createDepartment();
        $ph = $this->createUser('program head', $dept);
        $student = $this->createUser('student', $dept);
        $req = $this->materialRequest($dept, $student, 2, 5);

        Livewire::actingAs($ph)
            ->test('pages::coordinator.view-request-reserve', ['id' => $req->id])
            ->call('accept');

        $this->assertSame('pending', $req->fresh()->status);
        $this->assertSame(2, (int) DB::table('resource_all_locations')->value('allocated_quantity'));
    }

    public function test_single_view_cannot_reapprove_processed_request(): void
    {
        $dept = $this->createDepartment();
        $ph = $this->createUser('program head', $dept);
        $student = $this->createUser('student', $dept);
        $req = $this->facilityRequest($dept, $student, 'rejected');

        Livewire::actingAs($ph)
            ->test('pages::coordinator.view-request-reserve', ['id' => $req->id])
            ->call('accept');

        $this->assertSame('rejected', $req->fresh()->status);
    }

    public function test_single_view_reject_saves_reason(): void
    {
        Notification::fake();
        $dept = $this->createDepartment();
        $ph = $this->createUser('program head', $dept);
        $student = $this->createUser('student', $dept);
        $req = $this->facilityRequest($dept, $student);

        Livewire::actingAs($ph)
            ->test('pages::coordinator.view-request-reserve', ['id' => $req->id])
            ->set('rejectReason', 'Room under repair')
            ->call('reject');

        $this->assertSame('rejected', $req->fresh()->status);
        $this->assertDatabaseHas('request_approvals', ['request_id' => $req->id, 'remarks' => 'Room under repair']);
    }

    // ---------- #2 Printable receipt ----------

    public function test_guest_is_redirected_from_printable(): void
    {
        $this->get(route('printable.receipt', ['request_id' => 1]))->assertRedirect(route('login'));
    }

    public function test_other_student_cannot_print_someone_elses_receipt(): void
    {
        $dept = $this->createDepartment();
        $owner = $this->createUser('student', $dept);
        $other = $this->createUser('student', $dept);
        $req = $this->facilityRequest($dept, $owner, 'approved');

        $this->actingAs($other)->get(route('printable.receipt', ['request_id' => $req->id]))->assertForbidden();
    }

    public function test_owner_and_department_head_can_print(): void
    {
        $dept = $this->createDepartment();
        $owner = $this->createUser('student', $dept);
        $ph = $this->createUser('program head', $dept);
        $req = $this->facilityRequest($dept, $owner, 'approved');

        $this->actingAs($owner)->get(route('printable.receipt', ['request_id' => $req->id]))->assertOk();
        $this->actingAs($ph)->get(route('printable.receipt', ['request_id' => $req->id]))->assertOk();
    }

    public function test_program_head_of_other_department_cannot_print(): void
    {
        $dept = $this->createDepartment();
        $otherDept = $this->createDepartment('Business');
        $owner = $this->createUser('student', $dept);
        $otherHead = $this->createUser('program head', $otherDept);
        $req = $this->facilityRequest($dept, $owner, 'approved');

        $this->actingAs($otherHead)->get(route('printable.receipt', ['request_id' => $req->id]))->assertForbidden();
    }

    // ---------- #4 Admin calendar ----------

    public function test_admin_calendar_marks_days_with_one_query(): void
    {
        $dept = $this->createDepartment();
        $admin = $this->createUser('admin', $dept);
        $student = $this->createUser('student', $dept);
        $req = $this->facilityRequest($dept, $student, 'approved');
        $date = $req->items()->first()->request_date;
        $date = \Carbon\Carbon::parse($date);

        $component = Livewire::actingAs($admin)->test('pages::admin.schedule.calendar.calendar-schedule')
            ->set('year', $date->year)->set('month', $date->month);

        DB::enableQueryLog();
        $days = $component->instance()->daysWithEvents();
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertTrue($days[$date->format('Y-m-d')]['reservation']);
        $this->assertFalse($days[$date->format('Y-m-d')]['material']);
        $this->assertLessThanOrEqual(2, $queries);
    }
}
