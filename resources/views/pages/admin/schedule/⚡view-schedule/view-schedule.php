<?php

namespace App\Livewire\Admin\Schedule;

use App\Models\Resource;
use App\Models\ResourceType;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public string $search = '';

    public function updatingSearch() {}

    public function delete(int $id)
    {
        Resource::findOrFail($id)->delete();
        session()->flash('success', 'Facility deleted successfully.');
    }

    public function toggleStatus(int $id)
    {
        $facility = Resource::findOrFail($id);
        $facility->status = $facility->status === 'available' ? 'maintenance' : 'available';
        $facility->save();
    }

    /**
     * All facilities (resources tagged under the "Facility" type),
     * matching the search filter, sorted by name.
     */
    #[Computed]
public function facilities()
{
    $facilityType = ResourceType::where('type_name', \App\Models\ResourceType::FACILITY)->first();

    // No "Facility" type exists yet -> there can be no facilities. Return empty, not everything.
    if (! $facilityType) {
        return collect();
    }

    return Resource::query()
        ->where('resource_type_id', $facilityType->id)
        ->when(
            $this->search,
            fn($q) => $q->where('resource_name', 'like', '%' . $this->search . '%')
        )
        ->orderBy('resource_name')
        ->get();
}
};
