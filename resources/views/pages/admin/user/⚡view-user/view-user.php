<?php

use App\Models\Department;
use App\Models\User;
use App\Support\Accounts;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::admin')] class extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search     = '';
    #[Url(as: 'role', except: '')]
    public string $roleFilter = '';
    #[Url(as: 'dept', except: '')]
    public string $deptFilter = '';
    #[Url(as: 'show', except: 'active')]
    public string $show       = 'active';   // active | deactivated

    public function mount(): void
    {
        abort_unless(auth()->user()->can('users.view'), 403);
    }

    public function updatingSearch()     { $this->resetPage(); }
    public function updatingRoleFilter() { $this->resetPage(); }
    public function updatingDeptFilter() { $this->resetPage(); }
    public function updatingShow()       { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->reset('search', 'roleFilter', 'deptFilter');
        $this->resetPage();
    }

    #[Computed]
    public function departments()
    {
        return Department::orderBy('department_name')->get(['id', 'department_name']);
    }

    /** Approved (active) or deactivated admin / program head / faculty accounts, 15 per page. */
    #[Computed]
    public function users()
    {
        return User::with(['roles', 'department'])
            ->whereHas('roles', fn ($r) => $r->whereIn('name', ['admin', 'program head', 'faculty']))
            ->where('status', $this->show === 'deactivated' ? 'deactivated' : 'approved')
            ->when($this->search !== '', fn ($q) => $q->where(fn ($s) => $s
                ->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')))
            ->when(in_array($this->roleFilter, ['admin', 'program head', 'faculty'], true),
                fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', $this->roleFilter)))
            ->when($this->deptFilter !== '', fn ($q) => $q->where('department_id', (int) $this->deptFilter))
            ->orderBy('name')
            ->select('id', 'name', 'email', 'department_id', 'status', 'must_change_password', 'created_at')
            ->paginate(15);
    }

    /** How many accounts are active / deactivated (for the two tabs). */
    #[Computed]
    public function counts(): array
    {
        $base = fn () => User::whereHas('roles', fn ($r) => $r->whereIn('name', ['admin', 'program head', 'faculty']));

        return [
            'active'      => $base()->where('status', 'approved')->count(),
            'deactivated' => $base()->where('status', 'deactivated')->count(),
        ];
    }

    /** ✅ Pending requests / borrowed items of the people on this page. */
    #[Computed]
    public function openItems(): array
    {
        return Accounts::openItems($this->users->pluck('id')->all());
    }

    /* ------------------------- deactivate / reactivate ------------------------- */

    /** Text for the Deactivate confirmation (warns about open requests / borrowed items). */
    public function deactivateConfirm(User $user): string
    {
        $open = Accounts::openItemsText($this->openItems[$user->id] ?? []);

        return "Deactivate {$user->name}? They won't be able to sign in. Their requests and history are kept, and you can reactivate the account later."
            . ($open ? " Note: they still have {$open}." : '');
    }

    /** ✅ A teacher who left: can't sign in anymore, but nothing is erased. */
    public function deactivate(int $id): void
    {
        abort_unless(auth()->user()->can('users.deactivate'), 403);

        $user = User::findOrFail($id);

        if ($user->id === auth()->id() || $user->hasRole('admin')) {
            session()->flash('error', 'Admin accounts cannot be deactivated here.');
            return;
        }

        if ($user->status !== 'approved') {
            session()->flash('error', "{$user->name} is not an active account.");
            return;
        }

        $items = Accounts::openItems([$user->id])[$user->id];

        // ✅ Furniture / equipment they keep goes to the next teacher first
        if ($items['assigned'] > 0) {
            $list = \App\Support\Assignments::heldBy($user->id)->map(fn ($i) => "{$i->item_name} ×{$i->quantity}")->implode(', ');
            session()->flash('error', "{$user->name} still keeps assigned items ({$list}). Hand them over to the next teacher first "
                . '(Borrowed Items → Assigned), then deactivate the account.');
            session()->flash('handover_user', $user->id);
            return;
        }

        $open = Accounts::openItemsText($items);

        $user->update(['status' => 'deactivated']);

        session()->flash('success', "{$user->name} has been deactivated and can no longer sign in."
            . ($open ? " They still have {$open} — follow up on Borrowed Items / Program Head Requests." : ''));
        unset($this->users, $this->counts, $this->openItems);
    }

    public function reactivate(int $id): void
    {
        abort_unless(auth()->user()->can('users.deactivate'), 403);

        $user = User::findOrFail($id);

        if ($user->status !== 'deactivated') {
            return;
        }

        $user->update(['status' => 'approved']);

        session()->flash('success', "{$user->name} can sign in again.");
        unset($this->users, $this->counts, $this->openItems);
    }
};
