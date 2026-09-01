<?php

namespace App\Livewire\Admin;

use App\Models\Request as ResourceRequest;
use App\Models\Resource;
use App\Models\Notification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public $statusFilter = 'pending';
    public $search       = '';

    /**
     * All requests from Student/Faculty (and anyone else) land here for
     * Admin's review — no filtering by requester role.
     */
    protected function incomingRequestsQuery()
    {
        return ResourceRequest::query();
    }

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

        return $query->latest()->paginate(10);
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

    /**
     * Materials currently allocated to each department, built up from
     * approved requests. Powers the "Department Materials" section
     * below the requests table.
     */
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

    public function approve($id)
    {
        $request = $this->incomingRequestsQuery()
            ->with('items')
            ->findOrFail($id);

        if ($request->status !== 'pending') {
            session()->flash('error', 'This request has already been processed.');
            return;
        }

        // Deduct stock for ANY item tied to a material — this covers both
        // standalone Material Requests AND materials attached to a
        // Facility Reservation. Facility items themselves (resource_id
        // null, e.g. "LISC", "ROOM 301") are never touched.
        $materialItems = $request->items->filter(function ($item) {
            return $item->resource_id || Resource::whereRaw('LOWER(resource_name) = ?', [strtolower($item->item_name)])->exists();
        });

        // Step 1 — resolve each material item to its resource
        $resolved = [];
        foreach ($materialItems as $item) {
            $resource = $item->resource_id
                ? Resource::find($item->resource_id)
                : Resource::whereRaw('LOWER(resource_name) = ?', [strtolower($item->item_name)])->first();

            if (!$resource) {
                session()->flash('error', "Cannot approve: \"{$item->item_name}\" was not found in inventory. Add it to stock first.");
                return;
            }

            if ($resource->quantity_available < $item->quantity) {
                session()->flash('error', "Cannot approve: only {$resource->quantity_available} unit(s) of \"{$resource->resource_name}\" available, but {$item->quantity} requested.");
                return;
            }

            $resolved[] = ['resource' => $resource, 'qty' => $item->quantity];
        }

        // Step 2 — all checks passed, safely deduct from central stock
        // AND credit the requester's department with that quantity so
        // the program head can see/track what materials their
        // department currently holds (resource_all_locations table).
        foreach ($resolved as $pair) {
            $pair['resource']->decrement('quantity_available', $pair['qty']);

            $existing = DB::table('resource_all_locations')
                ->where('resource_id', $pair['resource']->id)
                ->where('department_id', $request->department_id)
                ->first();

            if ($existing) {
                DB::table('resource_all_locations')
                    ->where('id', $existing->id)
                    ->update([
                        'allocated_quantity' => $existing->allocated_quantity + $pair['qty'],
                        'updated_at'         => now(),
                    ]);
            } else {
                DB::table('resource_all_locations')->insert([
                    'resource_id'         => $pair['resource']->id,
                    'department_id'       => $request->department_id,
                    'allocated_quantity'  => $pair['qty'],
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }
        }

        $request->update(['status' => 'approved']);

        Notification::create([
            'user_id' => $request->user_id,
            'message' => 'Your request has been approved by the admin.',
            'type'    => 'Gmail',
            'status'  => 'pending',
        ]);

        session()->flash('message', 'Request approved. Inventory updated.');
    }

    public function reject($id)
    {
        $request = $this->incomingRequestsQuery()->findOrFail($id);

        if ($request->status !== 'pending') {
            session()->flash('error', 'This request has already been processed.');
            return;
        }

        $request->update(['status' => 'rejected']);

        Notification::create([
            'user_id' => $request->user_id,
            'message' => 'Your request has been rejected by the admin.',
            'type'    => 'Gmail',
            'status'  => 'pending',
        ]);

        session()->flash('message', 'Request rejected and requester notified.');
    }

    public function clearFilters()
    {
        $this->statusFilter = 'pending';
        $this->reset('search');
    }
};
