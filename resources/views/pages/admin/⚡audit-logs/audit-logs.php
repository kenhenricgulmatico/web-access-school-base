<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\AuditLogDetails;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    /** What the filter chips show. */
    public const WHAT = [
        'requests'  => 'Requests',
        'resources' => 'Rooms & materials',
        'accounts'  => 'Accounts & departments',
        'signins'   => 'Sign-ins',
        'all'       => 'All',
    ];

    public const ROLES = ['admin' => 'Admin', 'program head' => 'Program Head', 'faculty' => 'Faculty'];

    #[Url(except: '')]
    public string $search     = '';
    #[Url(except: 'requests')]
    public string $what       = 'requests';
    #[Url(as: 'role', except: '')]
    public string $roleFilter = '';            // ✅ '' = everyone, including the Admin's own actions
    #[Url(as: 'person', except: '')]
    public $personId          = '';
    #[Url(as: 'from', except: '')]
    public string $dateFrom   = '';
    #[Url(as: 'to', except: '')]
    public string $dateTo     = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('audit.view-all'), 403);

        if (! array_key_exists($this->what, self::WHAT)) {
            $this->what = 'requests';
        }
        if ($this->roleFilter !== '' && ! array_key_exists($this->roleFilter, self::ROLES)) {
            $this->roleFilter = '';
        }
    }

    public function updatingSearch(): void     { $this->resetPage(); }
    public function updatingWhat(): void       { $this->resetPage(); }
    public function updatingRoleFilter(): void { $this->resetPage(); }
    public function updatingPersonId(): void   { $this->resetPage(); }
    public function updatingDateFrom(): void   { $this->resetPage(); }
    public function updatingDateTo(): void     { $this->resetPage(); }

    public function setWhat(string $what): void
    {
        $this->what = array_key_exists($what, self::WHAT) ? $what : 'requests';
        $this->resetPage();
    }

    /** ✅ Click a name: only that person's activity (click again for everyone). */
    public function filterPerson(int $id): void
    {
        $this->personId = (string) $this->personId === (string) $id ? '' : (string) $id;
        $this->resetPage();
    }

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

    public function clearFilters(): void
    {
        $this->reset('search', 'roleFilter', 'personId', 'dateFrom', 'dateTo');
        $this->what = 'requests';
        $this->resetPage();
    }

    /** [from, to]: real calendar dates in the right order. */
    public function range(): array
    {
        $ok   = fn ($d) => $d && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $d, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? $d : null;
        $from = $ok($this->dateFrom);
        $to   = $ok($this->dateTo);

        return $from && $to && $from > $to ? [$to, $from] : [$from, $to];
    }

    #[Computed]
    public function person(): ?User
    {
        return $this->personId !== '' ? User::find((int) $this->personId) : null;
    }

    /** Everything the filters select (no paging). */
    protected function filtered()
    {
        [$from, $to] = $this->range();

        return AuditLog::query()
            ->when($this->what === 'requests',  fn ($q) => $q->whereIn('table_name', ['requests', 'request_items']))
            ->when($this->what === 'resources', fn ($q) => $q->where('table_name', 'resources'))
            ->when($this->what === 'accounts',  fn ($q) => $q->whereIn('table_name', ['users', 'departments', 'roles']))
            ->when($this->what === 'signins',   fn ($q) => $q->whereIn('action', ['login', 'logout']))
            ->when($this->roleFilter !== '', fn ($q) => $q->whereHas('user.roles', fn ($r) => $r->where('name', $this->roleFilter)))
            ->when($this->personId !== '' && $this->personId !== null, fn ($q) => $q->where('user_id', (int) $this->personId))
            ->when($this->search !== '', fn ($q) => $q->whereHas('user', fn ($u) => $u->where(fn ($s) => $s
                ->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%'))))
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to));
    }

    #[Computed]
    public function logs()
    {
        return $this->filtered()
            ->with(['user.roles', 'user.department'])
            ->latest()
            ->paginate(20);
    }

    /** Requests on this page that still exist (for "Open request"). */
    #[Computed]
    public function openableRequests(): array
    {
        $ids = $this->logs->getCollection()->map(fn ($l) => AuditLogDetails::requestId($l))->filter()->unique();

        return $ids->isEmpty() ? [] : \App\Models\Request::whereIn('id', $ids)->pluck('id')->flip()->all();
    }

    /* ------------------------------ cards ------------------------------ */

    #[Computed]
    public function requestsThisWeek(): int
    {
        return AuditLog::where('table_name', 'requests')->where('action', 'created')
            ->where('created_at', '>=', today()->startOfWeek())->count();
    }

    /** ✅ Requests the Admin approved or rejected this week. */
    #[Computed]
    public function decisionsThisWeek(): int
    {
        return AuditLog::where('table_name', 'requests')->where('action', 'updated')
            ->where('created_at', '>=', today()->startOfWeek())
            ->whereHas('user.roles', fn ($r) => $r->where('name', 'admin'))
            ->get(['record'])
            ->map(fn ($l) => is_array($l->record) ? $l->record : json_decode((string) $l->record, true))
            ->filter(fn ($r) => in_array($r['status'] ?? null, ['approved', 'rejected'], true))
            // each request once (later changes to an approved request don't count again)
            ->unique(fn ($r) => $r['id'] ?? spl_object_id((object) $r))
            ->count();
    }

    #[Computed]
    public function signInsToday(): int
    {
        return AuditLog::where('action', 'login')->whereDate('created_at', today())->count();
    }

    #[Computed]
    public function activePeople(): int
    {
        return AuditLog::whereNotNull('user_id')->where('created_at', '>=', today()->startOfWeek())
            ->distinct()->count('user_id');
    }

    /* ------------------------------ download ------------------------------ */

    public function exportCsv()
    {
        abort_unless(auth()->user()->can('audit.view-all'), 403);

        $rows = $this->filtered()->with('user.roles')->latest()->limit(10000)->get();
        $cell = fn ($v) => ($v = (string) ($v ?? '')) !== '' && ! is_numeric($v) && in_array($v[0], ['=', '+', '-', '@'], true) ? "'" . $v : $v;

        return response()->streamDownload(function () use ($rows, $cell) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Date', 'Time', 'Person', 'Email', 'Role', 'What happened', 'Details'], ',', '"', '');
            foreach ($rows as $log) {
                fputcsv($out, [
                    $log->created_at->format('Y-m-d'),
                    $log->created_at->format('h:i A'),
                    $cell($log->user->name ?? 'System / deleted user'),
                    $cell($log->user->email ?? ''),
                    ucwords((string) ($log->user?->roles->first()?->name ?? '')),
                    AuditLogDetails::title($log),
                    $cell(AuditLogDetails::summary($log)),
                ], ',', '"', '');
            }
            fclose($out);
        }, 'audit-log-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
};
