<?php

namespace App\Livewire\Admin\Inventory;

use App\Models\Stock;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Illuminate\Support\Carbon;
use Livewire\WithPagination;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $search      = '';
    public string $dateFrom    = '';
    public string $dateTo      = '';
    #[Url(as: 'type')]
    public string $entryType   = ''; // '' | restock | approval | return | removal
    // ✅ One material's full movement (Stock Materials links here with ?material=ID)
    #[Url(as: 'material')]
    public $materialId         = '';

    public const TYPES = [
        'restock'  => 'Stock In',
        'approval' => 'Approved by Admin',
        'return'   => 'Returned',
        'removal'  => 'Removed',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()->can('stock-history.view'), 403);
    }

    public function updatingMaterialId()
    {
        $this->resetPage();
    }

    /** ✅ Type chip: click again to show all. */
    public function setType(string $type): void
    {
        $this->entryType = ($type === $this->entryType || ! array_key_exists($type, self::TYPES)) ? '' : $type;
        $this->resetPage();
    }

    /** ✅ Quick date ranges. */
    public function setRange(string $range): void
    {
        [$this->dateFrom, $this->dateTo] = match ($range) {
            'today' => [today()->toDateString(), today()->toDateString()],
            'week'  => [today()->startOfWeek()->toDateString(), today()->toDateString()],
            'month' => [today()->startOfMonth()->toDateString(), today()->toDateString()],
            default => ['', ''],
        };
        $this->resetPage();
    }

    /** Which quick range the dates match ('' = none / custom). */
    public function activeRange(): string
    {
        $t = today()->toDateString();
        return match (true) {
            $this->dateTo !== $t                                         => '',
            $this->dateFrom === $t                                       => 'today',
            $this->dateFrom === today()->startOfWeek()->toDateString()  => 'week',
            $this->dateFrom === today()->startOfMonth()->toDateString() => 'month',
            default                                                      => '',
        };
    }

    /**
     * ✅ [from, to] as valid dates, in the right order (a From after To is swapped,
     *    a half-typed date is ignored).
     */
    public function range(): array
    {
        $parse = fn ($d) => $d && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? rescue(fn () => Carbon::parse($d)->toDateString(), null, false) : null;
        $from  = $parse($this->dateFrom);
        $to    = $parse($this->dateTo);

        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }

    /** True when From was after To (the page says it swapped them). */
    public function datesSwapped(): bool
    {
        return $this->dateFrom && $this->dateTo && $this->dateFrom > $this->dateTo;
    }

    #[Computed]
    public function materials()
    {
        return \App\Models\Resource::query()
            ->whereHas('resourceType', fn ($q) => $q->where('type_name', '!=', \App\Models\ResourceType::FACILITY))
            ->orderBy('resource_name')
            ->get(['id', 'resource_name', 'unit']);
    }

    #[Computed]
    public function selectedMaterial()
    {
        return $this->materialId ? $this->materials->firstWhere('id', (int) $this->materialId) : null;
    }

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
        [$from, $to] = $this->range();

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
            ->when($this->materialId, fn ($q) => $q->where('r.id', (int) $this->materialId))
            ->when($from, fn($q) => $q->whereDate('s.arrival_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('s.arrival_date', '<=', $to))
            ->select(
                's.id',
                // ✅ A negative quantity is stock the Admin took out (used up, damaged, lost, correction)
                DB::raw("CASE WHEN s.quantity_added < 0 THEN 'removal' ELSE 'restock' END as entry_type"),
                'r.resource_name',
                'rt.type_name as resource_type_name',
                's.quantity_added',
                's.quantity_before',
                's.quantity_after',
                DB::raw('COALESCE(s.supplier, s.reason) as supplier'),
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
            ->when($this->materialId, fn ($q) => $q->where('r.id', (int) $this->materialId))
            // ✅ the moment it was approved (older rows: the request's last change)
            ->when($from, fn($q) => $q->whereRaw('DATE(COALESCE(ri.approved_at, req.updated_at)) >= ?', [$from]))
            ->when($to, fn($q) => $q->whereRaw('DATE(COALESCE(ri.approved_at, req.updated_at)) <= ?', [$to]))
            ->select(
                'ri.id',
                DB::raw("'approval' as entry_type"),
                'r.resource_name',
                'rt.type_name as resource_type_name',
                'ri.quantity as quantity_added',
                // ✅ Stock level recorded at approval time (null for older approvals —
                //    working it out from today's stock was only right for the latest one)
                'ri.stock_before_approval as quantity_before',
                'ri.stock_after_approval as quantity_after',
                DB::raw('NULL as supplier'),
                'd.department_name',
                DB::raw('NULL as unit_price'),
                DB::raw('DATE(COALESCE(ri.approved_at, req.updated_at)) as arrival_date'),
                DB::raw('TIME(ri.approved_at) as arrival_time'),
                'u.name as actor_name',
                'req.purpose as remarks',
                DB::raw('COALESCE(ri.approved_at, req.updated_at) as event_at')
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
            ->when($this->materialId, fn ($q) => $q->where('r.id', (int) $this->materialId))
            ->when($from, fn($q) => $q->whereDate('ri.returned_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('ri.returned_at', '<=', $to))
            ->select(
                'ri.id',
                DB::raw("'return' as entry_type"),
                'r.resource_name',
                'rt.type_name as resource_type_name',
                'ri.quantity as quantity_added',
                // ✅ Stock level recorded when the Admin confirmed the return (null for older returns)
                'ri.stock_before_return as quantity_before',
                'ri.stock_after_return as quantity_after',
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
            'removal'  => $pick('removal'),
        ];
    }

    /** ✅ Cards: what came in, what went out, and the difference — for the current filters. */
    #[Computed]
    public function totals(): array
    {
        $sum = $this->summary;
        $in  = $sum['restock']['qty'] + $sum['return']['qty'];
        $out = $sum['approval']['qty'] + abs($sum['removal']['qty']);

        return [
            'in'         => $in,
            'out'        => $out,
            'net'        => $in - $out,
            'inEntries'  => $sum['restock']['entries'] + $sum['return']['entries'],
            'outEntries' => $sum['approval']['entries'] + $sum['removal']['entries'],
        ];
    }

    /** "All time", "Today", "Nov 01 – Nov 27, 2026"… for the cards. */
    public function periodLabel(): string
    {
        [$from, $to] = $this->range();

        if (! $from && ! $to) {
            return 'All time';
        }
        if ($from && $from === $to) {
            return $from === today()->toDateString() ? 'Today' : Carbon::parse($from)->format('M d, Y');
        }

        return ($from ? Carbon::parse($from)->format('M d, Y') : 'Start') . ' – ' . ($to ? Carbon::parse($to)->format('M d, Y') : 'today');
    }

    /** ✅ Download what's on screen (all pages) as a spreadsheet. */
    public function exportCsv()
    {
        abort_unless(auth()->user()->can('stock-history.export'), 403);

        $rows = $this->historyQuery()
            ->when($this->entryType !== '', fn ($q) => $q->where('entry_type', $this->entryType))
            ->orderByDesc('event_at')
            ->limit(10000)
            ->get();

        // A cell starting with = + - @ would run as a formula in Excel → prefix it with '
        $cell = function ($v) {
            $v = (string) ($v ?? '');
            return $v !== '' && ! is_numeric($v) && in_array($v[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'" . $v : $v;
        };

        $filename = 'stock-history-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows, $cell) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows ₱, ñ, — correctly
            fputcsv($out, ['Date', 'Time', 'Type', 'Material', 'Material type', 'Qty', 'Before', 'After', 'Supplier / Department / Reason', 'By', 'Remarks'], ',', '"', '');

            foreach ($rows as $r) {
                $isOut  = in_array($r->entry_type, ['approval', 'removal'], true);
                $source = match ($r->entry_type) {
                    'removal' => Stock::REASONS[$r->supplier] ?? 'Removed',
                    'restock' => $r->supplier,
                    default   => $r->department_name,
                };

                fputcsv($out, [
                    $r->arrival_date ? Carbon::parse($r->arrival_date)->format('Y-m-d') : '',
                    $r->arrival_time ? Carbon::parse($r->arrival_time)->format('h:i A') : '',
                    self::TYPES[$r->entry_type] ?? $r->entry_type,
                    $cell($r->resource_name),
                    $cell($r->resource_type_name),
                    ($isOut ? -1 : 1) * abs((int) $r->quantity_added),
                    $r->quantity_before,
                    $r->quantity_after,
                    $cell($source),
                    $cell($r->actor_name),
                    $cell($r->remarks),
                ], ',', '"', '');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'dateFrom', 'dateTo', 'entryType', 'materialId');
        $this->resetPage();
    }

    #[Computed]
    public function totalRestocks()
    {
        // ✅ restocks only — removals are stock rows with a negative quantity
        return Stock::where('quantity_added', '>', 0)->count();
    }

    #[Computed]
    public function totalQuantityAdded()
    {
        return Stock::where('quantity_added', '>', 0)->sum('quantity_added');
    }

    #[Computed]
    public function totalValue()
    {
        return Stock::where('quantity_added', '>', 0)
            ->selectRaw('SUM(quantity_added * unit_price) as total')
            ->value('total') ?? 0;
    }

    #[Computed]
    public function todayRestocks()
    {
        return Stock::where('quantity_added', '>', 0)->whereDate('arrival_date', today())->count();
    }
};
