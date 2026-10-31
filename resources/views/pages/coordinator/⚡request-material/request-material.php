<?php

use App\Models\Request as ResourceRequest;
use App\Models\Notification;
use App\Notifications\RequestStatusNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.coordinator')] class extends Component
{
    // ✅ Reject dialog state
    public ?int   $rejectingRequestId = null;
    public string $rejectReason       = '';

    public function mount(): void
    {
        // ✅ Program Head-only page — requires `requests.view-department`
        abort_unless(auth()->user()->can('requests.view-department'), 403);
    }

    protected function scopedQuery()
    {
        return ResourceRequest::where('request_type_id', 2)
            ->where('department_id', Auth::user()->department_id)
            ->whereHas('user', function ($query) {
                $query->role(['student', 'faculty']);
            });
    }

    #[Computed]
    public function requests()
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
        // ✅ Server-side guard — requires `requests.approve`
        abort_unless(auth()->user()->can('requests.approve'), 403);

        $request = $this->scopedQuery()->with('items')->findOrFail($id);

        if ($request->status !== 'pending') {
            session()->flash('error', 'This request has already been processed.');
            return;
        }

        $departmentId = Auth::user()->department_id;
        $approverName = Auth::user()->name;

        try {
            DB::transaction(function () use ($request, $departmentId, $approverName) {

                foreach ($request->items as $item) {
                    $allocation = DB::table('resource_all_locations')
                        ->where('resource_id', $item->resource_id)
                        ->where('department_id', $departmentId)
                        ->lockForUpdate()
                        ->first();

                    if (!$allocation || $allocation->allocated_quantity < $item->quantity) {
                        throw new \RuntimeException(
                            "Not enough stock for \"{$item->item_name}\". Please request a restock from Admin first."
                        );
                    }
                }

                foreach ($request->items as $item) {
                    DB::table('resource_all_locations')
                        ->where('resource_id', $item->resource_id)
                        ->where('department_id', $departmentId)
                        ->decrement('allocated_quantity', $item->quantity);
                }

                $request->update(['status' => 'approved']);

                // ── Record the approval ──
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

                $itemsList = $request->items
                    ->map(fn ($i) => "{$i->item_name} (x{$i->quantity})")
                    ->implode(', ');

                Notification::create([
                    'user_id'    => $request->user_id,
                    'request_id' => $request->id,
                    'message'    => "{$approverName} approved your material request: {$itemsList}.",
                    'type'       => 'Gmail',
                    'status'     => 'pending',
                ]);
            });

            // ✅ E-mail the requester
            $itemsList = $request->items
                ->map(fn ($i) => "{$i->item_name} (x{$i->quantity})")
                ->implode(', ');

            rescue(fn () => $request->user?->notify(new RequestStatusNotification(
                status:       'approved',
                approverName: "{$approverName} (Program Head)",
                requestKind:  'Material Request',
                details:      $itemsList,
                requestId:    $request->id,
            )));

            session()->flash('message', 'Request approved. Department allocation updated.');
            unset($this->requests);
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    // ============================================================
    // ✅ REJECT WITH REASON
    // ============================================================
    public function openReject(int $id): void
    {
        // ✅ Server-side guard — requires `requests.reject`
        abort_unless(auth()->user()->can('requests.reject'), 403);

        $this->rejectingRequestId = $id;
        $this->rejectReason       = '';
    }

    public function cancelReject(): void
    {
        $this->rejectingRequestId = null;
        $this->rejectReason       = '';
    }

    public function confirmReject(): void
    {
        // ✅ Server-side guard — requires `requests.reject`
        abort_unless(auth()->user()->can('requests.reject'), 403);

        if (! $this->rejectingRequestId) {
            return;
        }

        $request = $this->scopedQuery()->with('items')->findOrFail($this->rejectingRequestId);

        if ($request->status !== 'pending') {
            session()->flash('error', 'This request has already been processed.');
            $this->cancelReject();
            return;
        }

        // 1. Mark rejected
        $request->update(['status' => 'rejected']);

        // 2. Save rejection row with reason
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

        // 3. Notify requester
        $approverName = Auth::user()->name;
        $itemsList = $request->items
            ->map(fn ($i) => "{$i->item_name} (x{$i->quantity})")
            ->implode(', ');

        $message = "{$approverName} rejected your material request: {$itemsList}.";
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
            requestKind:  'Material Request',
            details:      $itemsList,
            reason:       $this->rejectReason ?: null,
            requestId:    $request->id,
        )));

        session()->flash('message', 'Request rejected and requester notified.');

        $this->cancelReject();
        unset($this->requests);
    }
};
