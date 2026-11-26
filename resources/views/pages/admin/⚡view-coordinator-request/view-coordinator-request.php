<?php

use App\Models\Request as ResourceRequest;
use App\Models\Resource;
use App\Traits\ReviewsProgramHeadRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    use ReviewsProgramHeadRequests; // approve / reject (shared with Program Head Requests list)

    public ResourceRequest $reservation;

    public function mount($id)
    {
        $this->reservation = ResourceRequest::with(['user', 'department', 'requestType', 'items.resource'])
            ->findOrFail($id);
    }

    /**
     * Approve / Reject are only offered for pending requests that belong
     * on the Program Head Requests list (same scope the list page uses).
     */
    public function getCanReviewProperty(): bool
    {
        return $this->reservation->status === 'pending'
            && $this->incomingRequestsQuery()->whereKey($this->reservation->id)->exists();
    }

    public function approveRequest(): void
    {
        $this->approve($this->reservation->id);
        $this->reloadReservation();
    }

    public function openRejectForm(): void
    {
        $this->openReject($this->reservation->id);
    }

    public function confirmRejectRequest(): void
    {
        $this->rejectingRequestId = $this->reservation->id;
        $this->confirmReject();
        $this->reloadReservation();
    }

    protected function reloadReservation(): void
    {
        $this->reservation = $this->reservation->fresh(['user', 'department', 'requestType', 'items.resource']);
    }

    public function getItemsWithStockProperty()
    {
        return $this->reservation->items->map(function ($item) {
            $resource = $item->resource_id
                ? $item->resource
                : Resource::whereRaw('LOWER(resource_name) = ?', [strtolower($item->item_name)])->first();

            return [
                'item'     => $item,
                'resource' => $resource,
                'hasStock' => $resource && $resource->quantity_available >= $item->quantity,
            ];
        });
    }
};
