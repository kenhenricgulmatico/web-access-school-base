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
        // ✅ Admin reminder: borrowed material not returned yet
        if ($n->type === 'Overdue') {
            return [
                'id'            => $n->id,
                'type'          => 'Overdue',
                'is_facility'   => false,
                'requester'     => 'Admin — Please return borrowed items',
                'department'    => $n->request->department->department_name ?? '—',
                'purpose'       => $n->message,
                'status'        => $n->status,
                'action_status' => 'overdue',
                'request_id'    => $n->request_id,
                'time_ago'      => $n->created_at->diffForHumans(),
            ];
        }

        // ✅ A student / faculty clicked "Return to Program Head"
        if ($n->type === 'Return') {
            return [
                'id'            => $n->id,
                'type'          => 'Return',
                'is_facility'   => false,
                'requester'     => $n->request->user->name ?? 'Student',
                'department'    => $n->request->department->department_name ?? '—',
                'purpose'       => $n->message,
                'status'        => $n->status,
                'action_status' => 'info',
                'request_id'    => $n->request_id,
                'time_ago'      => $n->created_at->diffForHumans(),
            ];
        }

        $message = strtolower($n->message);

        $actionStatus = match (true) {
            str_contains($message, 'rejected') => 'rejected',
            str_contains($message, 'approved') => 'approved',
            default                            => 'info',
        };

        $isFacility = $n->request
            ? $n->request->items->contains(fn ($item) => is_null($item->resource_id))
            : str_contains($message, 'facility');

        $requesterName = $n->request->user->name ?? null;

        if (! $requesterName && preg_match('/^(.*?)\s+submitted/i', $n->message, $matches)) {
            $requesterName = trim($matches[1]);
        }

        $requesterName = $requesterName ?: 'Admin';

        return [
            'id'            => $n->id,
            'type'          => $n->type === 'Gmail' ? 'System' : $n->type,
            'is_facility'   => $isFacility,
            'requester'     => $requesterName,
            'department'    => $n->request->department->department_name ?? '—',
            'purpose'       => $n->request->purpose ?? $n->message,
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
        // ✅ Overdue reminder → open "My borrowed items"
        if ($notification->type === 'Overdue') {
            // Borrowed for a faculty member (endorsed) → it lives in the Faculty tab
            return $notification->request?->forwarded_from_request_id
                ? route('coordinator.borrowed-items')
                : route('coordinator.borrowed-items', ['view' => 'mine']);
        }

        // ✅ Student / faculty return → Students & Faculty tab
        if ($notification->type === 'Return') {
            return route('coordinator.borrowed-items');
        }

        $message = strtolower($notification->message);
        $isDecision = str_contains($message, 'approved') || str_contains($message, 'rejected');

        if ($isDecision) {
            return route('coordinator.request-to-admin.view-request', [
                'request' => $notification->request_id,
            ]);
        }

        $isFacility = $notification->request
            ? $notification->request->items->contains(fn ($item) => is_null($item->resource_id))
            : str_contains($message, 'facility');

        return $isFacility
            ? route('coordinator.facility')
            : route('coordinator.material');
    }
};
