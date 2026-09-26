<?php

namespace App\Livewire\Admin;

use App\Models\Request as ResourceRequest;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    #[Computed]
    public function reservations()
    {
        return ResourceRequest::with(['user.department', 'items'])
            ->where('request_type_id', 1)
            ->where('status', 'pending')
            ->latest()
            ->get();
    }

    public function accept(int $id)
    {
        ResourceRequest::findOrFail($id)->update([
            'status' => 'approved',
        ]);
    }

    public function reject(int $id)
    {
        ResourceRequest::findOrFail($id)->update([
            'status' => 'rejected',
        ]);
    }
};
