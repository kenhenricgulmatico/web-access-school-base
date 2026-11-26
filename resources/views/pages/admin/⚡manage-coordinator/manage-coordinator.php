<?php

use App\Traits\ReviewsProgramHeadRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use App\Models\RequestItem;
use App\Models\RequestType;
use App\Support\TimeSlots;
use Carbon\Carbon;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;
    use ReviewsProgramHeadRequests; // approve / reject (shared with the View page)

    // ✅ Opens on the Admin's work queue: pending requests
    #[Url]
    public $statusFilter = 'pending';
    public $search       = '';

    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingSearch(): void       { $this->resetPage(); }

    #[Computed]
    public function requests()
    {
        $query = $this->incomingRequestsQuery()
            ->with(['user', 'department', 'requestType', 'items.resource']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('purpose', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', fn($u) =>
                        $u->where('name', 'like', '%' . $this->search . '%')
                    )
                    ->orWhereHas('department', fn($d) =>
                        $d->where('department_name', 'like', '%' . $this->search . '%')
                    );
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $query->with('approvals');

        // ✅ Pending: the soonest "needed on" date first. Everything else: most recent first.
        $this->statusFilter === 'pending'
            ? $query->orderBy(RequestItem::selectRaw('MIN(request_date)')->whereColumn('request_items.request_id', 'requests.id'))
            : $query->latest();

        return $query->paginate(10);
    }

    /** ✅ Earliest "needed on" date of a request (Carbon or null). */
    public function neededOn($request): ?Carbon
    {
        $date = $request->items->pluck('request_date')->filter()->min();

        return $date ? Carbon::parse($date) : null;
    }

    /** ✅ Why it was rejected (latest rejection note), if any. */
    public function rejectionReason($request): ?string
    {
        return $request->approvals->where('status', 'rejected')->sortByDesc('id')->first()?->remarks;
    }

    /**
     * ✅ Items the MAIN stock doesn't have enough of for this request (empty = can approve).
     *    e.g. ["Bond Paper A4 (need 40, have 20)"]. Facilities don't count.
     */
    public function shortItems($request): array
    {
        $needed = [];
        foreach ($request->items->whereNotNull('resource_id') as $item) {
            $needed[$item->resource_id] ??= ['name' => $item->item_name, 'qty' => 0, 'have' => (int) ($item->resource?->quantity_available ?? 0)];
            $needed[$item->resource_id]['qty'] += (int) $item->quantity;
        }

        return collect($needed)
            ->filter(fn ($n) => $n['have'] < $n['qty'])
            ->map(fn ($n) => "{$n['name']} (need {$n['qty']}, have {$n['have']})")
            ->values()
            ->all();
    }

    /**
     * ✅ Approved bookings that overlap this facility request (empty = the room is free).
     *    The faculty request it was endorsed from never counts.
     */
    public function clashes($request): array
    {
        $item = $request->items->firstWhere('resource_id', null);

        if ((int) $request->request_type_id !== RequestType::FACILITY || ! $item || ! $item->start_time || ! $item->end_time) {
            return [];
        }

        $start = Carbon::parse($item->start_time)->format('H:i');
        $end   = Carbon::parse($item->end_time)->format('H:i');

        return collect(TimeSlots::approvedBookings($item->item_name, Carbon::parse($item->request_date)->toDateString(), $request->id))
            ->filter(fn ($b) => $start < $b['end'] && $end > $b['start'])
            ->map(fn ($b) => "{$b['label']} ({$b['department']})")
            ->values()
            ->all();
    }

    #[Computed]
    public function pendingCount()
    {
        return $this->incomingRequestsQuery()->where('status', 'pending')->count();
    }

    #[Computed]
    public function approvedCount()
    {
        return $this->incomingRequestsQuery()->where('status', 'approved')->count();
    }

    #[Computed]
    public function rejectedCount()
    {
        return $this->incomingRequestsQuery()->where('status', 'rejected')->count();
    }

    #[Computed]
    public function departmentAllocations()
    {
        return DB::table('resource_all_locations')
            ->join('resources', 'resources.id', '=', 'resource_all_locations.resource_id')
            ->join('departments', 'departments.id', '=', 'resource_all_locations.department_id')
            ->where('resource_all_locations.allocated_quantity', '>', 0)
            ->select(
                'resource_all_locations.id',
                'resource_all_locations.allocated_quantity',
                'resource_all_locations.updated_at',
                'resources.resource_name',
                'departments.department_name'
            )
            ->orderByDesc('resource_all_locations.updated_at')
            ->limit(10)
            ->get();
    }

    public function clearFilters()
    {
        $this->reset('search');
    }
};
