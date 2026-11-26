<?php

use App\Models\User;
use App\Notifications\StudentApprovedNotification;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = 'all';
    public string $statusFilter = 'all';

    // ✅ Reject dialog state
    public ?int   $rejectingUserId = null;
    public string $rejectReason    = '';

    public function mount(): void
    {
        // ✅ Server-side guard — page requires `users.view`
        abort_unless(auth()->user()->can('users.view'), 403);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedRoleFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    #[Computed]
    public function users()
    {
        return User::with('roles', 'department')
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['faculty', 'program head']))
            ->when(
                $this->search,
                fn($q) =>
                $q->where(
                    fn($q2) =>
                    $q2->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                )
            )
            ->when(
                $this->roleFilter !== 'all',
                fn($q) =>
                $q->whereHas('roles', fn($q2) => $q2->where('name', $this->roleFilter))
            )
            ->when(
                $this->statusFilter !== 'all',
                fn($q) =>
                $q->where('status', $this->statusFilter)
            )
            ->latest()
            ->paginate(10);
    }

    public function approve(int $userId)
    {
        // ✅ Server-side guard
        abort_unless(auth()->user()->can('users.approve'), 403);

        $user = User::with('roles')->findOrFail($userId);

        if ($user->status !== 'pending') {
            session()->flash('error', 'This user has already been processed.');
            return;
        }

        $user->update(['status' => 'approved']);

        // Pass the user's actual role so the email says the right thing
        $roleName = ucfirst($user->roles->first()?->name ?? 'user');

        try {
            $user->notify(new StudentApprovedNotification($roleName));
        } catch (\Exception $e) {
            // Mail may not be configured in dev — account is still approved either way
        }

        session()->flash('success', "{$user->name} has been approved.");
    }

    // ============================================================
    // ✅ REJECT WITH REASON
    // ============================================================
    public function openReject(int $userId): void
    {
        // ✅ Server-side guard
        abort_unless(auth()->user()->can('users.approve'), 403);

        $this->rejectingUserId = $userId;
        $this->rejectReason    = '';
    }

    public function cancelReject(): void
    {
        $this->rejectingUserId = null;
        $this->rejectReason    = '';
    }

    public function confirmReject(): void
    {
        // ✅ Server-side guard
        abort_unless(auth()->user()->can('users.approve'), 403);

        if (! $this->rejectingUserId) {
            return;
        }

        $user = User::with('roles')->findOrFail($this->rejectingUserId);

        if ($user->status !== 'pending') {
            session()->flash('error', 'This user has already been processed.');
            $this->cancelReject();
            return;
        }

        $user->update(['status' => 'rejected']);

        // Optional: log the reason (adjust as needed)
        if ($this->rejectReason) {
            \Illuminate\Support\Facades\Log::info(
                "User {$user->id} ({$user->email}) rejected by admin. Reason: {$this->rejectReason}"
            );
        }

        // Optional: send a notification with the reason
        // try {
        //     $user->notify(new \App\Notifications\AccountRejectedNotification($this->rejectReason));
        // } catch (\Throwable $e) {
        //     \Log::warning('Rejection notification failed: ' . $e->getMessage());
        // }

        session()->flash('success', "{$user->name} has been rejected.");

        $this->cancelReject();
    }
};  
