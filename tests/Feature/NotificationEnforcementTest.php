<?php

namespace Tests\Feature;

use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    /**
     * Program head approves a material request → requester is notified.
     */
    public function test_coordinator_material_approval_creates_notification_for_requester(): void
    {
        $dept        = $this->createDepartment();
        $programHead = $this->createUser('program head', $dept);
        $student     = $this->createUser('student', $dept);

        $material = $this->createMaterial('Bond Paper A4', 100);

        \DB::table('resource_all_locations')->insert([
            'resource_id'        => $material->id,
            'department_id'      => $dept->id,
            'allocated_quantity' => 100,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        $pending = ResourceRequest::create([
            'user_id'         => $student->id,
            'department_id'   => $dept->id,
            'request_type_id' => 2,
            'purpose'         => 'Notification test — material approval',
            'status'          => 'pending',
        ]);

        RequestItem::create([
            'request_id'   => $pending->id,
            'resource_id'  => $material->id,
            'item_name'    => $material->resource_name,
            'quantity'     => 5,
            'request_date' => now()->toDateString(),
        ]);

        Livewire::actingAs($programHead)
            ->test('pages::coordinator.request-material')
            ->call('accept', $pending->id);

        $this->assertDatabaseHas('notifications', [
            'user_id'    => $student->id,
            'request_id' => $pending->id,
        ]);
    }

    /**
     * Program head rejects a material request → requester is notified.
     */
    public function test_coordinator_material_rejection_creates_notification_for_requester(): void
    {
        $dept        = $this->createDepartment();
        $programHead = $this->createUser('program head', $dept);
        $student     = $this->createUser('student', $dept);

        $material = $this->createMaterial('Bond Paper A4', 100);

        \DB::table('resource_all_locations')->insert([
            'resource_id'        => $material->id,
            'department_id'      => $dept->id,
            'allocated_quantity' => 100,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        $pending = ResourceRequest::create([
            'user_id'         => $student->id,
            'department_id'   => $dept->id,
            'request_type_id' => 2,
            'purpose'         => 'Notification test — material rejection',
            'status'          => 'pending',
        ]);

        RequestItem::create([
            'request_id'   => $pending->id,
            'resource_id'  => $material->id,
            'item_name'    => $material->resource_name,
            'quantity'     => 5,
            'request_date' => now()->toDateString(),
        ]);

        Livewire::actingAs($programHead)
            ->test('pages::coordinator.request-material')
            ->call('openReject', $pending->id)
            ->set('rejectReason', 'Insufficient stock')
            ->call('confirmReject');

        $this->assertDatabaseHas('notifications', [
            'user_id'    => $student->id,
            'request_id' => $pending->id,
        ]);
    }

    /**
     * Program head approves a facility reservation → requester is notified.
     */
    public function test_coordinator_facility_approval_creates_notification_for_requester(): void
    {
        $dept        = $this->createDepartment();
        $programHead = $this->createUser('program head', $dept);
        $student     = $this->createUser('student', $dept);

        $date = now()->addDays(3)->toDateString();

        $pending = ResourceRequest::create([
            'user_id'         => $student->id,
            'department_id'   => $dept->id,
            'request_type_id' => 1,
            'purpose'         => 'Notification test — facility approval',
            'status'          => 'pending',
        ]);

        RequestItem::create([
            'request_id'   => $pending->id,
            'resource_id'  => null,
            'item_name'    => 'Computer Laboratory 1',
            'quantity'     => 1,
            'request_date' => $date,
            'start_time'   => '09:00',
            'end_time'     => '10:00',
        ]);

        Livewire::actingAs($programHead)
            ->test('pages::coordinator.reservation-facility')
            ->call('accept', $pending->id);

        $this->assertDatabaseHas('notifications', [
            'user_id'    => $student->id,
            'request_id' => $pending->id,
        ]);
    }

    /**
     * Admin approves a program head's request → requester is notified.
     */
    public function test_admin_manage_coordinator_approval_creates_notification_for_requester(): void
    {
        $dept        = $this->createDepartment();
        $admin       = $this->createUser('admin', $dept);
        $programHead = $this->createUser('program head', $dept);

        $date = now()->addDays(3)->toDateString();

        $pending = ResourceRequest::create([
            'user_id'         => $programHead->id,
            'department_id'   => $dept->id,
            'request_type_id' => 1,
            'purpose'         => 'Notification test — admin approval',
            'status'          => 'pending',
        ]);

        RequestItem::create([
            'request_id'   => $pending->id,
            'resource_id'  => null,
            'item_name'    => 'Computer Laboratory 1',
            'quantity'     => 1,
            'request_date' => $date,
            'start_time'   => '09:00',
            'end_time'     => '10:00',
        ]);

        Livewire::actingAs($admin)
            ->test('pages::admin.manage-coordinator')
            ->call('approve', $pending->id);

        $this->assertDatabaseHas('notifications', [
            'user_id'    => $programHead->id,
            'request_id' => $pending->id,
        ]);
    }
}
