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
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Program Head: borrowed items.
 *
 *  "Students & Faculty" — returnable items the department's students and faculty
 *                          borrowed. The Program Head marks them returned.
 *  "My borrowed items"  — Pcs items the Program Head borrowed FROM THE ADMIN.
 *                          The Program Head clicks "Return to Admin";
 *                          the Admin confirms it on the admin Borrowed Items page.
 */
new #[Layout('layouts.coordinator')] class extends Component
{
    use WithPagination;

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
            'overdue'  => $q->whereNull('returned_at')->whereDate('due_back_at', '<', today())->orderBy('due_back_at'),
            'returned' => $q->whereNotNull('returned_at')->latest('returned_at'),
            default    => $q->whereNull('returned_at')->orderBy('due_back_at'),
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

        try {
            ReturnTracking::markReturned($item, $this->returnCondition, $this->returnNotes, Auth::user());
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            $this->returningId = null;
            return;
        }

        session()->flash('message', $this->returnCondition === 'good'
            ? "\"{$item->item_name}\" marked as returned and added back to your department's stock."
            : "\"{$item->item_name}\" marked as returned ({$this->returnCondition}). It was not added back to stock.");

        $this->returningId = null;
        unset($this->items, $this->counts);
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

    /** Pcs items from MY approved requests to the Admin. */
    protected function myItemsQuery()
    {
        return RequestItem::query()
            ->whereNotNull('request_items.resource_id')
            ->whereHas('resource', fn ($r) => $r->where('unit', 'Pcs'))
            ->whereHas('request', fn ($q) => $q
                ->where('user_id', Auth::id())
                ->where('status', 'approved'));
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
        $item = $this->myItemsQuery()
            ->whereNull('returned_at')
            ->findOrFail($id);

        if ($item->return_requested_at) {
            session()->flash('error', 'You already sent this return. Please wait for the Admin to confirm it.');
            return;
        }

        $item->forceFill(['return_requested_at' => now()])->save();

        // Tell every Admin
        $who = Auth::user()->name;
        foreach (User::role('admin')->get() as $admin) {
            Notification::create([
                'user_id'    => $admin->id,
                'request_id' => $item->request_id,
                'message'    => "{$who} (Program Head) is returning {$item->quantity} pcs of \"{$item->item_name}\". Please check and mark it returned in Borrowed Items.",
                'type'       => 'Gmail',
                'status'     => 'pending',
            ]);
        }

        session()->flash('message', "Return sent for \"{$item->item_name}\" ({$item->quantity} pcs). The Admin will confirm it when the items are received.");
        unset($this->myItems, $this->counts);
    }

    /** Undo a return that was sent by mistake (only before the Admin confirms). */
    public function cancelRequestReturn(int $id): void
    {
        $item = $this->myItemsQuery()
            ->whereNull('returned_at')
            ->findOrFail($id);

        $item->forceFill(['return_requested_at' => null])->save();

        session()->flash('message', "Return for \"{$item->item_name}\" was cancelled.");
        unset($this->myItems, $this->counts);
    }
};
