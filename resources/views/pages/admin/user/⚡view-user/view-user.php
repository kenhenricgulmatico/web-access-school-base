<?php

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::admin')] class extends Component
{
    use WithPagination;

    #[Computed()]
    public function users()
    {
        // Approved admin / program head / faculty accounts (there is no student role anymore)
        return User::with(['roles', 'department'])
            ->whereHas('roles', fn($r) => $r->whereIn('name', ['admin', 'program head', 'faculty']))
            ->where('status', 'approved')
            ->select('id', 'name', 'email', 'department_id', 'created_at')
            ->paginate(5);
    }
};
