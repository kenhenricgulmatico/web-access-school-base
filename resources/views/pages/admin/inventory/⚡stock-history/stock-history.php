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
    public string $entryType   = ''; // '' | restock | approval | return

    public function updatingEntryType()
    {
        $this->resetPage();
    }

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

    /** Only requests filed by Program Head accounts. */
    protected function programHeadIds()
    {
        return function ($q) {
            $q->select('mhr.model_id')
                ->from('model_has_roles as mhr')
                ->join('roles as rl', 'rl.id', '=', 'mhr.role_id')
                ->where('mhr.model_type', User::class)
                ->whereIn('rl.name', ['program head']);
        };
    }

    /**
     * Combined history:
     *  - restock  → Stock In (+) from the `stocks` table
     *  - approval → materials released (−) when the Admin approved a Program Head request
     *  - return   → ✅ borrowed Material items returned in GOOD condition (+), back in the main stock
     *
     * Search and date filters apply to all three; the type chips filter on top (see histories()).
     */
    protected function historyQuery()
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
            ->whereIn('req.user_id', $this->programHeadIds())
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

        // ✅ Borrowed Pcs items the Admin marked as returned in GOOD condition (+ back to stock)
        $returns = DB::table('request_items as ri')
            ->join('requests as req', 'req.id', '=', 'ri.request_id')
            ->join('resources as r', 'r.id', '=', 'ri.resource_id')
            ->leftJoin('resource_types as rt', 'rt.id', '=', 'r.resource_type_id')
            ->join('departments as d', 'd.id', '=', 'req.department_id')
            ->join('users as u', 'u.id', '=', 'req.user_id')            // borrower (Program Head)
            ->leftJoin('users as rec', 'rec.id', '=', 'ri.received_by')  // Admin who confirmed it
            ->whereNotNull('ri.returned_at')
            ->where('ri.return_condition', 'good')
            ->where('r.is_returnable', 1)
            ->whereIn('req.user_id', $this->programHeadIds())
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('r.resource_name', 'like', '%' . $this->search . '%')
                        ->orWhere('d.department_name', 'like', '%' . $this->search . '%')
                        ->orWhere('u.name', 'like', '%' . $this->search . '%')
                        ->orWhere('rec.name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->dateFrom, fn($q) => $q->whereDate('ri.returned_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('ri.returned_at', '<=', $this->dateTo))
            ->select(
                'ri.id',
                DB::raw("'return' as entry_type"),
                'r.resource_name',
                'rt.type_name as resource_type_name',
                'ri.quantity as quantity_added',
                DB::raw('NULL as quantity_before'),
                DB::raw('NULL as quantity_after'),
                DB::raw('NULL as supplier'),
                'd.department_name',
                DB::raw('NULL as unit_price'),
                DB::raw('DATE(ri.returned_at) as arrival_date'),
                DB::raw('TIME(ri.returned_at) as arrival_time'),
                'rec.name as actor_name',
                DB::raw("CONCAT('Returned by ', u.name, IFNULL(CONCAT(' - ', ri.return_notes), '')) as remarks"),
                'ri.returned_at as event_at'
            );

        return DB::query()->fromSub($restocks->unionAll($approvals)->unionAll($returns), 'h');
    }

    #[Computed]
    public function histories()
    {
        return $this->historyQuery()
            ->when($this->entryType !== '', fn ($q) => $q->where('entry_type', $this->entryType))
            ->orderByDesc('event_at')
            ->paginate(15);
    }

    /** ✅ Totals for the cards (follow the search + date filters): entry_type => [count, qty]. */
    #[Computed]
    public function summary(): array
    {
        $rows = $this->historyQuery()
            ->selectRaw('entry_type, COUNT(*) as entries, COALESCE(SUM(quantity_added), 0) as qty')
            ->groupBy('entry_type')
            ->get()
            ->keyBy('entry_type');

        $pick = fn ($type) => [
            'entries' => (int) ($rows[$type]->entries ?? 0),
            'qty'     => (int) ($rows[$type]->qty ?? 0),
        ];

        return [
            'restock'  => $pick('restock'),
            'approval' => $pick('approval'),
            'return'   => $pick('return'),
        ];
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'dateFrom', 'dateTo', 'entryType');
        $this->resetPage();
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
