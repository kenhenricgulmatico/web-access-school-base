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

    protected array $excludedTypes = ['Facility'];

    public string $search       = '';
    public string $statusFilter = '';
    public bool $showModal      = false;
    public bool $showAddModal   = false;
    public bool $showEditModal  = false;

    // Add Stock Form
    public int    $resource_id    = 0;
    public int    $quantity_added = 1;
    public string $supplier       = '';
    public string $arrival_date   = '';
    public string $arrival_time   = '';
    public string $remarks        = '';

    // Add Material Form
    public string $resource_name     = '';
    public string $description       = '';
    public string $type_name         = '';
    public int    $initial_quantity  = 0;
    public string $unit              = 'Ream';
    public string $material_supplier = '';
    public bool   $is_returnable     = false; // must be given back (chairs, projectors…)

    // Edit Material Form
    public int    $edit_id            = 0;
    public string $edit_resource_name = '';
    public string $edit_description   = '';
    public string $edit_type_name     = '';
    public string $edit_unit          = 'Ream';
    public string $edit_status        = 'available';
    public bool   $edit_is_returnable = false;

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
        return Resource::with(['resourceType', 'latestStock'])
            ->where(function ($q) {
                $q->whereHas('resourceType', function ($q2) {
                    $q2->whereNotIn('type_name', $this->excludedTypes);
                })->orWhereDoesntHave('resourceType');
            })
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
        return $this->materialsBaseQuery()
            ->where('quantity_available', '<=', 5)
            ->where('quantity_available', '>', 0)
            ->count();
    }

    #[Computed]
    public function outOfStock(): int
    {
        return $this->materialsBaseQuery()
            ->where('quantity_available', 0)
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
        $this->edit_is_returnable = (bool) $resource->is_returnable;

        $this->showEditModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal      = false;
        $this->showAddModal   = false;
        $this->showEditModal  = false;
        $this->reset([
            'resource_id', 'quantity_added', 'supplier',
            'arrival_date', 'arrival_time', 'remarks',
            'resource_name', 'description', 'type_name', 'initial_quantity',
            'unit', 'material_supplier',
            'edit_id', 'edit_resource_name', 'edit_description', 'edit_type_name',
            'edit_unit', 'edit_status', 'is_returnable', 'edit_is_returnable',
        ]);
        $this->arrival_date = now()->format('Y-m-d');
        $this->unit         = 'Ream';
        $this->edit_unit    = 'Ream';
        $this->edit_status  = 'available';
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
        $before   = $resource->quantity_available;
        $after    = $before + $this->quantity_added;

        Stock::create([
            'resource_id'     => $this->resource_id,
            'user_id'         => Auth::id(),
            'quantity_added'  => $this->quantity_added,
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

        $this->validate([
            'resource_name'     => 'required|string|max:255',
            'type_name'         => 'required|string|max:255|not_in:' . implode(',', $this->excludedTypes),
            'initial_quantity'  => 'required|integer|min:0',
            'unit'              => 'required|string|max:50',
            'material_supplier' => 'nullable|string|max:255',
        ]);

        try {
            $resourceType = ResourceType::firstOrCreate(['type_name' => $this->type_name]);

            $resource = Resource::create([
                'resource_name'      => $this->resource_name,
                'description'        => $this->description !== '' ? $this->description : $this->resource_name,
                'resource_type_id'   => $resourceType->id,
                'quantity_available' => $this->initial_quantity,
                'unit'               => $this->unit,
                'status'             => 'available',
                'is_returnable'      => $this->is_returnable,
            ]);

            if ($this->initial_quantity > 0) {
                Stock::create([
                    'resource_id'     => $resource->id,
                    'user_id'         => Auth::id(),
                    'quantity_added'  => $this->initial_quantity,
                    'quantity_before' => 0,
                    'quantity_after'  => $this->initial_quantity,
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
            'edit_type_name'     => 'required|string|max:255|not_in:' . implode(',', $this->excludedTypes),
            'edit_unit'          => 'required|string|max:50',
            'edit_status'        => 'required|in:available,unavailable,maintenance',
        ]);

        try {
            $resource     = Resource::findOrFail($this->edit_id);
            $resourceType = ResourceType::firstOrCreate(['type_name' => $this->edit_type_name]);

            $resource->update([
                'resource_name'    => $this->edit_resource_name,
                'description'      => $this->edit_description !== '' ? $this->edit_description : $this->edit_resource_name,
                'resource_type_id' => $resourceType->id,
                'unit'             => $this->edit_unit,
                'status'           => $this->edit_status,
                'is_returnable'    => $this->edit_is_returnable,
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

        Resource::findOrFail($id)->delete();
        session()->flash('success', 'Material deleted successfully.');
    }
};
