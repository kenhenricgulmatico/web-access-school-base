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
use App\Support\BookingDate;

new #[Layout('layouts.student-faculty')] class extends Component
{
    public $requestId;
    public $purpose = '';
    public $items = [];

    public $availableResources = [];

    // ✅ When the borrowed Pcs items will be returned (max 1 month)
    public $return_by = '';

    // ✅ The day the materials are needed (today up to ~6 months ahead)
    public $request_date = '';

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

    // Livewire uses these methods (they add the return-date rule to $rules / $messages)
    protected function rules(): array
    {
        return array_merge($this->rules, ['request_date' => BookingDate::rules()], $this->returnByRules());
    }

    protected function messages(): array
    {
        return array_merge($this->messages, BookingDate::messages('request_date'), $this->returnByMessages());
    }

    protected function formatQuantity(int $qty, ?string $unit): string
    {
        $unit = trim($unit ?? '') ?: 'Ream';

        return "{$qty} {$unit}";
    }

    public function mount($id)
    {
        // ✅ Matches seeder: faculty/student get material-requests.update
        abort_unless(
            auth()->user()->can('material-requests.update'),
            403
        );

        $this->requestId = $id;
        $request = ResourceRequest::with('items')
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        if ($request->status !== 'pending') {
            session()->flash('error', 'Only pending requests can be edited.');
            return redirect()->route('portal.material');
        }

        // ✅ Endorsed to the Admin → the Admin is reviewing exactly this version; no edits
        if (\App\Support\ForwardedRequests::isWaitingForAdmin($request)) {
            session()->flash('error', 'This request was sent to the Admin by your Program Head, so it can no longer be edited. Cancel it and make a new one if you need changes.');
            return redirect()->route('portal.material');
        }

        $user = Auth::user();

        // ✅ What students / faculty can request:
        //    - Ream / Set items (bond paper, markers…): ONLY what their department has.
        //    - Pcs items (chairs…): what the department has + what the Admin can still lend.
        //      The department does not need to have them yet — the Program Head can borrow
        //      them from the Admin first (Request to Admin), then approve this request.
        if ($user->department_id) {
            $this->availableResources = DB::table('resources')
                ->join('resource_types', 'resource_types.id', '=', 'resources.resource_type_id')
                ->leftJoin('resource_all_locations', function ($join) use ($user) {
                    $join->on('resource_all_locations.resource_id', '=', 'resources.id')
                         ->where('resource_all_locations.department_id', '=', $user->department_id);
                })
                ->where('resources.status', 'available')
                ->where('resource_types.type_name', '!=', \App\Models\ResourceType::FACILITY)
                ->orderBy('resources.resource_name')
                ->select(
                    'resources.id as resource_id',
                    'resources.resource_name',
                    'resources.unit',
                    'resources.is_returnable',
                    // Material: department + Admin  |  Supplies: department only
                    DB::raw("CASE WHEN resources.is_returnable = 1
                                  THEN COALESCE(resource_all_locations.allocated_quantity, 0) + resources.quantity_available
                                  ELSE COALESCE(resource_all_locations.allocated_quantity, 0)
                             END as allocated_quantity")
                )
                ->get()
                ->filter(fn ($resource) => (int) $resource->allocated_quantity > 0)
                ->map(function ($resource) {
                    $resource->available_formatted = $this->formatQuantity(
                        (int) $resource->allocated_quantity,
                        $resource->unit
                    );
                    return (array) $resource;
                })
                ->values()
                ->toArray();
        }

        $this->purpose = $request->purpose;

        // ✅ Keep the saved "date needed" (today if it has already passed)
        $savedDate = $request->items->pluck('request_date')->filter()->min();
        $this->request_date = ($savedDate && \Carbon\Carbon::parse($savedDate)->gte(today()))
            ? \Carbon\Carbon::parse($savedDate)->toDateString()
            : today()->toDateString();
        $this->items = $request->items->map(function ($item) {
            return [
                'id' => $item->id,
                'resource_id' => $item->resource_id,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        // ✅ Return date chosen earlier (saved on the Pcs items only)
        $saved = $request->items->whereNotNull('due_back_at')->first()?->due_back_at;
        $this->return_by = $saved ? $saved->format('Y-m-d') : '';
    }

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
            return 'More than what is in stock — please request less';
        }

        return null;
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
        // ✅ Server-side guard — updating requires material-requests.update
        abort_unless(
            auth()->user()->can('material-requests.update'),
            403
        );

        $this->validate();

        $request = ResourceRequest::where('user_id', Auth::id())
            ->where('id', $this->requestId)
            ->where('status', 'pending')
            ->firstOrFail();

        if (\App\Support\ForwardedRequests::isWaitingForAdmin($request)) {
            session()->flash('error', 'This request was sent to the Admin by your Program Head, so it can no longer be edited.');
            return redirect()->route('portal.material');
        }

        $user = Auth::user();

        foreach ($this->items as $i => $item) {
            // ✅ Not more than what is available (Pcs: department + Admin, Ream / Set: department)
            $choice = $this->getMaterialResource($item['resource_id']);

            if (! $choice) {
                $this->addError("items.$i.resource_id", 'This material is not available for your department.');
                return;
            }

            if ((int) $item['quantity'] > (int) $choice['allocated_quantity']) {
                $this->addError("items.$i.quantity", 'Not enough in stock for this quantity. Please request less.');
                return;
            }
        }

        $request->update(['purpose' => $this->purpose]);

        $existingIds = collect($this->items)->filter(fn($i) => isset($i['id']))->pluck('id')->toArray();

        RequestItem::where('request_id', $request->id)
            ->whereNotIn('id', $existingIds)
            ->delete();

        foreach ($this->items as $item) {
            $resource = DB::table('resources')->where('id', $item['resource_id'])->first();

            if (isset($item['id'])) {
                RequestItem::where('id', $item['id'])->update([
                    'resource_id' => $item['resource_id'],
                    'item_name' => $resource->resource_name,
                    'quantity' => $item['quantity'],
                    'request_date' => $this->request_date, // ✅ was never updated before
                    // ✅ Only Pcs items get the return date. Ream / Set: none.
                    'due_back_at' => ($resource->is_returnable && $this->return_by) ? $this->return_by : null,
                ]);
            } else {
                RequestItem::create([
                    'request_id' => $request->id,
                    'resource_id' => $item['resource_id'],
                    'item_name' => $resource->resource_name,
                    'quantity' => $item['quantity'],
                    'request_date' => $this->request_date, // ✅ was "today" on every edit
                    'start_time' => null,
                    'end_time' => null,
                    // ✅ Only Pcs items get the return date. Ream / Set: none.
                    'due_back_at' => ($resource->is_returnable && $this->return_by) ? $this->return_by : null,
                ]);
            }
        }

        // ✅ Only the Program Head(s) of this department — linked to the request
        $coordinators = User::role('program head')
            ->where('department_id', Auth::user()->department_id)
            ->get();

        foreach ($coordinators as $coordinator) {
            Notification::create([
                'user_id' => $coordinator->id,
                'request_id' => $request->id,
                'message' => 'A material request was updated by ' . Auth::user()->name,
                'type' => 'Gmail',
                'status' => 'pending',
            ]);
        }

        session()->flash('success', 'Material request updated successfully.');
        return redirect()->route('portal.material');
    }

    // ============================================================
    // ✅ RETURN DEADLINE — ONLY for items counted in pieces (Pcs, e.g. chairs)
    //    Ream and Set items are used up, so they never get a return date.
    // ============================================================

    /** Longest a borrower may keep Pcs items: 1 month. */
    public const MAX_BORROW_MONTHS = 1;

    /** True when at least one selected item is a Material (it must be returned). */
    public function hasBorrowItems(): bool
    {
        $ids = collect($this->items)
            ->pluck('resource_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return $ids && \App\Models\Resource::whereIn('id', $ids)->where('is_returnable', true)->exists();
    }

    /** Earliest return date: the day the materials are needed (never before today). */
    public function returnByMin(): string
    {
        $start = $this->request_date
            ? (rescue(fn () => \Carbon\Carbon::parse($this->request_date), today(), false))
            : today();

        return ($start->lt(today()) ? today() : $start)->format('Y-m-d');
    }

    /** Latest return date: 1 month after the earliest date. */
    public function returnByMax(): string
    {
        return \Carbon\Carbon::parse($this->returnByMin())
            ->addMonthsNoOverflow(self::MAX_BORROW_MONTHS)
            ->format('Y-m-d');
    }

    /** Validation for the return date (only when Pcs items are selected). */
    protected function returnByRules(): array
    {
        return $this->hasBorrowItems()
            ? ['return_by' => 'required|date|after_or_equal:' . $this->returnByMin() . '|before_or_equal:' . $this->returnByMax()]
            : [];
    }

    protected function returnByMessages(): array
    {
        $min = \Carbon\Carbon::parse($this->returnByMin())->format('M d, Y');
        $max = \Carbon\Carbon::parse($this->returnByMax())->format('M d, Y');

        return [
            'return_by.required'        => 'Please choose when you will return the borrowed items.',
            'return_by.date'            => 'Please enter a valid return date.',
            'return_by.after_or_equal'  => "The return date cannot be before {$min}.",
            'return_by.before_or_equal' => "You can borrow items for 1 month only. Please return them by {$max}.",
        ];
    }

    public function cancel()
    {
        return redirect()->route('portal.material');
    }
};
