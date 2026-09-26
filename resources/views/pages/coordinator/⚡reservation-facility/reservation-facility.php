<?php

use App\Models\Notification;
use App\Models\Request as ResourceRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.coordinator')] class extends Component
{
    protected function scopedQuery()
    {
        return ResourceRequest::where('request_type_id', 1)
            ->whereHas('user', function ($q) {
                $q->where('department_id', Auth::user()->department_id)
                  ->whereDoesntHave('roles', function ($role) {
                      $role->where('name', 'program head');
                  });
            });
    }

    #[Computed]
    public function reservations()
    {
        return $this->scopedQuery()
            ->with(['user.department', 'items'])
            ->whereIn('status', ['pending', 'approved', 'rejected'])
            ->latest()
            ->get();
    }

    public function accept(int $id)
    {
        $request = $this->scopedQuery()->findOrFail($id);
        $request->update(['status' => 'approved']);

        $facilityItem = $request->items->firstWhere('resource_id', null);
        $approverName = Auth::user()->name;

        $details = $facilityItem
            ? "{$facilityItem->item_name} on " . Carbon::parse($facilityItem->request_date)->format('M d, Y')
                . " ({$facilityItem->start_time} - {$facilityItem->end_time})"
            : $request->purpose;

        Notification::create([
            'user_id'    => $request->user_id,
            'request_id' => $request->id,
            'message'    => "{$approverName} approved your facility reservation for {$details}.",
            'type'       => 'Gmail',
            'status'     => 'pending',
        ]);
    }

    public function reject(int $id)
    {
        $request = $this->scopedQuery()->findOrFail($id);
        $request->update(['status' => 'rejected']);

        $facilityItem = $request->items->firstWhere('resource_id', null);
        $approverName = Auth::user()->name;

        $details = $facilityItem
            ? "{$facilityItem->item_name} on " . Carbon::parse($facilityItem->request_date)->format('M d, Y')
                . " ({$facilityItem->start_time} - {$facilityItem->end_time})"
            : $request->purpose;

        Notification::create([
            'user_id'    => $request->user_id,
            'request_id' => $request->id,
            'message'    => "{$approverName} rejected your facility reservation for {$details}.",
            'type'       => 'Gmail',
            'status'     => 'pending',
        ]);
    }
};
