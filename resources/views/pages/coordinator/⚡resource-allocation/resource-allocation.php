<?php

use App\Models\RequestType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Component;

new #[Layout('layouts.coordinator')] class extends Component
{
    public string $search = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount()
    {
        abort_unless(auth()->user()->can('allocations.view'), 403);
    }

    protected function formatQuantity(int $qty, ?string $unit): string
    {
        $unit = trim($unit ?? '') ?: 'Pcs';

        return "{$qty} {$unit}";
    }

    // ── Top card: what the department currently holds ──
    #[Computed]
    public function materialsSummary()
    {
        return DB::table('resource_all_locations as ral')
            ->join('resources as r', 'r.id', '=', 'ral.resource_id')
            ->join('resource_types as rt', 'rt.id', '=', 'r.resource_type_id')
            ->where('ral.department_id', Auth::user()->department_id)
            ->where('ral.allocated_quantity', '>', 0)
            ->where('rt.type_name', '!=', 'Facility')
            ->select('r.resource_name', 'ral.allocated_quantity', 'r.unit')
            ->orderByDesc('ral.allocated_quantity')
            ->limit(6)
            ->get()
            ->map(function ($row) {
                $row->formatted_quantity = $this->formatQuantity((int) $row->allocated_quantity, $row->unit);
                return $row;
            });
    }

    #[Computed]
    public function totalAllocated()
    {
        return DB::table('resource_all_locations as ral')
            ->join('resources as r', 'r.id', '=', 'ral.resource_id')
            ->join('resource_types as rt', 'rt.id', '=', 'r.resource_type_id')
            ->where('ral.department_id', Auth::user()->department_id)
            ->where('rt.type_name', '!=', 'Facility')
            ->sum('ral.allocated_quantity');
    }

    #[Computed]
    public function resourceTypeCount()
    {
        return DB::table('resource_all_locations as ral')
            ->join('resources as r', 'r.id', '=', 'ral.resource_id')
            ->join('resource_types as rt', 'rt.id', '=', 'r.resource_type_id')
            ->where('ral.department_id', Auth::user()->department_id)
            ->where('rt.type_name', '!=', 'Facility')
            ->count();
    }

    // ── Material logs: one row per material item (materials only) ──
    // Only student / faculty whose OWN department matches the program head's.
    #[Computed]
    public function materialLogs()
    {
        $departmentId = Auth::user()->department_id;

        // Requester's role (student / faculty)
        $roleSub = DB::table('model_has_roles as mhr')
            ->join('roles as rl', 'rl.id', '=', 'mhr.role_id')
            ->whereColumn('mhr.model_id', 'u.id')
            ->where('mhr.model_type', \App\Models\User::class)
            ->whereIn('rl.name', ['student', 'faculty'])
            ->select('rl.name')
            ->limit(1);

        $rows = DB::table('request_items as ri')
            ->join('requests as req', 'req.id', '=', 'ri.request_id')
            ->join('users as u', 'u.id', '=', 'req.user_id')
            ->join('resources as r', 'r.id', '=', 'ri.resource_id')
            ->leftJoin('resource_types as rt', 'rt.id', '=', 'r.resource_type_id')
            ->leftJoin('departments as d', 'd.id', '=', 'u.department_id')   // requester's department
            ->where('u.department_id', $departmentId)                        // based on their department
            ->where('req.user_id', '!=', Auth::id())                         // not my own requests
            ->whereIn('req.request_type_id', [RequestType::FACILITY, RequestType::MATERIAL])
            ->whereIn('req.status', ['approved', 'rejected'])
            // only student / faculty requesters (excludes program heads)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('model_has_roles as mhr2')
                  ->join('roles as rl2', 'rl2.id', '=', 'mhr2.role_id')
                  ->whereColumn('mhr2.model_id', 'u.id')
                  ->where('mhr2.model_type', \App\Models\User::class)
                  ->whereIn('rl2.name', ['student', 'faculty']);
            })
            ->select(
                'ri.id as item_id',
                'ri.resource_id',
                'ri.item_name',
                'ri.quantity',
                'r.unit',
                'rt.type_name as resource_type',
                'req.id as request_id',
                'req.request_type_id',
                'req.status',
                'req.updated_at as decided_at',
                'u.name as requester_name',
                'd.department_name'
            )
            ->selectSub($roleSub, 'requester_role')
            ->orderByDesc('req.updated_at')
            ->orderByDesc('ri.id')
            ->get();

        if ($rows->isEmpty()) {
            return $rows;
        }

        // Rebuild before → after by walking backward from the current stock
        $current = DB::table('resource_all_locations')
            ->where('department_id', $departmentId)
            ->pluck('allocated_quantity', 'resource_id')
            ->map(fn ($q) => (int) $q)
            ->all();

        $rows = $rows->map(function ($row) use (&$current) {
            $balance = $current[$row->resource_id] ?? 0;
            $qty     = (int) $row->quantity;

            if ($row->status === 'approved') {
                $row->after  = $balance;
                $row->before = $balance + $qty;
                $current[$row->resource_id] = $row->before;
            } else {
                $row->before = $balance;
                $row->after  = $balance;
            }

            $row->source = $row->request_type_id == RequestType::FACILITY ? 'With facility reservation' : 'Material request';

            return $row;
        });

        // Filters
        $search = Str::lower(trim($this->search));

        if ($search !== '') {
            $rows = $rows->filter(function ($row) use ($search) {
                return Str::contains(Str::lower(implode(' ', [
                    $row->item_name,
                    $row->resource_type,
                    $row->requester_name,
                    $row->requester_role,
                    $row->department_name,
                    $row->source,
                ])), $search);
            });
        }

        if ($this->dateFrom !== '') {
            $from = Carbon::parse($this->dateFrom)->startOfDay();
            $rows = $rows->filter(fn ($row) => Carbon::parse($row->decided_at)->gte($from));
        }

        if ($this->dateTo !== '') {
            $to = Carbon::parse($this->dateTo)->endOfDay();
            $rows = $rows->filter(fn ($row) => Carbon::parse($row->decided_at)->lte($to));
        }

        return $rows->take(100)->values();
    }
};
