<?php

use App\Models\RequestType;
use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.student-faculty')] class extends Component
{
    public ResourceRequest $requestModel;

    // 1 = Facility Reservation, 2 = Material Request (see RequestTypeSeeder)
    public bool $isFacility = false;

    /**
     * Only the logged-in user's own request — works for either
     * a Material Request (type 2) or a Facility Reservation (type 1).
     */
    public function mount(int $id)
    {
        $this->requestModel = ResourceRequest::where('user_id', Auth::id())
            ->with(['items', 'approvals.approver'])
            ->findOrFail($id);

        $this->isFacility = (int) $this->requestModel->request_type_id === RequestType::FACILITY;
    }

    /**
     * Latest rejection approval row (with reason), if the request was rejected.
     * Mirrors the logic in view-single-button.php (coordinator's own request view).
     */
    public function getRejectionProperty()
    {
        return $this->requestModel->approvals
            ->where('status', 'rejected')
            ->sortByDesc('approved_at')
            ->first();
    }

    public function cancelRequest()
    {
        if ($this->requestModel->status !== 'pending') {
            session()->flash('error', 'Request cannot be cancelled.');
            return;
        }

        $this->requestModel->update(['status' => 'cancelled']);
        $this->requestModel->refresh();
        session()->flash('message', 'Request cancelled successfully.');
    }

    public function deleteRequest()
    {
        if ($this->requestModel->status !== 'cancelled') {
            session()->flash('error', 'Only cancelled requests can be deleted.');
            return;
        }

        RequestItem::where('request_id', $this->requestModel->id)->delete();
        $this->requestModel->delete();

        session()->flash('message', 'Request deleted successfully.');

        $this->redirect(
            $this->isFacility ? route('portal.reservation') : route('portal.material'),
            navigate: true
        );
    }
};
