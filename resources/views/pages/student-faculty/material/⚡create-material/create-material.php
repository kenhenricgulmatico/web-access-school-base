<?php

use App\Models\RequestType;
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
    public $purpose = '';
    public $items = [
        ['resource_id' => '', 'quantity' => 1],
    ];

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

    protected function formatQuantity(int $qtyInPcs, ?string $unit): string
    {
        $unit = trim($unit ?? '') ?: 'Ream';

        return "{$qtyInPcs} {$unit}";
    }

    public function mount()
    {
        // ✅ Matches seeder: faculty/student get material-requests.create
        abort_unless(
            auth()->user()->can('material-requests.create'),
            403
        );

        $user = Auth::user();

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
                        $resource->unit,
                    );
                    return (array) $resource;
                })
                ->toArray();
        }
    }

    public function getMaterialResource($resourceId)
    {
        if (!$resourceId) return null;

        return collect($this->availableResources)->firstWhere('resource_id', (int) $resourceId);
    }

    public function getAvailableStock($resourceId): ?string
    {
        $resource = $this->getMaterialResource($resourceId);

        if (!$resource) {
            return null;
        }

        return $resource['available_formatted'];
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

    public function addItem()
    {
        $this->items[] = ['resource_id' => '', 'quantity' => 1];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function submit()
    {
        // ✅ Server-side guard — creating requires material-requests.create
        abort_unless(
            auth()->user()->can('material-requests.create'),
            403
        );

        $this->validate();

        $user = Auth::user();

        if (!$user->department_id) {
            session()->flash('error', 'Your account has no department assigned. Please contact admin.');
            return;
        }

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
                    : '0 Pcs';
                $this->addError("items.$i.quantity", "Only {$available} available in your department.");
                return;
            }
        }

        $request = ResourceRequest::create([
            'user_id' => $user->id,
            'department_id' => $user->department_id,
            'request_type_id' => RequestType::MATERIAL,
            'purpose' => $this->purpose,
            'status' => 'pending',
            'current_responsibility_center_id' => $user->responsibility_center_id ?? null,
        ]);

        foreach ($this->items as $item) {
            $resource = DB::table('resources')->where('id', $item['resource_id'])->first();

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

        $programHeads = User::role('program head')->get();
        foreach ($programHeads as $programHead) {
            Notification::create([
                'user_id' => $programHead->id,
                'message' => "New material request from {$user->name}: {$request->items->count()} item(s).",
                'type' => 'Gmail',
                'status' => 'pending',
            ]);
        }

        session()->flash('success', 'Material request submitted! Waiting for program head approval.');
        return redirect()->route('portal.material');
    }

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

    public function cancel()
    {
        return redirect()->route('portal.material');
    }
};
