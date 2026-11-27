<?php

namespace App\Livewire\Pages\Admin\Deparments;

use App\Models\Department;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->can('departments.manage'), 403);
    }

    public string $department_name = '';

    protected array $rules = [
        'department_name' => 'required|string|max:255|unique:departments,department_name',
    ];

    protected array $messages = [
        'department_name.required' => 'Department name is required.',
        'department_name.unique'   => 'This department already exists.',
    ];

    public function submit()
    {
        abort_unless(auth()->user()->can('departments.manage'), 403);
        $this->validate();

        Department::create([
            'department_name' => $this->department_name,
        ]);

        session()->flash('success', 'Department created successfully.');
        return redirect()->route('admin.departments');
    }

    public function cancel()
    {
        return redirect()->route('admin.departments');
    }
};
