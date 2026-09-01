<?php

namespace App\Livewire\Admin\Inventory;

use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\Stock;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $statusFilter = '';
    public bool $showModal      = false;
    public bool $showAddModal   = false;

    // Add Stock Form
    public int    $resource_id    = 0;
    public int    $quantity_added = 1;
    public string $supplier       = '';
    public string $unit_price     = '';
    public string $arrival_date   = '';
    public string $arrival_time   = '';
    public string $remarks        = '';

    // Add Material Form
    public string $resource_name        = '';
    public string $description          = '';
    public string $type_name            = '';
    public int    $initial_quantity     = 0;
    public string $material_supplier    = '';
    public string $material_unit_price  = '';

    public function mount(): void
    {
        $this->arrival_date = now()->format('Y-m-d');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function materials()
    {
        return Resource::with(['resourceType', 'latestStock'])
            ->when($this->search, fn($q) =>
                $q->where('resource_name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
            )
            ->when($this->statusFilter, fn($q) =>
                $q->where('status', $this->statusFilter)
            )
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function allResources()
    {
        return Resource::orderBy('resource_name')->get();
    }

    #[Computed]
    public function totalMaterials(): int
    {
        return Resource::count();
    }

    #[Computed]
    public function lowStock(): int
    {
        return Resource::where('quantity_available', '<=', 5)
            ->where('quantity_available', '>', 0)
            ->count();
    }

    #[Computed]
    public function outOfStock(): int
    {
        return Resource::where('quantity_available', 0)->count();
    }

    #[Computed]
    public function totalUnits(): int
    {
        return Resource::sum('quantity_available');
    }

    public function openStockModal(int $id): void
    {
        $this->reset(['quantity_added', 'supplier', 'unit_price', 'arrival_time', 'remarks']);
        $this->resource_id  = $id;
        $this->arrival_date = now()->format('Y-m-d');
        $this->showModal    = true;
    }

    public function closeModal(): void
    {
        $this->showModal    = false;
        $this->showAddModal = false;
        $this->reset([
            'resource_id', 'quantity_added', 'supplier', 'unit_price',
            'arrival_date', 'arrival_time', 'remarks',
            'resource_name', 'description', 'type_name', 'initial_quantity',
            'material_supplier', 'material_unit_price',
        ]);
        $this->arrival_date = now()->format('Y-m-d');
    }

    public function addStock(): void
    {
        $this->validate([
            'resource_id'    => 'required|exists:resources,id',
            'quantity_added' => 'required|integer|min:1',
            'supplier'       => 'nullable|string|max:255',
            'unit_price'     => 'nullable|numeric|min:0',
        ]);

        $resource = Resource::with('resourceType')->findOrFail($this->resource_id);
        $before   = $resource->quantity_available;
        $after    = $before + $this->quantity_added;

        Stock::create([
            'resource_id'     => $this->resource_id,
            'user_id'         => Auth::id(),
            'quantity_added'  => $this->quantity_added,
            'quantity_before' => $before,
            'quantity_after'  => $after,
            'supplier'        => $this->supplier !== '' ? $this->supplier : ($resource->resourceType->type_name ?? 'Unspecified'),
            'unit_price'      => $this->unit_price !== '' ? $this->unit_price : null,
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
        $this->validate([
            'resource_name'        => 'required|string|max:255',
            'description'          => 'required|string',
            'type_name'            => 'required|string|max:255',
            'initial_quantity'     => 'required|integer|min:0',
            'material_supplier'    => 'nullable|string|max:255',
            'material_unit_price'  => 'nullable|numeric|min:0',
        ]);

        // ✅ Find or create resource type by name
        $resourceType = ResourceType::firstOrCreate(
            ['type_name' => $this->type_name]
        );

        $resource = Resource::create([
            'resource_name'      => $this->resource_name,
            'description'        => $this->description,
            'resource_type_id'   => $resourceType->id,
            'quantity_available' => $this->initial_quantity,
            'status'             => 'available',
        ]);

        if ($this->initial_quantity > 0) {
            Stock::create([
                'resource_id'     => $resource->id,
                'user_id'         => Auth::id(),
                'quantity_added'  => $this->initial_quantity,
                'quantity_before' => 0,
                'quantity_after'  => $this->initial_quantity,
                'supplier'        => $this->material_supplier !== '' ? $this->material_supplier : $resourceType->type_name,
                'unit_price'      => $this->material_unit_price !== '' ? $this->material_unit_price : null,
                'arrival_date'    => now()->format('Y-m-d'),
                'remarks'         => 'Initial stock',
            ]);
        }

        $this->closeModal();
        session()->flash('success', 'Material added successfully.');
    }

    public function delete(int $id): void
    {
        Resource::findOrFail($id)->delete();
        session()->flash('success', 'Material deleted successfully.');
    }
};
