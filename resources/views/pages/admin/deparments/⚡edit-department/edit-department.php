<?php

use App\Models\Department;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public $department_id;
    public $department_name = '';

    public function mount($id)
    {
        abort_unless(auth()->user()->can('departments.manage'), 403);
        $this->department_id = $id;
        $department = Department::findOrFail($id);
        $this->department_name = $department->department_name;
    }

    protected $rules = [
        'department_name' => 'required|string|max:255|unique:departments,department_name,{department_id}',
    ];

    protected $messages = [
        'department_name.required' => 'Department name is required.',
        'department_name.unique' => 'This department already exists.',
    ];

    public function update()
    {
        abort_unless(auth()->user()->can('departments.manage'), 403);
        $this->validate();

        $department = Department::findOrFail($this->department_id);
        $department->update([
            'department_name' => $this->department_name,
        ]);

        session()->flash('success', 'Department updated successfully!');

        return redirect()->route('admin.departments');
    }

    public function cancel()
    {
        return redirect()->route('admin.departments');
    }
};
