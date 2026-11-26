<?php

namespace Tests\Feature;

use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FacilityConflictDateScopingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    /** Same facility, SAME date, SAME time → must conflict */
    public function test_same_facility_same_date_same_time_conflicts(): void
    {
        $this->assertConflictBlocked(
            existingDate: '2026-12-30',
            existingStart: '09:00',
            existingEnd: '10:00',
            newDate: '2026-12-30',
            newStart: '09:00',
            newEnd: '10:00',
        );
    }

    /** Same facility, SAME date, OVERLAPPING time → must conflict */
    public function test_same_facility_same_date_overlapping_time_conflicts(): void
    {
        $this->assertConflictBlocked(
            existingDate: '2026-12-30',
            existingStart: '09:00',
            existingEnd: '10:00',
            newDate: '2026-12-30',
            newStart: '09:30',
            newEnd: '10:30',
        );
    }

    /** Same facility, SAME date, NON-overlapping time → must NOT conflict */
    public function test_same_facility_same_date_non_overlapping_time_does_not_conflict(): void
    {
        $this->assertConflictAllowed(
            existingDate: '2026-12-30',
            existingStart: '09:00',
            existingEnd: '10:00',
            newDate: '2026-12-30',
            newStart: '14:00',
            newEnd: '15:00',
        );
    }

    /** Same facility, DIFFERENT date, SAME time → must NOT conflict */
    public function test_same_facility_different_date_same_time_does_not_conflict(): void
    {
        $this->assertConflictAllowed(
            existingDate: '2026-12-30',
            existingStart: '09:00',
            existingEnd: '10:00',
            newDate: '2026-12-31',
            newStart: '09:00',
            newEnd: '10:00',
        );
    }

    /** Same facility, DIFFERENT date, OVERLAPPING time → must NOT conflict */
    public function test_same_facility_different_date_overlapping_time_does_not_conflict(): void
    {
        $this->assertConflictAllowed(
            existingDate: '2026-12-30',
            existingStart: '09:00',
            existingEnd: '10:00',
            newDate: '2026-12-31',
            newStart: '09:30',
            newEnd: '10:30',
        );
    }

    // ─────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────

    private function assertConflictBlocked(
        string $existingDate, string $existingStart, string $existingEnd,
        string $newDate, string $newStart, string $newEnd,
    ): void {
        [$programHead, $student, $pending] = $this->setupExistingBooking(
            $existingDate, $existingStart, $existingEnd,
            $newDate, $newStart, $newEnd,
        );

        Livewire::actingAs($programHead)
            ->test('pages::coordinator.reservation-facility')
            ->call('accept', $pending->id);

        $this->assertDatabaseHas('requests', [
            'id'     => $pending->id,
            'status' => 'pending',   // still pending → blocked
        ]);
    }

    private function assertConflictAllowed(
        string $existingDate, string $existingStart, string $existingEnd,
        string $newDate, string $newStart, string $newEnd,
    ): void {
        [$programHead, $student, $pending] = $this->setupExistingBooking(
            $existingDate, $existingStart, $existingEnd,
            $newDate, $newStart, $newEnd,
        );

        Livewire::actingAs($programHead)
            ->test('pages::coordinator.reservation-facility')
            ->call('accept', $pending->id);

        $this->assertDatabaseHas('requests', [
            'id'     => $pending->id,
            'status' => 'approved',  // approved → allowed
        ]);
    }

    /**
     * Creates:
     *   - 1 department
     *   - 1 program head
     *   - 1 student (with an already-approved booking)
     *   - 1 other student (with a pending booking)
     * Returns [programHead, pendingStudent, pendingRequest]
     */
    private function setupExistingBooking(
        string $existingDate, string $existingStart, string $existingEnd,
        string $newDate, string $newStart, string $newEnd,
    ): array {
        $dept        = $this->createDepartment();
        $programHead = $this->createUser('program head', $dept);
        $studentA    = $this->createUser('faculty', $dept);
        $studentB    = $this->createUser('faculty', $dept);

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
            'request_date' => $existingDate,
            'start_time'   => $existingStart,
            'end_time'     => $existingEnd,
        ]);

        $pending = ResourceRequest::create([
            'user_id'         => $studentB->id,
            'department_id'   => $dept->id,
            'request_type_id' => 1,
            'purpose'         => 'New',
            'status'          => 'pending',
        ]);

        RequestItem::create([
            'request_id'   => $pending->id,
            'resource_id'  => null,
            'item_name'    => 'Computer Laboratory 1',
            'quantity'     => 1,
            'request_date' => $newDate,
            'start_time'   => $newStart,
            'end_time'     => $newEnd,
        ]);

        return [$programHead, $studentB, $pending];
    }
}
