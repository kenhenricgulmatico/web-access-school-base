<?php

use App\Models\RequestItem;
use App\Models\User;
use App\Support\Assignments;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * ✅ Assigned items — kept, no return date (e.g. a table and chair for the faculty room).
 *    scope = admin       → everyone's; hand over, or take back into the main stock
 *            department  → my department's; hand over, or take back into the department stock
 *            me          → what I keep (read only)
 */
new class extends Component
{
    use WithPagination;

    public string $scope = 'me';

    public string $search = '';
    #[Url(as: 'holder', except: '')]
    public $holderId = '';

    // Hand over form
    public ?int $handingId = null;
    public $toUserId       = '';
    public string $handNote = '';

    // Take back form
    public ?int $returningId     = null;
    public string $returnCondition = 'good';
    public string $returnNotes     = '';

    public function mount(string $scope = 'me'): void
    {
        $this->scope = in_array($scope, ['admin', 'department', 'me'], true) ? $scope : 'me';
        abort_unless(auth()->user()->can('borrowed-items.view'), 403);
    }

    public function updatingSearch(): void { $this->resetPage(); }

    protected function query()
    {
        $me = Auth::user();

        return RequestItem::assignedNow()
            ->whereHas('resource', fn ($r) => $r->where('is_returnable', true))
            ->when($this->scope === 'department', fn ($q) => $q->whereHas('holder', fn ($h) => $h->where('department_id', $me->department_id)))
            ->when($this->scope === 'me', fn ($q) => $q->where('holder_id', $me->id))
            ->when($this->scope !== 'me' && $this->holderId !== '' && $this->holderId !== null, fn ($q) => $q->where('holder_id', (int) $this->holderId))
            ->when($this->search !== '' && $this->scope !== 'me', fn ($q) => $q->where(fn ($w) => $w
                ->where('item_name', 'like', '%' . $this->search . '%')
                ->orWhereHas('holder', fn ($h) => $h->where('name', 'like', '%' . $this->search . '%'))
                ->orWhereHas('holder.department', fn ($d) => $d->where('department_name', 'like', '%' . $this->search . '%'))));
    }

    #[Computed]
    public function items()
    {
        return $this->query()
            ->with(['holder.department', 'holder.roles', 'request.user', 'handovers.from', 'handovers.to'])
            ->orderBy('assigned_at', 'desc')
            ->paginate(15);
    }

    #[Computed]
    public function holderName(): ?string
    {
        return $this->holderId !== '' ? User::whereKey((int) $this->holderId)->value('name') : null;
    }

    public function canManage(): bool
    {
        return $this->scope !== 'me' && auth()->user()->can('borrowed-items.manage');
    }

    /** The Admin takes back what came from the main stock; the Program Head what came from the department stock. */
    public function canTakeBack(RequestItem $item): bool
    {
        if (! $this->canManage()) {
            return false;
        }

        return Assignments::returnsTo($item) === ($this->scope === 'admin' ? 'main' : 'department');
    }

    protected function find(int $id): RequestItem
    {
        return $this->query()->with('holder')->findOrFail($id);
    }

    /* ------------------------------ hand over ------------------------------ */

    public function openHandOver(int $id): void
    {
        abort_unless($this->canManage(), 403);
        $this->cancelForms();
        $this->handingId = $this->find($id)->id;
    }

    #[Computed]
    public function recipients()
    {
        return $this->handingId ? Assignments::recipients($this->find($this->handingId)) : collect();
    }

    public function saveHandOver(): void
    {
        abort_unless($this->canManage(), 403);

        $this->validate([
            'toUserId' => 'required|integer',
            'handNote' => 'nullable|string|max:255',
        ], ['toUserId.required' => 'Choose who receives it.']);

        $item = $this->find($this->handingId);
        $to   = User::findOrFail((int) $this->toUserId);

        try {
            Assignments::handOver($item, $to, Auth::user(), $this->handNote ?: null);
        } catch (\RuntimeException $e) {
            $this->addError('toUserId', $e->getMessage());
            return;
        }

        session()->flash('message', "{$item->item_name} ×{$item->quantity} handed over to {$to->name}.");
        $this->cancelForms();
        unset($this->items);
    }

    /* ------------------------------ take back ------------------------------ */

    public function openReturn(int $id): void
    {
        abort_unless($this->canManage(), 403);
        $item = $this->find($id);
        abort_unless($this->canTakeBack($item), 403);

        $this->cancelForms();
        $this->returningId = $item->id;
    }

    public function saveReturn(): void
    {
        abort_unless($this->canManage(), 403);

        $this->validate([
            'returnCondition' => 'required|in:' . implode(',', array_keys(RequestItem::CONDITIONS)),
            'returnNotes'     => 'nullable|string|max:255',
        ]);

        $item = $this->find($this->returningId);
        abort_unless($this->canTakeBack($item), 403);

        try {
            $msg = Assignments::returnToStock($item, $this->returnCondition, Auth::user(), $this->returnNotes ?: null);
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        session()->flash('message', "{$item->item_name} ×{$item->quantity} taken back from " . ($item->holder?->name ?? 'the holder') . ". {$msg}");
        $this->cancelForms();
        unset($this->items);
    }

    public function cancelForms(): void
    {
        $this->reset('handingId', 'toUserId', 'handNote', 'returningId', 'returnNotes');
        $this->returnCondition = 'good';
        $this->resetErrorBag();
    }

    public function clearHolder(): void
    {
        $this->holderId = '';
        $this->resetPage();
    }
};
