<?php

namespace App\Livewire\Admin;

use App\Models\Request as ResourceRequest;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    #[Computed]
    public function requests()
    {
        return ResourceRequest::with(['user.department', 'items'])
            ->where('request_type_id', 2)
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
