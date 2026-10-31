<?php

use App\Models\Notification;
use App\Models\Request as ResourceRequest;
use App\Notifications\RequestStatusNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Program Head: view ONE student/faculty request and approve or reject it.
 *
 * Approving here runs the SAME checks as the Facility / Material list pages:
 *  - permission (requests.approve / requests.reject)
 *  - request must still be pending
 *  - facility must not be double-booked
 *  - department stock must cover every material, and is deducted
 *  - an approval row is saved (used by the printable receipt)
 *  - the requester gets an in-app notification and an e-mail
 */
new #[Layout('layouts.coordinator')] class extends Component
{
    public ResourceRequest $requestModel;

    public string $rejectReason = '';

    public function mount(int $id)
    {
        abort_unless(auth()->user()->can('requests.view-department'), 403);

        $this->requestModel = $this->scopedQuery()
            ->with(['user.department', 'items'])
            ->findOrFail($id);
    }

    /** Same department, never a program head's own submission. */
    protected function scopedQuery()
    {
        return ResourceRequest::whereHas('user', function ($q) {
            $q->where('department_id', Auth::user()->department_id)
              ->whereDoesntHave('roles', fn ($role) => $role->where('name', 'program head'));
        });
    }

    // ============================================================
    // APPROVE
    // ============================================================
    public function accept()
    {
        abort_unless(auth()->user()->can('requests.approve'), 403);

        $request = $this->scopedQuery()->with('items')->findOrFail($this->requestModel->id);

        if ($request->status !== 'pending') {
            session()->flash('error', 'This request has already been processed.');
            return $this->refreshModel();
        }

        $facilityItem  = $request->items->firstWhere('resource_id', null);
        $materialItems = $request->items->whereNotNull('resource_id');

        if ($facilityItem && $facilityItem->start_time && $this->facilityHasConflict(
            $facilityItem->item_name,
            $facilityItem->request_date,
            $facilityItem->start_time,
            $facilityItem->end_time,
            $request->id
        )) {
            session()->flash('error', 'This facility is already booked for an overlapping time. Please reject this request or ask the requester to choose another slot.');
            return;
        }

        $departmentId = Auth::user()->department_id;

        try {
            DB::transaction(function () use ($request, $materialItems, $departmentId) {
                // Lock and check every material first…
                foreach ($materialItems as $item) {
                    $allocation = DB::table('resource_all_locations')
                        ->where('resource_id', $item->resource_id)
                        ->where('department_id', $departmentId)
                        ->lockForUpdate()
                        ->first();

                    if (! $allocation || $allocation->allocated_quantity < $item->quantity) {
                        throw new \RuntimeException(
                            "Not enough allocated stock for \"{$item->item_name}\". Please request a restock from Admin first."
                        );
                    }
                }

                // …then deduct.
                foreach ($materialItems as $item) {
                    DB::table('resource_all_locations')
                        ->where('resource_id', $item->resource_id)
                        ->where('department_id', $departmentId)
                        ->decrement('allocated_quantity', $item->quantity);
                }

                $request->update(['status' => 'approved']);

                DB::table('request_approvals')->updateOrInsert(
                    ['request_id' => $request->id, 'approver_id' => Auth::id()],
                    [
                        'status'      => 'approved',
                        'remarks'     => null,
                        'approved_at' => now(),
                        'updated_at'  => now(),
                        'created_at'  => now(),
                    ]
                );
            });
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        $this->notifyRequester($request, 'approved');

        session()->flash('message', 'Request approved and requester notified.');
        $this->refreshModel();
    }

    // ============================================================
    // REJECT
    // ============================================================
    public function reject()
    {
        abort_unless(auth()->user()->can('requests.reject'), 403);

        $this->validate(['rejectReason' => 'nullable|string|max:500']);

        $request = $this->scopedQuery()->with('items')->findOrFail($this->requestModel->id);

        if ($request->status !== 'pending') {
            session()->flash('error', 'This request has already been processed.');
            return $this->refreshModel();
        }

        $request->update(['status' => 'rejected']);

        DB::table('request_approvals')->updateOrInsert(
            ['request_id' => $request->id, 'approver_id' => Auth::id()],
            [
                'status'      => 'rejected',
                'remarks'     => $this->rejectReason ?: null,
                'approved_at' => now(),
                'updated_at'  => now(),
                'created_at'  => now(),
            ]
        );

        $this->notifyRequester($request, 'rejected', $this->rejectReason ?: null);

        session()->flash('message', 'Request rejected and requester notified.');
        $this->rejectReason = '';
        $this->refreshModel();
    }

    // ============================================================
    // HELPERS
    // ============================================================
    protected function refreshModel(): void
    {
        $this->requestModel = $this->requestModel->fresh(['user.department', 'items']);
    }

    protected function notifyRequester(ResourceRequest $request, string $verb, ?string $reason = null): void
    {
        $approverName  = Auth::user()->name;
        $facilityItem  = $request->items->firstWhere('resource_id', null);
        $materialItems = $request->items->whereNotNull('resource_id');

        $parts = [];
        if ($facilityItem) {
            $parts[] = "{$facilityItem->item_name} on " . Carbon::parse($facilityItem->request_date)->format('M d, Y')
                . ($facilityItem->start_time ? " ({$facilityItem->start_time} - {$facilityItem->end_time})" : '');
        }
        if ($materialItems->isNotEmpty()) {
            $parts[] = $materialItems->map(fn ($item) => "{$item->item_name} x{$item->quantity}")->implode(', ');
        }
        $details = $parts ? implode(' and ', $parts) : $request->purpose;

        $message = "{$approverName} {$verb} your request for {$details}.";
        if ($reason) {
            $message .= ' Reason: ' . $reason;
        }

        // In-app notification (bell)
        Notification::create([
            'user_id'    => $request->user_id,
            'request_id' => $request->id,
            'message'    => $message,
            'type'       => 'Gmail',
            'status'     => 'pending',
        ]);

        // E-mail
        rescue(fn () => $request->user?->notify(new RequestStatusNotification(
            status:       $verb,
            approverName: "{$approverName} (Program Head)",
            requestKind:  $facilityItem ? 'Facility Reservation' : 'Material Request',
            details:      $details,
            reason:       $reason,
            requestId:    $request->id,
        )));
    }

    protected function facilityHasConflict(string $facilityName, string $date, string $startTime, string $endTime, int $excludeRequestId): bool
    {
        return DB::table('request_items as ri')
            ->join('requests as req', 'req.id', '=', 'ri.request_id')
            ->where('req.status', 'approved')
            ->where('req.id', '!=', $excludeRequestId)
            ->whereNull('ri.resource_id')
            ->where('ri.item_name', $facilityName)
            ->whereDate('ri.request_date', $date)
            ->where('ri.start_time', '<', $endTime)
            ->where('ri.end_time', '>', $startTime)
            ->exists();
    }
};
