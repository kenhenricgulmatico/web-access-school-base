<?php

namespace App\Livewire\Coordinator;

use App\Models\Notification;
use App\Models\Request as ResourceRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.coordinator')] class extends Component
{
    public ResourceRequest $requestModel;

    /**
     * Any request type, guarded the same way as the listings: same
     * department, never a program head's own submission. Not scoped to
     * request_type_id = 1 anymore since this page is linked from both
     * the Facility and Material tables.
     */
    public function mount(int $id)
    {
        $this->requestModel = ResourceRequest::whereHas('user', function ($q) {
                $q->where('department_id', Auth::user()->department_id)
                  ->whereDoesntHave('roles', function ($role) {
                      $role->where('name', 'program head');
                  });
            })
            ->with(['user.department', 'items'])
            ->findOrFail($id);
    }

    public function accept()
    {
        $this->requestModel->update(['status' => 'approved']);
        $this->notifyRequestor('approved');
        $this->requestModel->refresh();
    }

    public function reject()
    {
        $this->requestModel->update(['status' => 'rejected']);
        $this->notifyRequestor('rejected');
        $this->requestModel->refresh();
    }

    protected function notifyRequestor(string $verb)
    {
        $approverName = Auth::user()->name;
        $facilityItem  = $this->requestModel->items->firstWhere('resource_id', null);
        $materialItems = $this->requestModel->items->whereNotNull('resource_id');

        $parts = [];

        if ($facilityItem) {
            $parts[] = "{$facilityItem->item_name} on " . Carbon::parse($facilityItem->request_date)->format('M d, Y')
                . " ({$facilityItem->start_time} - {$facilityItem->end_time})";
        }

        if ($materialItems->isNotEmpty()) {
            $parts[] = $materialItems->map(fn ($item) => "{$item->item_name} x{$item->quantity}")->implode(', ');
        }

        $details = $parts ? implode(' and ', $parts) : $this->requestModel->purpose;

        Notification::create([
            'user_id'    => $this->requestModel->user_id,
            'request_id' => $this->requestModel->id,
            'message'    => "{$approverName} {$verb} your request for {$details}.",
            'type'       => 'Gmail',
            'status'     => 'pending',
        ]);
    }
};
