<?php

namespace App\Livewire\StudentFaculty;

use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use App\Models\Notification;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new #[Layout('layouts.student-faculty')] class extends Component
{
    public $requestId;
    public $purpose = '';
    public $items = [];

    public $availableResources = [];

    protected $rules = [
        'purpose' => 'required|string|min:10|max:500',
        'items' => 'required|array|min:1',
        'items.*.resource_id' => 'required|exists:resources,id',
        'items.*.quantity' => 'required|integer|min:1',
    ];

    protected $messages = [
        'purpose.required' => 'Please state your purpose',
        'purpose.min' => 'Purpose must be at least 10 characters',
        'items.required' => 'Add at least one material item',
        'items.*.resource_id.required' => 'Please select a material',
        'items.*.resource_id.exists' => 'Selected material is invalid.',
        'items.*.quantity.min' => 'Quantity must be at least 1',
    ];

    /**
     * Format a raw quantity using the resource's unit (e.g. "50 Ream").
     */
    protected function formatQuantity(int $qty, ?string $unit): string
    {
        $unit = trim($unit ?? '') ?: 'Ream';

        return "{$qty} {$unit}";
    }

    public function mount($id)
    {
        $this->requestId = $id;
        $request = ResourceRequest::with('items')
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        // Only allow editing if status is pending
        if ($request->status !== 'pending') {
            session()->flash('error', 'Only pending requests can be edited.');
            return redirect()->route('portal.material');
        }

        $user = Auth::user();

        // ✅ Load this department's available materials, same as the create form
        if ($user->department_id) {
            $this->availableResources = DB::table('resource_all_locations')
                ->join('resources', 'resources.id', '=', 'resource_all_locations.resource_id')
                ->join('resource_types', 'resource_types.id', '=', 'resources.resource_type_id')
                ->where('resource_all_locations.department_id', $user->department_id)
                ->where('resource_all_locations.allocated_quantity', '>', 0)
                ->where('resources.status', 'available')
                ->where('resource_types.type_name', '!=', 'Facility')
                ->orderBy('resources.resource_name')
                ->select(
                    'resources.id as resource_id',
                    'resources.resource_name',
                    'resources.unit',
                    'resource_all_locations.allocated_quantity'
                )
                ->get()
                ->map(function ($resource) {
                    $resource->available_formatted = $this->formatQuantity(
                        (int) $resource->allocated_quantity,
                        $resource->unit
                    );
                    return (array) $resource;
                })
                ->toArray();
        }

        $this->purpose = $request->purpose;
        $this->items = $request->items->map(function ($item) {
            return [
                'id' => $item->id,
                'resource_id' => $item->resource_id,
                'quantity' => $item->quantity,
            ];
        })->toArray();
    }

    /**
     * Look up a resource from $availableResources.
     */
    public function getMaterialResource($resourceId)
    {
        if (!$resourceId) return null;

        return collect($this->availableResources)->firstWhere('resource_id', (int) $resourceId);
    }

    public function getAvailableStock($resourceId): ?string
    {
        $resource = $this->getMaterialResource($resourceId);

        return $resource ? $resource['available_formatted'] : null;
    }

    public function getQuantityBreakdown($resourceId, $quantity): ?string
    {
        $resource = $this->getMaterialResource($resourceId);

        if (!$resource) {
            return null;
        }

        $qty = (int) $quantity;

        if ($qty <= 0) {
            return null;
        }

        return $this->formatQuantity($qty, $resource['unit']);
    }

    public function getStockWarning($resourceId, $quantity): ?string
    {
        $resource = $this->getMaterialResource($resourceId);

        if (!$resource || $quantity === '' || $quantity === null) {
            return null;
        }

        $qty = (int) $quantity;

        if ($qty > (int) $resource['allocated_quantity']) {
            return "Exceeds available stock ({$resource['available_formatted']})";
        }

        return null;
    }

    /**
     * Resources available for a given row's dropdown: everything minus
     * whatever's already picked in OTHER rows (prevents duplicate materials
     * across line items). The row's own current selection is always kept.
     */
    public function getOptionsForRow(int $currentIndex): array
    {
        $selectedElsewhere = collect($this->items)
            ->except($currentIndex)
            ->pluck('resource_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        return collect($this->availableResources)
            ->reject(fn ($resource) => in_array((int) $resource['resource_id'], $selectedElsewhere))
            ->all();
    }

    public function addItem()
    {
        $this->items[] = ['resource_id' => '', 'quantity' => 1];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function update()
    {
        $this->validate();

        $request = ResourceRequest::where('user_id', Auth::id())
            ->where('id', $this->requestId)
            ->where('status', 'pending')
            ->firstOrFail();

        $user = Auth::user();

        // ✅ Validate requested quantity against department allocation, same as create form
        foreach ($this->items as $i => $item) {
            $allocation = DB::table('resource_all_locations')
                ->join('resources', 'resources.id', '=', 'resource_all_locations.resource_id')
                ->where('resource_all_locations.resource_id', $item['resource_id'])
                ->where('resource_all_locations.department_id', $user->department_id)
                ->select('resource_all_locations.allocated_quantity', 'resources.unit')
                ->first();

            if (!$allocation || (int) $item['quantity'] > $allocation->allocated_quantity) {
                $available = $allocation
                    ? $this->formatQuantity((int) $allocation->allocated_quantity, $allocation->unit)
                    : '0 Ream';
                $this->addError("items.$i.quantity", "Only {$available} available in your department.");
                return;
            }
        }

        // Update purpose
        $request->update(['purpose' => $this->purpose]);

        // Get existing item IDs
        $existingIds = collect($this->items)->filter(fn($i) => isset($i['id']))->pluck('id')->toArray();

        // Delete items that were removed
        RequestItem::where('request_id', $request->id)
            ->whereNotIn('id', $existingIds)
            ->delete();

        // Update or create items
        foreach ($this->items as $item) {
            $resource = DB::table('resources')->where('id', $item['resource_id'])->first();

            if (isset($item['id'])) {
                RequestItem::where('id', $item['id'])->update([
                    'resource_id' => $item['resource_id'],
                    'item_name' => $resource->resource_name,
                    'quantity' => $item['quantity'],
                ]);
            } else {
                RequestItem::create([
                    'request_id' => $request->id,
                    'resource_id' => $item['resource_id'],
                    'item_name' => $resource->resource_name,
                    'quantity' => $item['quantity'],
                    'request_date' => now()->toDateString(),
                    'start_time' => null,
                    'end_time' => null,
                ]);
            }
        }

        // Notify coordinators about the update
        $coordinators = User::role('program head')->get();
        foreach ($coordinators as $coordinator) {
            Notification::create([
                'user_id' => $coordinator->id,
                'message' => 'Material request #' . $request->id . ' was updated by ' . Auth::user()->name,
                'type' => 'Gmail',
                'status' => 'pending',
            ]);
        }

        session()->flash('success', 'Material request updated successfully.');
        return redirect()->route('portal.material');
    }

    public function cancel()
    {
        return redirect()->route('portal.material');
    }
};
