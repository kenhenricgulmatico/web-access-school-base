<?php

namespace Tests\Feature;

use App\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    public function test_approving_a_material_request_decrements_department_allocation(): void
    {
        $dept     = $this->createDepartment();
        $material = $this->createMaterial('Bond Paper A4', 100);

        DB::table('resource_all_locations')->insert([
            'resource_id'        => $material->id,
            'department_id'      => $dept->id,
            'allocated_quantity' => 100,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        DB::table('resource_all_locations')
            ->where('resource_id', $material->id)
            ->where('department_id', $dept->id)
            ->decrement('allocated_quantity', 10);

        $this->assertDatabaseHas('resource_all_locations', [
            'resource_id'        => $material->id,
            'department_id'      => $dept->id,
            'allocated_quantity' => 90,
        ]);
    }

    public function test_stock_creation_updates_quantity_and_writes_history(): void
    {
        $material = $this->createMaterial('Bond Paper A4', 0);
        $admin    = $this->createUser('admin', null);

        $before = $material->quantity_available;
        $after  = $before + 50;

        Stock::create([
            'resource_id'     => $material->id,
            'user_id'         => $admin->id,
            'quantity_added'  => 50,
            'quantity_before' => $before,
            'quantity_after'  => $after,
            'supplier'        => 'ABC Trading',
            'arrival_date'    => now()->toDateString(),
            'arrival_time'    => now()->format('H:i'),
        ]);

        $material->update(['quantity_available' => $after]);

        $this->assertDatabaseHas('resources', [
            'id'                 => $material->id,
            'quantity_available' => 50,
        ]);

        $this->assertDatabaseHas('stocks', [
            'resource_id'    => $material->id,
            'quantity_added' => 50,
            'supplier'       => 'ABC Trading',
        ]);
    }
}
