<?php

namespace App\Livewire\StudentFaculty;

use App\Models\RequestType;
use App\Models\Request as ResourceRequest;
use App\Support\ForwardedRequests;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.student-faculty')] class extends Component
{
    use WithPagination;

    // ✅ pending | approved | closed (cancelled & rejected)
    #[Url]
    public string $tab = 'pending';

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

        if (! in_array($this->tab, ['pending', 'approved', 'closed'], true)) {
            $this->tab = 'pending';
        }
    }

    public function updatingTab(): void
    {
        $this->resetPage();
    }

    /** My material requests in one tab. */
    protected function forTab(string $tab)
    {
        $query = ResourceRequest::where('user_id', Auth::id())
            ->where('request_type_id', RequestType::MATERIAL);

        return match ($tab) {
            'pending'  => $query->where('status', 'pending'),
            'approved' => $query->where('status', 'approved'),
            default    => $query->whereIn('status', ['cancelled', 'rejected']),
        };
    }

    #[Computed]
    public function counts(): array
    {
        return [
            'pending'  => $this->forTab('pending')->count(),
            'approved' => $this->forTab('approved')->count(),
            'closed'   => $this->forTab('closed')->count(),
        ];
    }

    #[Computed]
    public function materialRequests()
    {
        return $this->forTab($this->tab)
            ->with(['items', 'approvals'])
            ->latest()
            ->paginate(10);
    }

    /** ✅ Endorsed by the Program Head and the Admin hasn't decided yet → can't be edited. */
    public function isEndorsed(ResourceRequest $request): bool
    {
        return ForwardedRequests::isWaitingForAdmin($request);
    }

    /**
     * ✅ Everything one card / row shows (used by both the phone cards and the table):
     *    date needed, status label + colours, rejection reason, items still to return.
     */
    public function rowInfo(ResourceRequest $request): array
    {
        $endorsed = $request->status === 'pending' && $this->isEndorsed($request);
        $needed   = $request->items->pluck('request_date')->filter()->min();

        [$label, $badge] = match (true) {
            $endorsed                         => ['Endorsed — waiting for Admin', 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'],
            $request->status === 'pending'    => ['Pending', 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300'],
            $request->status === 'approved'   => ['Approved', 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'],
            $request->status === 'rejected'   => ['Rejected', 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'],
            $request->status === 'cancelled'  => ['Cancelled', 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'],
            default                           => [ucfirst($request->status), 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'],
        };

        // Approved borrowed (Pcs) items still with me
        $toReturn = $request->status === 'approved'
            ? $request->items->filter(fn ($i) => $i->due_back_at && ! $i->returned_at)
            : collect();

        return [
            'endorsed' => $endorsed,
            'label'    => $label,
            'badge'    => $badge,
            'needed'   => $needed ? Carbon::parse($needed)->format('M d, Y') : null,
            'reason'   => $request->status === 'rejected'
                ? $request->approvals->where('status', 'rejected')->sortByDesc('id')->first()?->remarks
                : null,
            'toReturn' => $toReturn->count(),
            'returnBy' => $toReturn->min('due_back_at')?->format('M d, Y'),
        ];
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
            // ✅ Also withdraws the endorsed copy at the Admin and tells the Program Head / Admin
            $note = ForwardedRequests::onStudentCancelled($request->load('items'), Auth::user());
            session()->flash('message', 'Request cancelled.' . ($note ? ' ' . $note : '') . ' Your Program Head was told.');
        } else {
            session()->flash('error', 'Request cannot be cancelled.');
        }

        unset($this->materialRequests, $this->counts);
    }
};
