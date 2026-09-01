<?php

namespace App\Livewire\Portal;

use App\Models\Request as ResourceRequest;
use App\Models\Resource;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.student-faculty')] class extends Component
{
    use WithPagination;

    #[Computed]
    public function reservations()
    {
        return ResourceRequest::with(['items', 'department'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);
    }

    public function cancelReservation($id)
    {
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
