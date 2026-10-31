<?php

namespace App\Livewire\StudentFaculty;

use App\Models\RequestType;
use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.student-faculty')] class extends Component
{
    use WithPagination;

    public function mount(): void
    {
        // ✅ Matches seeder: faculty/student get material-requests.view
        abort_unless(
            auth()->user()->canAny([
                'material-requests.view',
                'material-requests.view-all',
            ]),
            403
        );
    }

    #[Computed]
    public function materialRequests()
    {
        return ResourceRequest::with(['items'])
            ->where('user_id', Auth::id())
            ->where('request_type_id', RequestType::MATERIAL) // Material Request
            ->latest()
            ->paginate(10);
    }

    public function cancelRequest($id)
    {
        // ✅ Matches seeder: material-requests.cancel
        abort_unless(
            auth()->user()->can('material-requests.cancel'),
            403
        );

        $request = ResourceRequest::where('user_id', Auth::id())
            ->where('id', $id)
            ->where('status', 'pending')
            ->first();

        if ($request) {
            $request->update(['status' => 'cancelled']);
            session()->flash('message', 'Request cancelled successfully.');
        } else {
            session()->flash('error', 'Request cannot be cancelled.');
        }
    }

    public function deleteRequest($id)
    {
        // ⚠️ No `material-requests.delete` in seeder — this will always 403
        // for faculty/student. Blade guards it with @can, so the button
        // never renders. Kept here to match the blade's @can contract.
        abort_unless(
            auth()->user()->can('material-requests.delete'),
            403
        );

        $request = ResourceRequest::where('user_id', Auth::id())
            ->where('id', $id)
            ->where('status', 'cancelled')
            ->first();

        if ($request) {
            RequestItem::where('request_id', $request->id)->delete();
            $request->delete();
            session()->flash('message', 'Request deleted successfully.');
        } else {
            session()->flash('error', 'Only cancelled requests can be deleted.');
        }
    }
};
