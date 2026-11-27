<?php

namespace App\Livewire\Coordinator\RequestToAdmin;

use App\Models\Request as ResourceRequest;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.coordinator')] class extends Component
{
    public ResourceRequest $requestModel;

    public function mount(int $id): void
    {
        $request = ResourceRequest::with([
            'items',
            'requestType',
            'department',
            'approvals.approver',
        ])->findOrFail($id);

        if ($request->user_id !== Auth::id()) {
            abort(403, 'You do not own this request.');
        }

        $this->requestModel = $request;
    }

    /**
     * Latest rejection approval row (with reason), if any.
     */
    public function getRejectionProperty()
    {
        return $this->requestModel->approvals
            ->where('status', 'rejected')
            ->sortByDesc('approved_at')
            ->first();
    }

    public function statusBadgeClasses(string $status): string
    {
        return match ($status) {
            'approved'  => 'bg-[#E8F3EC] text-[#1C6B45] dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]',
            'rejected'  => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-400',
            default     => 'bg-gray-100 text-gray-800 dark:bg-neutral-700 dark:text-neutral-300',
        };
    }

    public function back()
    {
        return redirect()->route('coordinator.request-to-admin.view-request');
    }
};
