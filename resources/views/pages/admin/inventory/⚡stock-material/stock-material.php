<?php

namespace App\Livewire\Admin\Inventory;

use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\Stock;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    protected array $excludedTypes = [\App\Models\ResourceType::FACILITY];

    /**
     * ✅ Maximum stock allowed per unit.
     *    Pcs  → up to 10,000
     *    Ream → up to 1,000
     *    Set  → up to 1,000
     */
    public const UNIT_LIMITS = [
        'Pcs'  => 10000,
        'Ream' => 1000,
        'Set'  => 1000,
    ];

    public string $search       = '';
    public string $statusFilter = '';
    // ✅ Click a card: '' (all) | low | out
    #[Url(as: 'stock')]
    public string $stockFilter  = '';
    public bool $showRemoveModal = false;
    public bool $showModal      = false;
    public bool $showAddModal   = false;
    public bool $showEditModal  = false;

    // Add Stock Form
    // ✅ Untyped on purpose: when the input box is cleared, Livewire sends ""
    //    (a string). A typed `int` property would crash with a TypeError.
    public $resource_id    = 0;
    public $quantity_added = 1;
    public string $supplier       = '';
    public string $arrival_date   = '';
    public string $arrival_time   = '';
    public string $remarks        = '';

    // Add Material Form
    public string $resource_name     = '';
    public string $description       = '';
    public string $type_name         = '';
    public        $initial_quantity  = 0; // ✅ untyped (see above)
    public string $unit              = 'Ream';
    public string $material_supplier = '';
    public        $low_stock_at      = 5; // ✅ warn when stock is at or below this
    // ✅ "Must be returned" is decided by the type (Material = returned, Supplies = used up)

    // Edit Material Form
    public int    $edit_id            = 0;
    public string $edit_resource_name = '';
    public string $edit_description   = '';
    public string $edit_type_name     = '';
    public string $edit_unit          = 'Ream';
    public string $edit_status        = 'available';
    public        $edit_low_stock_at  = 5;

    // ✅ Remove Stock Form (used up, damaged, lost, count correction)
    public int    $remove_id     = 0;
    public        $remove_qty    = 1;
    public string $remove_reason = 'used_up';
    public string $remove_notes  = '';

    public function mount(): void
    {
        // ✅ Server-side guard — page requires `stock-materials.view`
        abort_unless(auth()->user()->can('stock-materials.view'), 403);

        $this->arrival_date = now()->format('Y-m-d');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStockFilter(): void
    {
        $this->resetPage();
    }

    /** ✅ Card click: show only low / out-of-stock materials (click again to show all). */
    public function filterStock(string $which): void
    {
        $this->stockFilter = ($which === $this->stockFilter || ! in_array($which, ['low', 'out'], true)) ? '' : $which;
        $this->resetPage();
    }

    protected function materialsBaseQuery()
    {
        return Resource::where(function ($q) {
            $q->whereHas('resourceType', function ($q2) {
                $q2->whereNotIn('type_name', $this->excludedTypes);
            })->orWhereDoesntHave('resourceType');
        });
    }

    #[Computed]
    public function materials()
    {
        return $this->materialsBaseQuery()
            ->with(['resourceType', 'latestRestock'])
            // ✅ Grouped, so the search only narrows the list (it used to skip the Status filter)
            ->when($this->search, fn($q) => $q->where(fn ($s) => $s
                ->where('resource_name', 'like', '%' . $this->search . '%')
                ->orWhere('description', 'like', '%' . $this->search . '%')))
            ->when($this->statusFilter, fn($q) =>
                $q->where('status', $this->statusFilter)
            )
            ->when($this->stockFilter === 'low', fn ($q) => $this->whereLow($q))
            ->when($this->stockFilter === 'out', fn ($q) => $q->where('quantity_available', '<=', 0))
            ->latest()
            ->paginate(10);
    }

    /** ✅ At or below the material's own "warn me" level, but not empty. */
    protected function whereLow($query)
    {
        return $query->where('quantity_available', '>', 0)
            ->whereColumn('quantity_available', '<=', 'low_stock_at');
    }

    /**
     * ✅ Material items (chairs, projectors…) Program Heads borrowed and haven't brought back yet:
     *    resource_id => pieces out.
     */
    #[Computed]
    public function lentOut(): array
    {
        return \App\Models\RequestItem::query()
            ->whereNull('returned_at')
            ->whereNull('assigned_at')          // kept items are counted in assignedOut()
            ->whereNotNull('resource_id')
            ->whereHas('resource', fn ($r) => $r->where('is_returnable', true))
            ->whereHas('request', fn ($q) => $q->where('status', 'approved')
                ->whereHas('user', fn ($u) => $u->role('program head')))
            ->selectRaw('resource_id, SUM(quantity) as n')
            ->groupBy('resource_id')
            ->pluck('n', 'resource_id')
            ->map(fn ($n) => (int) $n)
            ->all();
    }

    /**
     * ✅ Material items from the main stock that someone KEEPS (assigned, no return date):
     *    resource_id => pieces. Counted on the Program Head's copy, so each set is counted once.
     */
    #[Computed]
    public function assignedOut(): array
    {
        return \App\Models\RequestItem::query()
            ->whereNull('returned_at')
            ->whereNotNull('assigned_at')
            ->whereNotNull('resource_id')
            ->whereHas('request', fn ($q) => $q->where('status', 'approved')
                ->whereHas('user', fn ($u) => $u->role('program head')))
            ->selectRaw('resource_id, SUM(quantity) as n')
            ->groupBy('resource_id')
            ->pluck('n', 'resource_id')
            ->map(fn ($n) => (int) $n)
            ->all();
    }

    #[Computed]
    public function allResources()
    {
        return Resource::where(function ($q) {
                $q->whereHas('resourceType', function ($q2) {
                    $q2->whereNotIn('type_name', $this->excludedTypes);
                })->orWhereDoesntHave('resourceType');
            })
            ->orderBy('resource_name')
            ->get();
    }

    #[Computed]
    public function totalMaterials(): int
    {
        return $this->materialsBaseQuery()->count();
    }

    #[Computed]
    public function lowStock(): int
    {
        return $this->whereLow($this->materialsBaseQuery())->count();
    }

    #[Computed]
    public function outOfStock(): int
    {
        return $this->materialsBaseQuery()
            ->where('quantity_available', '<=', 0)
            ->count();
    }

    #[Computed]
    public function totalUnits(): int
    {
        return $this->materialsBaseQuery()->sum('quantity_available');
    }

    public function openStockModal(int $id): void
    {
        // ✅ Server-side guard — adding stock requires `stock-materials.create`
        abort_unless(auth()->user()->can('stock-materials.create'), 403);

        $this->reset(['quantity_added', 'supplier', 'arrival_time', 'remarks']);
        $this->resource_id  = $id;
        $this->arrival_date = now()->format('Y-m-d');
        $this->showModal    = true;
    }

    public function openAddModal(): void
    {
        // ✅ Server-side guard — adding a material requires `stock-materials.create`
        abort_unless(auth()->user()->can('stock-materials.create'), 403);

        $this->showAddModal = true;
    }

    public function openEditModal(int $id): void
    {
        // ✅ Server-side guard — editing a material requires `stock-materials.update`
        abort_unless(auth()->user()->can('stock-materials.update'), 403);

        $resource = Resource::with('resourceType')->findOrFail($id);

        $this->edit_id            = $resource->id;
        $this->edit_resource_name = $resource->resource_name;
        $this->edit_description   = $resource->description;
        $this->edit_type_name     = $resource->resourceType->type_name ?? '';
        $this->edit_unit          = $resource->unit ?? 'Ream';
        $this->edit_status        = $resource->status ?? 'available';
        $this->edit_low_stock_at  = (int) ($resource->low_stock_at ?? 5);

        $this->showEditModal = true;
    }

    /** ✅ Take stock OUT (used up, damaged, lost, count correction). */
    public function openRemoveModal(int $id): void
    {
        abort_unless(auth()->user()->can('stock-materials.update'), 403);

        $resource = $this->materialsBaseQuery()->findOrFail($id);

        $this->reset(['remove_qty', 'remove_reason', 'remove_notes']);
        $this->resetErrorBag();
        $this->remove_id       = $resource->id;
        $this->showRemoveModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal       = false;
        $this->showRemoveModal = false;
        $this->showAddModal   = false;
        $this->showEditModal  = false;
        $this->reset([
            'resource_id', 'quantity_added', 'supplier',
            'arrival_date', 'arrival_time', 'remarks',
            'resource_name', 'description', 'type_name', 'initial_quantity',
            'unit', 'material_supplier',
            'edit_id', 'edit_resource_name', 'edit_description', 'edit_type_name',
            'edit_unit', 'edit_status', 'edit_low_stock_at', 'low_stock_at',
            'remove_id', 'remove_qty', 'remove_reason', 'remove_notes',
        ]);
        $this->resetErrorBag();
        $this->arrival_date = now()->format('Y-m-d');
        $this->unit         = 'Ream';
        $this->edit_unit    = 'Ream';
        $this->edit_status  = 'available';
    }

    /** Max stock for a unit (null = no limit for unknown units). */
    protected function unitLimit(?string $unit): ?int
    {
        return self::UNIT_LIMITS[$unit ?: 'Ream'] ?? null;
    }

    public function addStock(): void
    {
        // ✅ Server-side guard
        abort_unless(auth()->user()->can('stock-materials.create'), 403);

        $this->validate([
            'resource_id'    => 'required|exists:resources,id',
            'quantity_added' => 'required|integer|min:1',
            'supplier'       => 'nullable|string|max:255',
        ]);

        $resource = Resource::with('resourceType')->findOrFail($this->resource_id);
        $qtyAdded = (int) $this->quantity_added;
        $before   = (int) $resource->quantity_available;
        $after    = $before + $qtyAdded;

        // ✅ Unit limit check (Pcs 10,000 / Ream 1,000 / Set 1,000)
        $unit  = $resource->unit ?: 'Ream';
        $limit = $this->unitLimit($unit);

        if ($limit !== null && $after > $limit) {
            $canAdd = max($limit - $before, 0);
            $this->addError(
                'quantity_added',
                "Stock limit for {$unit} is " . number_format($limit) . " {$unit}. "
                . "Current stock is " . number_format($before) . ", so you can only add up to " . number_format($canAdd) . " more."
            );
            return;
        }

        Stock::create([
            'resource_id'     => $this->resource_id,
            'user_id'         => Auth::id(),
            'quantity_added'  => $qtyAdded,
            'quantity_before' => $before,
            'quantity_after'  => $after,
            'supplier'        => $this->supplier !== '' ? $this->supplier : ($resource->resourceType->type_name ?? 'Unspecified'),
            'arrival_date'    => now()->format('Y-m-d'),
            'arrival_time'    => now()->format('H:i'),
            'remarks'         => $this->remarks !== '' ? $this->remarks : null,
        ]);

        $resource->update(['quantity_available' => $after]);
        $this->closeModal();
        session()->flash('success', 'Stock added successfully.');
    }

    public function addMaterial(): void
    {
        // ✅ Server-side guard
        abort_unless(auth()->user()->can('stock-materials.create'), 403);

        // ✅ Unit limit (Pcs 10,000 / Ream 1,000 / Set 1,000)
        $limit = $this->unitLimit($this->unit);

        $this->validate([
            'resource_name'     => 'required|string|max:255',
            'type_name'         => 'required|in:' . implode(',', \App\Models\ResourceType::STOCK_TYPES),
            'initial_quantity'  => 'required|integer|min:0' . ($limit !== null ? '|max:' . $limit : ''),
            'unit'              => 'required|string|max:50',
            'material_supplier' => 'nullable|string|max:255',
            'low_stock_at'      => 'required|integer|min:0' . ($limit !== null ? '|max:' . $limit : ''),
        ], [
            'low_stock_at.required' => 'Enter a number (0 = never warn).',
            'initial_quantity.max' => "Stock limit for {$this->unit} is " . number_format((int) $limit) . " {$this->unit}.",
        ]);

        $initialQty = (int) $this->initial_quantity;

        try {
            $resourceType = ResourceType::firstOrCreate(['type_name' => $this->type_name]);

            $resource = Resource::create([
                'resource_name'      => $this->resource_name,
                'description'        => $this->description !== '' ? $this->description : $this->resource_name,
                'resource_type_id'   => $resourceType->id,
                'quantity_available' => $initialQty,
                'unit'               => $this->unit,
                'low_stock_at'       => (int) $this->low_stock_at,
                'status'             => 'available',
            ]);

            if ($initialQty > 0) {
                Stock::create([
                    'resource_id'     => $resource->id,
                    'user_id'         => Auth::id(),
                    'quantity_added'  => $initialQty,
                    'quantity_before' => 0,
                    'quantity_after'  => $initialQty,
                    'supplier'        => $this->material_supplier !== '' ? $this->material_supplier : $resourceType->type_name,
                    'arrival_date'    => now()->format('Y-m-d'),
                    'remarks'         => 'Initial stock',
                ]);
            }

            $this->closeModal();
            session()->flash('success', 'Material added successfully.');

        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Could not add material: ' . $e->getMessage());
        }
    }

    public function updateMaterial(): void
    {
        // ✅ Server-side guard
        abort_unless(auth()->user()->can('stock-materials.update'), 403);

        $this->validate([
            'edit_resource_name' => 'required|string|max:255',
            'edit_description'   => 'nullable|string',
            'edit_type_name'     => 'required|in:' . implode(',', \App\Models\ResourceType::STOCK_TYPES),
            'edit_unit'          => 'required|string|max:50',
            'edit_status'        => 'required|in:available,unavailable,maintenance',
            'edit_low_stock_at'  => 'required|integer|min:0|max:10000',
        ], [
            'edit_low_stock_at.required' => 'Enter a number (0 = never warn).',
        ]);

        // ✅ Changing the unit must not leave the stock above the new unit's limit
        $resource = Resource::findOrFail($this->edit_id);
        $limit    = $this->unitLimit($this->edit_unit);

        if ($limit !== null && $resource->quantity_available > $limit) {
            $this->addError(
                'edit_unit',
                "Stock limit for {$this->edit_unit} is " . number_format($limit) . " {$this->edit_unit}, "
                . "but this material has " . number_format($resource->quantity_available) . " in stock. Choose another unit."
            );
            return;
        }

        try {
            $resourceType = ResourceType::firstOrCreate(['type_name' => $this->edit_type_name]);

            $resource->update([
                'resource_name'    => $this->edit_resource_name,
                'description'      => $this->edit_description !== '' ? $this->edit_description : $this->edit_resource_name,
                'resource_type_id' => $resourceType->id,
                'unit'             => $this->edit_unit,
                'status'           => $this->edit_status,
                'low_stock_at'     => (int) $this->edit_low_stock_at,
            ]);

            $this->closeModal();
            session()->flash('success', 'Material updated successfully.');

        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Could not update material: ' . $e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        // ✅ Server-side guard — deleting requires `stock-materials.delete`
        abort_unless(auth()->user()->can('stock-materials.delete'), 403);

        $resource = $this->materialsBaseQuery()->findOrFail($id);

        if ($why = $this->deleteBlocker($resource)) {
            session()->flash('error', "Cannot delete {$resource->resource_name}: {$why}. "
                . 'Deleting it would also erase those records. Set it to "Unavailable" (Edit) instead.');
            return;
        }

        $name = $resource->resource_name;
        DB::transaction(fn () => $resource->delete());
        session()->flash('success', "{$name} deleted.");
    }

    /**
     * ✅ Why a material can't be deleted (null = safe).
     *    The database deletes a material's request items and stock history with it,
     *    so only a material nobody has used yet may go.
     */
    public function deleteBlocker(Resource $resource): ?string
    {
        $reasons = [];

        if ($kept = $this->assignedOut[$resource->id] ?? 0) {
            $reasons[] = number_format($kept) . ' ' . ($resource->unit ?: 'Pcs') . ' are kept by teachers (assigned)';
        }

        if ($lent = $this->lentOut[$resource->id] ?? 0) {
            $reasons[] = number_format($lent) . ' ' . ($resource->unit ?: 'Pcs') . ' still lent out';
        }

        $requests = \App\Models\RequestItem::where('resource_id', $resource->id)->distinct()->count('request_id');
        if ($requests > 0) {
            $reasons[] = "it is in {$requests} " . \Illuminate\Support\Str::plural('request', $requests);
        }

        // the "Initial stock" entry made when the material was added doesn't count
        $history = $resource->stocks()
            ->where(fn ($q) => $q->whereNull('remarks')->orWhere('remarks', '!=', 'Initial stock'))
            ->count();
        if ($history > 0) {
            $reasons[] = "it has {$history} stock history " . \Illuminate\Support\Str::plural('entry', $history);
        }

        return $reasons ? implode(', ', $reasons) : null;
    }

    /** ✅ Take stock out, with a reason, recorded in Stock History (Before → After). */
    public function removeStock(): void
    {
        abort_unless(auth()->user()->can('stock-materials.update'), 403);

        $this->validate([
            'remove_id'     => 'required|integer',
            'remove_qty'    => 'required|integer|min:1',
            'remove_reason' => 'required|in:' . implode(',', array_keys(Stock::REASONS)),
            'remove_notes'  => 'nullable|string|max:255|required_if:remove_reason,correction',
        ], [
            'remove_qty.min'           => 'Remove at least 1.',
            'remove_notes.required_if' => 'Please say what the correct count is based on (e.g. "Counted on Nov 27").',
        ]);

        $resource = $this->materialsBaseQuery()->findOrFail($this->remove_id);
        $qty      = (int) $this->remove_qty;
        $unit     = $resource->unit ?: 'Ream';
        $error    = null;

        DB::transaction(function () use ($resource, $qty, $unit, &$error) {
            $before = (int) Resource::whereKey($resource->id)->lockForUpdate()->value('quantity_available');

            if ($qty > $before) {
                $error = 'Only ' . number_format($before) . " {$unit} in stock.";
                return;
            }

            Stock::create([
                'resource_id'     => $resource->id,
                'user_id'         => Auth::id(),
                'quantity_added'  => -$qty,
                'quantity_before' => $before,
                'quantity_after'  => $before - $qty,
                'supplier'        => null,
                'arrival_date'    => now()->format('Y-m-d'),
                'arrival_time'    => now()->format('H:i'),
                'remarks'         => $this->remove_notes !== '' ? $this->remove_notes : null,
                'reason'          => $this->remove_reason,
            ]);

            Resource::whereKey($resource->id)->update(['quantity_available' => $before - $qty]);
        });

        if ($error) {
            $this->addError('remove_qty', $error);
            return;
        }

        $label = Stock::REASONS[$this->remove_reason];
        $this->closeModal();
        session()->flash('success', "Removed {$qty} {$unit} of {$resource->resource_name} ({$label}). It is recorded in Stock History.");
    }
};
