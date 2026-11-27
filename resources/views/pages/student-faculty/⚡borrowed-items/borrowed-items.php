<?php

use App\Models\Notification;
use App\Models\RequestItem;
use App\Models\User;
use App\Support\ReturnTracking;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Student / Faculty: items I borrowed that must be given back.
 *
 *  - "Not returned" — items still with me, with the return date and any reminder
 *    from my Program Head. I click "Return to Program Head" when I give them back.
 *  - "Returned"     — items my Program Head already confirmed.
 */
new #[Layout('layouts.student-faculty')] class extends Component
{
    use WithPagination;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('borrowed-items.view'), 403);
    }


    public string $filter = 'outstanding'; // outstanding | returned

    public function updatingFilter(): void { $this->resetPage(); }

    /** Returnable items from MY approved requests. */
    protected function myQuery()
    {
        return RequestItem::query()
            ->whereNotNull('due_back_at')
            ->whereHas('request', fn ($q) => $q
                ->where('user_id', Auth::id())
                ->where('status', 'approved'));
    }

    /** ✅ How many assigned items (kept, no return date) I hold. */
    #[Computed]
    public function keptCount(): int
    {
        return \App\Models\RequestItem::assignedNow()->where('holder_id', \Illuminate\Support\Facades\Auth::id())->count();
    }

    #[Computed]
    public function counts(): array
    {
        return [
            'outstanding' => (clone $this->myQuery())->whereNull('returned_at')->count(),
            'overdue'     => (clone $this->myQuery())->whereNull('returned_at')->whereNull('return_requested_at')
                                ->whereDate('due_back_at', '<', today())->count(),
            'returned'    => (clone $this->myQuery())->whereNotNull('returned_at')->count(),
            // ✅ Due today or tomorrow (not sent back yet) — warned before they become overdue
            'due_soon'    => (clone $this->myQuery())->whereNull('returned_at')->whereNull('return_requested_at')
                                ->whereDate('due_back_at', '>=', today())
                                ->whereDate('due_back_at', '<=', today()->addDay())->count(),
        ];
    }

    #[Computed]
    public function items()
    {
        $q = $this->myQuery()->with(['receiver', 'request']);

        $this->filter === 'returned'
            ? $q->whereNotNull('returned_at')->latest('returned_at')
            : $q->whereNull('returned_at')->orderBy('due_back_at');

        return $q->paginate(15);
    }

    /** ✅ The Program Head(s) of my department — the people I hand the items back to. */
    #[Computed]
    public function programHeads()
    {
        return User::role('program head')
            ->where('department_id', Auth::user()->department_id)
            ->with('department')
            ->orderBy('name')
            ->get();
    }

    /** ✅ request_id => how many of its items I can still send back (for "Return all"). */
    #[Computed]
    public function returnableCounts(): array
    {
        return (clone $this->myQuery())
            ->whereNull('returned_at')
            ->whereNull('return_requested_at')
            ->selectRaw('request_id, COUNT(*) as n')
            ->groupBy('request_id')
            ->pluck('n', 'request_id')
            ->map(fn ($n) => (int) $n)
            ->all();
    }

    /** One bell notice to my department's Program Head(s). */
    protected function tellProgramHeads(int $requestId, string $message, string $type = 'Return'): void
    {
        foreach ($this->programHeads as $programHead) {
            Notification::create([
                'user_id'    => $programHead->id,
                'request_id' => $requestId,
                'message'    => $message,
                'type'       => $type,
                'status'     => 'pending',
            ]);
        }
    }

    /** "Due in 2 days", "Overdue by 3 days", "Waiting for Program Head to confirm", "Returned (Good)". */
    public function statusLabel(RequestItem $item): string
    {
        if (! $item->returned_at && $item->return_requested_at) {
            return 'Waiting for Program Head to confirm';
        }

        return ReturnTracking::label($item);
    }

    /** ✅ Everything one row / card needs (used by both the phone cards and the table). */
    public function rowInfo(RequestItem $item): array
    {
        $isOverdue = $item->isOverdue() && ! $item->return_requested_at;

        return [
            'label'     => $this->statusLabel($item),
            'isOverdue' => $isOverdue,
            'tone'      => match (true) {
                $item->returned_at && $item->return_condition === 'good' => 'returned',
                (bool) $item->returned_at                                 => 'damaged',
                (bool) $item->return_requested_at                         => 'waiting',
                $isOverdue                                                => 'overdue',
                $item->due_back_at->lte(today()->addDay())                => 'soon',
                default                                                   => 'borrowed',
            },
            'reminder'  => $isOverdue ? $this->reminder($item) : null,
            // "Return all" only when this request still has 2+ items I can send back
            'returnAll' => ! $item->returned_at && ! $item->return_requested_at
                ? (int) ($this->returnableCounts[$item->request_id] ?? 0)
                : 0,
        ];
    }

    /** The Program Head's latest "Overdue" reminder about this item (or null). */
    public function reminder(RequestItem $item): ?Notification
    {
        return Notification::where('user_id', Auth::id())
            ->where('type', 'Overdue')
            ->where('request_id', $item->request_id)
            ->where('message', 'like', "%{$item->item_name} (x{$item->quantity})%")
            ->latest()
            ->first();
    }

    /** I'm giving the item back — tell my Program Head to check and confirm it. */
    public function requestReturn(int $id): void
    {
        abort_unless(auth()->user()->can('borrowed-items.manage'), 403);
        $item = $this->myQuery()->whereNull('returned_at')->findOrFail($id);

        if ($item->return_requested_at) {
            session()->flash('error', 'You already sent this return. Please wait for your Program Head to confirm it.');
            return;
        }

        $item->forceFill(['return_requested_at' => now()])->save();

        $me = Auth::user();
        $this->tellProgramHeads($item->request_id,
            "{$me->name} is returning {$item->item_name} (x{$item->quantity}). Please check the item and confirm the return in Borrowed Items.");

        session()->flash('message', "Return sent for {$item->item_name} (x{$item->quantity}). Your Program Head will confirm it when they receive the item.");
        unset($this->items, $this->counts, $this->returnableCounts);
    }

    /** ✅ Give back every item of one request that is still with me — one click, one notice. */
    public function requestReturnAll(int $requestId): void
    {
        abort_unless(auth()->user()->can('borrowed-items.manage'), 403);
        $items = $this->myQuery()
            ->where('request_id', $requestId)
            ->whereNull('returned_at')
            ->whereNull('return_requested_at')
            ->get();

        if ($items->isEmpty()) {
            session()->flash('error', 'There is nothing left to return from this request.');
            return;
        }

        foreach ($items as $item) {
            $item->forceFill(['return_requested_at' => now()])->save();
        }

        $list = $items->map(fn ($i) => "{$i->item_name} (x{$i->quantity})")->implode(', ');
        $me   = Auth::user();

        $this->tellProgramHeads($requestId,
            "{$me->name} is returning {$list}. Please check the items and confirm the return in Borrowed Items.");

        session()->flash('message', "Return sent for {$list}. Your Program Head will confirm when they receive the items.");
        unset($this->items, $this->counts, $this->returnableCounts);
    }

    /** Undo a return sent by mistake (only before the Program Head confirms). */
    public function cancelRequestReturn(int $id): void
    {
        abort_unless(auth()->user()->can('borrowed-items.manage'), 403);
        $item = $this->myQuery()->whereNull('returned_at')->findOrFail($id);

        if (! $item->return_requested_at) {
            return; // nothing to cancel
        }

        $item->forceFill(['return_requested_at' => null])->save();

        // ✅ The Program Head was told "X is returning…" — tell them it's off
        $this->tellProgramHeads($item->request_id,
            Auth::user()->name . " cancelled the return of {$item->item_name} (x{$item->quantity}). The item is still with them — no need to check it.");

        session()->flash('message', "Return for {$item->item_name} was cancelled. Your Program Head was told.");
        unset($this->items, $this->counts, $this->returnableCounts);
    }
};
