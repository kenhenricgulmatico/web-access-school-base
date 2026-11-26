<?php

namespace Tests\Feature;

use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class CoordinatorApprovalDecrementsAllocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    /**
     * When the program head approves a material request, the department's
     * allocated_quantity in `resource_all_locations` should be decremented
     * by the requested amount.
     */
    public function test_program_head_approval_decrements_department_allocation(): void
    {
        // 1. Setup
        $dept        = $this->createDepartment();
        $programHead = $this->createUser('program head', $dept);
        $student     = $this->createUser('faculty', $dept);

        $material = $this->createMaterial('Bond Paper A4', 100);

        // 2. Give the department 50 units of this material
        DB::table('resource_all_locations')->insert([
            'resource_id'        => $material->id,
            'department_id'      => $dept->id,
            'allocated_quantity' => 50,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        // 3. Student submits a request for 10 units
        $request = ResourceRequest::create([
            'user_id'         => $student->id,
            'department_id'   => $dept->id,
            'request_type_id' => 2, // Material Request
            'purpose'         => 'For printing modules',
            'status'          => 'pending',
        ]);

        RequestItem::create([
            'request_id'   => $request->id,
            'resource_id'  => $material->id,
            'item_name'    => $material->resource_name,
            'quantity'     => 10,
            'request_date' => now()->toDateString(),
        ]);

        // 4. Program head approves via /coordinator/material
        Livewire::actingAs($programHead)
            ->test('pages::coordinator.request-material')
            ->call('accept', $request->id);

        // 5. Allocation should be 50 - 10 = 40
        $this->assertDatabaseHas('resource_all_locations', [
            'resource_id'        => $material->id,
            'department_id'      => $dept->id,
            'allocated_quantity' => 40,
        ]);

        // 6. And the request should be marked approved
        $this->assertDatabaseHas('requests', [
            'id'     => $request->id,
            'status' => 'approved',
        ]);
    }

    /**
     * If the request has multiple material line items, ALL of them should
     * be decremented on approval.
     */
    public function test_multi_item_material_request_decrements_all_allocations(): void
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
                'allocated_quantity' => 100,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'resource_id'        => $marker->id,
                'department_id'      => $dept->id,
                'allocated_quantity' => 30,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
        ]);

        $request = ResourceRequest::create([
            'user_id'         => $student->id,
            'department_id'   => $dept->id,
            'request_type_id' => 2,
            'purpose'         => 'For classroom',
            'status'          => 'pending',
        ]);

        RequestItem::create([
            'request_id'   => $request->id,
            'resource_id'  => $paper->id,
            'item_name'    => $paper->resource_name,
            'quantity'     => 20,
            'request_date' => now()->toDateString(),
        ]);

        RequestItem::create([
            'request_id'   => $request->id,
            'resource_id'  => $marker->id,
            'item_name'    => $marker->resource_name,
            'quantity'     => 5,
            'request_date' => now()->toDateString(),
        ]);

        Livewire::actingAs($programHead)
            ->test('pages::coordinator.request-material')
            ->call('accept', $request->id);

        // Paper: 100 - 20 = 80
        $this->assertDatabaseHas('resource_all_locations', [
            'resource_id'        => $paper->id,
            'department_id'      => $dept->id,
            'allocated_quantity' => 80,
        ]);

        // Marker: 30 - 5 = 25
        $this->assertDatabaseHas('resource_all_locations', [
            'resource_id'        => $marker->id,
            'department_id'      => $dept->id,
            'allocated_quantity' => 25,
        ]);
    }

    /**
     * If the department doesn't have enough allocated stock for the
     * request, the approval should be blocked and the allocation left
     * untouched.
     */
    public function test_approval_blocked_when_allocation_insufficient(): void
    {
        $dept        = $this->createDepartment();
        $programHead = $this->createUser('program head', $dept);
        $student     = $this->createUser('faculty', $dept);

        $material = $this->createMaterial('Bond Paper A4', 100);

        // Only 5 units allocated
        DB::table('resource_all_locations')->insert([
            'resource_id'        => $material->id,
            'department_id'      => $dept->id,
            'allocated_quantity' => 5,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        // Student asks for 10
        $request = ResourceRequest::create([
            'user_id'         => $student->id,
            'department_id'   => $dept->id,
            'request_type_id' => 2,
            'purpose'         => 'Too many',
            'status'          => 'pending',
        ]);

        RequestItem::create([
            'request_id'   => $request->id,
            'resource_id'  => $material->id,
            'item_name'    => $material->resource_name,
            'quantity'     => 10,
            'request_date' => now()->toDateString(),
        ]);

        Livewire::actingAs($programHead)
            ->test('pages::coordinator.request-material')
            ->call('accept', $request->id);

        // Allocation should still be 5 — NOT decremented
        $this->assertDatabaseHas('resource_all_locations', [
            'resource_id'        => $material->id,
            'department_id'      => $dept->id,
            'allocated_quantity' => 5,
        ]);

        // Request should still be pending
        $this->assertDatabaseHas('requests', [
            'id'     => $request->id,
            'status' => 'pending',
        ]);
    }

    /**
     * If the request has multiple items and one of them can't be fulfilled,
     * NONE of them should be decremented (transaction rollback).
     */
    public function test_partial_stock_failure_rolls_back_all_decrements(): void
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
                'allocated_quantity' => 100,   // plenty
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'resource_id'        => $marker->id,
                'department_id'      => $dept->id,
                'allocated_quantity' => 3,     // not enough for the request below
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
        ]);

        $request = ResourceRequest::create([
            'user_id'         => $student->id,
            'department_id'   => $dept->id,
            'request_type_id' => 2,
            'purpose'         => 'Mixed',
            'status'          => 'pending',
        ]);

        // Paper: 20 requested — can be fulfilled
        RequestItem::create([
            'request_id'   => $request->id,
            'resource_id'  => $paper->id,
            'item_name'    => $paper->resource_name,
            'quantity'     => 20,
            'request_date' => now()->toDateString(),
        ]);

        // Marker: 10 requested — but only 3 available
        RequestItem::create([
            'request_id'   => $request->id,
            'resource_id'  => $marker->id,
            'item_name'    => $marker->resource_name,
            'quantity'     => 10,
            'request_date' => now()->toDateString(),
        ]);

        Livewire::actingAs($programHead)
            ->test('pages::coordinator.request-material')
            ->call('accept', $request->id);

        // Paper allocation should be UNCHANGED — transaction rolled back
        $this->assertDatabaseHas('resource_all_locations', [
            'resource_id'        => $paper->id,
            'department_id'      => $dept->id,
            'allocated_quantity' => 100,
        ]);

        // Marker allocation should be UNCHANGED
        $this->assertDatabaseHas('resource_all_locations', [
            'resource_id'        => $marker->id,
            'department_id'      => $dept->id,
            'allocated_quantity' => 3,
        ]);

        // Request should still be pending
        $this->assertDatabaseHas('requests', [
            'id'     => $request->id,
            'status' => 'pending',
        ]);
    }
}
