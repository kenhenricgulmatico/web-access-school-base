<?php

use App\Models\RequestItem;
use App\Support\ReturnTracking;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Program Head: track borrowed (returnable) items of the department and mark them returned.
 */
new #[Layout('layouts.coordinator')] class extends Component
{
    use WithPagination;

    public string $filter = 'outstanding'; // outstanding | overdue | returned
    public string $search = '';

    // "Mark returned" form
    public ?int   $returningId     = null;
    public string $returnCondition = 'good';
    public string $returnNotes     = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('requests.view-department'), 403);
    }

    public function updatingFilter(): void { $this->resetPage(); }
    public function updatingSearch(): void { $this->resetPage(); }

    /** Returnable items borrowed by this department's students and faculty. */
    protected function baseQuery()
    {
        return RequestItem::query()
            ->whereNotNull('due_back_at')
            ->whereHas('request', fn ($q) => $q
                ->where('department_id', Auth::user()->department_id)
                ->where('status', 'approved'));
    }

    #[Computed]
    public function counts(): array
    {
        return [
            'outstanding' => (clone $this->baseQuery())->whereNull('returned_at')->count(),
            'overdue'     => (clone $this->baseQuery())->whereNull('returned_at')->whereDate('due_back_at', '<', today())->count(),
            'returned'    => (clone $this->baseQuery())->whereNotNull('returned_at')->count(),
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
};
