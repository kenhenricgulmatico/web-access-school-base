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
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin: "Material" items (chairs, projectors…) that Program Heads borrow and return.
 *
 *  1. "To approve" tab — pending Program Head requests that contain Pcs items.
 *     The Admin sets a RETURN DEADLINE and clicks Accept (or Rejects with a reason).
 *  2. "Not returned" / "Overdue" tabs — borrowed pieces still out.
 *     Items the Program Head sent back ("Return to Admin") are listed first.
 *     ✅ Overdue items: "Send reminder" messages the Program Head.
 *  3. "Mark returned" / "Confirm return" — Good condition goes back to the main stock;
 *     in every case the pieces leave the department's allocated stock.
 */
new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('borrowed-items.view'), 403);
    }


    #[Url]
    public string $filter = 'pending'; // pending | outstanding | overdue | returned
    public string $search = '';

    /** Return deadline typed by the Admin, per request id. */
    public array $deadlines = [];

    /** ✅ Per request: 'borrow' (return by a date) or 'assign' (they keep it — furniture for a faculty room). */
    public array $modes = [];

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

        // ✅ Works on MySQL (the app) and SQLite (the tests)
        $plusDays = DB::getDriverName() === 'sqlite'
            ? "date(request_items.request_date, '+{$days} days')"
            : "DATE_ADD(request_items.request_date, INTERVAL {$days} DAY)";

        return "COALESCE(request_items.due_back_at, CASE
                    WHEN (SELECT r.request_type_id FROM requests r WHERE r.id = request_items.request_id) = {$facility}
                    THEN request_items.request_date
                    ELSE {$plusDays}
                END)";
    }

    /** Pcs items from APPROVED Program Head requests. */
    protected function baseQuery()
    {
        return RequestItem::query()
            ->whereNotNull('request_items.resource_id')
            ->whereNull('request_items.assigned_at')   // ✅ assigned items (kept, no return) are on the Assigned tab
            ->whereHas('resource', fn ($r) => $r->where('is_returnable', true))
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
                ->whereHas('resource', fn ($r) => $r->where('is_returnable', true)));
    }

    #[Computed]
    public function counts(): array
    {
        $due = $this->dueDateSql();

        return [
            'pending'     => $this->pendingQuery()->count(),
            'assigned'    => RequestItem::assignedNow()->whereHas('resource', fn ($r) => $r->where('is_returnable', true))->count(),
            'outstanding' => (clone $this->baseQuery())->whereNull('returned_at')->count(),
            'overdue'     => (clone $this->baseQuery())->whereNull('returned_at')->whereRaw("{$due} < ?", [today()->toDateString()])->count(),
            'returned'    => (clone $this->baseQuery())->whereNotNull('returned_at')->count(),
            // ✅ Program Head clicked "Return to Admin" — waiting for the Admin to confirm
            'sentBack'    => (clone $this->baseQuery())->whereNull('returned_at')->whereNotNull('return_requested_at')->count(),
            // ✅ Still with the Program Head, due back today or tomorrow
            'dueSoon'     => (clone $this->baseQuery())->whereNull('returned_at')->whereNull('return_requested_at')
                                ->whereRaw("{$due} >= ?", [today()->toDateString()])
                                ->whereRaw("{$due} <= ?", [today()->addDay()->toDateString()])->count(),
        ];
    }

    #[Computed]
    public function pendingRequests()
    {
        $q = $this->pendingQuery()
            ->with(['user.department', 'department', 'items.resource.resourceType'])
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
            ->filter(fn ($i) => $i->resource?->is_returnable && $i->due_back_at)
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

    /**
     * ✅ What this request needs from the main stock: resource_id => [name, qty, have].
     *    Same items accept() takes (facilities don't count).
     */
    protected function stockNeeded(ResourceRequest $request): array
    {
        $needed = [];
        foreach ($request->items->whereNotNull('resource_id') as $item) {
            if (! $item->resource || ($item->resource->resourceType?->type_name ?? null) === \App\Models\ResourceType::FACILITY) {
                continue;
            }
            $needed[$item->resource_id] ??= ['name' => $item->item_name, 'qty' => 0, 'have' => (int) $item->resource->quantity_available];
            $needed[$item->resource_id]['qty'] += (int) $item->quantity;
        }

        return $needed;
    }

    /** ✅ Items the main stock doesn't have enough of (empty = Accept will work). */
    public function shortItems(ResourceRequest $request): array
    {
        return collect($this->stockNeeded($request))
            ->filter(fn ($n) => $n['have'] < $n['qty'])
            ->map(fn ($n) => "{$n['name']} (need {$n['qty']}, have {$n['have']})")
            ->values()
            ->all();
    }

    /** ✅ "Chair: 100 available" for each Pcs item, shown when there is enough. */
    public function stockLine(ResourceRequest $request): string
    {
        return collect($this->stockNeeded($request))
            ->map(fn ($n) => "{$n['name']}: {$n['have']} available")
            ->implode(' · ');
    }

    /** ✅ The Accept confirmation spells out what will happen. */
    public function acceptConfirm(ResourceRequest $request): string
    {
        $parts = collect($this->stockNeeded($request))
            ->map(fn ($n) => "{$n['name']} ×{$n['qty']} (main stock {$n['have']} → " . ($n['have'] - $n['qty']) . ')')
            ->implode('; ');

        if (($this->modes[$request->id] ?? 'borrow') === 'assign') {
            return 'Assign to ' . ($request->user->name ?? 'this Program Head') . ': ' . ($parts ?: 'this request')
                . ' — they keep it, no return date?';
        }

        $deadline = $this->deadlines[$request->id] ?? null;
        $by       = $deadline ? ', return by ' . Carbon::parse($deadline)->format('M d, Y') : '';

        return 'Accept for ' . ($request->user->name ?? 'this Program Head') . ': ' . ($parts ?: 'this request') . $by . '?';
    }

    /** ✅ request_id => how many of its items the Program Head sent back that aren't confirmed yet. */
    #[Computed]
    public function sentBackCounts(): array
    {
        return (clone $this->baseQuery())
            ->whereNull('returned_at')
            ->whereNotNull('return_requested_at')
            ->selectRaw('request_id, COUNT(*) as n')
            ->groupBy('request_id')
            ->pluck('n', 'request_id')
            ->map(fn ($n) => (int) $n)
            ->all();
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

    /** ✅ A borrowed item the borrower will keep (e.g. faculty room furniture) → assigned, no return date. */
    public function convertToAssigned(int $itemId): void
    {
        abort_unless(auth()->user()->can('borrowed-items.manage'), 403);

        $item = $this->baseQuery()->whereNull('returned_at')->findOrFail($itemId);

        try {
            $holder = \App\Support\Assignments::assign($item, Auth::user());
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        session()->flash('message', "{$holder->item_name} ×{$holder->quantity} is now assigned to " . ($holder->holder?->name ?? 'the borrower') . ' — kept, no return date. See the Assigned tab.');
        unset($this->items, $this->counts);
    }

    // ============================================================
    // ✅ ACCEPT WITH A RETURN DEADLINE
    // ============================================================
    public function accept(int $id): void
    {
        abort_unless(auth()->user()->can('borrowed-items.manage'), 403);
        abort_unless(
            auth()->user()->canAny(['program-head-requests.approve', 'requests.approve']),
            403
        );

        $request = $this->pendingQuery()->with('items.resource')->findOrFail($id);
        $assign  = ($this->modes[$id] ?? 'borrow') === 'assign';

        // --- Validate the deadline (not needed when the items are assigned) ---
        $earliest = $this->earliestDeadline($request);
        $this->resetErrorBag("deadlines.{$id}");

        if (! $assign) $this->validate(
            ["deadlines.{$id}" => 'required|date|after_or_equal:' . $earliest->format('Y-m-d') . '|before_or_equal:' . today()->addYear()->format('Y-m-d')],
            [
                "deadlines.{$id}.required"        => 'Please set a return deadline.',
                "deadlines.{$id}.date"            => 'Please enter a valid date.',
                "deadlines.{$id}.after_or_equal"  => 'The deadline cannot be before ' . $earliest->format('M d, Y') . ' (the day the items are needed).',
                "deadlines.{$id}.before_or_equal" => 'The deadline must be within one year.',
            ]
        );

        $deadline = $assign ? null : Carbon::parse($this->deadlines[$id])->startOfDay();

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
        $facilityTypeId = DB::table('resource_types')->where('type_name', \App\Models\ResourceType::FACILITY)->value('id');

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

                // ✅ The return deadline goes on every Pcs item of this request (assigned items have none)
                if ($deadline) {
                    RequestItem::where('request_id', $fresh->id)
                        ->whereNotNull('resource_id')
                        ->whereHas('resource', fn ($r) => $r->where('is_returnable', true))
                        ->update(['due_back_at' => $deadline->toDateString()]);
                }

                RequestApproval::create([
                    'request_id'  => $fresh->id,
                    'approver_id' => Auth::id(),
                    'status'      => 'approved',
                    'remarks'     => $deadline ? 'Return deadline: ' . $deadline->format('M d, Y') : 'Assigned — kept, no return date',
                    'approved_at' => now(),
                ]);

                Notification::create([
                    'user_id'    => $fresh->user_id,
                    'request_id' => $fresh->id,
                    'message'    => Auth::user()->name . ' (Admin) approved your request. '
                                  . ($deadline ? 'Please return the borrowed items by ' . $deadline->format('M d, Y') . '.' : 'The items are assigned — kept, with no return date.'),
                    'type'       => 'Gmail',
                    'status'     => 'pending',
                ]);
            });
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        $this->emailRequester($request->id, $deadline);

        unset($this->deadlines[$id], $this->modes[$id], $this->pendingRequests, $this->counts, $this->items);

        // ✅ Endorsed from a student / faculty request → approve that one automatically too
        $note = \App\Support\ForwardedRequests::onAdminApproved(ResourceRequest::find($id));

        // ✅ Assign: the items are kept (no return date) by the Program Head — or by the faculty member it was for
        $holders = [];
        if ($assign) {
            $items = RequestItem::where('request_id', $id)
                ->whereNotNull('resource_id')
                ->whereHas('resource', fn ($r) => $r->where('is_returnable', true))
                ->get();
            foreach ($items as $item) {
                $holders[] = \App\Support\Assignments::assign($item, Auth::user())->holder?->name;
            }
            unset($this->counts);
        }

        session()->flash('message', 'Request approved. ' . ($assign
            ? 'Assigned to ' . (collect($holders)->filter()->unique()->implode(', ') ?: 'the requester') . ' — kept, no return date.'
            : 'Return deadline set to ' . $deadline->format('M d, Y') . '.') . ($note ? ' ' . $note : ''));
    }

    /** E-mail the Program Head, including the return deadline. */
    protected function emailRequester(int $requestId, ?Carbon $deadline): void
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
            ->filter(fn ($i) => $i->resource?->is_returnable)
            ->map(fn ($i) => "{$i->item_name} x{$i->quantity}")
            ->implode(', ');

        $details .= $deadline
            ? ". Please return {$pcs} by " . $deadline->format('M d, Y') . '.'
            : ". {$pcs}: assigned to you — kept, no return date.";

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
        abort_unless(auth()->user()->can('borrowed-items.manage'), 403);
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
        abort_unless(auth()->user()->can('borrowed-items.manage'), 403);
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

        // ✅ Endorsed from a student / faculty request → reject that one too
        $note = \App\Support\ForwardedRequests::onAdminRejected($request->fresh(), $this->rejectReason ?: null, Auth::user());

        unset($this->deadlines[$request->id], $this->pendingRequests, $this->counts);
        $this->cancelReject();

        session()->flash('message', 'Request rejected and the Program Head was notified.' . ($note ? ' ' . $note : ''));
    }

    // ============================================================
    // ✅ OVERDUE → SEND A REMINDER MESSAGE TO THE PROGRAM HEAD
    // ============================================================

    /** When the Admin last reminded the Program Head about this item (or null). */
    public function lastReminder(RequestItem $item): ?Carbon
    {
        return Notification::where('type', 'Overdue')
            ->where('request_id', $item->request_id)
            ->where('message', 'like', "%{$item->item_name} ({$item->quantity} pcs)%")
            ->latest()
            ->first(['created_at'])
            ?->created_at;
    }

    public function sendReminder(int $id): void
    {
        abort_unless(auth()->user()->can('borrowed-items.manage'), 403);
        $due = $this->dueDateSql();

        $item = $this->baseQuery()
            ->select('request_items.*')
            ->selectRaw("{$due} as due_date")
            ->with('request.user')
            ->whereNull('returned_at')
            ->whereRaw("{$due} < ?", [today()->toDateString()])
            ->find($id);

        if (! $item) {
            session()->flash('error', 'This item is no longer overdue.');
            return;
        }

        if ($this->lastReminder($item)?->isToday()) {
            session()->flash('error', 'You already sent a reminder for this item today.');
            return;
        }

        $programHead = $item->request->user;
        if (! $programHead) {
            session()->flash('error', 'The Program Head of this request could not be found.');
            return;
        }

        $dueDate = Carbon::parse($item->due_date);
        $days    = (int) $dueDate->diffInDays(today());
        $dayText = $days . ' ' . str('day')->plural($days);

        // Shows in the Program Head's notification bell
        Notification::create([
            'user_id'    => $programHead->id,
            'request_id' => $item->request_id,
            'message'    => "You have not returned the {$item->item_name} ({$item->quantity} pcs) yet. "
                          . "It was due on {$dueDate->format('M d, Y')} and is now overdue by {$dayText}. "
                          . 'Please return the material to the Admin as soon as possible.',
            'type'       => 'Overdue',
            'status'     => 'pending',
        ]);

        unset($this->items);

        session()->flash('message', "Reminder sent to {$programHead->name} for {$item->item_name} ({$item->quantity} pcs).");
    }

    // ============================================================
    // MARK RETURNED
    // ============================================================
    public function openReturn(int $id): void
    {
        abort_unless(auth()->user()->can('borrowed-items.manage'), 403);
        // ✅ The Admin confirms a return only after the Program Head clicked "Return to Admin"
        $item = $this->baseQuery()->whereNull('returned_at')->find($id);

        if (! $item || ! $item->return_requested_at) {
            session()->flash('error', 'The Program Head has not returned this item yet. You can confirm it once they click "Return to Admin".');
            return;
        }

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
        abort_unless(auth()->user()->can('borrowed-items.manage'), 403);
        $this->validate([
            'returnCondition' => 'required|in:' . implode(',', array_keys(RequestItem::CONDITIONS)),
            'returnNotes'     => 'nullable|string|max:500',
        ]);

        $item = $this->baseQuery()->findOrFail($this->returningId);

        try {
            $message = $this->confirmOne($item, $this->returnCondition, $this->returnNotes ?: null);
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            $this->returningId = null;
            return;
        }

        session()->flash('message', $message);

        $this->returningId = null;
        unset($this->items, $this->counts, $this->sentBackCounts);
    }

    /**
     * ✅ Confirm one returned item: mark it returned, take it off the department,
     *    put Good items back in the main stock (remembering Before → After),
     *    and tell the Program Head. Returns the message for the Admin.
     */
    protected function confirmOne(RequestItem $item, string $condition, ?string $notes): string
    {
        DB::transaction(function () use ($item, $condition, $notes) {
            $item = RequestItem::with('request')->lockForUpdate()->findOrFail($item->id);

            if ($item->returned_at) {
                throw new \RuntimeException('This item was already marked as returned.');
            }

            // ✅ Only after the Program Head sent it back (and so the faculty member returned it first)
            if (! $item->return_requested_at) {
                throw new \RuntimeException('The Program Head has not returned this item yet.');
            }

            $item->update([
                'returned_at'      => now(),
                'return_condition' => $condition,
                'return_notes'     => $notes ?: null,
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
            if ($condition === 'good') {
                // ✅ Remember the stock level before / after, for Stock History's "Before → After"
                $before = (int) DB::table('resources')
                    ->where('id', $item->resource_id)
                    ->lockForUpdate()
                    ->value('quantity_available');

                DB::table('resources')
                    ->where('id', $item->resource_id)
                    ->increment('quantity_available', $item->quantity);

                $item->update([
                    'stock_before_return' => $before,
                    'stock_after_return'  => $before + (int) $item->quantity,
                ]);
            }
        });

        // ✅ Tell the Program Head their return was received (with the condition)
        $conditionText = RequestItem::CONDITIONS[$condition] ?? ucfirst($condition);
        if ($borrowerId = $item->request?->user_id) {
            Notification::create([
                'user_id'    => $borrowerId,
                'request_id' => $item->request_id,
                'message'    => 'The Admin (' . Auth::user()->name . ") confirmed your return of {$item->quantity} pcs of \"{$item->item_name}\" — condition: {$conditionText}."
                              . ($notes ? " Note: {$notes}" : ''),
                'type'       => 'Return',
                'status'     => 'pending',
            ]);
        }

        return $condition === 'good'
            ? "\"{$item->item_name}\" ({$item->quantity} pcs) marked as returned and added back to the main stock."
            : "\"{$item->item_name}\" ({$item->quantity} pcs) marked as returned ({$condition}). It was not added back to stock.";
    }

    /** ✅ Every item of one request the Program Head sent back → confirm all in Good condition. */
    public function confirmAllGood(int $requestId): void
    {
        abort_unless(auth()->user()->can('borrowed-items.manage'), 403);
        $items = $this->baseQuery()
            ->with('request.user')
            ->where('request_id', $requestId)
            ->whereNull('returned_at')
            ->whereNotNull('return_requested_at')
            ->get();

        if ($items->isEmpty()) {
            session()->flash('error', 'There is nothing to confirm for this request.');
            return;
        }

        $done = 0;
        foreach ($items as $item) {
            try {
                $this->confirmOne($item, 'good', null);
                $done++;
            } catch (\RuntimeException $e) {
                // already confirmed meanwhile — skip it
            }
        }

        $who = $items->first()->request?->user?->name ?? 'the Program Head';
        session()->flash('message', "Confirmed {$done} " . str('item')->plural($done) . " from {$who} in Good condition and added them back to the main stock.");

        $this->returningId = null;
        unset($this->items, $this->counts, $this->sentBackCounts);
    }
};
