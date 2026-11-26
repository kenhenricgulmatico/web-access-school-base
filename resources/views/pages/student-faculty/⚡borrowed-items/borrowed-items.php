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

    #[Computed]
    public function counts(): array
    {
        return [
            'outstanding' => (clone $this->myQuery())->whereNull('returned_at')->count(),
            'overdue'     => (clone $this->myQuery())->whereNull('returned_at')->whereNull('return_requested_at')
                                ->whereDate('due_back_at', '<', today())->count(),
            'returned'    => (clone $this->myQuery())->whereNotNull('returned_at')->count(),
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

    /** "Due in 2 days", "Overdue by 3 days", "Waiting for Program Head to confirm", "Returned (Good)". */
    public function statusLabel(RequestItem $item): string
    {
        if (! $item->returned_at && $item->return_requested_at) {
            return 'Waiting for Program Head to confirm';
        }

        return ReturnTracking::label($item);
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
        $item = $this->myQuery()->whereNull('returned_at')->findOrFail($id);

        if ($item->return_requested_at) {
            session()->flash('error', 'You already sent this return. Please wait for your Program Head to confirm it.');
            return;
        }

        $item->forceFill(['return_requested_at' => now()])->save();

        $me = Auth::user();
        $programHeads = User::role('program head')
            ->where('department_id', $me->department_id)
            ->get();

        foreach ($programHeads as $programHead) {
            Notification::create([
                'user_id'    => $programHead->id,
                'request_id' => $item->request_id,
                'message'    => "{$me->name} is returning {$item->item_name} (x{$item->quantity}). Please check the item and confirm the return in Borrowed Items.",
                'type'       => 'Return',
                'status'     => 'pending',
            ]);
        }

        session()->flash('message', "Return sent for {$item->item_name} (x{$item->quantity}). Your Program Head will confirm it when they receive the item.");
        unset($this->items, $this->counts);
    }

    /** Undo a return sent by mistake (only before the Program Head confirms). */
    public function cancelRequestReturn(int $id): void
    {
        $item = $this->myQuery()->whereNull('returned_at')->findOrFail($id);

        $item->forceFill(['return_requested_at' => null])->save();

        session()->flash('message', "Return for {$item->item_name} was cancelled.");
        unset($this->items, $this->counts);
    }
};
