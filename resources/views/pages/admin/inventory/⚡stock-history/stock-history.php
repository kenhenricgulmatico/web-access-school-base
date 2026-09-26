<?php

namespace App\Livewire\Admin\Inventory;

use App\Models\Stock;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $search      = '';
    public string $dateFrom    = '';
    public string $dateTo      = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingDateFrom()
    {
        $this->resetPage();
    }
    public function updatingDateTo()
    {
        $this->resetPage();
    }

    /**
     * Combined history — restocks from the `stocks` table PLUS
     * materials released because Admin approved a request containing
     * them (`request_items` joined to `requests` where status =
     * 'approved', resource_id not null so facility line items are
     * excluded). Each row carries entry_type so the view can badge it
     * as "Stock In" vs "Approved by Admin", and both branches respect
     * the same search box and date range filters.
     *
     * ✅ The approvals branch is restricted to requests FILED BY
     * Program Head / Coordinator accounts only — student and faculty
     * requests are excluded from this particular log.
     */
    #[Computed]
    public function histories()
    {
        $restocks = DB::table('stocks as s')
            ->join('resources as r', 'r.id', '=', 's.resource_id')
            ->leftJoin('resource_types as rt', 'rt.id', '=', 'r.resource_type_id')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('r.resource_name', 'like', '%' . $this->search . '%')
                        ->orWhere('s.supplier', 'like', '%' . $this->search . '%')
                        ->orWhere('u.name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->dateFrom, fn($q) => $q->whereDate('s.arrival_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('s.arrival_date', '<=', $this->dateTo))
            ->select(
                's.id',
                DB::raw("'restock' as entry_type"),
                'r.resource_name',
                'rt.type_name as resource_type_name',
                's.quantity_added',
                's.quantity_before',
                's.quantity_after',
                's.supplier',
                DB::raw('NULL as department_name'),
                's.unit_price',
                's.arrival_date',
                's.arrival_time',
                'u.name as actor_name',
                's.remarks',
                's.created_at as event_at'
            );

        $approvals = DB::table('request_items as ri')
            ->join('requests as req', 'req.id', '=', 'ri.request_id')
            ->join('resources as r', 'r.id', '=', 'ri.resource_id')
            ->leftJoin('resource_types as rt', 'rt.id', '=', 'r.resource_type_id')
            ->join('departments as d', 'd.id', '=', 'req.department_id')
            ->join('users as u', 'u.id', '=', 'req.user_id')
            ->where('req.status', 'approved')
            ->whereNotNull('ri.resource_id') // materials only — facility line items excluded
            // ✅ FIX: only requests filed by Program Head / Coordinator accounts
            ->whereIn('req.user_id', function ($q) {
                $q->select('mhr.model_id')
                    ->from('model_has_roles as mhr')
                    ->join('roles as rl', 'rl.id', '=', 'mhr.role_id')
                    ->where('mhr.model_type', User::class)
                    ->whereIn('rl.name', ['program head']);
            })
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('r.resource_name', 'like', '%' . $this->search . '%')
                        ->orWhere('d.department_name', 'like', '%' . $this->search . '%')
                        ->orWhere('u.name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->dateFrom, fn($q) => $q->whereDate('req.updated_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('req.updated_at', '<=', $this->dateTo))
            ->select(
                'ri.id',
                DB::raw("'approval' as entry_type"),
                'r.resource_name',
                'rt.type_name as resource_type_name',
                'ri.quantity as quantity_added',
                DB::raw('r.quantity_available + ri.quantity as quantity_before'),
                DB::raw('r.quantity_available as quantity_after'),
                DB::raw('NULL as supplier'),
                'd.department_name',
                DB::raw('NULL as unit_price'),
                DB::raw('DATE(req.updated_at) as arrival_date'),
                DB::raw('NULL as arrival_time'),
                'u.name as actor_name',
                'req.purpose as remarks',
                'req.updated_at as event_at'
            );

        return $restocks->unionAll($approvals)
            ->orderByDesc('event_at')
            ->paginate(15);
    }

    #[Computed]
    public function totalRestocks()
    {
        return Stock::count();
    }

    #[Computed]
    public function totalQuantityAdded()
    {
        return Stock::sum('quantity_added');
    }

    #[Computed]
    public function totalValue()
    {
        return Stock::selectRaw('SUM(quantity_added * unit_price) as total')
            ->value('total') ?? 0;
    }

    #[Computed]
    public function todayRestocks()
    {
        return Stock::whereDate('arrival_date', today())->count();
    }
};
