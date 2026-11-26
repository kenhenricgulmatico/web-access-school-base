<?php

namespace App\Livewire\Admin;

use App\Models\Department;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    #[Computed]
    public function departments()
    {
        return Department::withCount('users')->latest()->get();
    }

    /**
     * ✅ Only an EMPTY department can be deleted.
     *    The database cascades: deleting a department would also delete every user
     *    account, request and stock allocation that belongs to it — with no undo.
     */
    public function delete(int $id)
    {
        abort_unless(auth()->user()->can('departments.manage'), 403);

        $department = Department::withCount(['users', 'requests'])->findOrFail($id);

        if ($department->users_count > 0 || $department->requests_count > 0) {
            $parts = array_filter([
                $department->users_count    ? $department->users_count . ' ' . \Illuminate\Support\Str::plural('user', $department->users_count) : null,
                $department->requests_count ? $department->requests_count . ' ' . \Illuminate\Support\Str::plural('request', $department->requests_count) : null,
            ]);

            session()->flash('error', "Can't delete {$department->department_name}: " . implode(' and ', $parts)
                . ' still belong to it. Move the users to another department first — deleting it would erase their accounts and history.');
            return;
        }

        $department->delete();
        session()->flash('success', 'Department deleted successfully.');
    }
};
