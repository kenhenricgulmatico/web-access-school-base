<?php

namespace App\Livewire\Coordinator\RequestToAdmin;

use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.coordinator')] class extends Component
{
    // Edit modal state
    public bool $showEditModal = false;
    public ?int $editingId = null;
    public $request_type_id = '';

    // Shared fields
    public $purpose = '';
    public $request_date = '';

    // Facility fields
    public $facility_name = '';
    public $start_time = '';
    public $end_time = '';

    // Material fields
    public $items = [
        ['name' => '', 'quantity' => 1],
    ];

    protected function rules()
    {
        $rules = [
            'purpose'      => 'required|string|min:10|max:500',
            'request_date' => 'required|date|after_or_equal:today',
        ];

        if ($this->request_type_id == 1) {
            $rules['facility_name'] = 'required|string|max:255';
            $rules['start_time']    = 'required';
            $rules['end_time']      = 'required|after:start_time';
        }

        if ($this->request_type_id == 2) {
            $rules['items']            = 'required|array|min:1';
            $rules['items.*.name']     = 'required|string|max:255';
            $rules['items.*.quantity'] = 'required|integer|min:1';
        }

        return $rules;
    }

    protected $messages = [
        'purpose.required'         => 'Please state your purpose',
        'purpose.min'              => 'Purpose must be at least 10 characters',
        'request_date.required'    => 'Please select a date',
        'request_date.after_or_equal' => 'Date must be today or later',
        'facility_name.required'   => 'Please enter a facility name',
        'start_time.required'      => 'Please select start time',
        'end_time.required'        => 'Please select end time',
        'end_time.after'           => 'End time must be after start time',
        'items.required'           => 'Please add at least one material',
        'items.*.name.required'    => 'Please enter the material name',
        'items.*.quantity.required'=> 'Please enter quantity',
        'items.*.quantity.min'     => 'Quantity must be at least 1',
    ];

    #[Computed]
    public function requests()
    {
        return ResourceRequest::with(['items', 'requestType'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    public function openEdit(int $id)
    {
        $request = ResourceRequest::with('items')->findOrFail($id);

        if ($request->user_id !== Auth::id()) {
            session()->flash('error', 'You are not authorized to edit this request.');
            return;
        }

        if ($request->status !== 'pending') {
            session()->flash('error', 'Only pending requests can be edited.');
            return;
        }

        $this->editingId       = $id;
        $this->request_type_id = $request->request_type_id;
        $this->purpose         = $request->purpose;

        if ($request->request_type_id == 1) {
            $item                = $request->items->first();
            $this->facility_name = $item?->item_name ?? '';
            $this->request_date  = $item?->request_date ?? '';
            $this->start_time    = $item?->start_time ?? '';
            $this->end_time      = $item?->end_time ?? '';
        } else {
            $this->request_date = $request->items->first()?->request_date ?? '';
            $this->items = $request->items->map(fn($i) => [
                'name'     => $i->item_name,
                'quantity' => $i->quantity,
            ])->toArray();
        }

        $this->showEditModal = true;
    }

    public function closeEdit()
    {
        $this->showEditModal   = false;
        $this->editingId       = null;
        $this->request_type_id = '';
        $this->reset(['purpose', 'request_date', 'facility_name', 'start_time', 'end_time']);
        $this->items = [['name' => '', 'quantity' => 1]];
    }

    public function saveEdit()
    {
        $this->validate();

        $request = ResourceRequest::where('user_id', Auth::id())->findOrFail($this->editingId);

        if ($request->status !== 'pending') {
            session()->flash('error', 'This request can no longer be edited.');
            $this->closeEdit();
            return;
        }

        $request->update(['purpose' => $this->purpose]);

        // Delete old items and recreate
        $request->items()->delete();

        if ($this->request_type_id == 1) {
            RequestItem::create([
                'request_id'   => $request->id,
                'resource_id'  => null,
                'item_name'    => $this->facility_name,
                'quantity'     => 1,
                'request_date' => $this->request_date,
                'start_time'   => $this->start_time,
                'end_time'     => $this->end_time,
            ]);
        } else {
            foreach ($this->items as $item) {
                RequestItem::create([
                    'request_id'   => $request->id,
                    'resource_id'  => null,
                    'item_name'    => $item['name'],
                    'quantity'     => $item['quantity'],
                    'request_date' => $this->request_date,
                    'start_time'   => null,
                    'end_time'     => null,
                ]);
            }
        }

        $this->closeEdit();
        session()->flash('success', 'Request updated successfully!');
    }

    public function addItem()
    {
        $this->items[] = ['name' => '', 'quantity' => 1];
    }

    public function removeItem(int $index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function delete(int $id)
    {
        $request = ResourceRequest::where('user_id', Auth::id())->findOrFail($id);

        if ($request->status !== 'pending') {
            session()->flash('error', 'Only pending requests can be deleted.');
            return;
        }

        $request->delete();
        session()->flash('success', 'Request deleted.');
    }
};
