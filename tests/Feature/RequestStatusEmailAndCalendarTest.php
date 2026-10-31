<?php

namespace Tests\Feature;

use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use App\Notifications\RequestStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class RequestStatusEmailAndCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    protected function materialRequest($dept, $student): ResourceRequest
    {
        $material = $this->createMaterial('Bond Paper A4', 100);

        \DB::table('resource_all_locations')->insert([
            'resource_id' => $material->id, 'department_id' => $dept->id,
            'allocated_quantity' => 100, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $req = ResourceRequest::create([
            'user_id' => $student->id, 'department_id' => $dept->id,
            'request_type_id' => 2, 'purpose' => 'Email test', 'status' => 'pending',
        ]);

        RequestItem::create([
            'request_id' => $req->id, 'resource_id' => $material->id,
            'item_name' => $material->resource_name, 'quantity' => 5,
            'request_date' => now()->toDateString(),
        ]);

        return $req;
    }

    protected function facilityRequest($dept, $student, string $status = 'pending'): ResourceRequest
    {
        $req = ResourceRequest::create([
            'user_id' => $student->id, 'department_id' => $dept->id,
            'request_type_id' => 1, 'purpose' => 'Facility email test', 'status' => $status,
        ]);

        RequestItem::create([
            'request_id' => $req->id, 'resource_id' => null,
            'item_name' => 'Computer Lab 1', 'quantity' => 1,
            'request_date' => now()->toDateString(),
            'start_time' => '08:00:00', 'end_time' => '10:00:00',
        ]);

        return $req;
    }

    public function test_material_approval_emails_requester(): void
    {
        Notification::fake();
        $dept = $this->createDepartment();
        $ph = $this->createUser('program head', $dept);
        $student = $this->createUser('student', $dept);
        $req = $this->materialRequest($dept, $student);

        Livewire::actingAs($ph)->test('pages::coordinator.request-material')->call('accept', $req->id);

        Notification::assertSentTo($student, RequestStatusNotification::class,
            fn ($n) => $n->toMail($student)->subject === 'Your Material Request has been approved');
    }

    public function test_material_rejection_emails_requester_with_reason(): void
    {
        Notification::fake();
        $dept = $this->createDepartment();
        $ph = $this->createUser('program head', $dept);
        $student = $this->createUser('student', $dept);
        $req = $this->materialRequest($dept, $student);

        Livewire::actingAs($ph)->test('pages::coordinator.request-material')
            ->call('openReject', $req->id)
            ->set('rejectReason', 'Out of budget')
            ->call('confirmReject');

        Notification::assertSentTo($student, RequestStatusNotification::class, function ($n) use ($student) {
            $mail = $n->toMail($student);
            return $mail->subject === 'Your Material Request has been rejected'
                && str_contains($mail->viewData['html'], 'Out of budget');
        });
    }

    public function test_facility_approval_and_rejection_email_requester(): void
    {
        Notification::fake();
        $dept = $this->createDepartment();
        $ph = $this->createUser('program head', $dept);
        $student = $this->createUser('student', $dept);

        $a = $this->facilityRequest($dept, $student);
        Livewire::actingAs($ph)->test('pages::coordinator.reservation-facility')->call('accept', $a->id);

        $b = $this->facilityRequest($dept, $student);
        $b->items()->update(['item_name' => 'Gym']);
        Livewire::actingAs($ph)->test('pages::coordinator.reservation-facility')
            ->call('openReject', $b->id)->call('confirmReject');

        Notification::assertSentToTimes($student, RequestStatusNotification::class, 2);
    }

    public function test_failed_approval_does_not_email(): void
    {
        Notification::fake();
        $dept = $this->createDepartment();
        $ph = $this->createUser('program head', $dept);
        $student = $this->createUser('student', $dept);
        $req = $this->materialRequest($dept, $student);
        \DB::table('resource_all_locations')->update(['allocated_quantity' => 0]);

        Livewire::actingAs($ph)->test('pages::coordinator.request-material')->call('accept', $req->id);

        Notification::assertNotSentTo($student, RequestStatusNotification::class);
    }

    public function test_portal_calendar_shows_own_requests_and_booked_facilities(): void
    {
        $dept = $this->createDepartment();
        $student = $this->createUser('student', $dept);
        $other = $this->createUser('faculty', $dept);

        $this->facilityRequest($dept, $student, 'pending');
        $this->facilityRequest($dept, $other, 'approved')->items()->update(['item_name' => 'AVR Hall']);

        $this->actingAs($student)->get(route('portal.calendar'))->assertOk();

        Livewire::actingAs($student)->test('pages::student-faculty.calendar')
            ->assertSee('Computer Lab 1')          // my own pending request
            ->assertSee('pending')
            ->set('activeTab', 'availability')
            ->assertSee('AVR Hall')               // someone else's approved booking
            ->assertDontSee($other->name);        // but not who booked it
    }
}
