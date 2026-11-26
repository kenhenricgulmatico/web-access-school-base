<?php

namespace Tests\Feature;

use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class CoordinatorFacilityApprovalDecrementsMaterialsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    public function test_facility_approval_decrements_attached_materials(): void
    {
        $dept        = $this->createDepartment();
        $programHead = $this->createUser('program head', $dept);
        $student     = $this->createUser('faculty', $dept);

        $paper  = $this->createMaterial('Bond Paper A4', 100);
        $marker = $this->createMaterial('Marker', 50);

        DB::table('resource_all_locations')->insert([
            [
                'resource_id'        => $paper->id,
                'department_id'      => $dept->id,
                'allocated_quantity' => 70,  // matches your screenshot
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'resource_id'        => $marker->id,
                'department_id'      => $dept->id,
                'allocated_quantity' => 50,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
        ]);

        $date = now()->addDays(3)->toDateString();

        // Facility reservation with materials attached
        $request = ResourceRequest::create([
            'user_id'         => $student->id,
            'department_id'   => $dept->id,
            'request_type_id' => 1, // Facility
            'purpose'         => 'Room + materials',
            'status'          => 'pending',
        ]);

        // The facility line itself
        RequestItem::create([
            'request_id'   => $request->id,
            'resource_id'  => null,
            'item_name'    => 'room 101',
            'quantity'     => 1,
            'request_date' => $date,
            'start_time'   => '09:00',
            'end_time'     => '10:00',
        ]);

        // Attached materials
        RequestItem::create([
            'request_id'   => $request->id,
            'resource_id'  => $paper->id,
            'item_name'    => $paper->resource_name,
            'quantity'     => 20,
            'request_date' => $date,
            'start_time'   => '09:00',
            'end_time'     => '10:00',
        ]);

        RequestItem::create([
            'request_id'   => $request->id,
            'resource_id'  => $marker->id,
            'item_name'    => $marker->resource_name,
            'quantity'     => 10,
            'request_date' => $date,
            'start_time'   => '09:00',
            'end_time'     => '10:00',
        ]);

        Livewire::actingAs($programHead)
            ->test('pages::coordinator.reservation-facility')
            ->call('accept', $request->id);

        // Paper: 70 - 20 = 50
        $this->assertDatabaseHas('resource_all_locations', [
            'resource_id'        => $paper->id,
            'department_id'      => $dept->id,
            'allocated_quantity' => 50,
        ]);

        // Marker: 50 - 10 = 40
        $this->assertDatabaseHas('resource_all_locations', [
            'resource_id'        => $marker->id,
            'department_id'      => $dept->id,
            'allocated_quantity' => 40,
        ]);

        // Request approved
        $this->assertDatabaseHas('requests', [
            'id'     => $request->id,
            'status' => 'approved',
        ]);
    }

    public function test_facility_approval_blocked_when_material_stock_insufficient(): void
    {
        $dept        = $this->createDepartment();
        $programHead = $this->createUser('program head', $dept);
        $student     = $this->createUser('faculty', $dept);

        $paper = $this->createMaterial('Bond Paper A4', 100);

        // Only 5 allocated, but the request wants 20
        DB::table('resource_all_locations')->insert([
            'resource_id'        => $paper->id,
            'department_id'      => $dept->id,
            'allocated_quantity' => 5,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        $date = now()->addDays(3)->toDateString();

        $request = ResourceRequest::create([
            'user_id'         => $student->id,
            'department_id'   => $dept->id,
            'request_type_id' => 1,
            'purpose'         => 'Too many materials',
            'status'          => 'pending',
        ]);

        RequestItem::create([
            'request_id'   => $request->id,
            'resource_id'  => null,
            'item_name'    => 'room 101',
            'quantity'     => 1,
            'request_date' => $date,
            'start_time'   => '09:00',
            'end_time'     => '10:00',
        ]);

        RequestItem::create([
            'request_id'   => $request->id,
            'resource_id'  => $paper->id,
            'item_name'    => $paper->resource_name,
            'quantity'     => 20,
            'request_date' => $date,
            'start_time'   => '09:00',
            'end_time'     => '10:00',
        ]);

        Livewire::actingAs($programHead)
            ->test('pages::coordinator.reservation-facility')
            ->call('accept', $request->id);

        // Allocation unchanged
        $this->assertDatabaseHas('resource_all_locations', [
            'resource_id'        => $paper->id,
            'department_id'      => $dept->id,
            'allocated_quantity' => 5,
        ]);

        // Request still pending
        $this->assertDatabaseHas('requests', [
            'id'     => $request->id,
            'status' => 'pending',
        ]);
    }
}
