<?php

namespace App\Livewire\Portal;

use App\Models\RequestItem;
use App\Models\RequestType;
use App\Models\Request as ResourceRequest;
use App\Support\ForwardedRequests;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.student-faculty')] class extends Component
{
    use WithPagination;

    // ✅ upcoming | past | closed (cancelled & rejected)
    #[Url]
    public string $tab = 'upcoming';

    public function mount(): void
    {
        // ✅ Matches seeder: faculty/student get facility-requests.view + material-requests.view
        abort_unless(
            auth()->user()->canAny([
                'facility-requests.view',
                'material-requests.view',
            ]),
            403
        );

        if (! in_array($this->tab, ['upcoming', 'past', 'closed'], true)) {
            $this->tab = 'upcoming';
        }
    }

    public function updatingTab(): void
    {
        $this->resetPage();
    }

    /** My facility reservations. */
    protected function mine()
    {
        return ResourceRequest::where('user_id', Auth::id())
            ->where('request_type_id', RequestType::FACILITY);
    }

    /** SQL: the day the room is booked for (the facility line of the request). */
    protected function bookingDate()
    {
        return RequestItem::select('request_date')
            ->whereColumn('request_items.request_id', 'requests.id')
            ->whereNull('resource_id')
            ->limit(1);
    }

    /** Apply one tab to a query. */
    protected function forTab($query, string $tab)
    {
        $today = today()->toDateString();

        return match ($tab) {
            // Still to come: pending or approved, booked for today or later — soonest first
            'upcoming' => $query->whereIn('status', ['pending', 'approved'])
                ->whereHas('items', fn ($i) => $i->whereNull('resource_id')->whereDate('request_date', '>=', $today))
                ->orderBy($this->bookingDate()),
            // Already happened (or the day passed while still pending) — latest first
            'past'     => $query->whereIn('status', ['pending', 'approved'])
                ->whereHas('items', fn ($i) => $i->whereNull('resource_id')->whereDate('request_date', '<', $today))
                ->orderByDesc($this->bookingDate()),
            // Cancelled & rejected
            default    => $query->whereIn('status', ['cancelled', 'rejected'])->latest(),
        };
    }

    #[Computed]
    public function counts(): array
    {
        return [
            'upcoming' => $this->forTab($this->mine(), 'upcoming')->count(),
            'past'     => $this->forTab($this->mine(), 'past')->count(),
            'closed'   => $this->forTab($this->mine(), 'closed')->count(),
        ];
    }

    #[Computed]
    public function reservations()
    {
        return $this->forTab($this->mine(), $this->tab)
            ->with(['items', 'department', 'approvals'])
            ->paginate(10);
    }

    /** ✅ Endorsed by the Program Head and the Admin hasn't decided yet. */
    public function isEndorsed(ResourceRequest $request): bool
    {
        return ForwardedRequests::isWaitingForAdmin($request);
    }

    /** ✅ Why it was rejected (the latest rejection note), if any. */
    public function rejectionReason(ResourceRequest $request): ?string
    {
        return $request->approvals
            ->where('status', 'rejected')
            ->sortByDesc('id')
            ->first()?->remarks;
    }

    /**
     * Can the current user edit this reservation?
     * ✅ Not once it's endorsed to the Admin — the Admin is reviewing exactly that version.
     */
    public function canEdit(ResourceRequest $request): bool
    {
        if ($request->user_id !== Auth::id() || $request->status !== 'pending') {
            return false;
        }

        if ($this->isEndorsed($request)) {
            return false;
        }

        return auth()->user()->canAny([
            'facility-requests.update',
            'material-requests.update',
        ]);
    }

    /** Can the current user cancel this reservation? */
    public function canCancel(ResourceRequest $request): bool
    {
        if ($request->user_id !== Auth::id() || $request->status !== 'pending') {
            return false;
        }

        return auth()->user()->canAny([
            'facility-requests.cancel',
            'material-requests.cancel',
        ]);
    }

    public function cancelReservation($id)
    {
        abort_unless(
            auth()->user()->canAny([
                'facility-requests.cancel',
                'material-requests.cancel',
            ]),
            403
        );

        $request = ResourceRequest::with('items')->findOrFail($id);

        if ($request->user_id !== Auth::id()) {
            session()->flash('error', 'You are not authorized to cancel this reservation.');
            return;
        }

        if ($request->status !== 'pending') {
            session()->flash('error', 'Only pending reservations can be cancelled.');
            return;
        }

        // ✅ Also withdraws the endorsed copy at the Admin and tells the Program Head / Admin
        $note = ForwardedRequests::onStudentCancelled($request, Auth::user());

        session()->flash('success', 'Reservation cancelled.' . ($note ? ' ' . $note : '') . ' Your Program Head was told.');
        unset($this->reservations, $this->counts);
    }
};
