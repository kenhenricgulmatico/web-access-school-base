<div>
<div class="space-y-5 sm:space-y-6 max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8">
    @php
        $todo  = $this->todo;
        $tasks = array_values(array_filter([
            $todo['requests'] ? ['n' => $todo['requests'], 'text' => \Illuminate\Support\Str::plural('request', $todo['requests']) . ' to approve', 'url' => route('admin.manage-coordinator'), 'tone' => 'amber'] : null,
            $todo['out']      ? ['n' => $todo['out'],      'text' => 'out of stock',                                                       'url' => route('admin.inventory-stock-material', ['stock' => 'out']), 'tone' => 'red'] : null,
            $todo['low']      ? ['n' => $todo['low'],      'text' => 'low on stock',                                                       'url' => route('admin.inventory-stock-material', ['stock' => 'low']), 'tone' => 'amber'] : null,
            $todo['overdue']  ? ['n' => $todo['overdue'],  'text' => \Illuminate\Support\Str::plural('item', $todo['overdue']) . ' overdue',   'url' => route('admin.borrowed-items', ['filter' => 'overdue']), 'tone' => 'red'] : null,
            $todo['accounts'] ? ['n' => $todo['accounts'], 'text' => \Illuminate\Support\Str::plural('account', $todo['accounts']) . ' to approve', 'url' => route('admin.students'), 'tone' => 'amber'] : null,
        ]));
        $chip = [
            'amber' => 'bg-white text-amber-800 ring-amber-300 hover:bg-amber-50 dark:bg-neutral-900 dark:text-amber-300 dark:ring-amber-500/40',
            'red'   => 'bg-white text-red-700 ring-red-300 hover:bg-red-50 dark:bg-neutral-900 dark:text-red-300 dark:ring-red-500/40',
        ];
    @endphp

    {{-- ✅ Greeting (Philippine time) + what needs the Admin today --}}
    @php $greet = now()->hour < 12 ? 'Good morning' : (now()->hour < 18 ? 'Good afternoon' : 'Good evening'); @endphp
    <div class="rounded-2xl bg-[#E8F3EC] dark:bg-[#1C6B45]/20 ring-1 ring-inset ring-[#1C6B45]/10 dark:ring-[#7FBF8E]/15 px-4 py-4 sm:px-6 sm:py-5">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#123524] dark:text-white">{{ $greet }}, {{ ucfirst(\Illuminate\Support\Str::of(auth()->user()->name)->trim()->explode(' ')->first()) }}</h1>
                <p class="mt-1 text-sm text-[#1C6B45]/90 dark:text-[#9FD3AE]">{{ now()->format('l, F j, Y') }} · Administrator</p>
            </div>
            @if (empty($tasks))
                <p class="text-sm font-semibold text-[#123524] dark:text-white md:text-right">✓ All caught up — nothing needs you right now</p>
            @endif
        </div>

        @if (! empty($tasks))
            <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">Needs your attention today</p>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($tasks as $t)
                    <a href="{{ $t['url'] }}" wire:navigate
                       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium ring-1 ring-inset transition {{ $chip[$t['tone']] }}">
                        <span class="text-base font-bold leading-none">{{ $t['n'] }}</span> {{ $t['text'] }} <span aria-hidden="true">→</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Stats — 2×2 on a phone, one row on a laptop --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
        @foreach ([
            ['Total requests', $this->totalRequests, 'From Program Heads', route('admin.manage-coordinator'), 'bg-[#123524] dark:bg-[#1C6B45]', 'text-white', 'M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5'],
            ['Waiting for you', $this->pendingRequests, 'Requests to approve', route('admin.manage-coordinator'), 'bg-amber-50 dark:bg-amber-500/15', 'text-amber-600 dark:text-amber-300', 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            ['Program Heads', $this->totalProgramHeads, 'Registered', route('admin.users'), 'bg-[#E8F3EC] dark:bg-[#1C6B45]/25', 'text-[#1C6B45] dark:text-[#9FD3AE]', 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z'],
            ['Faculty', $this->totalFaculty, 'Registered', route('admin.users'), 'bg-[#E8F3EC] dark:bg-[#1C6B45]/25', 'text-[#1C6B45] dark:text-[#9FD3AE]', 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342'],
        ] as [$label, $value, $hint, $url, $iconBg, $iconText, $path])
            <a href="{{ $url }}" wire:navigate class="flex flex-col bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 shadow-sm rounded-2xl p-4 sm:p-5 transition hover:shadow-md {{ $label === 'Waiting for you' && $value ? '!border-amber-300 dark:!border-amber-500/40' : '' }}">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-neutral-400">{{ $label }}</p>
                    <span class="hidden sm:flex size-9 rounded-full items-center justify-center shrink-0 {{ $iconBg }}">
                        <svg class="size-5 {{ $iconText }}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" /></svg>
                    </span>
                </div>
                <p class="mt-1 text-2xl sm:text-3xl font-bold {{ $label === 'Waiting for you' && $value ? 'text-amber-700 dark:text-amber-300' : 'text-[#123524] dark:text-white' }}">{{ $value }}</p>
                <p class="text-[11px] sm:text-xs text-gray-400 mt-1">{{ $hint }}</p>
            </a>
        @endforeach
    </div>

    {{-- Monthly Requests Chart --}}
    <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 shadow-sm rounded-2xl p-6">

        {{-- Header with Year Dropdown --}}
        <div class="flex items-start justify-between mb-5 gap-3 flex-wrap">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Monthly Requests</h2>
                <p class="text-sm text-gray-400 dark:text-neutral-500">Grouped by department, {{ $chartYear }}</p>
            </div>

            <div class="flex items-center gap-2">
                <label for="chartYear" class="text-xs font-medium text-gray-500 dark:text-neutral-400">Year</label>
                <select
                    id="chartYear"
                    wire:model.live="chartYear"
                    class="text-sm bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 text-gray-700 dark:text-neutral-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-green-500 cursor-pointer">
                    @foreach ($this->availableYears as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Summary strip --}}
        <div id="monthlySummary" wire:ignore class="flex flex-wrap gap-3 mb-5"></div>

        {{-- Chart --}}
        <div class="relative w-full" style="height: 520px;">
            <canvas id="monthlyChart" wire:ignore></canvas>
        </div>

        {{-- Legend --}}
        <div id="monthlyLegend" wire:ignore class="flex flex-wrap justify-center gap-x-6 gap-y-3 mt-6 pt-5 border-t border-gray-100 dark:border-neutral-800"></div>

        {{-- Hidden payload: passes fresh data from PHP to JS on each Livewire render --}}
        <div
            id="chartPayload"
            wire:ignore
            data-labels='@json($this->monthlyData["labels"])'
            data-series='@json($this->monthlyData["departments"])'
            data-year="{{ $chartYear }}"
            style="display:none;"></div>
    </div>

    {{-- ✅ Request Status + Request Types (donuts, same year as Monthly Requests) --}}
    @php $breakdown = $this->yearBreakdown; @endphp
    <div class="grid md:grid-cols-2 gap-6">
        @foreach ([
            ['title' => 'Request Status', 'sub' => 'Approved, pending and rejected', 'slices' => $breakdown['status']],
            ['title' => 'Request Types',  'sub' => 'Facility reservations vs material requests', 'slices' => $breakdown['type']],
        ] as $chart)
            <div wire:key="donut-{{ $loop->index }}-{{ $chartYear }}"
                 class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 shadow-sm rounded-2xl p-6">
                <div class="mb-5">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">{{ $chart['title'] }}</h2>
                    <p class="text-sm text-gray-400 dark:text-neutral-500">{{ $chart['sub'] }}, {{ $chartYear }}</p>
                </div>

                @if ($breakdown['total'] === 0)
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <p class="text-sm font-medium text-gray-500 dark:text-neutral-400">No requests in {{ $chartYear }}</p>
                        <p class="text-xs text-gray-400 dark:text-neutral-500 mt-1">Pick another year above.</p>
                    </div>
                @else
                    <div class="flex flex-col sm:flex-row items-center gap-6">
                        {{-- Donut: circumference 100, so each slice's length = its percent --}}
                        <div class="relative size-40 shrink-0">
                            <svg viewBox="0 0 42 42" class="size-full -rotate-90" role="img"
                                 aria-label="{{ $chart['title'] }}: {{ collect($chart['slices'])->map(fn ($s) => $s['label'] . ' ' . $s['count'])->implode(', ') }}">
                                <circle cx="21" cy="21" r="15.9155" fill="none" stroke-width="6" class="stroke-gray-100 dark:stroke-neutral-800" stroke="#F3F4F6"></circle>
                                @php $offset = 0; @endphp
                                @foreach ($chart['slices'] as $slice)
                                    @if ($slice['count'] > 0)
                                        <circle cx="21" cy="21" r="15.9155" fill="none" stroke-width="6"
                                            stroke="{{ $slice['color'] }}"
                                            stroke-dasharray="{{ $slice['percent'] }} {{ 100 - $slice['percent'] }}"
                                            stroke-dashoffset="{{ -$offset }}">
                                            <title>{{ $slice['label'] }}: {{ $slice['count'] }} ({{ $slice['percent'] }}%)</title>
                                        </circle>
                                        @php $offset += $slice['percent']; @endphp
                                    @endif
                                @endforeach
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-2xl font-bold text-gray-800 dark:text-white leading-none">{{ $breakdown['total'] }}</span>
                                <span class="text-[11px] text-gray-400 dark:text-neutral-500 mt-1">requests</span>
                            </div>
                        </div>

                        {{-- Legend --}}
                        <ul class="flex-1 w-full space-y-2.5">
                            @foreach ($chart['slices'] as $slice)
                                <li class="flex items-center justify-between gap-3">
                                    <span class="flex items-center gap-2 min-w-0">
                                        <span class="size-2.5 rounded-full shrink-0" style="background: {{ $slice['color'] }}"></span>
                                        <span class="text-sm text-gray-700 dark:text-neutral-300 truncate">{{ $slice['label'] }}</span>
                                    </span>
                                    <span class="text-sm whitespace-nowrap">
                                        <span class="font-semibold text-gray-800 dark:text-white">{{ $slice['count'] }}</span>
                                        <span class="text-gray-400 dark:text-neutral-500">· {{ $slice['percent'] }}%</span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Recent Requests Table --}}
    <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Recent Requests</h2>
                <p class="text-sm text-gray-400 dark:text-neutral-500">Latest request per requester, click a program head to see their history</p>
            </div>
        </div>
        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase">Requestor</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase">Purpose</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                @forelse($this->recentRequests as $request)
                    <tr wire:key="recent-{{ $request->id }}" class="hover:bg-gray-50 dark:hover:bg-neutral-800 transition">
                        <td class="px-6 py-4">
                            @if (in_array($request->user_id, $this->programHeadIds))
                                <button type="button" wire:click="showHistory({{ $request->user_id }})"
                                    title="View request history"
                                    class="group flex items-center gap-3 text-left cursor-pointer">
                                    <div class="w-8 h-8 rounded-full bg-[#0B2418] text-white flex items-center justify-center text-sm font-bold">
                                        {{ strtoupper(substr($request->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800 dark:text-white group-hover:text-[#1C6B45] group-hover:underline">{{ $request->user->name }}</p>
                                        <p class="text-xs text-gray-400 dark:text-neutral-500">{{ $request->user->email }}</p>
                                    </div>
                                </button>
                            @else
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-[#0B2418] text-white flex items-center justify-center text-sm font-bold">
                                        {{ strtoupper(substr($request->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800 dark:text-white">{{ $request->user->name }}</p>
                                        <p class="text-xs text-gray-400 dark:text-neutral-500">{{ $request->user->email }}</p>
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                @if($request->request_type_id == \App\Models\RequestType::FACILITY) bg-[#E8F3EC] dark:bg-[#1C6B45]/40 text-[#1C6B45] dark:text-[#7FBF8E]
                                @else bg-slate-100 dark:bg-slate-900/40 text-slate-700 dark:text-slate-400 @endif">
                                {{ $request->requestType->type_name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                            {{ Str::limit($request->purpose, 40) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                @if($request->status === 'pending') bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400
                                @elseif($request->status === 'approved') bg-green-100 dark:bg-[#1C6B45]/40 text-[#1C6B45] dark:text-green-400
                                @elseif($request->status === 'rejected') bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400
                                @elseif($request->status === 'cancelled') bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300
                                @endif">
                                @if($request->status === 'pending') Pending
                                @elseif($request->status === 'approved') Approved
                                @elseif($request->status === 'rejected') Rejected
                                @elseif($request->status === 'cancelled') Cancelled
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-400 dark:text-neutral-500">
                            {{ $request->created_at->format('M d, Y') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-400 dark:text-neutral-500">No requests yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

{{-- Program Head Request History Modal --}}
@if ($this->historyUser)
    @php
        $hist          = $this->historyRequests;
        $total         = $hist->count();
        $approvedCount = $hist->where('status', 'approved')->count();
        $rejectedCount = $hist->where('status', 'rejected')->count();
        $reviewCount   = $total - $approvedCount - $rejectedCount;
        $deptName      = $hist->first()?->department?->department_name;
        $rate          = $total > 0 ? round(($approvedCount / $total) * 100) : 0;
        $pct           = fn ($n) => $total > 0 ? round(($n / $total) * 100, 1) : 0;

        $groupOf = fn ($status) => $status === 'approved' ? 'approved' : ($status === 'rejected' ? 'rejected' : 'review');

        $rowsData = $hist->map(fn ($h) => [
            'id'     => $h->id,
            'type'   => $h->request_type_id == \App\Models\RequestType::FACILITY ? 'facility' : 'material',
            'group'  => $groupOf($h->status),
            'search' => strtolower($h->purpose . ' ' . $h->items->pluck('item_name')->implode(' ')),
        ])->values();

        $statusStyles = [
            'approved'  => 'bg-green-100 text-[#123524] dark:bg-[#1C6B45]/30 dark:text-green-300',
            'rejected'  => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
            'pending'   => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
            'cancelled' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        ];
    @endphp

    <div x-data="{
            show: false,
            search: '',
            type: 'all',
            status: 'all',
            open: null,
            rows: @js($rowsData),
            isVisible(id) {
                const r = this.rows.find(r => r.id === id);
                if (!r) return false;
                return (this.status === 'all' || this.status === r.group)
                    && (this.type === 'all' || this.type === r.type)
                    && (this.search.trim() === '' || r.search.includes(this.search.toLowerCase().trim()));
            },
            get visibleCount() { return this.rows.filter(r => this.isVisible(r.id)).length; },
            toggle(id) { this.open = this.open === id ? null : id; },
            clear() { this.search = ''; this.type = 'all'; this.status = 'all'; this.open = null; }
         }"
         x-init="$nextTick(() => show = true)">

        <template x-teleport="body">
            <div @keydown.escape.window="$wire.closeHistory()"
                 class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6">

                <div @click="$wire.closeHistory()"
                     x-show="show"
                     x-transition.opacity.duration.150ms
                     class="absolute inset-0 bg-black/50"></div>

                <div x-show="show"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     class="relative w-full max-w-4xl max-h-[90vh] flex flex-col bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 overflow-hidden">

                    <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-11 h-11 shrink-0 rounded-full bg-[#0B2418] text-white flex items-center justify-center text-base font-bold">
                                {{ strtoupper(substr($this->historyUser->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-green-400">Request History</p>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white truncate leading-tight">{{ $this->historyUser->name }}</h3>
                                <p class="text-xs text-gray-500 dark:text-neutral-400 truncate">
                                    {{ $this->historyUser->email }}@if ($deptName) · {{ $deptName }}@endif
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <span class="hidden sm:inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-[#1C6B45] dark:bg-[#1C6B45]/30 dark:text-green-300">
                                <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                {{ $rate }}% approved
                            </span>
                            <button type="button" @click="$wire.closeHistory()" title="Close (Esc)"
                                class="p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-neutral-700 dark:hover:text-neutral-200 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6 space-y-5 bg-gray-50/70 dark:bg-neutral-900/30">

                        <div class="flex flex-wrap gap-3">
                            <button type="button" @click="status = 'all'"
                                :class="status === 'all' ? 'bg-[#0B2418] text-white' : 'bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-neutral-200'"
                                class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition">
                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                All: {{ $total }}
                            </button>

                            <button type="button" @click="status = 'approved'"
                                :class="status === 'approved' ? 'bg-green-400 text-white' : 'bg-green-100 dark:bg-[#1C6B45]/30 text-[#123524] dark:text-green-300'"
                                class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition">
                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                Approved: {{ $approvedCount }}
                            </button>

                            <button type="button" @click="status = 'review'"
                                :class="status === 'review' ? 'bg-amber-400 text-white' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300'"
                                class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition">
                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="9" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3" />
                                </svg>
                                In Review: {{ $reviewCount }}
                            </button>

                            <button type="button" @click="status = 'rejected'"
                                :class="status === 'rejected' ? 'bg-red-400 text-white' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300'"
                                class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition">
                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Rejected: {{ $rejectedCount }}
                            </button>
                        </div>

                        @if ($total > 0)
                            <div class="flex h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-neutral-700">
                                <div class="bg-green-500" style="width: {{ $pct($approvedCount) }}%"></div>
                                <div class="bg-amber-400" style="width: {{ $pct($reviewCount) }}%"></div>
                                <div class="bg-red-500" style="width: {{ $pct($rejectedCount) }}%"></div>
                            </div>
                        @endif

                        <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-4">
                            <div class="flex flex-wrap gap-4 items-end">
                                <div class="flex-1 min-w-[200px]">
                                    <label class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1 block">Search</label>
                                    <div class="relative">
                                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <circle cx="11" cy="11" r="7" />
                                            <path stroke-linecap="round" d="M21 21l-4.35-4.35" />
                                        </svg>
                                        <input type="text" x-model="search" placeholder="Search purpose or item..."
                                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#1C6B45]">
                                    </div>
                                </div>
                                <div class="w-full sm:w-48">
                                    <label class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1 block">Type</label>
                                    <select x-model="type"
                                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#1C6B45]">
                                        <option value="all">All</option>
                                        <option value="facility">Facility Reservation</option>
                                        <option value="material">Material Request</option>
                                    </select>
                                </div>
                                <div>
                                    <button type="button" @click="clear()"
                                        class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 dark:bg-neutral-700 dark:hover:bg-neutral-600 text-gray-700 dark:text-neutral-300 rounded-lg transition">
                                        Clear filters
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                                <thead class="bg-gray-50 dark:bg-neutral-900/30">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Type</th>
                                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Purpose</th>
                                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Status</th>
                                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Date</th>
                                        <th class="w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
                                    @foreach ($hist as $h)
                                        @php
                                            $isFacility = $h->request_type_id == \App\Models\RequestType::FACILITY;
                                            $group      = $groupOf($h->status);
                                            $itemsText  = $h->items->take(2)->map(fn ($i) => $isFacility ? $i->item_name : $i->item_name . ' × ' . $i->quantity)->implode(', ');
                                            $moreItems  = $h->items->count() - 2;
                                            $badge      = $statusStyles[$h->status] ?? 'bg-gray-100 text-gray-700 dark:bg-neutral-700 dark:text-neutral-300';
                                        @endphp

                                        <tr x-show="isVisible({{ $h->id }})"
                                            @click="toggle({{ $h->id }})"
                                            class="cursor-pointer hover:bg-gray-50 dark:hover:bg-neutral-700/30 transition">

                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full
                                                    {{ $isFacility
                                                        ? 'bg-green-100 text-[#1C6B45] dark:bg-[#1C6B45]/30 dark:text-green-300'
                                                        : 'bg-[#E8F3EC] text-[#1C6B45] dark:bg-[#1C6B45]/30 dark:text-[#9FD3AE]' }}">
                                                    {{ $h->requestType?->type_name ?? '—' }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-3 max-w-xs">
                                                <p class="text-xs font-medium text-gray-800 dark:text-neutral-200 leading-tight truncate" title="{{ $h->purpose }}">{{ $h->purpose }}</p>
                                                @if ($itemsText)
                                                    <p class="text-[11px] text-gray-400 dark:text-neutral-500 leading-tight mt-0.5 truncate">
                                                        {{ $itemsText }}@if ($moreItems > 0) +{{ $moreItems }} more @endif
                                                    </p>
                                                @endif
                                            </td>

                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-medium rounded-full {{ $badge }}">
                                                    @if ($group === 'approved')
                                                        <svg class="size-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                                    @elseif ($group === 'rejected')
                                                        <svg class="size-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    @else
                                                        <svg class="size-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3" /></svg>
                                                    @endif
                                                    {{ ucwords(str_replace('_', ' ', $h->status)) }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <p class="text-xs text-gray-600 dark:text-neutral-300 leading-tight">{{ $h->created_at->format('M d, Y') }}</p>
                                                <p class="text-[11px] text-gray-400 dark:text-neutral-500 leading-tight">{{ $h->created_at->format('g:i A') }}</p>
                                            </td>

                                            <td class="pr-3 text-right">
                                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-150 inline-block"
                                                     :class="open === {{ $h->id }} ? 'rotate-180' : ''"
                                                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </td>
                                        </tr>

                                        <tr x-show="isVisible({{ $h->id }}) && open === {{ $h->id }}"
                                            class="bg-gray-50 dark:bg-neutral-900/40">
                                            <td colspan="5" class="px-4 py-3">
                                                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400 mb-2">
                                                    {{ $isFacility ? 'Reserved facilities' : 'Requested materials' }}
                                                </p>

                                                @if ($h->items->isNotEmpty())
                                                    <div class="flex flex-wrap gap-2">
                                                        @foreach ($h->items as $item)
                                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs rounded-lg bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-600 text-gray-700 dark:text-neutral-200">
                                                                <span class="font-medium">{{ $item->item_name }}</span>
                                                                @if ($isFacility)
                                                                    @if ($item->request_date)
                                                                        <span class="text-gray-400 dark:text-neutral-400">· {{ \Carbon\Carbon::parse($item->request_date)->format('M d, Y') }}</span>
                                                                    @endif
                                                                    @if ($item->start_time && $item->end_time)
                                                                        <span class="text-gray-400 dark:text-neutral-400">· {{ \Carbon\Carbon::parse($item->start_time)->format('g:i A') }}–{{ \Carbon\Carbon::parse($item->end_time)->format('g:i A') }}</span>
                                                                    @endif
                                                                @else
                                                                    <span class="text-gray-400 dark:text-neutral-400">× {{ $item->quantity }}</span>
                                                                @endif
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <p class="text-xs text-gray-400 dark:text-neutral-500">No item details.</p>
                                                @endif

                                                <p class="mt-3 text-[11px] text-gray-400 dark:text-neutral-500">
                                                    Submitted {{ $h->created_at->diffForHumans() }}
                                                </p>
                                            </td>
                                        </tr>
                                    @endforeach

                                    <tr x-show="visibleCount === 0">
                                        <td colspan="5" class="px-6 py-14 text-center">
                                            <svg class="w-10 h-10 text-gray-300 dark:text-neutral-600 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                                            </svg>
                                            <p class="text-sm font-medium text-gray-500 dark:text-neutral-400">No requests found</p>
                                            <p class="text-xs text-gray-400 dark:text-neutral-500 mt-1">
                                                {{ $total === 0 ? 'This program head has no requests yet.' : 'Try adjusting your filters.' }}
                                            </p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="px-6 py-3 border-t border-gray-200 dark:border-neutral-700 flex items-center justify-between gap-4 bg-white dark:bg-neutral-800">
                        <p class="text-xs text-gray-500 dark:text-neutral-400">
                            Showing <span class="font-semibold text-gray-700 dark:text-neutral-200" x-text="visibleCount"></span>
                            of {{ $total }} request{{ $total === 1 ? '' : 's' }}
                            <span class="hidden sm:inline text-gray-400 dark:text-neutral-500">· click a row to see its items</span>
                        </p>
                        <button type="button" @click="$wire.closeHistory()"
                            class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 dark:bg-neutral-700 dark:hover:bg-neutral-600 text-gray-700 dark:text-neutral-300 rounded-lg transition">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endif

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function () {
        let monthlyChart = null;

        function buildChart(labels, series, canvas) {
            const isDark     = document.documentElement.classList.contains('dark');
            const gridColor  = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.045)';
            const tickColor  = isDark ? 'rgba(160,160,160,0.9)'  : 'rgba(100,105,115,0.9)';
            const labelColor = isDark ? '#e5e7eb' : '#1f2937';

            // ✅ School greens, one shade per department (darkest first)
            const greens = ['#123524', '#1C6B45', '#2F8A5B', '#5FA777', '#7FBF8E', '#A9D6B5', '#3E5C4B', '#8FA99A'];
            function getDeptColor(name, index) {
                return greens[index % greens.length];
            }

            const sortedSeries = [...series]
                .map(d => ({ ...d, total: d.data.reduce((a, b) => a + b, 0) }))
                .sort((a, b) => b.total - a.total);

            let peakBar = 1;
            sortedSeries.forEach(dept => {
                dept.data.forEach(v => { if (v > peakBar) peakBar = v; });
            });
            const yMax = Math.ceil((peakBar + 2) / 2) * 2;

            const datasets = sortedSeries.map((dept, i) => {
                const color = getDeptColor(dept.name, i);
                return {
                    label: dept.name,
                    data: dept.data,
                    backgroundColor: color,
                    borderColor: color,
                    borderWidth: 0,
                    barPercentage: 0.75,
                    categoryPercentage: 0.8,
                    borderRadius: 3,
                    borderSkipped: false,
                    hoverBackgroundColor: color,
                };
            });

            const existing = Chart.getChart(canvas);
            if (existing) existing.destroy();

            const ctx = canvas.getContext('2d');

            monthlyChart = new Chart(ctx, {
                type: 'bar',
                data: { labels: labels, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: { top: 16, right: 12, bottom: 6, left: 6 } },
                    animation: { duration: 700, easing: 'easeOutQuart' },
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: isDark ? 'rgba(23,23,23,0.98)' : 'rgba(255,255,255,0.99)',
                            titleColor: labelColor,
                            bodyColor: labelColor,
                            borderColor: isDark ? 'rgba(255,255,255,0.12)' : 'rgba(0,0,0,0.08)',
                            borderWidth: 1,
                            padding: { top: 12, right: 16, bottom: 12, left: 16 },
                            cornerRadius: 10,
                            boxPadding: 8,
                            titleFont: { size: 13, weight: '700' },
                            bodyFont: { size: 12, weight: '500' },
                            bodySpacing: 6,
                            displayColors: true,
                            usePointStyle: true,
                            filter: (item) => item.parsed.y > 0,
                            itemSort: (a, b) => b.parsed.y - a.parsed.y,
                            callbacks: {
                                label: ctx => `  ${ctx.dataset.label}: ${ctx.parsed.y}`,
                                footer: (items) => {
                                    const total = items.reduce((sum, i) => sum + i.parsed.y, 0);
                                    return `Total: ${total}`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            stacked: false,
                            ticks: { color: tickColor, font: { size: 12, weight: '600' }, padding: 12, autoSkip: false },
                            grid: { display: false, drawOnChartArea: false },
                            border: { display: false },
                        },
                        y: {
                            stacked: false,
                            beginAtZero: true,
                            max: yMax,
                            ticks: {
                                color: tickColor,
                                font: { size: 11, weight: '500' },
                                padding: 14,
                                stepSize: 2,
                                callback: (v) => v === 0 ? '0' : v,
                            },
                            grid: { color: gridColor, drawBorder: false, drawTicks: false },
                            border: { display: false },
                        },
                    },
                },
            });

            renderSummary(sortedSeries, getDeptColor);
            renderLegend(monthlyChart);
        }

        function renderSummary(sortedSeries, getDeptColor) {
            const el = document.getElementById('monthlySummary');
            if (!el) return;

            el.innerHTML = '';

            const top = sortedSeries[0];
            const grand = sortedSeries.reduce((sum, d) => sum + d.total, 0);

            if (!top || grand === 0) {
                el.innerHTML = `<p class="text-xs text-gray-400 dark:text-neutral-500">No data for this year yet.</p>`;
                return;
            }

            const topColor = getDeptColor(top.name, 0);
            const chip = (label, value, color) => `
                <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gray-50 dark:bg-neutral-800 border border-gray-100 dark:border-neutral-700/70">
                    <span class="w-2 h-2 rounded-full shrink-0" style="background-color:${color}"></span>
                    <span class="text-[10px] uppercase tracking-wider text-gray-500 dark:text-neutral-400 font-medium">${label}</span>
                    <span class="text-sm font-semibold text-gray-800 dark:text-white">${value}</span>
                </div>
            `;

            el.innerHTML =
                chip('Top', top.name + ' · ' + top.total, topColor) +
                chip('Total', grand, '#9ca3af') +
                chip('Depts', sortedSeries.length, '#9ca3af');
        }

        function renderLegend(chart) {
            const legendEl = document.getElementById('monthlyLegend');
            if (!legendEl) return;

            legendEl.innerHTML = '';

            chart.data.datasets.forEach((dataset, index) => {
                const meta = chart.getDatasetMeta(index);
                const hidden = meta.hidden === true;
                const total = dataset.data.reduce((a, b) => a + b, 0);

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'flex items-center gap-2 text-xs transition-opacity hover:opacity-80';
                btn.style.opacity = hidden ? '0.35' : '1';

                btn.innerHTML = `
                    <span class="inline-block w-3.5 h-3.5 rounded-sm" style="background-color:${dataset.backgroundColor}"></span>
                    <span class="uppercase tracking-wide text-[11px] ${hidden ? 'line-through text-gray-400 dark:text-neutral-500' : 'text-gray-700 dark:text-neutral-200'} font-semibold">
                        ${dataset.label}
                    </span>
                    <span class="text-[11px] text-gray-400 dark:text-neutral-500 font-medium">(${total})</span>
                `;

                btn.addEventListener('click', () => {
                    meta.hidden = !hidden;
                    chart.update();
                    renderLegend(chart);
                });

                legendEl.appendChild(btn);
            });
        }

        function drawChart() {
            const payload = document.getElementById('chartPayload');
            if (!payload) return;

            const canvas = document.getElementById('monthlyChart');
            if (!canvas) return;

            const labels = JSON.parse(payload.dataset.labels);
            const series = JSON.parse(payload.dataset.series);

            buildChart(labels, series, canvas);
        }

        // Initial render on page load
        document.addEventListener('DOMContentLoaded', drawChart);
        document.addEventListener('livewire:navigated', drawChart);

        // ── Re-render when the PHP component dispatches chartYearChanged ──
        document.addEventListener('livewire:init', () => {
            Livewire.on('chartYearChanged', (payload) => {
                // payload is an array when dispatched from Livewire 3
                const data = Array.isArray(payload) ? payload[0] : payload;

                const canvas = document.getElementById('monthlyChart');
                if (!canvas) return;

                buildChart(data.labels, data.series, canvas);
            });
        });
    })();
</script>
</div>
