<?php

use App\Support\ReturnTracking;
use App\Models\RequestType;
use App\Models\Notification;
use App\Models\Request as ResourceRequest;
use App\Models\User;
use App\Notifications\RequestStatusNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\RequestItem;
use App\Support\TimeSlots;

new #[Layout('layouts.coordinator')] class extends Component
{
    use WithPagination;

    // ✅ pending | approved | rejected
    #[Url]
    public string $tab = 'pending';

    // ✅ Search by faculty name or room
    public string $search = '';

    // ✅ NEW: Reject dialog state
    public ?int   $rejectingRequestId = null;
    public string $rejectReason       = '';

    protected function scopedQuery()
    {
        return ResourceRequest::where('request_type_id', RequestType::FACILITY)
            ->whereHas('user', function ($q) {
                $q->where('department_id', Auth::user()->department_id)
                  ->whereDoesntHave('roles', function ($role) {
                      $role->where('name', 'program head');
                  });
            });
    }

    public function mount(): void
    {
        abort_unless(auth()->user()->can('requests.view-department'), 403);
        if (! in_array($this->tab, ['pending', 'approved', 'rejected'], true)) {
            $this->tab = 'pending';
        }
    }

    public function updatingTab(): void    { $this->resetPage(); $this->rejectingRequestId = null; }
    public function updatingSearch(): void { $this->resetPage(); }

    /** One tab of reservations, narrowed by the search box. */
    protected function tabQuery(string $tab)
    {
        $q = $this->scopedQuery()->where('status', $tab);

        if (trim($this->search) !== '') {
            $term = '%' . trim($this->search) . '%';
            $q->where(fn ($w) => $w
                ->whereHas('user', fn ($u) => $u->where('name', 'like', $term))
                ->orWhereHas('items', fn ($i) => $i->whereNull('resource_id')->where('item_name', 'like', $term)));
        }

        return $q;
    }

    #[Computed]
    public function counts(): array
    {
        return [
            'pending'  => $this->tabQuery('pending')->count(),
            'approved' => $this->tabQuery('approved')->count(),
            'rejected' => $this->tabQuery('rejected')->count(),
        ];
    }

    #[Computed]
    public function reservations()
    {
        $q = $this->tabQuery($this->tab)->with(['user.department', 'items', 'approvals']);

        // ✅ Pending: the soonest booking first. Done tabs: most recent first.
        $this->tab === 'pending'
            ? $q->orderBy(RequestItem::select('request_date')->whereColumn('request_items.request_id', 'requests.id')->whereNull('resource_id')->limit(1))
            : $q->latest('updated_at');

        return $q->paginate(10);
    }

    /** ✅ Why it was rejected (latest rejection note), if any. */
    public function rejectionReason($request): ?string
    {
        return $request->approvals->where('status', 'rejected')->sortByDesc('id')->first()?->remarks;
    }

    /**
     * ✅ Is the room free for this request?
     *    ['booked' => [approved bookings that overlap], 'competing' => [other pending requests for the same slot]]
     *    The request's own endorsed copy never counts.
     */
    public function clashes($request): array
    {
        $item = $request->items->firstWhere('resource_id', null);

        if (! $item || ! $item->start_time || ! $item->end_time || ! $item->request_date) {
            return ['booked' => [], 'competing' => []];
        }

        $date  = Carbon::parse($item->request_date)->toDateString();
        $start = Carbon::parse($item->start_time)->format('H:i');
        $end   = Carbon::parse($item->end_time)->format('H:i');

        $booked = collect(TimeSlots::approvedBookings($item->item_name, $date, $request->id))
            ->filter(fn ($b) => $start < $b['end'] && $end > $b['start'])
            ->map(fn ($b) => "{$b['label']} ({$b['department']})")
            ->values()
            ->all();

        $competing = RequestItem::whereNull('resource_id')
            ->where('item_name', $item->item_name)
            ->whereDate('request_date', $date)
            ->where('start_time', '<', $item->end_time)
            ->where('end_time', '>', $item->start_time)
            ->where('request_id', '!=', $request->id)
            ->whereHas('request', fn ($q) => $q
                ->where('status', 'pending')
                // endorsed copies are the same request as the faculty one — don't list them twice
                ->whereNull('forwarded_from_request_id'))
            ->with('request.user')
            ->get()
            ->map(fn ($i) => ($i->request?->user?->name ?? 'Someone') . ' (' . Carbon::parse($i->start_time)->format('g:i A') . '–' . Carbon::parse($i->end_time)->format('g:i A') . ')')
            ->unique()
            ->values()
            ->all();

        return ['booked' => $booked, 'competing' => $competing];
    }

    /** ✅ Program Head calendar, opened on this request's day with its room selected. */
    public function calendarUrl($request): ?string
    {
        $item = $request->items->firstWhere('resource_id', null);

        return $item && $item->request_date
            ? route('coordinator.calendar', ['date' => Carbon::parse($item->request_date)->toDateString(), 'room' => $item->item_name])
            : null;
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

                // ✅ Returnable items (chairs, projectors…) get a "return by" date
                ReturnTracking::assignDueDates($request);

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
    // ============================================================
    // ✅ FORWARD TO ADMIN (one click — no form)
    // ============================================================
    public function forwardToAdmin(int $id): void
    {
        abort_unless(auth()->user()->can('program-head-requests.create'), 403);

        $request = $this->scopedQuery()->with(['user', 'items.resource'])->findOrFail($id);

        [$ok, $message] = \App\Support\ForwardedRequests::forward($request, Auth::user());

        session()->flash($ok ? 'message' : 'error', $message);
        unset($this->reservations, $this->counts);
    }

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

    /**
     * Is there an APPROVED booking that overlaps this slot?
     *
     * ✅ FIX: The Program Head's OWN approved booking (the one they sent to
     * Admin for this same student/faculty slot) is NOT counted as a conflict.
     * We skip an approved booking when:
     *   - it was made by a Program Head of THIS department, AND
     *   - it fully covers the student's time slot.
     * Any other overlap (another student, another department, a partial
     * overlap) is still blocked.
     */
    protected function facilityHasConflict(
        string $facilityName,
        string $date,
        string $startTime,
        string $endTime,
        int $excludeRequestId
    ): bool {
        $departmentId  = Auth::user()->department_id;
        $userMorphType = (new User)->getMorphClass();

        return DB::table('request_items as ri')
            ->join('requests as req', 'req.id', '=', 'ri.request_id')
            ->where('req.status', 'approved')
            ->where('req.id', '!=', $excludeRequestId)
            ->whereNull('ri.resource_id')
            ->where('ri.item_name', $facilityName)
            ->whereDate('ri.request_date', $date)
            ->where('ri.start_time', '<', $endTime)
            ->where('ri.end_time', '>', $startTime)
            // ✅ Ignore the Program Head's own booking that covers this slot
            ->whereNot(function ($q) use ($departmentId, $userMorphType, $startTime, $endTime) {
                $q->where('ri.start_time', '<=', $startTime)
                  ->where('ri.end_time', '>=', $endTime)
                  ->whereExists(function ($sub) use ($departmentId, $userMorphType) {
                      $sub->select(DB::raw(1))
                          ->from('users as u')
                          ->join('model_has_roles as mhr', function ($join) use ($userMorphType) {
                              $join->on('mhr.model_id', '=', 'u.id')
                                   ->where('mhr.model_type', $userMorphType);
                          })
                          ->join('roles as r', 'r.id', '=', 'mhr.role_id')
                          ->whereColumn('u.id', 'req.user_id')
                          ->where('r.name', 'program head')
                          ->where('u.department_id', $departmentId);
                  });
            })
            ->exists();
    }
};
