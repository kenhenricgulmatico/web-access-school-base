<?php

namespace App\Livewire\Portal;

use App\Models\RequestType;
use App\Models\Request as ResourceRequest;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.student-faculty')] class extends Component
{
    use WithPagination;

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
    }

    #[Computed]
    public function reservations()
    {
        return ResourceRequest::with(['items', 'department'])
            ->where('user_id', Auth::id())
            ->where('request_type_id', RequestType::FACILITY)
            ->latest()
            ->paginate(10);
    }

    /**
     * Can the current user edit this reservation?
     */
    public function canEdit(ResourceRequest $request): bool
    {
        if ($request->user_id !== Auth::id()) {
            return false;
        }

        if ($request->status !== 'pending') {
            return false;
        }

        return auth()->user()->canAny([
            'facility-requests.update',
            'material-requests.update',
        ]);
    }

    /**
     * Can the current user cancel this reservation?
     */
    public function canCancel(ResourceRequest $request): bool
    {
        if ($request->user_id !== Auth::id()) {
            return false;
        }

        if ($request->status !== 'pending') {
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

        $request->update(['status' => 'cancelled']);

        session()->flash('success', 'Reservation cancelled.');
    }
};
