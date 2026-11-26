<?php

namespace App\Traits;

use App\Models\Request as ResourceRequest;
use App\Models\Resource;
use App\Models\Notification;
use App\Notifications\RequestStatusNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Shared Approve / Reject logic for Program Head requests reviewed by the Admin.
 * Used by the "Program Head Requests" list and the request "View" page.
 */
trait ReviewsProgramHeadRequests
{
    // ✅ Reject dialog state
    public ?int   $rejectingRequestId = null;
    public string $rejectReason       = '';

    /**
     * Requests from Program Head accounts only land here for Admin's review.
     *
     * ✅ Shown here:
     *    - Ream / Set items and facility-only requests, and
     *    - every request the Program Head FORWARDED from a student / faculty request
     *      (even with Pcs items — they keep the return date the student chose).
     *    The Program Head's own Pcs borrowing (chairs…) is handled on Borrowed Items,
     *    where the Admin sets a return deadline.
     */
    protected function incomingRequestsQuery()
    {
        return ResourceRequest::whereHas('user', function ($query) {
                $query->role(['program head']);
            })
            ->where(function ($q) {
                $q->whereNotNull('forwarded_from_request_id')
                  ->orWhereDoesntHave('items', function ($item) {
                      $item->whereNotNull('resource_id')
                           ->whereHas('resource', fn ($r) => $r->where('is_returnable', true));
                  });
            });
    }

    // ============================================================
    // APPROVE
    // ============================================================
    public function approve($id)
    {
        // ✅ Server-side permission guard
        abort_unless(
            auth()->user()->canAny([
                'program-head-requests.approve',
                'requests.approve',
            ]),
            403
        );

        $request = $this->incomingRequestsQuery()
            ->with('items')
            ->findOrFail($id);

        if ($request->status !== 'pending') {
            session()->flash('error', 'This request has already been processed.');
            return;
        }

        // ✅ Facility conflict check — block overlapping approved reservations
        $facilityItem = $request->items->firstWhere('resource_id', null);

        if ($facilityItem && $this->facilityHasConflict(
            $facilityItem->item_name,
            $facilityItem->request_date,
            $facilityItem->start_time,
            $facilityItem->end_time,
            $request->id
        )) {
            session()->flash('error', 'This facility is already booked for an overlapping time.');
            return;
        }

        $facilityTypeId = DB::table('resource_types')
            ->where('type_name', \App\Models\ResourceType::FACILITY)
            ->value('id');

        $needed = [];
        foreach ($request->items as $item) {
            $resource = $item->resource_id
                ? Resource::find($item->resource_id)
                : Resource::whereRaw('LOWER(resource_name) = ?', [strtolower($item->item_name)])->first();

            if (!$resource) continue;
            if ($facilityTypeId && $resource->resource_type_id == $facilityTypeId) continue;

            if (!isset($needed[$resource->id])) {
                $needed[$resource->id] = 0;
            }
            $needed[$resource->id] += (int) $item->quantity;
        }

        try {
            DB::transaction(function () use ($request, $needed) {
                $fresh = ResourceRequest::lockForUpdate()->find($request->id);

                if (!$fresh || $fresh->status !== 'pending') {
                    throw new \RuntimeException('This request has already been processed.');
                }

                foreach ($needed as $resourceId => $qty) {
                    $resource = Resource::lockForUpdate()->find($resourceId);

                    if ($resource->quantity_available < $qty) {
                        throw new \RuntimeException(
                            "Cannot approve: only {$resource->quantity_available} unit(s) of \"{$resource->resource_name}\" available, but {$qty} requested."
                        );
                    }

                    $stockBefore = (int) $resource->quantity_available;
                    $resource->decrement('quantity_available', $qty);

                    // ✅ Remember Before → After for Stock History
                    \App\Support\StockSnapshot::recordApproval($fresh->id, $resource->id, $stockBefore);

                    $existing = DB::table('resource_all_locations')
                        ->where('resource_id', $resource->id)
                        ->where('department_id', $fresh->department_id)
                        ->first();

                    if ($existing) {
                        DB::table('resource_all_locations')
                            ->where('id', $existing->id)
                            ->update([
                                'allocated_quantity' => $existing->allocated_quantity + $qty,
                                'updated_at'         => now(),
                            ]);
                    } else {
                        DB::table('resource_all_locations')->insert([
                            'resource_id'        => $resource->id,
                            'department_id'      => $fresh->department_id,
                            'allocated_quantity' => $qty,
                            'created_at'         => now(),
                            'updated_at'         => now(),
                        ]);
                    }
                }

                $fresh->update(['status' => 'approved']);

                \App\Models\RequestApproval::create([
                    'request_id'  => $fresh->id,
                    'approver_id' => Auth::id(),
                    'status'      => 'approved',
                    'remarks'     => null,
                    'approved_at' => now(),
                ]);

                Notification::create([
                    'user_id'    => $fresh->user_id,
                    'request_id' => $fresh->id,
                    'message'    => Auth::user()->name . ' (Admin) approved your request.',
                    'type'       => 'Gmail',
                    'status'     => 'pending',
                ]);
            });
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        // ✅ E-mail the Program Head who made the request
        $this->emailRequester($request->id, 'approved');

        // ✅ Forwarded from a student / faculty request → approve that one automatically
        $note = \App\Support\ForwardedRequests::onAdminApproved($request->fresh());

        session()->flash('message', 'Request approved. Inventory updated.' . ($note ? ' ' . $note : ''));
    }

    // ============================================================
    // REJECT WITH REASON
    // ============================================================
    public function openReject(int $id): void
    {
        abort_unless(
            auth()->user()->canAny([
                'program-head-requests.reject',
                'requests.reject',
            ]),
            403
        );

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
        abort_unless(
            auth()->user()->canAny([
                'program-head-requests.reject',
                'requests.reject',
            ]),
            403
        );

        if (! $this->rejectingRequestId) return;

        $request = $this->incomingRequestsQuery()->findOrFail($this->rejectingRequestId);

        if ($request->status !== 'pending') {
            session()->flash('error', 'This request has already been processed.');
            $this->cancelReject();
            return;
        }

        $request->update(['status' => 'rejected']);

        \App\Models\RequestApproval::create([
            'request_id'  => $request->id,
            'approver_id' => Auth::id(),
            'status'      => 'rejected',
            'remarks'     => $this->rejectReason ?: null,
            'approved_at' => now(),
        ]);

        $message = Auth::user()->name . ' (Admin) rejected your request.';
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

        // ✅ E-mail the Program Head who made the request
        $this->emailRequester($request->id, 'rejected', $this->rejectReason ?: null);

        // ✅ Forwarded from a student / faculty request → reject that one too
        $note = \App\Support\ForwardedRequests::onAdminRejected($request->fresh(), $this->rejectReason ?: null, Auth::user());

        session()->flash('message', 'Request rejected and requester notified.' . ($note ? ' ' . $note : ''));

        $this->cancelReject();
    }

    protected function emailRequester(int $requestId, string $status, ?string $reason = null): void
    {
        $request = ResourceRequest::with(['user', 'items'])->find($requestId);
        if (! $request?->user) {
            return;
        }

        $facilityItem = $request->items->firstWhere('resource_id', null);
        $details = $request->items
            ->map(fn ($i) => $i->resource_id
                ? "{$i->item_name} (x{$i->quantity})"
                : "{$i->item_name} on " . \Carbon\Carbon::parse($i->request_date)->format('M d, Y')
                    . ($i->start_time ? " ({$i->start_time} - {$i->end_time})" : ''))
            ->implode(', ') ?: $request->purpose;

        rescue(fn () => $request->user->notify(new RequestStatusNotification(
            status:       $status,
            approverName: Auth::user()->name . ' (Admin)',
            requestKind:  $facilityItem ? 'Facility Reservation' : 'Material Request',
            details:      $details,
            reason:       $reason,
            requestId:    $request->id,
            link:         route('coordinator.request-to-admin.view-admin', $request->id),
        )));
    }

    // ============================================================
    // ✅ FACILITY CONFLICT DETECTION
    // ============================================================
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
            ->whereDate('ri.request_date', $facilityName ? $date : $date)
            ->where('ri.start_time', '<', $endTime)
            ->where('ri.end_time', '>', $startTime)
            ->exists();
    }
}
