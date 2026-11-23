<?php

use App\Models\Notification;
use App\Models\Request as ResourceRequest;
use App\Models\RequestApproval;
use App\Models\RequestItem;
use App\Models\RequestType;
use App\Models\Resource;
use App\Notifications\RequestStatusNotification;
use App\Support\ReturnTracking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin: items counted in PIECES (unit = "Pcs", e.g. chairs) that Program Heads borrow.
 *
 *  1. "To approve" tab — pending Program Head requests that contain Pcs items.
 *     The Admin sets a RETURN DEADLINE and clicks Accept (or Rejects with a reason).
 *  2. "Not returned" / "Overdue" tabs — borrowed pieces still out.
 *     Items the Program Head sent back ("Return to Admin") are listed first.
 *  3. "Mark returned" / "Confirm return" — Good condition goes back to the main stock;
 *     in every case the pieces leave the department's allocated stock.
 */
new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $filter = 'pending'; // pending | outstanding | overdue | returned
    public string $search = '';

    /** Return deadline typed by the Admin, per request id. */
    public array $deadlines = [];

    // ✅ Reject dialog (To approve tab)
    public ?int   $rejectingRequestId = null;
    public string $rejectReason       = '';

    // "Mark returned" form
    public ?int   $returningId     = null;
    public string $returnCondition = 'good';
    public string $returnNotes     = '';

    public function updatingFilter(): void { $this->resetPage(); }
    public function updatingSearch(): void { $this->resetPage(); }

    // ============================================================
    // QUERIES
    // ============================================================

    /**
     * SQL for the "return by" date.
     * Uses the deadline the Admin set; for older approvals without one,
     * falls back to the reservation day / request date + RETURN_DAYS.
     */
    protected function dueDateSql(): string
    {
        $facility = (int) RequestType::FACILITY;
        $days     = (int) ReturnTracking::RETURN_DAYS;

        return "COALESCE(request_items.due_back_at, CASE
                    WHEN (SELECT r.request_type_id FROM requests r WHERE r.id = request_items.request_id) = {$facility}
                    THEN request_items.request_date
                    ELSE DATE_ADD(request_items.request_date, INTERVAL {$days} DAY)
                END)";
    }

    /** Pcs items from APPROVED Program Head requests. */
    protected function baseQuery()
    {
        return RequestItem::query()
            ->whereNotNull('request_items.resource_id')
            ->whereHas('resource', fn ($r) => $r->where('unit', 'Pcs'))
            ->whereHas('request', fn ($q) => $q
                ->where('status', 'approved')
                ->whereHas('user', fn ($u) => $u->role('program head')));
    }

    /** PENDING Program Head requests that ask for at least one Pcs item. */
    protected function pendingQuery()
    {
        return ResourceRequest::query()
            ->where('status', 'pending')
            ->whereHas('user', fn ($u) => $u->role('program head'))
            ->whereHas('items', fn ($i) => $i
                ->whereNotNull('resource_id')
                ->whereHas('resource', fn ($r) => $r->where('unit', 'Pcs')));
    }

    #[Computed]
    public function counts(): array
    {
        $due = $this->dueDateSql();

        return [
            'pending'     => $this->pendingQuery()->count(),
            'outstanding' => (clone $this->baseQuery())->whereNull('returned_at')->count(),
            'overdue'     => (clone $this->baseQuery())->whereNull('returned_at')->whereRaw("{$due} < ?", [today()->toDateString()])->count(),
            'returned'    => (clone $this->baseQuery())->whereNotNull('returned_at')->count(),
            // ✅ Program Head clicked "Return to Admin" — waiting for the Admin to confirm
            'sentBack'    => (clone $this->baseQuery())->whereNull('returned_at')->whereNotNull('return_requested_at')->count(),
        ];
    }

    #[Computed]
    public function pendingRequests()
    {
        $q = $this->pendingQuery()
            ->with(['user.department', 'department', 'items.resource'])
            ->oldest();

        if ($this->search !== '') {
            $term = '%' . $this->search . '%';
            $q->where(fn ($w) => $w
                ->whereHas('items', fn ($i) => $i->where('item_name', 'like', $term))
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term))
                ->orWhereHas('department', fn ($d) => $d->where('department_name', 'like', $term)));
        }

        $requests = $q->paginate(10);

        // Pre-fill each deadline box with a suggested date (only if still empty)
        foreach ($requests as $request) {
            if (empty($this->deadlines[$request->id])) {
                $this->deadlines[$request->id] = $this->suggestedDeadline($request);
            }
        }

        return $requests;
    }

    #[Computed]
    public function items()
    {
        $due = $this->dueDateSql();

        $q = $this->baseQuery()
            ->select('request_items.*')
            ->selectRaw("{$due} as due_date")
            ->with(['request.user.department', 'request.department', 'receiver', 'resource']);

        // ✅ Items the Program Head already sent back ("Return to Admin") are listed first
        match ($this->filter) {
            'overdue'  => $q->whereNull('returned_at')->whereRaw("{$due} < ?", [today()->toDateString()])
                            ->orderByRaw('return_requested_at IS NULL')->orderBy('due_date'),
            'returned' => $q->whereNotNull('returned_at')->latest('returned_at'),
            default    => $q->whereNull('returned_at')
                            ->orderByRaw('return_requested_at IS NULL')->orderBy('due_date'),
        };

        if ($this->search !== '') {
            $term = '%' . $this->search . '%';
            $q->where(fn ($w) => $w
                ->where('item_name', 'like', $term)
                ->orWhereHas('request.user', fn ($u) => $u->where('name', 'like', $term))
                ->orWhereHas('request.department', fn ($d) => $d->where('department_name', 'like', $term)));
        }

        return $q->paginate(15);
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /** Earliest allowed deadline: the day the items are needed (never before today). */
    protected function earliestDeadline(ResourceRequest $request): Carbon
    {
        $needed = $request->items->min('request_date');
        $needed = $needed ? Carbon::parse($needed)->startOfDay() : today();

        return $needed->lt(today()) ? today() : $needed;
    }

    /**
     * Suggested deadline:
     *  ✅ the return date the Program Head chose for the Pcs items, if any;
     *  otherwise the reservation day (facility) or needed date + RETURN_DAYS.
     */
    protected function suggestedDeadline(ResourceRequest $request): string
    {
        $chosen = $request->items
            ->filter(fn ($i) => $i->resource?->unit === 'Pcs' && $i->due_back_at)
            ->first()?->due_back_at;

        if ($chosen) {
            return Carbon::parse($chosen)->format('Y-m-d');
        }

        $earliest = $this->earliestDeadline($request);

        return ((int) $request->request_type_id === (int) RequestType::FACILITY
            ? $earliest
            : $earliest->copy()->addDays(ReturnTracking::RETURN_DAYS)
        )->format('Y-m-d');
    }

    /** "Due today", "Due in 2 days", "Overdue by 3 days", "Returned (Good)". */
    public function statusLabel(RequestItem $item): string
    {
        if ($item->returned_at) {
            return 'Returned (' . (RequestItem::CONDITIONS[$item->return_condition] ?? ucfirst((string) $item->return_condition)) . ')';
        }

        $days = (int) today()->diffInDays(Carbon::parse($item->due_date), false);

        return match (true) {
            $days < 0   => 'Overdue by ' . abs($days) . ' ' . str('day')->plural(abs($days)),
            $days === 0 => 'Due today',
            default     => 'Due in ' . $days . ' ' . str('day')->plural($days),
        };
    }

    // ============================================================
    // ✅ ACCEPT WITH A RETURN DEADLINE
    // ============================================================
    public function accept(int $id): void
    {
        abort_unless(
            auth()->user()->canAny(['program-head-requests.approve', 'requests.approve']),
            403
        );

        $request = $this->pendingQuery()->with('items.resource')->findOrFail($id);

        // --- Validate the deadline ---
        $earliest = $this->earliestDeadline($request);
        $this->resetErrorBag("deadlines.{$id}");

        $this->validate(
            ["deadlines.{$id}" => 'required|date|after_or_equal:' . $earliest->format('Y-m-d') . '|before_or_equal:' . today()->addYear()->format('Y-m-d')],
            [
                "deadlines.{$id}.required"        => 'Please set a return deadline.',
                "deadlines.{$id}.date"            => 'Please enter a valid date.',
                "deadlines.{$id}.after_or_equal"  => 'The deadline cannot be before ' . $earliest->format('M d, Y') . ' (the day the items are needed).',
                "deadlines.{$id}.before_or_equal" => 'The deadline must be within one year.',
            ]
        );

        $deadline = Carbon::parse($this->deadlines[$id])->startOfDay();

        // --- Facility conflict check (same rule as Program Head Requests) ---
        $facilityItem = $request->items->firstWhere('resource_id', null);

        if ($facilityItem && $facilityItem->start_time && DB::table('request_items as ri')
            ->join('requests as req', 'req.id', '=', 'ri.request_id')
            ->where('req.status', 'approved')
            ->where('req.id', '!=', $request->id)
            ->whereNull('ri.resource_id')
            ->where('ri.item_name', $facilityItem->item_name)
            ->whereDate('ri.request_date', $facilityItem->request_date)
            ->where('ri.start_time', '<', $facilityItem->end_time)
            ->where('ri.end_time', '>', $facilityItem->start_time)
            ->exists()) {
            session()->flash('error', 'This facility is already booked for an overlapping time.');
            return;
        }

        // --- Stock needed per resource (facilities are not stock) ---
        $facilityTypeId = DB::table('resource_types')->where('type_name', 'Facility')->value('id');

        $needed = [];
        foreach ($request->items as $item) {
            $resource = $item->resource_id
                ? $item->resource
                : Resource::whereRaw('LOWER(resource_name) = ?', [strtolower($item->item_name)])->first();

            if (! $resource) continue;
            if ($facilityTypeId && $resource->resource_type_id == $facilityTypeId) continue;

            $needed[$resource->id] = ($needed[$resource->id] ?? 0) + (int) $item->quantity;
        }

        try {
            DB::transaction(function () use ($request, $needed, $deadline) {
                $fresh = ResourceRequest::lockForUpdate()->find($request->id);

                if (! $fresh || $fresh->status !== 'pending') {
                    throw new \RuntimeException('This request has already been processed.');
                }

                foreach ($needed as $resourceId => $qty) {
                    $resource = Resource::lockForUpdate()->find($resourceId);

                    if ($resource->quantity_available < $qty) {
                        throw new \RuntimeException(
                            "Cannot approve: only {$resource->quantity_available} unit(s) of \"{$resource->resource_name}\" available, but {$qty} requested."
                        );
                    }

                    $resource->decrement('quantity_available', $qty);

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

                // ✅ The return deadline goes on every Pcs item of this request
                RequestItem::where('request_id', $fresh->id)
                    ->whereNotNull('resource_id')
                    ->whereHas('resource', fn ($r) => $r->where('unit', 'Pcs'))
                    ->update(['due_back_at' => $deadline->toDateString()]);

                RequestApproval::create([
                    'request_id'  => $fresh->id,
                    'approver_id' => Auth::id(),
                    'status'      => 'approved',
                    'remarks'     => 'Return deadline: ' . $deadline->format('M d, Y'),
                    'approved_at' => now(),
                ]);

                Notification::create([
                    'user_id'    => $fresh->user_id,
                    'request_id' => $fresh->id,
                    'message'    => Auth::user()->name . ' (Admin) approved your request. Please return the borrowed items by ' . $deadline->format('M d, Y') . '.',
                    'type'       => 'Gmail',
                    'status'     => 'pending',
                ]);
            });
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        $this->emailRequester($request->id, $deadline);

        unset($this->deadlines[$id], $this->pendingRequests, $this->counts, $this->items);

        session()->flash('message', 'Request approved. Return deadline set to ' . $deadline->format('M d, Y') . '.');
    }

    /** E-mail the Program Head, including the return deadline. */
    protected function emailRequester(int $requestId, Carbon $deadline): void
    {
        $request = ResourceRequest::with(['user', 'items.resource'])->find($requestId);
        if (! $request?->user) {
            return;
        }

        $facilityItem = $request->items->firstWhere('resource_id', null);

        $details = $request->items
            ->map(fn ($i) => $i->resource_id
                ? "{$i->item_name} (x{$i->quantity})"
                : "{$i->item_name} on " . Carbon::parse($i->request_date)->format('M d, Y')
                    . ($i->start_time ? " ({$i->start_time} - {$i->end_time})" : ''))
            ->implode(', ') ?: $request->purpose;

        $pcs = $request->items
            ->filter(fn ($i) => $i->resource?->unit === 'Pcs')
            ->map(fn ($i) => "{$i->item_name} x{$i->quantity}")
            ->implode(', ');

        $details .= ". Please return {$pcs} by " . $deadline->format('M d, Y') . '.';

        rescue(fn () => $request->user->notify(new RequestStatusNotification(
            status:       'approved',
            approverName: Auth::user()->name . ' (Admin)',
            requestKind:  $facilityItem ? 'Facility Reservation' : 'Material Request',
            details:      $details,
            requestId:    $request->id,
            link:         route('coordinator.request-to-admin.view-admin', $request->id),
        )));
    }

    // ============================================================
    // ✅ REJECT WITH REASON
    // ============================================================
    public function openReject(int $id): void
    {
        abort_unless(
            auth()->user()->canAny(['program-head-requests.reject', 'requests.reject']),
            403
        );

        $this->rejectingRequestId = $id;
        $this->rejectReason       = '';
        $this->resetValidation();
    }

    public function cancelReject(): void
    {
        $this->rejectingRequestId = null;
        $this->rejectReason       = '';
    }

    public function confirmReject(): void
    {
        abort_unless(
            auth()->user()->canAny(['program-head-requests.reject', 'requests.reject']),
            403
        );

        if (! $this->rejectingRequestId) return;

        $this->validate(['rejectReason' => 'nullable|string|max:500']);

        $request = $this->pendingQuery()->with(['user', 'items'])->findOrFail($this->rejectingRequestId);

        $request->update(['status' => 'rejected']);

        RequestApproval::create([
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

        // E-mail the Program Head
        $facilityItem = $request->items->firstWhere('resource_id', null);
        $details = $request->items
            ->map(fn ($i) => $i->resource_id
                ? "{$i->item_name} (x{$i->quantity})"
                : "{$i->item_name} on " . Carbon::parse($i->request_date)->format('M d, Y'))
            ->implode(', ') ?: $request->purpose;

        rescue(fn () => $request->user?->notify(new RequestStatusNotification(
            status:       'rejected',
            approverName: Auth::user()->name . ' (Admin)',
            requestKind:  $facilityItem ? 'Facility Reservation' : 'Material Request',
            details:      $details,
            reason:       $this->rejectReason ?: null,
            requestId:    $request->id,
            link:         route('coordinator.request-to-admin.view-admin', $request->id),
        )));

        unset($this->deadlines[$request->id], $this->pendingRequests, $this->counts);
        $this->cancelReject();

        session()->flash('message', 'Request rejected and the Program Head was notified.');
    }

    // ============================================================
    // MARK RETURNED
    // ============================================================
    public function openReturn(int $id): void
    {
        $this->returningId     = $id;
        $this->returnCondition = 'good';
        $this->returnNotes     = '';
        $this->resetValidation();
    }

    public function cancelReturn(): void
    {
        $this->returningId = null;
    }

    public function confirmReturn(): void
    {
        $this->validate([
            'returnCondition' => 'required|in:' . implode(',', array_keys(RequestItem::CONDITIONS)),
            'returnNotes'     => 'nullable|string|max:500',
        ]);

        $item = $this->baseQuery()->findOrFail($this->returningId);

        try {
            DB::transaction(function () use ($item) {
                $item = RequestItem::with('request')->lockForUpdate()->findOrFail($item->id);

                if ($item->returned_at) {
                    throw new \RuntimeException('This item was already marked as returned.');
                }

                $item->update([
                    'returned_at'      => now(),
                    'return_condition' => $this->returnCondition,
                    'return_notes'     => $this->returnNotes ?: null,
                    'received_by'      => Auth::id(),
                ]);

                // The department no longer holds these pieces
                $allocation = DB::table('resource_all_locations')
                    ->where('resource_id', $item->resource_id)
                    ->where('department_id', $item->request->department_id)
                    ->lockForUpdate()
                    ->first();

                if ($allocation) {
                    DB::table('resource_all_locations')
                        ->where('id', $allocation->id)
                        ->update([
                            'allocated_quantity' => max((int) $allocation->allocated_quantity - (int) $item->quantity, 0),
                            'updated_at'         => now(),
                        ]);
                }

                // Good condition → back to the Admin's main stock
                if ($this->returnCondition === 'good') {
                    DB::table('resources')
                        ->where('id', $item->resource_id)
                        ->increment('quantity_available', $item->quantity);
                }
            });
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            $this->returningId = null;
            return;
        }

        session()->flash('message', $this->returnCondition === 'good'
            ? "\"{$item->item_name}\" ({$item->quantity} pcs) marked as returned and added back to the main stock."
            : "\"{$item->item_name}\" ({$item->quantity} pcs) marked as returned ({$this->returnCondition}). It was not added back to stock.");

        $this->returningId = null;
        unset($this->items, $this->counts);
    }
};
