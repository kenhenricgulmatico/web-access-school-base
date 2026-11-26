<?php

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public int $unreadCount = 0;
    public int $totalCount = 0;
    public bool $showAll = false;
    public array $notifications = [];

    public function mount(): void
    {
        $this->loadNotifications();
    }

    #[On('avatar-updated')]
    public function refreshAvatar(): void
    {
        // Intentionally empty: receiving the event re-renders the header,
        // which re-reads Auth::user()->avatar.
    }

    public function loadNotifications(): void
    {
        if (! Auth::check()) {
            return;
        }

        $latest = Notification::with(['user', 'request.user', 'request.department', 'request.items'])
            ->where('user_id', Auth::id())
            ->latest()
            ->take($this->showAll ? 50 : 10)
            ->get();

        $this->totalCount = Notification::where('user_id', Auth::id())->count();

        $this->unreadCount = Notification::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->count();

        $this->notifications = $latest->map(fn ($n) => $this->formatNotification($n))->toArray();
    }

    private function formatNotification(Notification $n): array
    {
        $message = strtolower($n->message);

        $actionStatus = match (true) {
            $n->type === 'Overdue'             => 'overdue',   // ✅ Program Head reminder
            str_contains($message, 'rejected') => 'rejected',
            str_contains($message, 'approved') => 'approved',
            default                            => 'info',
        };

        $isFacility = $n->request
            ? $n->request->items->contains(fn ($item) => is_null($item->resource_id))
            : str_contains($message, 'facility');

        return [
            'id'            => $n->id,
            'type'          => $n->type === 'Gmail' ? 'System' : $n->type,
            'is_facility'   => $isFacility,
            'requester'     => $n->request->user->name ?? 'Admin',
            'department'    => $n->request->department->department_name ?? '—',
            // Main bold line: the full detailed action message
            // (e.g. "Justin approved your facility reservation for
            // Computer laboratory 3 on Sep 25, 2026 (12:00 - 15:00).")
            'purpose'       => $n->message,
            // Secondary line: the student's own submitted purpose text,
            // kept for context but no longer the headline.
            'message'       => $n->request->purpose ?? '',
            'status'        => $n->status,
            'action_status' => $actionStatus,
            'request_id'    => $n->request_id,
            'time_ago'      => $n->created_at->diffForHumans(),
        ];
    }

    public function toggleShowAll(): void
    {
        $this->showAll = ! $this->showAll;
        $this->loadNotifications();
    }

    public function openNotification(int $id): void
    {
        $notification = Notification::with('request.items')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (! $notification) {
            $this->loadNotifications();
            return;
        }

        if ($notification->status === 'pending') {
            $notification->update(['status' => 'sent']);
        }

        $this->redirect($this->resolveUrl($notification), navigate: true);
    }

    public function markAllAsRead(): void
    {
        Notification::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->update(['status' => 'sent']);

        $this->loadNotifications();
    }

    private function resolveUrl(Notification $notification): string
    {
        // ✅ Overdue reminder → open my Borrowed Items
        if ($notification->type === 'Overdue') {
            return route('portal.borrowed-items');
        }

        $message = strtolower($notification->message);

        $isFacility = $notification->request
            ? $notification->request->items->contains(fn ($item) => is_null($item->resource_id))
            : str_contains($message, 'facility');

        return $isFacility
            ? route('portal.reservation')
            : route('portal.material');
    }
};
