<?php

use App\Models\Notification;
use App\Models\RequestItem;
use App\Models\RequestType;
use App\Models\User;
use App\Support\ReturnTracking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Program Head: borrowed items.
 *
 *  "Faculty"           — Material items the department's faculty borrowed. The Program Head confirms
 *                         their return. ✅ If an item came from the Admin (endorsed request), the same row
 *                         then shows "Return to Admin".
 *  "My borrowed items" — Material items the Program Head borrowed FROM THE ADMIN for themselves.
 *                         The Program Head clicks "Return to Admin"; the Admin confirms it.
 */
new #[Layout('layouts.coordinator')] class extends Component
{
    use WithPagination;

    // ✅ #[Url] so the Admin's "Overdue" reminder can open ?view=mine directly
    #[Url]
    public string $view   = 'students';    // students | mine
    public string $filter = 'outstanding'; // outstanding | overdue | returned
    public string $search = '';

    // "Mark returned" form (students & faculty)
    public ?int   $returningId     = null;
    public string $returnCondition = 'good';
    public string $returnNotes     = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('requests.view-department'), 403);
    }

    public function updatingView(): void   { $this->resetPage(); $this->returningId = null; }
    public function updatingFilter(): void { $this->resetPage(); }
    public function updatingSearch(): void { $this->resetPage(); }

    // ============================================================
    // STUDENTS & FACULTY
    // ============================================================

    /**
     * Returnable items borrowed by this department's students and faculty.
     * ✅ Program Head requests (items borrowed from the Admin) are NOT here.
     */
    protected function baseQuery()
    {
        return RequestItem::query()
            ->whereNotNull('due_back_at')
            ->whereHas('request', fn ($q) => $q
                ->where('department_id', Auth::user()->department_id)
                ->where('status', 'approved')
                ->whereHas('user', fn ($u) => $u
                    ->whereDoesntHave('roles', fn ($r) => $r->where('name', 'program head'))));
    }

    #[Computed]
    public function counts(): array
    {
        return [
            'outstanding' => (clone $this->baseQuery())->whereNull('returned_at')->count(),
            'overdue'     => (clone $this->baseQuery())->whereNull('returned_at')->whereDate('due_back_at', '<', today())->count(),
            'returned'    => (clone $this->baseQuery())->whereNotNull('returned_at')->count(),
            'mine'        => (clone $this->myItemsQuery())->whereNull('returned_at')->count(),
        ];
    }

    #[Computed]
    public function items()
    {
        $q = $this->baseQuery()->with(['request.user', 'receiver']);

        match ($this->filter) {
            // ✅ Items the student/faculty already sent back are listed first
            'overdue'  => $q->whereNull('returned_at')->whereDate('due_back_at', '<', today())
                            ->orderByRaw('return_requested_at IS NULL')->orderBy('due_back_at'),
            'returned' => $q->whereNotNull('returned_at')->latest('returned_at'),
            default    => $q->whereNull('returned_at')
                            ->orderByRaw('return_requested_at IS NULL')->orderBy('due_back_at'),
        };

        if ($this->search !== '') {
            $term = '%' . $this->search . '%';
            $q->where(fn ($w) => $w
                ->where('item_name', 'like', $term)
                ->orWhereHas('request.user', fn ($u) => $u->where('name', 'like', $term)));
        }

        return $q->paginate(15);
    }

    public function openReturn(int $id): void
    {
        abort_unless(auth()->user()->can('requests.approve'), 403);

        // ✅ Only after the faculty member clicked "Return to Program Head"
        $item = $this->baseQuery()->whereNull('returned_at')->with('request.user')->find($id);

        if (! $item || ! $item->return_requested_at) {
            $who = $item?->request?->user?->name ?? 'The faculty member';
            session()->flash('error', "{$who} has not returned this item yet. You can confirm it once they click \"Return to Program Head\".");
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
        abort_unless(auth()->user()->can('requests.approve'), 403);

        $this->validate([
            'returnCondition' => 'required|in:' . implode(',', array_keys(RequestItem::CONDITIONS)),
            'returnNotes'     => 'nullable|string|max:500',
        ]);

        $item = $this->baseQuery()->findOrFail($this->returningId);

        if (! $item->returned_at && ! $item->return_requested_at) {
            session()->flash('error', 'The faculty member has not returned this item yet.');
            $this->returningId = null;
            return;
        }

        try {
            ReturnTracking::markReturned($item, $this->returnCondition, $this->returnNotes, Auth::user());
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            $this->returningId = null;
            return;
        }

        // ✅ Item came from the Admin (endorsed request) → send it straight back to the Admin, no second click
        $adminItem = ReturnTracking::adminItemFor($item);

        if ($adminItem && ! $adminItem->returned_at && ! $adminItem->return_requested_at) {
            $condition = RequestItem::CONDITIONS[$this->returnCondition] ?? ucfirst($this->returnCondition);
            $this->sendToAdmin($adminItem, "Returned by {$item->request?->user?->name} in {$condition} condition"
                . ($this->returnNotes ? " — {$this->returnNotes}" : '') . '.');

            session()->flash('message', "\"{$item->item_name}\" returned by the faculty member and sent back to the Admin. The Admin will confirm it when they receive it.");
        } else {
            session()->flash('message', $this->returnCondition === 'good'
                ? "\"{$item->item_name}\" marked as returned and added back to your department's stock."
                : "\"{$item->item_name}\" marked as returned ({$this->returnCondition}). It was not added back to stock.");
        }

        $this->returningId = null;
        unset($this->items, $this->counts, $this->readyForAdminCount, $this->myItems, $this->myOverdueCount);
    }

    /** Mark a borrowed-from-Admin item as "on its way back" and tell every Admin. */
    protected function sendToAdmin(RequestItem $adminItem, ?string $note = null): void
    {
        $adminItem->forceFill(['return_requested_at' => now()])->save();

        $who = Auth::user()->name;
        foreach (User::role('admin')->get() as $admin) {
            Notification::create([
                'user_id'    => $admin->id,
                'request_id' => $adminItem->request_id,
                'message'    => "{$who} (Program Head) is returning {$adminItem->quantity} pcs of \"{$adminItem->item_name}\". Please check and confirm it in Borrowed Items."
                              . ($note ? " {$note}" : ''),
                'type'       => 'Gmail',
                'status'     => 'pending',
            ]);
        }
    }

    // ============================================================
    // ✅ OVERDUE → SEND A REMINDER MESSAGE TO THE STUDENT / FACULTY
    // ============================================================

    /** When I last reminded the borrower about this item (or null). */
    public function studentReminder(RequestItem $item): ?Carbon
    {
        return Notification::where('type', 'Overdue')
            ->where('request_id', $item->request_id)
            ->where('message', 'like', "%{$item->item_name} (x{$item->quantity})%")
            ->latest()
            ->first(['created_at'])
            ?->created_at;
    }

    public function sendReminder(int $id): void
    {
        abort_unless(auth()->user()->can('requests.approve'), 403);

        $item = $this->baseQuery()
            ->with('request.user')
            ->whereNull('returned_at')
            ->whereDate('due_back_at', '<', today())
            ->find($id);

        if (! $item) {
            session()->flash('error', 'This item is no longer overdue.');
            return;
        }

        if ($this->studentReminder($item)?->isToday()) {
            session()->flash('error', 'You already sent a reminder for this item today.');
            return;
        }

        $borrower = $item->request->user;
        if (! $borrower) {
            session()->flash('error', 'The borrower of this item could not be found.');
            return;
        }

        $days    = (int) $item->due_back_at->diffInDays(today());
        $dayText = $days . ' ' . str('day')->plural($days);

        // Shows in the student / faculty notification bell
        Notification::create([
            'user_id'    => $borrower->id,
            'request_id' => $item->request_id,
            'message'    => "You have not returned the {$item->item_name} (x{$item->quantity}) yet. "
                          . "It was due on {$item->due_back_at->format('M d, Y')} and is now overdue by {$dayText}. "
                          . 'Please return it to your Program Head as soon as possible.',
            'type'       => 'Overdue',
            'status'     => 'pending',
        ]);

        unset($this->items);

        session()->flash('message', "Reminder sent to {$borrower->name} for {$item->item_name} (x{$item->quantity}).");
    }

    // ============================================================
    // ✅ MY BORROWED ITEMS (from the Admin)
    // ============================================================

    /** SQL for the "return by" date (the Admin's deadline, or a fallback for older approvals). */
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

    /** ALL Material items from my approved requests to the Admin (incl. ones endorsed for faculty). */
    protected function ownItemsQuery()
    {
        return RequestItem::query()
            ->whereNotNull('request_items.resource_id')
            ->whereHas('resource', fn ($r) => $r->where('is_returnable', true))
            ->whereHas('request', fn ($q) => $q
                ->where('user_id', Auth::id())
                ->where('status', 'approved'));
    }

    /**
     * "My borrowed items" tab: only what I borrowed FOR MYSELF.
     * ✅ Items endorsed for a faculty member are handled in the Faculty tab instead.
     */
    protected function myItemsQuery()
    {
        return $this->ownItemsQuery()
            ->whereHas('request', fn ($q) => $q->whereNull('forwarded_from_request_id'));
    }

    /** ✅ Faculty gave these back to me, and they came from the Admin → ready for "Return to Admin". */
    #[Computed]
    public function readyForAdminCount(): int
    {
        return (clone $this->ownItemsQuery())
            ->whereNull('returned_at')
            ->whereNull('return_requested_at')
            ->whereHas('request', fn ($q) => $q->whereNotNull('forwarded_from_request_id'))
            ->get()
            ->filter(fn ($i) => ReturnTracking::facultyItemFor($i)?->returned_at !== null)
            ->count();
    }

    #[Computed]
    public function myItems()
    {
        return $this->myItemsQuery()
            ->select('request_items.*')
            ->selectRaw($this->dueDateSql() . ' as due_date')
            ->with(['receiver'])
            ->orderByRaw('returned_at IS NOT NULL')   // not returned first
            ->orderBy('due_date')
            ->paginate(15);
    }

    /** ✅ My overdue items the Admin is still waiting for (not returned, not sent back yet). */
    #[Computed]
    public function myOverdueCount(): int
    {
        return (clone $this->myItemsQuery())
            ->whereNull('returned_at')
            ->whereNull('return_requested_at')
            ->whereRaw($this->dueDateSql() . ' < ?', [today()->toDateString()])
            ->count();
    }

    /** ✅ The Admin's latest "Overdue" reminder about this item (or null). */
    public function adminReminder(RequestItem $item): ?Notification
    {
        return Notification::where('user_id', Auth::id())
            ->where('type', 'Overdue')
            ->where('request_id', $item->request_id)
            ->where('message', 'like', "%{$item->item_name} ({$item->quantity} pcs)%")
            ->latest()
            ->first();
    }

    /** "Due in 2 days", "Overdue by 3 days", "Waiting for Admin to confirm", "Returned (Good)". */
    public function myStatusLabel(RequestItem $item): string
    {
        if ($item->returned_at) {
            return 'Returned (' . (RequestItem::CONDITIONS[$item->return_condition] ?? ucfirst((string) $item->return_condition)) . ')';
        }

        if ($item->return_requested_at) {
            return 'Waiting for Admin to confirm';
        }

        $days = (int) today()->diffInDays(Carbon::parse($item->due_date), false);

        return match (true) {
            $days < 0   => 'Overdue by ' . abs($days) . ' ' . str('day')->plural(abs($days)),
            $days === 0 => 'Due today',
            default     => 'Due in ' . $days . ' ' . str('day')->plural($days),
        };
    }

    /** Program Head says: "I'm returning these to the Admin." */
    public function requestReturn(int $id): void
    {
        $item = $this->ownItemsQuery()
            ->whereNull('returned_at')
            ->findOrFail($id);

        if ($item->return_requested_at) {
            session()->flash('error', 'You already sent this return. Please wait for the Admin to confirm it.');
            return;
        }

        // ✅ Borrowed for a faculty member → they must return it to you first
        if (ReturnTracking::stillWithFaculty($item)) {
            $faculty = ReturnTracking::facultyItemFor($item)?->request?->user?->name ?? 'The faculty member';
            session()->flash('error', "{$faculty} still has the {$item->item_name}. Confirm their return in the Faculty tab first, then return it to the Admin.");
            return;
        }

        $this->sendToAdmin($item);

        session()->flash('message', "Return sent for \"{$item->item_name}\" ({$item->quantity} pcs). The Admin will confirm it when the items are received.");
        unset($this->myItems, $this->items, $this->counts, $this->myOverdueCount, $this->readyForAdminCount);
    }

    /** Undo a return that was sent by mistake (only before the Admin confirms). */
    public function cancelRequestReturn(int $id): void
    {
        $item = $this->ownItemsQuery()
            ->whereNull('returned_at')
            ->findOrFail($id);

        $item->forceFill(['return_requested_at' => null])->save();

        session()->flash('message', "Return for \"{$item->item_name}\" was cancelled.");
        unset($this->myItems, $this->items, $this->counts, $this->myOverdueCount, $this->readyForAdminCount);
    }
};
