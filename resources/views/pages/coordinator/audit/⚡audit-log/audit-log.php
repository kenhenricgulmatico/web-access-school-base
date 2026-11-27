<?php

namespace App\Livewire\ProgramHead;

use App\Models\AuditLog;
use App\Models\Request as ResourceRequest;
use App\Models\User;
use App\Support\AuditLogDetails;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.coordinator')] class extends Component
{
    use WithPagination;

    /** What the filter chips show: logs from these tables / actions. */
    public const WHAT = [
        'requests' => 'Requests',
        'signins'  => 'Sign-ins',
        'accounts' => 'Account changes',
        'all'      => 'All',
    ];

    #[Url(except: '')]
    public string $search   = '';
    #[Url(except: 'requests')]
    public string $what     = 'requests';   // ✅ the real work first; sign-ins / automatic account updates on request
    #[Url(as: 'teacher', except: '')]
    public $teacherId       = '';
    #[Url(as: 'from', except: '')]
    public string $dateFrom = '';
    #[Url(as: 'to', except: '')]
    public string $dateTo   = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('audit.view-own-department'), 403);

        if (! array_key_exists($this->what, self::WHAT)) {
            $this->what = 'requests';
        }
    }

    public function updatingSearch(): void    { $this->resetPage(); }
    public function updatingWhat(): void      { $this->resetPage(); }
    public function updatingTeacherId(): void { $this->resetPage(); }
    public function updatingDateFrom(): void  { $this->resetPage(); }
    public function updatingDateTo(): void    { $this->resetPage(); }

    public function setWhat(string $what): void
    {
        $this->what = array_key_exists($what, self::WHAT) ? $what : 'requests';
        $this->resetPage();
    }

    /** ✅ Click a teacher's name: only their activity (click again for everyone). */
    public function filterTeacher(int $id): void
    {
        $this->teacherId = (string) $this->teacherId === (string) $id ? '' : (string) $id;
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
        $this->reset('search', 'teacherId', 'dateFrom', 'dateTo');
        $this->what = 'requests';
        $this->resetPage();
    }

    /** [from, to] as valid dates in the right order. */
    public function range(): array
    {
        // a real calendar date only (month 13 / day 99 are ignored)
        $ok   = fn ($d) => $d && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $d, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? $d : null;
        $from = $ok($this->dateFrom);
        $to   = $ok($this->dateTo);

        return $from && $to && $from > $to ? [$to, $from] : [$from, $to];
    }

    /** Faculty of my department. */
    #[Computed]
    public function teachers()
    {
        return User::role('faculty')
            ->where('department_id', Auth::user()->department_id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    private function baseDeptQuery()
    {
        $departmentId = Auth::user()->department_id;

        return AuditLog::query()
            ->whereHas('user', fn ($q) => $q
                ->where('department_id', $departmentId)
                ->whereHas('roles', fn ($r) => $r->where('name', 'faculty')));
    }

    /** Everything the filters select (no paging). */
    protected function filtered()
    {
        [$from, $to] = $this->range();

        return $this->baseDeptQuery()
            ->when($this->what === 'requests', fn ($q) => $q->whereIn('table_name', ['requests', 'request_items']))
            ->when($this->what === 'signins',  fn ($q) => $q->whereIn('action', ['login', 'logout']))
            ->when($this->what === 'accounts', fn ($q) => $q->where('table_name', 'users'))
            ->when($this->search !== '', fn ($q) => $q->whereHas('user', fn ($u) => $u->where(fn ($s) => $s
                ->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%'))))
            ->when($this->teacherId !== '' && $this->teacherId !== null, fn ($q) => $q->where('user_id', (int) $this->teacherId))
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to));
    }

    #[Computed]
    public function logs()
    {
        return $this->filtered()
            ->with(['user.department'])
            ->latest()
            ->paginate(15);
    }

    /** Requests on this page that still exist and belong to my department (for "Open request"). */
    #[Computed]
    public function openableRequests(): array
    {
        $ids = $this->logs->getCollection()
            ->map(fn ($l) => AuditLogDetails::requestId($l))
            ->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return [];
        }

        return ResourceRequest::whereIn('id', $ids)
            ->whereHas('user', fn ($u) => $u->where('department_id', Auth::user()->department_id)->role('faculty'))
            ->pluck('id')
            ->flip()
            ->all();
    }

    /* ------------------------------ cards ------------------------------ */

    #[Computed]
    public function requestsThisWeek(): int
    {
        return $this->baseDeptQuery()
            ->where('table_name', 'requests')->where('action', 'created')
            ->where('created_at', '>=', today()->startOfWeek())
            ->count();
    }

    #[Computed]
    public function activityToday(): int
    {
        return $this->baseDeptQuery()
            ->whereIn('table_name', ['requests', 'request_items'])
            ->whereDate('created_at', today())
            ->count();
    }

    #[Computed]
    public function signInsToday(): int
    {
        return $this->baseDeptQuery()->where('action', 'login')->whereDate('created_at', today())->count();
    }

    #[Computed]
    public function activeTeachers(): int
    {
        return $this->baseDeptQuery()
            ->where('created_at', '>=', today()->startOfWeek())
            ->distinct()
            ->count('user_id');
    }

    /* ------------------------------ download ------------------------------ */

    public function exportCsv()
    {
        abort_unless(auth()->user()->can('audit.view-own-department'), 403);

        $rows = $this->filtered()->with('user')->latest()->limit(5000)->get();

        // a cell starting with = + - @ would run as a formula in Excel
        $cell = fn ($v) => ($v = (string) ($v ?? '')) !== '' && ! is_numeric($v) && in_array($v[0], ['=', '+', '-', '@'], true) ? "'" . $v : $v;

        $name = 'audit-log-' . str(Auth::user()->department->department_name ?? 'department')->slug() . '-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows, $cell) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Date', 'Time', 'Teacher', 'Email', 'What happened', 'Details'], ',', '"', '');
            foreach ($rows as $log) {
                fputcsv($out, [
                    $log->created_at->format('Y-m-d'),
                    $log->created_at->format('h:i A'),
                    $cell($log->user->name ?? 'Deleted user'),
                    $cell($log->user->email ?? ''),
                    AuditLogDetails::title($log),
                    $cell(AuditLogDetails::summary($log)),
                ], ',', '"', '');
            }
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
};
