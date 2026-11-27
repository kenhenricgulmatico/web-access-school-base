<?php

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component
{
    public int $unreadCount = 0;
    public int $totalCount = 0;
    public int $pendingCoordinatorRequests = 0;
    public bool $showAll = false;
    public array $notifications = [];

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        if (! Auth::check()) return;

        // Only THIS logged-in user's own notifications (10 by default, up to 50 when "View all")
        $latest = Notification::with('user')
            ->where('user_id', Auth::id())
            ->latest()
            ->take($this->showAll ? 50 : 10)
            ->get();

        $this->totalCount = Notification::where('user_id', Auth::id())->count();

        $this->unreadCount = Notification::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->count();

        // Pending facility reservations / material requests from program heads
        $this->pendingCoordinatorRequests = \App\Models\Request::where('status', 'pending')
            ->whereHas('user', fn ($q) => $q->role('program head'))
            ->count();

        // Map database status 'pending' → 'unread', 'sent' → 'read'
        $this->notifications = $latest->map(fn ($n) => [
            'id'       => $n->id,
            'message'  => $n->message,
            'type'     => $n->type,
            'status'   => $n->status === 'pending' ? 'unread' : 'read',
            'time_ago' => $n->created_at->diffForHumans(),
            'user'     => $n->user?->name ?? 'System',
        ])->toArray();
    }

    // "View all notifications" / "Show less"
    public function toggleShowAll(): void
    {
        $this->showAll = ! $this->showAll;
        $this->loadNotifications();
    }

    // Click a notification: mark as read, then go to the right page
    public function openNotification(int $id): void
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (! $notification) {
            $this->loadNotifications();
            return;
        }

        if ($notification->status === 'pending') {
            $notification->update(['status' => 'sent']);
        }

        // ✅ A Program Head returning an item ("…please check and confirm it in Borrowed Items")
        //    → Borrowed Items, where the Admin confirms it — even when the item came from an endorsed
        //    request (that request's own link would open Request Details instead).
        if (self::isReturnNotice($notification)) {
            $this->redirect(route('admin.borrowed-items', ['filter' => 'outstanding']));
            return;
        }

        // ✅ Linked to a request → open that request (or Borrowed Items for a Pcs borrowing)
        $url = $notification->request_id
            ? \App\Support\AdminRequestNotice::adminUrl((int) $notification->request_id)
            : null;

        $this->redirect($url ?? $this->resolveUrl((string) $notification->message));
    }

    public function markAllAsRead(): void
    {
        Notification::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->update(['status' => 'sent']);

        $this->loadNotifications();
    }

    /** Item-return notices (by type, or by wording for older notices already in the bell). */
    private static function isReturnNotice(Notification $notification): bool
    {
        if (in_array($notification->type, ['Return', 'Overdue'], true)) {
            return true;
        }

        $message = Str::lower((string) $notification->message);

        return Str::contains($message, 'in borrowed items')
            || (Str::contains($message, 'is returning') && Str::contains($message, 'pcs of'));
    }

    /**
     * Decide where a notification should go based on its message.
     * Registration (student/faculty account) → /admin/students
     * Everything else (material / reservation requests) → /admin/manage-coordinator
     */
    private function resolveUrl(string $message): string
    {
        $registrationKeywords = ['regist', 'sign up', 'signed up', 'new account', 'account'];

        if (Str::contains(Str::lower($message), $registrationKeywords)) {
            return '/admin/students';
        }

        return '/admin/manage-coordinator';
    }
};
