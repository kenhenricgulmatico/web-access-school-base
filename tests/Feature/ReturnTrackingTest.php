<?php

namespace Tests\Feature;

use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use App\Models\RequestType;
use App\Notifications\ReturnReminderNotification;
use App\Support\ReturnTracking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ReturnTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    /** Creates a pending material request for $qty of a (non-)returnable item, with 10 allocated. */
    protected function borrow($dept, $student, bool $returnable, int $qty = 2): ResourceRequest
    {
        $res = $this->createMaterial($returnable ? 'Chair' : 'Bond Paper A4', 50);
        $res->update(['is_returnable' => $returnable]);

        DB::table('resource_all_locations')->insert([
            'resource_id' => $res->id, 'department_id' => $dept->id,
            'allocated_quantity' => 10, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $req = ResourceRequest::create([
            'user_id' => $student->id, 'department_id' => $dept->id,
            'request_type_id' => RequestType::MATERIAL, 'purpose' => 'Borrowing test', 'status' => 'pending',
        ]);

        RequestItem::create([
            'request_id' => $req->id, 'resource_id' => $res->id, 'item_name' => $res->resource_name,
            'quantity' => $qty, 'request_date' => now()->toDateString(),
        ]);

        return $req;
    }

    protected function allocated(): int
    {
        return (int) DB::table('resource_all_locations')->value('allocated_quantity');
    }

    public function test_approval_sets_return_date_only_for_returnable_items(): void
    {
        $dept = $this->createDepartment();
        $ph = $this->createUser('program head', $dept);
        $student = $this->createUser('student', $dept);

        $chair = $this->borrow($dept, $student, true);
        Livewire::actingAs($ph)->test('pages::coordinator.request-material')->call('accept', $chair->id);
        $this->assertSame(
            now()->addDays(ReturnTracking::RETURN_DAYS)->toDateString(),
            $chair->items()->first()->due_back_at->toDateString()
        );

        DB::table('resource_all_locations')->delete();
        $paper = $this->borrow($dept, $student, false);
        Livewire::actingAs($ph)->test('pages::coordinator.request-material')->call('accept', $paper->id);
        $this->assertNull($paper->items()->first()->due_back_at);
    }

    public function test_facility_reservation_items_are_due_on_reservation_day(): void
    {
        $dept = $this->createDepartment();
        $student = $this->createUser('student', $dept);
        $res = $this->createMaterial('Projector', 5);
        $res->update(['is_returnable' => true]);

        $req = ResourceRequest::create([
            'user_id' => $student->id, 'department_id' => $dept->id,
            'request_type_id' => RequestType::FACILITY, 'purpose' => 'Seminar', 'status' => 'approved',
        ]);
        $day = now()->addDays(5)->toDateString();
        RequestItem::create(['request_id' => $req->id, 'resource_id' => null, 'item_name' => 'AVR', 'quantity' => 1,
            'request_date' => $day, 'start_time' => '08:00', 'end_time' => '10:00']);
        RequestItem::create(['request_id' => $req->id, 'resource_id' => $res->id, 'item_name' => 'Projector', 'quantity' => 1,
            'request_date' => $day]);

        ReturnTracking::assignDueDates($req);

        $this->assertSame($day, RequestItem::where('item_name', 'Projector')->first()->due_back_at->toDateString());
    }

    public function test_returned_in_good_condition_goes_back_to_stock(): void
    {
        $dept = $this->createDepartment();
        $ph = $this->createUser('program head', $dept);
        $student = $this->createUser('student', $dept);
        $req = $this->borrow($dept, $student, true, 3);

        Livewire::actingAs($ph)->test('pages::coordinator.request-material')->call('accept', $req->id);
        $this->assertSame(7, $this->allocated());

        $item = $req->items()->first();
        Livewire::actingAs($ph)->test('pages::coordinator.borrowed-items')
            ->call('openReturn', $item->id)
            ->set('returnCondition', 'good')
            ->call('confirmReturn')
            ->assertHasNoErrors();

        $item->refresh();
        $this->assertNotNull($item->returned_at);
        $this->assertSame('good', $item->return_condition);
        $this->assertSame($ph->id, $item->received_by);
        $this->assertSame(10, $this->allocated());
    }

    public function test_lost_item_is_not_added_back_and_cannot_be_returned_twice(): void
    {
        $dept = $this->createDepartment();
        $ph = $this->createUser('program head', $dept);
        $student = $this->createUser('student', $dept);
        $req = $this->borrow($dept, $student, true, 3);
        Livewire::actingAs($ph)->test('pages::coordinator.request-material')->call('accept', $req->id);
        $item = $req->items()->first();

        ReturnTracking::markReturned($item, 'lost', 'Missing', $ph);
        $this->assertSame(7, $this->allocated());

        $this->expectException(\RuntimeException::class);
        ReturnTracking::markReturned($item, 'good', null, $ph);
    }

    public function test_reminder_email_sent_once_per_day_for_due_and_overdue_items(): void
    {
        Notification::fake();
        $dept = $this->createDepartment();
        $student = $this->createUser('student', $dept);
        $other = $this->createUser('faculty', $dept);

        $overdue = $this->borrow($dept, $student, true);
        $overdue->update(['status' => 'approved']);
        $overdue->items()->update(['due_back_at' => now()->subDays(2)->toDateString()]);

        $later = $this->borrow($dept, $other, true);
        $later->update(['status' => 'approved']);
        $later->items()->update(['due_back_at' => now()->addDays(5)->toDateString()]);

        $this->artisan('returns:send-reminders')->assertSuccessful();
        Notification::assertSentToTimes($student, ReturnReminderNotification::class, 1);
        Notification::assertNotSentTo($other, ReturnReminderNotification::class);

        // Same day again → no duplicate
        $this->artisan('returns:send-reminders')->assertSuccessful();
        Notification::assertSentToTimes($student, ReturnReminderNotification::class, 1);
    }

    public function test_returned_items_get_no_reminder(): void
    {
        Notification::fake();
        $dept = $this->createDepartment();
        $student = $this->createUser('student', $dept);
        $req = $this->borrow($dept, $student, true);
        $req->update(['status' => 'approved']);
        $req->items()->update(['due_back_at' => now()->toDateString(), 'returned_at' => now()]);

        $this->artisan('returns:send-reminders');
        Notification::assertNothingSent();
    }

    public function test_borrowed_items_page_only_shows_own_department_and_students_cannot_open_it(): void
    {
        $dept = $this->createDepartment();
        $otherDept = $this->createDepartment('Business');
        $ph = $this->createUser('program head', $dept);
        $student = $this->createUser('student', $dept);
        $outsider = $this->createUser('student', $otherDept);

        $mine = $this->borrow($dept, $student, true);
        $mine->update(['status' => 'approved']);
        $mine->items()->update(['due_back_at' => now()->toDateString(), 'item_name' => 'Chair OURS']);

        $theirs = $this->borrow($otherDept, $outsider, true);
        $theirs->update(['status' => 'approved']);
        $theirs->items()->update(['due_back_at' => now()->toDateString(), 'item_name' => 'Chair THEIRS']);

        Livewire::actingAs($ph)->test('pages::coordinator.borrowed-items')
            ->assertSee('Chair OURS')
            ->assertDontSee('Chair THEIRS');

        $this->actingAs($student)->get(route('coordinator.borrowed-items'))->assertForbidden();
    }

    public function test_admin_can_mark_material_returnable(): void
    {
        $dept = $this->createDepartment();
        $admin = $this->createUser('admin', $dept);
        $res = $this->createMaterial('Projector', 3);

        Livewire::actingAs($admin)->test('pages::admin.inventory.stock-material')
            ->call('openEditModal', $res->id)
            ->set('edit_is_returnable', true)
            ->call('updateMaterial');

        $this->assertTrue($res->fresh()->is_returnable);
    }
}
