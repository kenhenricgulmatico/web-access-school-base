<div class="select-none">
<div class="space-y-8 max-w-7xl mx-auto px-6 py-8">

    {{-- Stats Cards --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">

        {{-- Total Requests --}}
        <div class="relative overflow-hidden flex flex-col bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 shadow-sm rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-medium text-gray-500 dark:text-neutral-400">Total Requests</p>
                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-mortarboard size-5 text-blue-500 dark:text-blue-400" viewBox="0 0 16 16">
                    <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855a.75.75 0 0 0-.124 1.329l4.995 3.178 1.531 2.406a.5.5 0 0 0 .844-.536L6.637 10.07l7.494-7.494-1.895 4.738a.5.5 0 1 0 .928.372zm-2.54 1.183L5.93 9.363 1.591 6.602z"/>
                    <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1.5a.5.5 0 0 1-1 0V11a.5.5 0 0 1 1 0m0 3a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                    </svg>
                </div>
            </div>
            <h3 class="text-4xl font-bold text-gray-800 dark:text-white">{{ $this->totalRequests }}</h3>
            <p class="text-xs text-gray-400 dark:text-neutral-500 mt-1">Reached coordinator & above</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-blue-500 rounded-b-2xl"></div>
        </div>

        {{-- Total Students --}}
        <div class="relative overflow-hidden flex flex-col bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 shadow-sm rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-medium text-gray-500 dark:text-neutral-400">Total Students</p>
                <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-mortarboard size-5.5 text-purple-500 dark:text-purple-400" viewBox="0 0 16 16">
                    <path d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0L14 7.14V13a1 1 0 0 0-1 1v2h3v-2a1 1 0 0 0-1-1V6.739l.686-.275a.5.5 0 0 0 .025-.917zM8 8.46 1.758 5.965 8 3.052l6.242 2.913z"/>
                    <path d="M4.176 9.032a.5.5 0 0 0-.656.327l-.5 1.7a.5.5 0 0 0 .294.605l4.5 1.8a.5.5 0 0 0 .372 0l4.5-1.8a.5.5 0 0 0 .294-.605l-.5-1.7a.5.5 0 0 0-.656-.327L8 10.466zm-.068 1.873.22-.748 3.496 1.311a.5.5 0 0 0 .352 0l3.496-1.311.22.748L8 12.46z"/>
                    </svg>
                </div>
            </div>
            <h3 class="text-4xl font-bold text-gray-800 dark:text-white">{{ $this->totalStudents }}</h3>
            <p class="text-xs text-gray-400 dark:text-neutral-500 mt-1">Registered users</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-purple-500 rounded-b-2xl"></div>
        </div>

        {{-- Total Program Heads --}}
        <div class="relative overflow-hidden flex flex-col bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 shadow-sm rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-medium text-gray-500 dark:text-neutral-400">Program Heads</p>
                <div class="w-10 h-10 rounded-full bg-teal-100 dark:bg-teal-900/40 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="size-5 text-teal-500 dark:text-teal-400" viewBox="0 0 16 16">
                        <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                    </svg>
                </div>
            </div>
            <h3 class="text-4xl font-bold text-gray-800 dark:text-white">{{ $this->totalProgramHeads }}</h3>
            <p class="text-xs text-gray-400 dark:text-neutral-500 mt-1">Registered coordinators</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-teal-500 rounded-b-2xl"></div>
        </div>

        {{-- Pending --}}
        <div class="relative overflow-hidden flex flex-col bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 shadow-sm rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-medium text-gray-500 dark:text-neutral-400">Pending Review</p>
                <div class="w-10 h-10 rounded-full bg-yellow-100 dark:bg-yellow-900/40 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-mortarboard size-5 text-yellow-500 dark:text-yellow-400" viewBox="0 0 16 16">
                    <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-.964 1.205q.183-.183.35-.378l.758.653a8 8 0 0 1-.401.432z"/>
                    <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0z"/>
                    <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5"/>
                    </svg>
                </div>
            </div>
            <h3 class="text-4xl font-bold text-gray-800 dark:text-white">{{ $this->pendingRequests }}</h3>
            <p class="text-xs text-gray-400 dark:text-neutral-500 mt-1">Coordinator & admin level</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-yellow-400 rounded-b-2xl"></div>
        </div>

    </div>

    {{-- Monthly Requests by Department --}}
    <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 shadow-sm rounded-2xl p-6">
        <div class="flex items-center justify-between mb-1">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Monthly Requests</h2>
                <p class="text-sm text-gray-400 dark:text-neutral-500">By department, for {{ now()->year }}</p>
            </div>
            <span class="text-xs bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-400 px-3 py-1 rounded-full font-medium">This Year</span>
        </div>
        <div class="mt-4">
            <canvas id="monthlyChart" height="130" wire:ignore></canvas>
        </div>
        <div id="monthlyLegend" wire:ignore class="flex flex-wrap justify-center gap-x-4 gap-y-2 mt-4"></div>
    </div>

    {{-- Request Type Chart --}}
    <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 shadow-sm rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Request Types</h2>
                <p class="text-sm text-gray-400 dark:text-neutral-500">Facility Reservations vs Material Requests</p>
            </div>
            <div class="flex gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span>
                    <span class="text-gray-600 dark:text-neutral-400">Facility — {{ $this->facilityRequests }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-purple-500 inline-block"></span>
                    <span class="text-gray-600 dark:text-neutral-400">Material — {{ $this->materialRequests }}</span>
                </div>
            </div>
        </div>
        <canvas id="typeChart" height="60" wire:ignore></canvas>
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
                                {{-- Program head: clickable, opens history --}}
                                <button type="button" wire:click="showHistory({{ $request->user_id }})"
                                    title="View request history"
                                    class="group flex items-center gap-3 text-left cursor-pointer">
                                    <div class="w-8 h-8 rounded-full bg-green-700 text-white flex items-center justify-center text-sm font-bold">
                                        {{ strtoupper(substr($request->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800 dark:text-white group-hover:text-green-700 group-hover:underline">{{ $request->user->name }}</p>
                                        <p class="text-xs text-gray-400 dark:text-neutral-500">{{ $request->user->email }}</p>
                                    </div>
                                </button>
                            @else
                                {{-- Student / Faculty: plain, not clickable --}}
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-green-700 text-white flex items-center justify-center text-sm font-bold">
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
                                @if($request->request_type_id == 1) bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400
                                @else bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-400 @endif">
                                {{ $request->requestType->type_name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                            {{ Str::limit($request->purpose, 40) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                @if($request->status === 'approved') bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-400
                                @elseif($request->status === 'rejected') bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400
                                @if($request->status === 'approved') Approved
                                @elseif($request->status === 'rejected') Rejected
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

{{-- Program Head Request History Modal (teleported to <body> so it sits above the sidebar & header) --}}
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

        // Group for the pill filters: approved / review / rejected
        $groupOf = fn ($status) => $status === 'approved' ? 'approved' : ($status === 'rejected' ? 'rejected' : 'review');

        // Data Alpine uses to filter rows instantly (no page reload)
        $rowsData = $hist->map(fn ($h) => [
            'id'     => $h->id,
            'type'   => $h->request_type_id == 1 ? 'facility' : 'material',
            'group'  => $groupOf($h->status),
            'search' => strtolower($h->purpose . ' ' . $h->items->pluck('item_name')->implode(' ')),
        ])->values();

        $statusStyles = [
            'approved'           => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
            'rejected'           => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
            'pending'            => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
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
                 class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6 select-none">

                {{-- Backdrop --}}
                <div @click="$wire.closeHistory()"
                     x-show="show"
                     x-transition.opacity.duration.150ms
                     class="absolute inset-0 bg-black/50"></div>

                {{-- Panel --}}
                <div x-show="show"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     class="relative w-full max-w-4xl max-h-[90vh] flex flex-col bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 overflow-hidden">

                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-11 h-11 shrink-0 rounded-full bg-green-700 text-white flex items-center justify-center text-base font-bold">
                                {{ strtoupper(substr($this->historyUser->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-green-700 dark:text-green-400">Request History</p>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white truncate leading-tight">{{ $this->historyUser->name }}</h3>
                                <p class="text-xs text-gray-500 dark:text-neutral-400 truncate">
                                    {{ $this->historyUser->email }}@if ($deptName) · {{ $deptName }}@endif
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <span class="hidden sm:inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300">
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

                    {{-- Body --}}
                    <div class="flex-1 overflow-y-auto p-6 space-y-5 bg-gray-50/70 dark:bg-neutral-900/30">

                        {{-- Stat pills (same style as Incoming Requests) --}}
                        <div class="flex flex-wrap gap-3">
                            <button type="button" @click="status = 'all'"
                                :class="status === 'all' ? 'bg-green-700 text-white' : 'bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-neutral-200'"
                                class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition">
                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                All: {{ $total }}
                            </button>

                            <button type="button" @click="status = 'approved'"
                                :class="status === 'approved' ? 'bg-green-400 text-white' : 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300'"
                                class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition">
                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                Approved: {{ $approvedCount }}
                            </button>

                            <button type="button" @click="status = 'review'"
                                :class="status === 'review' ? 'bg-yellow-400 text-white' : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300'"
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

                        {{-- Ratio bar --}}
                        @if ($total > 0)
                            <div class="flex h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-neutral-700">
                                <div class="bg-green-500" style="width: {{ $pct($approvedCount) }}%"></div>
                                <div class="bg-yellow-400" style="width: {{ $pct($reviewCount) }}%"></div>
                                <div class="bg-red-500" style="width: {{ $pct($rejectedCount) }}%"></div>
                            </div>
                        @endif

                        {{-- Filters --}}
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
                                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                                <div class="w-full sm:w-48">
                                    <label class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1 block">Type</label>
                                    <select x-model="type"
                                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
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

                        {{-- Table --}}
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
                                            $isFacility = $h->request_type_id == 1;
                                            $group      = $groupOf($h->status);
                                            $itemsText  = $h->items->take(2)->map(fn ($i) => $isFacility ? $i->item_name : $i->item_name . ' × ' . $i->quantity)->implode(', ');
                                            $moreItems  = $h->items->count() - 2;
                                            $badge      = $statusStyles[$h->status] ?? 'bg-gray-100 text-gray-700 dark:bg-neutral-700 dark:text-neutral-300';
                                        @endphp

                                        {{-- Main row (click to expand) --}}
                                        <tr x-show="isVisible({{ $h->id }})"
                                            @click="toggle({{ $h->id }})"
                                            class="cursor-pointer hover:bg-gray-50 dark:hover:bg-neutral-700/30 transition">

                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full
                                                    {{ $isFacility
                                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                                        : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' }}">
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

                                        {{-- Expanded details --}}
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
                                                    Request #{{ $h->id }} · Submitted {{ $h->created_at->diffForHumans() }}
                                                </p>
                                            </td>
                                        </tr>
                                    @endforeach

                                    {{-- Empty state --}}
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

                    {{-- Footer --}}
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
    // ✅ Detect dark mode so chart text/gridlines stay readable on both themes
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.05)';
    const tickColor = isDark ? 'rgba(229,229,229,0.8)' : 'rgba(75,85,99,0.8)';

    // ✅ Monthly Requests, one line per department
    const monthlyLabels = @json($this->monthlyData['labels']);
    const departmentSeries = @json($this->monthlyData['departments']);

    // ✅ Fixed color per department (matched by keyword, so exact spelling doesn't matter)
    const RED    = 'rgba(239, 68, 68, 1)';
    const GRAY   = 'rgba(107, 114, 128, 1)';
    const ORANGE = 'rgba(249, 115, 22, 1)';
    const BLUE   = 'rgba(59, 130, 246, 1)';
    const GREEN  = 'rgba(34, 197, 94, 1)';
    const YELLOW = 'rgba(234, 179, 8, 1)';
    const fallbackColors = ['rgba(168, 85, 247, 1)', 'rgba(20, 184, 166, 1)'];

    function getDeptColor(name, index) {
        const n = (name || '').toLowerCase();
        if (n.includes('crim'))     return RED;     // Criminology
        if (n.includes('comput'))   return GRAY;    // Computer Studies
        if (n.includes('engin'))    return ORANGE;  // Engineering
        if (n.includes('educ'))     return BLUE;    // Education
        if (/\barts?\b/.test(n))    return GREEN;   // Arts
        if (n.includes('business')) return YELLOW;  // Business
        return fallbackColors[index % fallbackColors.length];
    }

    // ✅ Build a soft top-to-bottom gradient fill per dataset, fading to transparent
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');

    function makeGradient(ctx, color) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, color.replace('1)', '0.45)'));
        gradient.addColorStop(1, color.replace('1)', '0)'));
        return gradient;
    }

    const monthlyChart = new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: departmentSeries.map((dept, i) => {
                const color = getDeptColor(dept.name, i);
                return {
                    label: dept.name,
                    data: dept.data,
                    borderColor: color,
                    backgroundColor: makeGradient(monthlyCtx, color),
                    borderWidth: 2.5,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHitRadius: 12,
                    pointBackgroundColor: color,
                    fill: true,
                    tension: 0.4,
                };
            })
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false }, // ✅ using our own legend below instead
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y} requests`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, color: tickColor },
                    grid: { color: gridColor, borderDash: [4, 4] }
                },
                x: {
                    ticks: { color: tickColor },
                    grid: { display: false }
                }
            }
        }
    });

    // ✅ Build the custom legend with a real X drawn over the box when hidden
    function renderMonthlyLegend() {
        const legendEl = document.getElementById('monthlyLegend');
        legendEl.innerHTML = '';

        monthlyChart.data.datasets.forEach((dataset, index) => {
            const meta = monthlyChart.getDatasetMeta(index);
            const hidden = meta.hidden === true; // Chart.js sets this on toggle

            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'flex items-center gap-1.5 text-sm select-none';
            item.style.opacity = hidden ? '0.5' : '1';

            item.innerHTML = `
                <span class="relative inline-flex items-center justify-center w-3.5 h-3.5 rounded-sm"
                      style="background-color:${dataset.borderColor}">
                    ${hidden ? `
                        <svg class="absolute inset-0 w-full h-full text-white" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <line x1="3" y1="3" x2="11" y2="11"/>
                            <line x1="11" y1="3" x2="3" y2="11"/>
                        </svg>
                    ` : ''}
                </span>
                <span class="${hidden ? 'line-through text-gray-400 dark:text-neutral-500' : 'text-gray-600 dark:text-neutral-400'}">
                    ${dataset.label}
                </span>
            `;

            item.addEventListener('click', () => {
                meta.hidden = !hidden;
                monthlyChart.update();
                renderMonthlyLegend();
            });

            legendEl.appendChild(item);
        });
    }

    renderMonthlyLegend();

    new Chart(document.getElementById('typeChart'), {
        type: 'bar',
        data: {
            labels: ['Facility Reservation', 'Material Request'],
            datasets: [{
                label: 'Total',
                data: [{{ $this->facilityRequests }}, {{ $this->materialRequests }}],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(168, 85, 247, 0.8)',
                ],
                borderRadius: 8,
                barThickness: 50,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, color: tickColor },
                    grid: { color: gridColor }
                },
                x: { ticks: { color: tickColor }, grid: { display: false } }
            }
        }
    });
</script>
</div>
