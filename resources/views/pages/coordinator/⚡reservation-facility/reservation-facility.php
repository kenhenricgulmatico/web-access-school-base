<?php

use App\Models\Notification;
use App\Models\Request as ResourceRequest;
use App\Notifications\RequestStatusNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.coordinator')] class extends Component
{
    // ✅ NEW: Reject dialog state
    public ?int   $rejectingRequestId = null;
    public string $rejectReason       = '';

    protected function scopedQuery()
    {
        return ResourceRequest::where('request_type_id', 1)
            ->whereHas('user', function ($q) {
                $q->where('department_id', Auth::user()->department_id)
                  ->whereDoesntHave('roles', function ($role) {
                      $role->where('name', 'program head');
                  });
            });
    }

    #[Computed]
    public function reservations()
    {
        return $this->scopedQuery()
            ->with(['user.department', 'items'])
            ->whereIn('status', ['pending', 'approved', 'rejected'])
            ->latest()
            ->get();
    }

    // ============================================================
    // APPROVE
    // ============================================================
    public function accept(int $id)
    {
        $request = $this->scopedQuery()->with('items')->findOrFail($id);

        if ($request->status !== 'pending') {
            session()->flash('error', 'This request has already been processed.');
            return;
        }

        $facilityItem = $request->items->firstWhere('resource_id', null);

        if ($facilityItem && $this->facilityHasConflict(
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
        $approverName = Auth::user()->name;

        $materialItems = $request->items->whereNotNull('resource_id');

        try {
            DB::transaction(function () use ($request, $materialItems, $departmentId) {

                foreach ($materialItems as $item) {
                    $allocation = DB::table('resource_all_locations')
                        ->where('resource_id', $item->resource_id)
                        ->where('department_id', $departmentId)
                        ->lockForUpdate()
                        ->first();

                    if (!$allocation || $allocation->allocated_quantity < $item->quantity) {
                        throw new \RuntimeException(
                            "Not enough allocated stock for \"{$item->item_name}\". Please request a restock from Admin first."
                        );
                    }
                }

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

        $details = $facilityItem
            ? "{$facilityItem->item_name} on " . Carbon::parse($facilityItem->request_date)->format('M d, Y')
                . " ({$facilityItem->start_time} - {$facilityItem->end_time})"
            : $request->purpose;

        Notification::create([
            'user_id'    => $request->user_id,
            'request_id' => $request->id,
            'message'    => "{$approverName} approved your facility reservation for {$details}.",
            'type'       => 'Gmail',
            'status'     => 'pending',
        ]);

        // ✅ E-mail the requester
        rescue(fn () => $request->user?->notify(new RequestStatusNotification(
            status:       'approved',
            approverName: "{$approverName} (Program Head)",
            requestKind:  'Facility Reservation',
            details:      $details,
            requestId:    $request->id,
        )));
    }

    // ============================================================
    // ✅ REJECT WITH REASON
    // ============================================================
    public function openReject(int $id): void
    {
        $this->rejectingRequestId = $id;
        $this->rejectReason = '';
    }

    public function cancelReject(): void
    {
        $this->rejectingRequestId = null;
        $this->rejectReason = '';
    }

    public function confirmReject(): void
    {
        if (! $this->rejectingRequestId) return;

        $request = $this->scopedQuery()->with('items')->findOrFail($this->rejectingRequestId);

        if ($request->status !== 'pending') {
            session()->flash('error', 'This request has already been processed.');
            $this->cancelReject();
            return;
        }

        $request->update(['status' => 'rejected']);

        // ✅ Save rejection reason
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

        $facilityItem = $request->items->firstWhere('resource_id', null);
        $approverName = Auth::user()->name;

        $details = $facilityItem
            ? "{$facilityItem->item_name} on " . Carbon::parse($facilityItem->request_date)->format('M d, Y')
                . " ({$facilityItem->start_time} - {$facilityItem->end_time})"
            : $request->purpose;

        $message = "{$approverName} rejected your facility reservation for {$details}.";
        if ($this->rejectReason) {
            $message .= ' Reason: ' . $this->rejectReason;
        }

        Notification::create([
            'user_id'    => $request->user_id,
            'request_id' => $request->id,
            'message'    => $message,
            'type'       => 'Gmail',
            'status'     => 'pending',
        ]);

        // ✅ E-mail the requester
        rescue(fn () => $request->user?->notify(new RequestStatusNotification(
            status:       'rejected',
            approverName: "{$approverName} (Program Head)",
            requestKind:  'Facility Reservation',
            details:      $details,
            reason:       $this->rejectReason ?: null,
            requestId:    $request->id,
        )));

        session()->flash('message', 'Reservation rejected and requester notified.');

        $this->cancelReject();
    }

    protected function facilityHasConflict(
        string $facilityName,
        string $date,
        string $startTime,
        string $endTime,
        int $excludeRequestId
    ): bool {
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
