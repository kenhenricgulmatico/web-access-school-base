<div>
<div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8 space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#123524] dark:text-white">Audit Logs</h1>
            <p class="text-sm text-gray-500 dark:text-neutral-400 mt-0.5">
                What the faculty of
                <span class="font-medium text-[#123524] dark:text-green-300">{{ Auth::user()->department->department_name ?? 'your department' }}</span>
                did in the system
            </p>
        </div>
        {{-- ✅ Download what's shown --}}
        <button type="button" wire:click="exportCsv" wire:loading.attr="disabled" wire:target="exportCsv"
            class="self-start sm:self-auto py-2 px-3 inline-flex items-center gap-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-60 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-100 dark:hover:bg-neutral-700">
            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/></svg>
            <span wire:loading.remove wire:target="exportCsv">Download CSV</span>
            <span wire:loading wire:target="exportCsv">Preparing…</span>
        </button>
    </div>

    {{-- ✅ Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ([
            ['Requests this week', $this->requestsThisWeek, 'bg-[#E8F3EC] dark:bg-[#1C6B45]/25', 'text-[#1C6B45] dark:text-[#9FD3AE]', 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M8.25 8.25H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z'],
            ['Request activity today', $this->activityToday, 'bg-green-100 dark:bg-green-900/40', 'text-green-600 dark:text-green-300', 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5'],
            ['Sign-ins today', $this->signInsToday, 'bg-slate-100 dark:bg-slate-500/20', 'text-slate-600 dark:text-slate-300', 'M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75'],
            ['Active teachers this week', $this->activeTeachers . ' / ' . $this->teachers->count(), 'bg-amber-100 dark:bg-amber-900/40', 'text-amber-600 dark:text-amber-300', 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z'],
        ] as [$label, $value, $bubble, $icon, $path])
            <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl p-3 sm:p-4 shadow-sm">
                <div class="flex items-center gap-2 sm:gap-3 mb-1 sm:mb-2">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full {{ $bubble }} flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 {{ $icon }}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                    </div>
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-neutral-400">{{ $label }}</p>
                </div>
                <p class="text-xl sm:text-2xl font-semibold text-gray-800 dark:text-neutral-100">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    {{-- Log --}}
    <div class="@container bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">

        {{-- ✅ Filters --}}
        @php
            $fld     = 'py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-900 text-gray-800 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#123524] dark:focus:ring-green-700';
            $chipOn  = 'bg-[#123524] border-[#123524] text-white dark:bg-[#1C6B45] dark:border-green-700';
            $chipOff = 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700';
            $range   = $this->activeRange();
            $teacher = $teacherId !== '' ? $this->teachers->firstWhere('id', (int) $teacherId) : null;
            $filtered = $search !== '' || $teacherId !== '' || $dateFrom !== '' || $dateTo !== '' || $what !== 'requests';
        @endphp
        <div class="px-4 sm:px-5 py-4 border-b border-gray-200 dark:border-neutral-700 space-y-3">
            {{-- What --}}
            <div class="flex flex-wrap items-center gap-2" role="group" aria-label="What to show">
                @foreach ($this::WHAT as $key => $label)
                    <button type="button" wire:click="setWhat('{{ $key }}')"
                        class="px-3 py-1 text-xs font-semibold rounded-full border transition {{ $what === $key ? $chipOn : $chipOff }}">
                        {{ $label }}
                    </button>
                @endforeach
                <span class="text-[11px] text-gray-400 dark:text-neutral-500">
                    {{ $what === 'requests' ? 'Reservations and material requests' : ($what === 'signins' ? 'Sign-ins and sign-outs' : ($what === 'accounts' ? 'Account changes (often automatic, at sign-in)' : 'Everything')) }}
                </span>
            </div>

            <div class="grid gap-2 @xl:grid-cols-2 @4xl:grid-cols-[1fr_220px_150px_150px]">
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-neutral-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                    <label for="al-search" class="sr-only">Search</label>
                    <input id="al-search" type="text" wire:model.live.debounce.300ms="search" placeholder="Search name or email…" class="{{ $fld }} w-full pl-9">
                </div>
                <div>
                    <label for="al-teacher" class="sr-only">Teacher</label>
                    <select id="al-teacher" wire:model.live="teacherId" class="{{ $fld }} w-full">
                        <option value="">All teachers</option>
                        @foreach ($this->teachers as $t)
                            <option value="{{ $t->id }}" @selected((string) $t->id === (string) $teacherId)>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="al-from" class="sr-only">From</label>
                    <input id="al-from" type="date" wire:model.live="dateFrom" max="{{ today()->toDateString() }}" class="{{ $fld }} w-full" title="From">
                </div>
                <div>
                    <label for="al-to" class="sr-only">To</label>
                    <input id="al-to" type="date" wire:model.live="dateTo" max="{{ today()->toDateString() }}" class="{{ $fld }} w-full" title="To">
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @foreach (['today' => 'Today', 'week' => 'This week', 'month' => 'This month'] as $key => $label)
                    <button type="button" wire:click="setRange('{{ $key }}')"
                        class="px-3 py-1 text-xs font-medium rounded-full border transition {{ $range === $key ? $chipOn : $chipOff }}">
                        {{ $label }}
                    </button>
                @endforeach
                @if ($teacher)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                        Only {{ $teacher->name }}
                        <button type="button" wire:click="filterTeacher({{ $teacher->id }})" class="ms-0.5 hover:underline" aria-label="Show everyone">✕</button>
                    </span>
                @endif
                @if ($filtered)
                    <button type="button" wire:click="clearFilters"
                        class="ms-auto px-2.5 py-1 text-xs font-medium rounded-full text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30">
                        ✕ Clear filters
                    </button>
                @endif
                <span class="{{ $filtered ? '' : 'ms-auto' }} text-xs text-gray-500 dark:text-neutral-400">
                    {{ number_format($this->logs->total()) }} {{ \Illuminate\Support\Str::plural('entry', $this->logs->total()) }}
                </span>
            </div>
        </div>

        {{-- ✅ Table (cards on narrow screens) --}}
        <table class="w-full block @3xl:table divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="hidden @3xl:table-header-group bg-gray-50 dark:bg-neutral-900/50">
                <tr>
                    <th class="ps-5 pe-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Teacher</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">What happened</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">When</th>
                    <th class="pe-5 ps-3 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="block @3xl:table-row-group divide-y divide-gray-100 dark:divide-neutral-700">
                @forelse($this->logs as $log)
                    @php
                        $reqId   = \App\Support\AuditLogDetails::requestId($log);
                        $canOpen = $reqId && isset($this->openableRequests[$reqId]);
                        [$badge, $badgeText] = match (true) {
                            $log->action === 'login'     => ['bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300', 'Signed in'],
                            $log->action === 'logout'    => ['bg-gray-100 text-gray-600 dark:bg-neutral-700 dark:text-neutral-300', 'Signed out'],
                            $log->table_name === 'users' => ['bg-gray-100 text-gray-600 dark:bg-neutral-700 dark:text-neutral-300', 'Account'],
                            $log->action === 'created'   => ['bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300', $log->table_name === 'requests' ? 'New request' : 'Item added'],
                            $log->action === 'updated'   => ['bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300', $log->table_name === 'requests' ? 'Request changed' : 'Item changed'],
                            $log->action === 'deleted'   => ['bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300', 'Deleted'],
                            default                      => ['bg-gray-100 text-gray-600 dark:bg-neutral-700 dark:text-neutral-300', ucfirst((string) $log->action)],
                        };
                    @endphp
                    <tr wire:key="log-{{ $log->id }}" class="block @3xl:table-row px-4 py-3 @3xl:p-0 hover:bg-gray-50 dark:hover:bg-neutral-700/40 transition">

                        {{-- Teacher (click = only this teacher) --}}
                        <td class="block @3xl:table-cell @3xl:ps-5 @3xl:pe-3 py-1 @3xl:py-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-[#123524] text-white flex items-center justify-center text-sm font-bold shrink-0">
                                    {{ strtoupper(substr($log->user->name ?? '?', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    @if ($log->user)
                                        <button type="button" wire:click="filterTeacher({{ $log->user->id }})"
                                            title="Show only this teacher's activity"
                                            class="text-sm font-medium text-gray-800 dark:text-neutral-100 hover:text-[#1C6B45] hover:underline dark:hover:text-green-300 text-left">
                                            {{ $log->user->name }}
                                        </button>
                                    @else
                                        <p class="text-sm font-medium text-gray-800 dark:text-neutral-100">Deleted user</p>
                                    @endif
                                    <p class="text-xs text-gray-400 dark:text-neutral-500 truncate">{{ $log->user->email ?? '—' }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- What happened --}}
                        <td class="block @3xl:table-cell @3xl:px-3 py-1 @3xl:py-3 min-w-0">
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full whitespace-nowrap {{ $badge }}">{{ $badgeText }}</span>
                            <p class="mt-1 text-sm text-gray-700 dark:text-neutral-300 break-words @3xl:max-w-md">{{ \App\Support\AuditLogDetails::summary($log) }}</p>
                        </td>

                        {{-- When --}}
                        <td class="block @3xl:table-cell @3xl:px-3 py-1 @3xl:py-3 @3xl:whitespace-nowrap">
                            <span class="text-sm text-gray-700 dark:text-neutral-300" title="{{ $log->created_at->format('M d, Y h:i A') }}">
                                {{ $log->created_at->isToday() ? 'Today' : ($log->created_at->isYesterday() ? 'Yesterday' : $log->created_at->format('M d, Y')) }}
                            </span>
                            <span class="@3xl:block text-xs text-gray-400 dark:text-neutral-500">{{ $log->created_at->format('h:i A') }}</span>
                        </td>

                        {{-- Actions --}}
                        <td class="block @3xl:table-cell @3xl:pe-5 @3xl:ps-3 pt-2 @3xl:py-3 @3xl:text-right @3xl:whitespace-nowrap">
                            <button type="button"
                                @click="$dispatch('open-modal', {{ \Illuminate\Support\Js::from(\App\Support\AuditLogDetails::for($log)) }})"
                                class="text-sm font-semibold text-[#1C6B45] hover:underline dark:text-[#7FBF8E]">Details</button>
                            @if ($canOpen)
                                <span class="text-gray-300 dark:text-neutral-600 mx-1">·</span>
                                <a href="{{ route('coordinator.view-request-reserve', $reqId) }}"
                                   class="text-sm font-medium text-[#1C6B45] hover:underline dark:text-green-300">Open request</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr class="block @3xl:table-row">
                        <td colspan="4" class="block @3xl:table-cell px-4 sm:px-5 py-14 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-10 h-10 text-gray-300 dark:text-neutral-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M8.25 8.25H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
                                <p class="text-sm font-medium text-gray-500 dark:text-neutral-400">Nothing found</p>
                                <p class="text-xs text-gray-400 dark:text-neutral-500 text-center max-w-sm">
                                    {{ $search !== '' || $teacherId !== '' || $dateFrom !== '' || $dateTo !== ''
                                        ? 'Try other filters or dates.'
                                        : 'Activity appears here when faculty make reservations and requests.' }}
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($this->logs->hasPages())
            <div class="px-4 sm:px-5 py-4 border-t border-gray-200 dark:border-neutral-700">
                {{ $this->logs->links() }}
            </div>
        @endif
    </div>

    @include('partials.audit-details-modal')
</div>
</div>
